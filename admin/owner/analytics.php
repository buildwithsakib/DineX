<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/analytics-functions.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Analytics';
$activePage = 'analytics';

// Fetch data
$todayRevenue = get_today_revenue($pdo, $restaurantId);
$todayOrders = get_today_orders($pdo, $restaurantId);
$dailyRevenue = get_daily_revenue($pdo, $restaurantId, 30);
$dailyOrders = get_daily_orders($pdo, $restaurantId, 30);
$popularFoods = get_popular_foods($pdo, $restaurantId, 10);
$categorySales = get_category_sales($pdo, $restaurantId);
$cuisineSales = get_cuisine_sales($pdo, $restaurantId);
$foodTypeSales = get_food_type_sales($pdo, $restaurantId);
$peakHours = get_peak_hours($pdo, $restaurantId);
$tableRevenue = get_table_revenue($pdo, $restaurantId);
$gameAnalytics = get_game_analytics($pdo, $restaurantId);
$couponAnalytics = get_coupon_analytics($pdo, $restaurantId);

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Restaurant Analytics</h1>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6">
        <p class="text-3xl font-bold">₹<?= number_format($todayRevenue, 2) ?></p>
        <p class="text-gray-500">Today's Revenue</p>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <p class="text-3xl font-bold"><?= $todayOrders ?></p>
        <p class="text-gray-500">Today's Orders</p>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <p class="text-3xl font-bold"><?= count($tableRevenue) ?></p>
        <p class="text-gray-500">Active Tables</p>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <p class="text-3xl font-bold"><?= $couponAnalytics['total'] ?? 0 ?></p>
        <p class="text-gray-500">Total Coupons</p>
    </div>
</div>

<!-- Daily Sales Chart -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="text-lg font-bold mb-4">Daily Sales (Last 30 Days)</h2>
    <canvas id="dailyRevenueChart"></canvas>
</div>

<!-- Daily Orders Chart -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="text-lg font-bold mb-4">Daily Orders (Last 30 Days)</h2>
    <canvas id="dailyOrdersChart"></canvas>
</div>

<!-- Peak Hours -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="text-lg font-bold mb-4">Peak Hours (Orders per Hour)</h2>
    <canvas id="peakHoursChart"></canvas>
</div>

<!-- Popular Foods -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="text-lg font-bold mb-4">Most Popular Foods</h2>
    <canvas id="popularFoodsChart"></canvas>
</div>

<!-- Category Sales -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="text-lg font-bold mb-4">Category Sales</h2>
    <canvas id="categorySalesChart"></canvas>
</div>

<!-- Cuisine Sales -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="text-lg font-bold mb-4">Cuisine Sales</h2>
    <canvas id="cuisineSalesChart"></canvas>
</div>

<!-- Food Type Sales -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="text-lg font-bold mb-4">Food Type Sales</h2>
    <canvas id="foodTypeSalesChart"></canvas>
</div>

<!-- Table Revenue -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="text-lg font-bold mb-4">Table Revenue</h2>
    <canvas id="tableRevenueChart"></canvas>
</div>

