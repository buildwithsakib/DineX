<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['super_admin']);
$pageTitle = 'Platform Analytics';
$activePage = 'analytics';

$totalRevenue = (float)$pdo->query("SELECT SUM(total) FROM orders WHERE status != 'CANCELLED'")->fetchColumn();
$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRestaurants = (int)$pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Platform Analytics</h1>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-md p-6"><p class="text-3xl font-bold">₹<?= number_format($totalRevenue, 2) ?></p><p class="text-gray-500">Total Revenue</p></div>
    <div class="bg-white rounded-xl shadow-md p-6"><p class="text-3xl font-bold"><?= $totalOrders ?></p><p class="text-gray-500">Total Orders</p></div>
    <div class="bg-white rounded-xl shadow-md p-6"><p class="text-3xl font-bold"><?= $totalRestaurants ?></p><p class="text-gray-500">Restaurants</p></div>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>