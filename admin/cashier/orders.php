<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['cashier']);
require_permission($pdo, $user, 'orders.view');
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Orders';
$activePage = 'orders';

$orders = $pdo->prepare("SELECT o.*, t.table_number FROM orders o JOIN tables t ON t.id=o.table_id WHERE o.restaurant_id=? ORDER BY o.id DESC LIMIT 100");
$orders->execute([$restaurantId]);
$orders = $orders->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Orders</h1>
<table class="w-full bg-white rounded-xl shadow-md overflow-hidden">
    <thead class="bg-slate-50"><tr><th class="p-3">Order #</th><th>Table</th><th>Total</th><th>Status</th></tr></thead>
    <tbody><?php foreach ($orders as $o): ?><tr class="border-t"><td class="p-3 font-mono"><?= e($o['order_number']) ?></td><td><?= e($o['table_number']) ?></td><td>₹<?= number_format($o['total'],2) ?></td><td><?= e($o['status']) ?></td></tr><?php endforeach; ?></tbody>
</table>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>