<!-- Game Analytics -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="text-lg font-bold mb-4">Game Analytics</h2>
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-2">Game</th><th>Plays</th><th>Wins</th><th>Coupons Redeemed</th><th>Discount Given</th></tr></thead>
        <tbody>
            <?php foreach ($gameAnalytics as $ga): ?>
                <tr class="border-t">
                    <td class="p-2"><?= e($ga['game_name']) ?></td>
                    <td><?= (int)$ga['total_plays'] ?></td>
                    <td><?= (int)$ga['wins'] ?></td>
                    <td><?= (int)$ga['coupons_redeemed'] ?></td>
                    <td>₹<?= number_format((float)$ga['total_discount'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Coupon Analytics -->
<div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="text-lg font-bold mb-4">Coupon Analytics</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-slate-50 p-4 rounded-lg"><p class="text-xl font-bold"><?= (int)($couponAnalytics['total'] ?? 0) ?></p><p class="text-sm text-gray-500">Total</p></div>
        <div class="bg-slate-50 p-4 rounded-lg"><p class="text-xl font-bold"><?= (int)($couponAnalytics['unused'] ?? 0) ?></p><p class="text-sm text-gray-500">Unused</p></div>
        <div class="bg-slate-50 p-4 rounded-lg"><p class="text-xl font-bold"><?= (int)($couponAnalytics['used'] ?? 0) ?></p><p class="text-sm text-gray-500">Used</p></div>
        <div class="bg-slate-50 p-4 rounded-lg"><p class="text-xl font-bold">₹<?= number_format((float)($couponAnalytics['total_discount_given'] ?? 0), 2) ?></p><p class="text-sm text-gray-500">Total Discount Given</p></div>
    </div>
</div>

<script>
// Prepare data for charts
const dailyRevenueData = <?= json_encode(array_values($dailyRevenue)) ?>;
const dailyRevenueLabels = <?= json_encode(array_keys($dailyRevenue)) ?>;
const dailyOrdersData = <?= json_encode(array_values($dailyOrders)) ?>;
const dailyOrdersLabels = <?= json_encode(array_keys($dailyOrders)) ?>;
const peakHoursData = <?= json_encode(array_values($peakHours)) ?>;
const peakHoursLabels = <?= json_encode(range(0,23)) ?>;
const popularFoodsLabels = <?= json_encode(array_column($popularFoods, 'name')) ?>;
const popularFoodsData = <?= json_encode(array_map('intval', array_column($popularFoods, 'total_quantity'))) ?>;
const categoryLabels = <?= json_encode(array_column($categorySales, 'category')) ?>;
const categoryRevenueData = <?= json_encode(array_map('floatval', array_column($categorySales, 'revenue'))) ?>;
const cuisineLabels = <?= json_encode(array_column($cuisineSales, 'cuisine')) ?>;
const cuisineRevenueData = <?= json_encode(array_map('floatval', array_column($cuisineSales, 'revenue'))) ?>;
const foodTypeLabels = <?= json_encode(array_column($foodTypeSales, 'food_type')) ?>;
const foodTypeRevenueData = <?= json_encode(array_map('floatval', array_column($foodTypeSales, 'revenue'))) ?>;
const tableLabels = <?= json_encode(array_column($tableRevenue, 'table_number')) ?>;
const tableRevenueData = <?= json_encode(array_map('floatval', array_column($tableRevenue, 'revenue'))) ?>;
</script>

<script>
new Chart(document.getElementById('dailyRevenueChart'), {
    type: 'line',
    data: { labels: dailyRevenueLabels, datasets: [{ label: 'Revenue (₹)', data: dailyRevenueData, borderColor: '#0f172a', backgroundColor: 'rgba(15,23,42,0.1)', fill: true }] },
    options: { responsive: true }
});
new Chart(document.getElementById('dailyOrdersChart'), {
    type: 'bar',
    data: { labels: dailyOrdersLabels, datasets: [{ label: 'Orders', data: dailyOrdersData, backgroundColor: '#f59e0b' }] },
    options: { responsive: true }
});
new Chart(document.getElementById('peakHoursChart'), {
    type: 'bar',
    data: { labels: peakHoursLabels, datasets: [{ label: 'Orders', data: peakHoursData, backgroundColor: '#10b981' }] },
    options: { responsive: true }
});
new Chart(document.getElementById('popularFoodsChart'), {
    type: 'bar',
    data: { labels: popularFoodsLabels, datasets: [{ label: 'Quantity Sold', data: popularFoodsData, backgroundColor: '#3b82f6' }] },
    options: { responsive: true, indexAxis: 'y' }
});
new Chart(document.getElementById('categorySalesChart'), {
    type: 'pie',
    data: { labels: categoryLabels, datasets: [{ data: categoryRevenueData, backgroundColor: ['#f59e0b','#10b981','#3b82f6','#ef4444','#8b5cf6','#ec4899'] }] },
    options: { responsive: true }
});
new Chart(document.getElementById('cuisineSalesChart'), {
    type: 'doughnut',
    data: { labels: cuisineLabels, datasets: [{ data: cuisineRevenueData, backgroundColor: ['#f59e0b','#10b981','#3b82f6','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316'] }] },
    options: { responsive: true }
});
new Chart(document.getElementById('foodTypeSalesChart'), {
    type: 'pie',
    data: { labels: foodTypeLabels, datasets: [{ data: foodTypeRevenueData, backgroundColor: ['#22c55e','#ef4444','#eab308'] }] },
    options: { responsive: true }
});
new Chart(document.getElementById('tableRevenueChart'), {
    type: 'bar',
    data: { labels: tableLabels, datasets: [{ label: 'Revenue (₹)', data: tableRevenueData, backgroundColor: '#6366f1' }] },
    options: { responsive: true }
});
</script>

<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>