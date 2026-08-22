<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['manager']);
require_permission($pdo, $user, 'kitchen.view');
require_permission($pdo, $user, 'orders.update');

$restaurantId = $user['restaurant_id'];
$pageTitle = 'Kitchen Display';
$activePage = 'kitchen';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $orderId = validate_int($_POST['order_id'] ?? 0, 1);
    $newStatus = $_POST['status'] ?? '';
    $allowed = ['ACCEPTED','PREPARING','READY','SERVED','CANCELLED'];
    if ($orderId && in_array($newStatus, $allowed, true)) {
        $check = $pdo->prepare("SELECT id FROM orders WHERE id=? AND restaurant_id=?");
        $check->execute([$orderId, $restaurantId]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$newStatus, $orderId]);
            $pdo->prepare("INSERT INTO order_status_history (order_id, status, note, created_by_user_id) VALUES (?,?,'Updated by manager kitchen',?)")->execute([$orderId, $newStatus, $user['id']]);
        }
    }
    redirect('/dinex/admin/manager/kitchen.php');
}

$orders = $pdo->prepare("SELECT o.*, t.table_number FROM orders o JOIN tables t ON t.id=o.table_id WHERE o.restaurant_id=? AND o.status IN ('PLACED','ACCEPTED','PREPARING','READY') ORDER BY o.id ASC");
$orders->execute([$restaurantId]);
$orders = $orders->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Kitchen Display</h1>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach ($orders as $o): ?>
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="font-bold font-mono"><?= e($o['order_number']) ?></h2>
            <p class="text-amber-600 font-semibold"><?= e($o['table_number']) ?></p>
            <form method="POST" class="flex gap-2 mt-4">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                <button name="status" value="ACCEPTED" class="bg-slate-900 text-white px-3 py-1 rounded">Accept</button>
                <button name="status" value="PREPARING" class="bg-blue-600 text-white px-3 py-1 rounded">Preparing</button>
                <button name="status" value="READY" class="bg-emerald-600 text-white px-3 py-1 rounded">Ready</button>
                <button name="status" value="SERVED" class="bg-gray-600 text-white px-3 py-1 rounded">Served</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>