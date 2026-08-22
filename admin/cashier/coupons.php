<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';
require_once INCLUDES_PATH . '/coupon-functions.php';

$user = require_role($pdo, ['cashier']);
require_permission($pdo, $user, 'billing.view');
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Coupon Redemption';
$activePage = 'coupons';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'redeem') {
        $code = sanitize_text($_POST['code'] ?? '', 30);
        $orderId = validate_int($_POST['order_id'] ?? 0, 1);
        if ($code && $orderId) {
            // Get order details
            $order = $pdo->prepare("SELECT * FROM orders WHERE id=? AND restaurant_id=? LIMIT 1");
            $order->execute([$orderId, $restaurantId]);
            $orderData = $order->fetch();
            if ($orderData) {
                $validation = validate_coupon($pdo, $code, $restaurantId, $orderData['table_session_id'], $orderId, $orderData['subtotal']);
                if ($validation['valid']) {
                    $pdo->beginTransaction();
                    try {
                        redeem_coupon($pdo, $validation['coupon']['id'], $orderId, $validation['discount_amount'], $user['id']);
                        add_audit_log($pdo, $restaurantId, $user['id'], 'redeemed coupon', 'coupon', $validation['coupon']['id']);
                        $pdo->commit();
                        $message = 'Coupon redeemed. Discount: ₹' . number_format($validation['discount_amount'], 2);
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $message = 'Error: ' . $e->getMessage();
                    }
                } else {
                    $message = 'Invalid coupon: ' . $validation['reason'];
                }
            } else {
                $message = 'Order not found.';
            }
        }
    }
}

// List recent orders for coupon redemption
$orders = $pdo->prepare("SELECT o.*, t.table_number FROM orders o JOIN tables t ON t.id=o.table_id
                         WHERE o.restaurant_id=? AND o.status IN ('PLACED','ACCEPTED','PREPARING','READY','SERVED')
                         ORDER BY o.id DESC LIMIT 50");
$orders->execute([$restaurantId]);
$orders = $orders->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Coupon Redemption</h1>

<?php if ($message): ?><div class="mb-4 bg-blue-50 text-blue-800 p-4 rounded-lg"><?= e($message) ?></div><?php endif; ?>

<div class="bg-white rounded-xl shadow-md p-6 mb-6">
    <form method="POST" class="flex gap-4 flex-wrap">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="redeem">
        <select name="order_id" class="border rounded-lg px-4 py-2 flex-1">
            <option value="">Select Order</option>
            <?php foreach ($orders as $o): ?>
                <option value="<?= (int)$o['id'] ?>"><?= e($o['order_number']) ?> - <?= e($o['table_number']) ?> (₹<?= number_format($o['total'],2) ?>)</option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="code" placeholder="Coupon Code" class="border rounded-lg px-4 py-2">
        <button class="bg-slate-900 text-white px-6 py-2 rounded-lg">Redeem</button>
    </form>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>