<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/analytics-functions.php';

$user = require_role($pdo, ['manager']);
require_permission($pdo, $user, 'analytics.view');

$restaurantId = $user['restaurant_id'];
$pageTitle = 'Analytics';
$activePage = 'analytics';

$todayRevenue = get_today_revenue($pdo, $restaurantId);
$todayOrders = get_today_orders($pdo, $restaurantId);
$dailyRevenue = get_daily_revenue($pdo, $restaurantId, 30);
$popularFoods = get_popular_foods($pdo, $restaurantId, 5);

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Analytics</h1>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6">
        <p class="text-3xl font-bold">₹<?= number_format($todayRevenue, 2) ?></p>
        <p class="text-gray-500">Today's Revenue</p>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <p class="text-3xl font-bold"><?= $todayOrders ?></p>
        <p class="text-gray-500">Today's Orders</p>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <p class="text-3xl font-bold"><?= count($popularFoods) ?></p>
        <p class="text-gray-500">Popular Items</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="text-lg font-bold mb-4">Daily Revenue (30 Days)</h2>
    <canvas id="revenueChart"></canvas>
</div>

<div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="text-lg font-bold mb-4">Top 5 Popular Foods</h2>
    <canvas id="popularChart"></canvas>
</div>

<script>
const revenueData = <?= json_encode(array_values($dailyRevenue)) ?>;
const revenueLabels = <?= json_encode(array_keys($dailyRevenue)) ?>;
const popularLabels = <?= json_encode(array_column($popularFoods, 'name')) ?>;
const popularData = <?= json_encode(array_map('intval', array_column($popularFoods, 'total_quantity'))) ?>;

new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: { labels: revenueLabels, datasets: [{ label: 'Revenue', data: revenueData, borderColor: '#0f172a' }] }
});
new Chart(document.getElementById('popularChart'), {
    type: 'bar',
    data: { labels: popularLabels, datasets: [{ label: 'Quantity', data: popularData, backgroundColor: '#f59e0b' }] },
    options: { indexAxis: 'y' }
});
</script>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>