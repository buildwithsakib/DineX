<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Owner Dashboard';
$activePage = 'dashboard';

$today = date('Y-m-d');
$stats = [
    'today_sales' => (float)$pdo->prepare("SELECT SUM(total) FROM orders WHERE restaurant_id=? AND DATE(created_at)=? AND status != 'CANCELLED'")->execute([$restaurantId, $today]) ?: 0,
    'today_orders' => (int)$pdo->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id=? AND DATE(created_at)=?")->execute([$restaurantId, $today]) ?: 0,
    'active_tables' => (int)$pdo->prepare("SELECT COUNT(*) FROM tables WHERE restaurant_id=? AND status IN ('OCCUPIED','PREPARING','BILL_PENDING')")->execute([$restaurantId]) ?: 0,
    'preparing' => (int)$pdo->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id=? AND status='PREPARING'")->execute([$restaurantId]) ?: 0,
];

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Owner Dashboard</h1>
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="bg-white rounded-xl shadow-md p-6"><p class="text-3xl font-bold">₹<?= number_format($stats['today_sales'], 2) ?></p><p class="text-gray-500">Today's Sales</p></div>
    <div class="bg-white rounded-xl shadow-md p-6"><p class="text-3xl font-bold"><?= $stats['today_orders'] ?></p><p class="text-gray-500">Today's Orders</p></div>
    <div class="bg-white rounded-xl shadow-md p-6"><p class="text-3xl font-bold"><?= $stats['active_tables'] ?></p><p class="text-gray-500">Active Tables</p></div>
    <div class="bg-white rounded-xl shadow-md p-6"><p class="text-3xl font-bold"><?= $stats['preparing'] ?></p><p class="text-gray-500">Preparing</p></div>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>