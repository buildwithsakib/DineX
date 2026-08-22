<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Orders';
$activePage = 'orders';

$status = $_GET['status'] ?? 'ALL';
$allowedStatuses = ['ALL','PLACED','ACCEPTED','PREPARING','READY','SERVED','COMPLETED','CANCELLED'];
$status = in_array($status, $allowedStatuses, true) ? $status : 'ALL';

$sql = "SELECT o.*, t.table_number FROM orders o
        JOIN tables t ON t.id=o.table_id
        WHERE o.restaurant_id=?";
$params = [$restaurantId];
if ($status !== 'ALL') {
    $sql .= " AND o.status=?";
    $params[] = $status;
}
$sql .= " ORDER BY o.id DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Orders</h1>
<div class="mb-4 flex gap-2 flex-wrap">
    <?php foreach ($allowedStatuses as $s): ?>
        <a href="?status=<?= e($s) ?>" class="px-4 py-2 rounded-full text-sm <?= $status===$s ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border' ?>"><?= e($s) ?></a>
    <?php endforeach; ?>
</div>
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">Order #</th><th>Table</th><th>Total</th><th>Status</th><th>Created</th></tr></thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
                <tr class="border-t"><td class="p-3 font-mono"><?= e($o['order_number']) ?></td><td><?= e($o['table_number']) ?></td><td>₹<?= number_format($o['total'], 2) ?></td><td><?= e($o['status']) ?></td><td><?= e($o['created_at']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>