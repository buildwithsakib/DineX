<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Kitchen Display';
$activePage = 'kitchen';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $orderId = validate_int($_POST['order_id'] ?? 0, 1);
    $newStatus = $_POST['status'] ?? '';
    $allowed = ['ACCEPTED','PREPARING','READY','SERVED','COMPLETED','CANCELLED'];
    if ($orderId && in_array($newStatus, $allowed, true)) {
        // Verify ownership
        $check = $pdo->prepare("SELECT id FROM orders WHERE id=? AND restaurant_id=?");
        $check->execute([$orderId, $restaurantId]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$newStatus, $orderId]);
            $pdo->prepare("INSERT INTO order_status_history (order_id, status, note, created_by_user_id) VALUES (?,?,'Updated by kitchen',?)")->execute([$orderId, $newStatus, $user['id']]);
            if ($newStatus === 'READY' || $newStatus === 'SERVED') {
                $pdo->prepare("UPDATE tables t JOIN orders o ON o.table_id=t.id SET t.status='BILL_PENDING' WHERE o.id=?")->execute([$orderId]);
            }
            add_audit_log($pdo, $restaurantId, $user['id'], 'updated order status', 'order', $orderId, $newStatus);
        }
    }
    redirect('/dinex/admin/owner/kitchen.php');
}

$orders = $pdo->prepare("SELECT o.*, t.table_number, t.status AS table_status,
                          (SELECT GROUP_CONCAT(CONCAT(oi.quantity,' × ',oi.food_name_snapshot) SEPARATOR '<br>') FROM order_items oi WHERE oi.order_id=o.id) AS items
                          FROM orders o
                          JOIN tables t ON t.id=o.table_id
                          WHERE o.restaurant_id=? AND o.status IN ('PLACED','ACCEPTED','PREPARING','READY')
                          ORDER BY o.id ASC");
$orders->execute([$restaurantId]);
$orders = $orders->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Kitchen Display</h1>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach ($orders as $o): ?>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold font-mono"><?= e($o['order_number']) ?></h2>
                    <p class="text-lg font-semibold text-amber-600"><?= e($o['table_number']) ?></p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm <?= $o['status']==='READY' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>"><?= e($o['status']) ?></span>
            </div>
            <div class="text-sm text-gray-600 mb-4"><?= $o['items'] ? $o['items'] : 'No items' ?></div>
            <form method="POST" class="flex gap-2 flex-wrap">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                <button name="status" value="ACCEPTED" class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm">Accept</button>
                <button name="status" value="PREPARING" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Preparing</button>
                <button name="status" value="READY" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm">Ready</button>
                <button name="status" value="SERVED" class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">Served</button>
                <button name="status" value="CANCELLED" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm">Cancel</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>