<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';
require_once INCLUDES_PATH . '/billing-functions.php';
require_once INCLUDES_PATH . '/coupon-functions.php';

$user = require_role($pdo, ['cashier']);
require_permission($pdo, $user, 'billing.view');
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Billing';
$activePage = 'billing';

$message = '';
$order = null;
$items = [];
$totals = null;

// Lookup order
if (isset($_GET['order_id'])) {
    $orderId = validate_int($_GET['order_id'], 1);
    if ($orderId) {
        $orderStmt = $pdo->prepare("SELECT o.*, t.table_number FROM orders o JOIN tables t ON t.id=o.table_id WHERE o.id=? AND o.restaurant_id=? LIMIT 1");
        $orderStmt->execute([$orderId, $restaurantId]);
        $order = $orderStmt->fetch();
        if ($order) {
            $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id=? ORDER BY id");
            $itemsStmt->execute([$orderId]);
            $items = $itemsStmt->fetchAll();

            // Check for existing bill
            $billStmt = $pdo->prepare("SELECT * FROM bills WHERE order_id=? AND restaurant_id=? LIMIT 1");
            $billStmt->execute([$orderId, $restaurantId]);
            $existingBill = $billStmt->fetch();
            if ($existingBill) {
                $message = 'Bill already generated: ' . $existingBill['bill_number'];
                $totals = [
                    'subtotal' => $existingBill['subtotal'],
                    'tax' => $existingBill['tax'],
                    'discount' => $existingBill['discount'],
                    'coupon_discount' => $existingBill['coupon_discount'],
                    'total' => $existingBill['total'],
                ];
            }
        }
    }
}

// Process coupon application or bill generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order) {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'apply_coupon') {
        $code = sanitize_text($_POST['coupon_code'] ?? '', 30);
        if ($code) {
            $result = calculate_order_totals_with_coupon($pdo, $order['id'], $restaurantId, $code);
            if ($result['valid']) {
                $totals = $result;
                $message = 'Coupon applied.';
            } else {
                $message = 'Invalid coupon: ' . $result['reason'];
            }
        }
    } elseif ($action === 'generate_bill') {
        // Recalculate totals with optional coupon from hidden field
        $couponCode = $_POST['coupon_code'] ?? '';
        $result = calculate_order_totals_with_coupon($pdo, $order['id'], $restaurantId, $couponCode);
        if ($result['valid']) {
            $pdo->beginTransaction();
            try {
                $billId = create_bill(
                    $pdo,
                    $restaurantId,
                    $order['id'],
                    $order['table_id'],
                    $result['subtotal'],
                    $result['tax'],
                    $result['discount'],
                    $result['coupon_discount'],
                    $result['total']
                );
                // If coupon was applied, redeem it
                if (!empty($result['coupon'])) {
                    redeem_coupon($pdo, $result['coupon']['id'], $order['id'], $result['coupon_discount'], $user['id']);
                }
                // Update order status to COMPLETED? Or keep as served for payment.
                // We'll mark as COMPLETED when payment recorded.
                add_audit_log($pdo, $restaurantId, $user['id'], 'generated bill', 'bill', $billId);
                $pdo->commit();
                $message = 'Bill generated successfully.';
                // Reload bill
                header('Location: /dinex/admin/cashier/billing.php?order_id=' . $order['id']);
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'Error generating bill: ' . $e->getMessage();
            }
        } else {
            $message = 'Cannot generate bill: ' . $result['reason'];
        }
    }
}

// List orders ready for billing if no specific order selected
$pendingOrders = $pdo->prepare("SELECT o.*, t.table_number FROM orders o JOIN tables t ON t.id=o.table_id
                                WHERE o.restaurant_id=? AND o.status IN ('READY','SERVED') AND NOT EXISTS (SELECT 1 FROM bills b WHERE b.order_id=o.id)
                                ORDER BY o.id ASC LIMIT 50");
$pendingOrders->execute([$restaurantId]);
$pendingOrders = $pendingOrders->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Billing</h1>

<?php if ($message): ?>
    <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4"><?= e($message) ?></div>
<?php endif; ?>

<?php if (!$order): ?>
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="font-bold text-lg mb-4">Orders Ready for Billing</h2>
        <table class="w-full text-left">
            <thead><tr><th class="p-2">Order #</th><th>Table</th><th>Total</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($pendingOrders as $po): ?>
                    <tr class="border-t">
                        <td class="p-2 font-mono"><?= e($po['order_number']) ?></td>
                        <td><?= e($po['table_number']) ?></td>
                        <td>₹<?= number_format($po['total'],2) ?></td>
                        <td><a href="?order_id=<?= (int)$po['id'] ?>" class="text-blue-600 hover:underline">Bill</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl shadow-md p-6 max-w-3xl mx-auto">
        <h2 class="text-xl font-bold mb-2">Order #<?= e($order['order_number']) ?></h2>
        <p class="text-gray-500 mb-4"><?= e($order['table_number']) ?></p>

        <!-- Items -->
        <div class="border-t border-b py-4 my-4">
            <?php foreach ($items as $item): ?>
                <div class="flex justify-between py-1 text-sm">
                    <span><?= e($item['food_name_snapshot']) ?> × <?= (int)$item['quantity'] ?></span>
                    <span>₹<?= number_format($item['subtotal'],2) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Totals -->
        <div class="space-y-2">
            <div class="flex justify-between text-sm"><span>Subtotal</span><span>₹<?= number_format($order['subtotal'],2) ?></span></div>
            <div class="flex justify-between text-sm"><span>Tax</span><span>₹<?= number_format($order['tax'],2) ?></span></div>
            <div class="flex justify-between text-sm"><span>Discount</span><span>₹<?= number_format($order['discount'],2) ?></span></div>
            <div class="flex justify-between text-sm"><span>Coupon Discount</span><span>₹<?= number_format($totals['coupon_discount'] ?? 0,2) ?></span></div>
            <div class="flex justify-between font-bold text-lg"><span>Total</span><span>₹<?= number_format($totals['total'] ?? $order['total'],2) ?></span></div>
        </div>

        <!-- Coupon Form -->
        <form method="POST" class="flex gap-2 mt-6">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="apply_coupon">
            <input type="text" name="coupon_code" placeholder="Enter coupon code" class="border rounded-lg px-4 py-2 flex-1">
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg">Apply Coupon</button>
        </form>

        <!-- Generate Bill -->
        <form method="POST" class="mt-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="generate_bill">
            <input type="hidden" name="coupon_code" value="<?= e($totals['coupon']['code'] ?? '') ?>">
            <button class="w-full bg-emerald-600 text-white py-2 rounded-lg font-semibold">Generate Bill</button>
        </form>
    </div>
<?php endif; ?>

<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>
