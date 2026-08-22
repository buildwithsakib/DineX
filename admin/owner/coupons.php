<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Coupons';
$activePage = 'coupons';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'cancel') {
        $couponId = validate_int($_POST['coupon_id'] ?? 0, 1);
        $pdo->prepare("UPDATE coupons SET status='CANCELLED' WHERE id=? AND restaurant_id=? AND status='UNUSED'")
            ->execute([$couponId, $restaurantId]);
        $_SESSION['flash'] = 'Coupon cancelled.';
    }
    redirect('/dinex/admin/owner/coupons.php');
}

$coupons = $pdo->prepare("SELECT c.*, t.table_number, o.order_number FROM coupons c
                          LEFT JOIN tables t ON t.id = (SELECT table_id FROM table_sessions WHERE id = c.table_session_id)
                          LEFT JOIN orders o ON o.id = c.order_id
                          WHERE c.restaurant_id=? ORDER BY c.id DESC LIMIT 200");
$coupons->execute([$restaurantId]);
$coupons = $coupons->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Coupons</h1>
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">Code</th><th>Discount</th><th>Table</th><th>Order</th><th>Status</th><th>Expires</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($coupons as $c): ?>
                <tr class="border-t">
                    <td class="p-3 font-mono"><?= e($c['code']) ?></td>
                    <td><?= $c['discount_type']==='percentage' ? $c['discount_value'].'%' : '₹'.$c['discount_value'] ?></td>
                    <td><?= e($c['table_number'] ?? 'N/A') ?></td>
                    <td><?= e($c['order_number'] ?? 'N/A') ?></td>
                    <td><?= e($c['status']) ?></td>
                    <td><?= e($c['expires_at']) ?></td>
                    <td>
                        <?php if ($c['status'] === 'UNUSED'): ?>
                            <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="cancel"><input type="hidden" name="coupon_id" value="<?= (int)$c['id'] ?>"><button class="text-red-600 hover:underline text-sm">Cancel</button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>