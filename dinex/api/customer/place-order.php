<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/customer-session.php';
require_once __DIR__ . '/../../includes/feature-access.php';

header('Content-Type: application/json');

$session = get_active_table_session();
if (!$session) {
    json_response(['success' => false, 'message' => 'Session expired'], 403);
}

$restaurantId = (int)$session['restaurant_id'];
$tableId = (int)$session['table_id'];
$sessionId = (int)$session['id'];

// Check feature
if (!restaurant_has_feature($restaurantId, FEATURE_QR_ORDERING)) {
    json_response(['success' => false, 'message' => 'QR ordering not available'], 403);
}

$input = json_decode(file_get_contents('php://input'), true);
$cart = $input['cart'] ?? [];

if (empty($cart)) {
    json_response(['success' => false, 'message' => 'Cart is empty'], 400);
}

$pdo = db();
$subtotal = 0;
$orderItems = [];

try {
    $pdo->beginTransaction();

    // Validate each item and compute totals
    foreach ($cart as $item) {
        $foodId = (int)($item['id'] ?? 0);
        $qty = (int)($item['qty'] ?? 0);
        if ($foodId <= 0 || $qty <= 0) {
            throw new Exception('Invalid cart item');
        }
        $stmt = $pdo->prepare('SELECT * FROM foods WHERE id = :id AND restaurant_id = :rid AND is_available = 1 LIMIT 1');
        $stmt->execute([':id'=>$foodId, ':rid'=>$restaurantId]);
        $food = $stmt->fetch();
        if (!$food) {
            throw new Exception('Food not available');
        }
        $unitPrice = (float)$food['price'];
        $lineTotal = $unitPrice * $qty;
        $subtotal += $lineTotal;
        $orderItems[] = [
            'food_id' => $foodId,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'total_price' => $lineTotal,
        ];
    }

    $taxRate = (float)get_setting('default_tax_rate', '5.00');
    $taxAmount = $subtotal * ($taxRate / 100);
    $totalAmount = $subtotal + $taxAmount;

    $orderNumber = 'DX' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    $stmt = $pdo->prepare('
        INSERT INTO orders (restaurant_id, table_id, session_id, order_number, status, subtotal, tax_amount, total_amount)
        VALUES (:rid, :tid, :sid, :order_number, :status, :subtotal, :tax_amount, :total_amount)
    ');
    $stmt->execute([
        ':rid' => $restaurantId,
        ':tid' => $tableId,
        ':sid' => $sessionId,
        ':order_number' => $orderNumber,
        ':status' => ORDER_STATUS_PLACED,
        ':subtotal' => $subtotal,
        ':tax_amount' => $taxAmount,
        ':total_amount' => $totalAmount,
    ]);
    $orderId = (int)$pdo->lastInsertId();

    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, food_id, quantity, unit_price, total_price) VALUES (:oid, :fid, :qty, :up, :tp)');
    foreach ($orderItems as $oi) {
        $itemStmt->execute([
            ':oid' => $orderId,
            ':fid' => $oi['food_id'],
            ':qty' => $oi['quantity'],
            ':up' => $oi['unit_price'],
            ':tp' => $oi['total_price'],
        ]);
    }

    $historyStmt = $pdo->prepare('INSERT INTO order_status_history (order_id, status) VALUES (:oid, :status)');
    $historyStmt->execute([':oid'=>$orderId, ':status'=>ORDER_STATUS_PLACED]);

    // Create bill automatically
    $billNumber = 'BILL' . strtoupper(bin2hex(random_bytes(4)));
    $billStmt = $pdo->prepare('
        INSERT INTO bills (restaurant_id, order_id, table_session_id, bill_number, subtotal, tax_amount, discount_amount, total_amount, status)
        VALUES (:rid, :oid, :tsid, :billno, :subtotal, :tax, :discount, :total, "PENDING")
    ');
    $billStmt->execute([
        ':rid' => $restaurantId,
        ':oid' => $orderId,
        ':tsid' => $sessionId,
        ':billno' => $billNumber,
        ':subtotal' => $subtotal,
        ':tax' => $taxAmount,
        ':discount' => 0,
        ':total' => $totalAmount,
    ]);

    $pdo->commit();

    audit_log('CUSTOMER', null, $restaurantId, 'Order placed and bill created', ['order_id'=>$orderId, 'bill_number'=>$billNumber]);

    json_response(['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber]);
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['success' => false, 'message' => $e->getMessage()], 500);
}