<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';

$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    redirect(BASE_URL . '/admin/restaurant/orders.php');
}

// Fetch order, ensure it belongs to this restaurant
$stmt = $pdo->prepare('
    SELECT o.*, t.table_number
    FROM orders o
    JOIN tables t ON t.id = o.table_id
    WHERE o.id = :id AND o.restaurant_id = :rid
    LIMIT 1
');
$stmt->execute([':id' => $orderId, ':rid' => $restaurantId]);
$order = $stmt->fetch();

if (!$order) {
    redirect(BASE_URL . '/admin/restaurant/orders.php');
}

// Fetch order items
$itemsStmt = $pdo->prepare('
    SELECT oi.*, f.name AS food_name
    FROM order_items oi
    JOIN foods f ON f.id = oi.food_id
    WHERE oi.order_id = :order_id
');
$itemsStmt->execute([':order_id' => $orderId]);
$items = $itemsStmt->fetchAll();

// Fetch status history
$historyStmt = $pdo->prepare('
    SELECT * FROM order_status_history
    WHERE order_id = :order_id
    ORDER BY id ASC
');
$historyStmt->execute([':order_id' => $orderId]);
$history = $historyStmt->fetchAll();

// Handle status update
$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $newStatus = sanitize_input($_POST['status'] ?? '');
        $allowedStatuses = [ORDER_STATUS_PLACED, ORDER_STATUS_ACCEPTED, ORDER_STATUS_PREPARING, ORDER_STATUS_READY, ORDER_STATUS_SERVED, ORDER_STATUS_COMPLETED, ORDER_STATUS_CANCELLED];
        if (!in_array($newStatus, $allowedStatuses)) {
            $errors[] = 'Invalid status.';
        } else {
            $update = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id AND restaurant_id = :rid');
            $update->execute([':status' => $newStatus, ':id' => $orderId, ':rid' => $restaurantId]);
            $histInsert = $pdo->prepare('INSERT INTO order_status_history (order_id, status) VALUES (:oid, :status)');
            $histInsert->execute([':oid' => $orderId, ':status' => $newStatus]);
            audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Order status updated', ['order_id' => $orderId, 'status' => $newStatus]);
            $success = true;
            // Refresh order
            $stmt->execute([':id' => $orderId, ':rid' => $restaurantId]);
            $order = $stmt->fetch();
            // Refresh history
            $historyStmt->execute([':order_id' => $orderId]);
            $history = $historyStmt->fetchAll();
        }
    }
}

$pageTitle = 'Order Details';
include __DIR__ . '/../templates/restaurant-header.php';
?>

<main class="p-6">
    <a href="orders.php" class="text-sm text-gray-500 hover:underline"><i class="fa-solid fa-arrow-left"></i> Back to Orders</a>

    <div class="mt-4 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Order #<?= e($order['order_number']) ?></h1>
        <span class="px-3 py-1 text-xs rounded bg-blue-100 text-blue-700"><?= e($order['status']) ?></span>
    </div>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Order updated.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="bg-white rounded-xl shadow p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold">Items</h2>
            <table class="w-full mt-4 text-left text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2">Food</th>
                        <th class="px-4 py-2">Qty</th>
                        <th class="px-4 py-2">Unit Price</th>
                        <th class="px-4 py-2">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="px-4 py-2"><?= e($item['food_name']) ?></td>
                        <td class="px-4 py-2"><?= (int)$item['quantity'] ?></td>
                        <td class="px-4 py-2">₹<?= e(number_format($item['unit_price'], 2)) ?></td>
                        <td class="px-4 py-2">₹<?= e(number_format($item['total_price'], 2)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-6 border-t pt-4 space-y-1 text-sm">
                <p>Subtotal: ₹<?= e(number_format($order['subtotal'], 2)) ?></p>
                <p>Tax: ₹<?= e(number_format($order['tax_amount'], 2)) ?></p>
                <p>Discount: ₹<?= e(number_format($order['discount_amount'], 2)) ?></p>
                <p class="font-bold">Total: ₹<?= e(number_format($order['total_amount'], 2)) ?></p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold">Status Update</h2>
                <form method="POST" class="mt-4">
                    <?= csrf_field() ?>
                    <select name="status" class="w-full border rounded px-3 py-2">
                        <?php foreach ([ORDER_STATUS_PLACED, ORDER_STATUS_ACCEPTED, ORDER_STATUS_PREPARING, ORDER_STATUS_READY, ORDER_STATUS_SERVED, ORDER_STATUS_COMPLETED, ORDER_STATUS_CANCELLED] as $status): ?>
                            <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="mt-3 w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Update</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold">Status History</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    <?php foreach ($history as $entry): ?>
                        <li class="flex justify-between">
                            <span><?= e($entry['status']) ?></span>
                            <span class="text-gray-500"><?= e(date('d M H:i', strtotime($entry['created_at']))) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>