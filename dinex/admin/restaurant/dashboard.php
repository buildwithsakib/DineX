<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$staff = require_restaurant_auth();
$restaurantId = $staff['restaurant_id'];
$pdo = db();

// Stats
$todaySales = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM bills WHERE restaurant_id = $restaurantId AND DATE(created_at) = CURDATE() AND status = 'PAID'")->fetchColumn();
$todayOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE restaurant_id = $restaurantId AND DATE(created_at) = CURDATE()")->fetchColumn();
$activeTables = (int)$pdo->query("SELECT COUNT(*) FROM table_sessions WHERE restaurant_id = $restaurantId AND status = 'ACTIVE'")->fetchColumn();
$preparingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE restaurant_id = $restaurantId AND status IN ('ACCEPTED','PREPARING')")->fetchColumn();
$readyOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE restaurant_id = $restaurantId AND status = 'READY'")->fetchColumn();

$recentOrders = $pdo->query("
    SELECT o.*, t.table_number
    FROM orders o
    JOIN tables t ON t.id = o.table_id
    WHERE o.restaurant_id = $restaurantId
    ORDER BY o.id DESC
    LIMIT 10
")->fetchAll();

$pageTitle = 'Dashboard';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-500">Welcome, <?= e($staff['name']) ?> (<?= e($staff['role']) ?>)</p>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Today's Sales</p>
            <p class="text-3xl font-bold text-green-600">₹<?= number_format($todaySales, 2) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Today's Orders</p>
            <p class="text-3xl font-bold text-gray-900"><?= $todayOrders ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Active Tables</p>
            <p class="text-3xl font-bold text-blue-600"><?= $activeTables ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Preparing / Ready</p>
            <p class="text-3xl font-bold text-amber-600"><?= $preparingOrders ?> / <?= $readyOrders ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow mt-8">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Recent Orders</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-6 py-3">Order #</th><th class="px-6 py-3">Table</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Total</th><th class="px-6 py-3">Time</th></tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td class="px-6 py-3"><?= e($order['order_number']) ?></td>
                        <td class="px-6 py-3"><?= e($order['table_number']) ?></td>
                        <td class="px-6 py-3"><span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700"><?= e($order['status']) ?></span></td>
                        <td class="px-6 py-3">₹<?= number_format($order['total_amount'], 2) ?></td>
                        <td class="px-6 py-3 text-sm"><?= e(date('H:i', strtotime($order['created_at']))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>