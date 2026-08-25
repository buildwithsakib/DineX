<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

// Overall metrics
$totalSales = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM bills WHERE restaurant_id = $restaurantId AND status = 'PAID'")->fetchColumn();
$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE restaurant_id = $restaurantId")->fetchColumn();
$totalBills = (int)$pdo->query("SELECT COUNT(*) FROM bills WHERE restaurant_id = $restaurantId")->fetchColumn();
$avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

// Daily sales for last 7 days
$dailySales = [];
$dailyLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dailyLabels[] = date('D', strtotime($date));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM bills WHERE restaurant_id = :rid AND status = 'PAID' AND DATE(created_at) = :date");
    $stmt->execute([':rid'=>$restaurantId, ':date'=>$date]);
    $dailySales[] = (float)$stmt->fetchColumn();
}

// Popular foods
$popularFoods = $pdo->query("
    SELECT f.name, SUM(oi.quantity) AS qty
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN foods f ON f.id = oi.food_id
    WHERE o.restaurant_id = $restaurantId
    GROUP BY f.id
    ORDER BY qty DESC
    LIMIT 5
")->fetchAll();

// Category performance
$categoryPerformance = $pdo->query("
    SELECT c.name, SUM(oi.quantity) AS qty, SUM(oi.total_price) AS revenue
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN foods f ON f.id = oi.food_id
    LEFT JOIN categories c ON c.id = f.category_id
    WHERE o.restaurant_id = $restaurantId
    GROUP BY c.id
    ORDER BY qty DESC
    LIMIT 5
")->fetchAll();

// Cuisine performance
$cuisinePerformance = $pdo->query("
    SELECT cu.name, SUM(oi.quantity) AS qty
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN foods f ON f.id = oi.food_id
    LEFT JOIN cuisines cu ON cu.id = f.cuisine_id
    WHERE o.restaurant_id = $restaurantId
    GROUP BY cu.id
    ORDER BY qty DESC
    LIMIT 5
")->fetchAll();

// Peak hours (last 30 days)
$peakHours = $pdo->query("
    SELECT HOUR(created_at) AS hour, COUNT(*) AS order_count
    FROM orders
    WHERE restaurant_id = $restaurantId AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY HOUR(created_at)
    ORDER BY hour ASC
")->fetchAll();

// Table analytics
$tableAnalytics = $pdo->query("
    SELECT t.table_number, COUNT(o.id) AS order_count, COALESCE(SUM(o.total_amount),0) AS total_sales
    FROM tables t
    LEFT JOIN orders o ON o.table_id = t.id AND o.restaurant_id = t.restaurant_id
    WHERE t.restaurant_id = $restaurantId
    GROUP BY t.id
    ORDER BY order_count DESC
")->fetchAll();

// Game analytics
$gameAnalytics = $pdo->query("
    SELECT g.name, COUNT(gs.id) AS plays, COUNT(c.id) AS coupons_generated
    FROM games g
    LEFT JOIN game_sessions gs ON gs.game_id = g.id
    LEFT JOIN coupons c ON c.table_session_id = gs.table_session_id AND c.restaurant_id = g.restaurant_id
    WHERE g.restaurant_id = $restaurantId
    GROUP BY g.id
    ORDER BY plays DESC
")->fetchAll();

// Coupon analytics
$couponAnalytics = $pdo->query("
    SELECT COUNT(*) AS total_coupons,
           SUM(is_used) AS used_coupons,
           SUM(CASE WHEN discount_type='PERCENT' THEN discount_value ELSE 0 END) AS total_percent_discount,
           SUM(CASE WHEN discount_type='FIXED' THEN discount_value ELSE 0 END) AS total_fixed_discount
    FROM coupons
    WHERE restaurant_id = $restaurantId
")->fetch();

$pageTitle = 'Analytics';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Analytics</h1>

    <!-- Summary cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Total Sales</p>
            <p class="text-3xl font-bold text-green-600">₹<?= number_format($totalSales, 2) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Total Orders</p>
            <p class="text-3xl font-bold"><?= $totalOrders ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Total Bills</p>
            <p class="text-3xl font-bold"><?= $totalBills ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Avg Order Value</p>
            <p class="text-3xl font-bold">₹<?= number_format($avgOrderValue, 2) ?></p>
        </div>
    </div>

    <!-- Daily sales chart -->
    <div class="bg-white rounded-xl shadow p-6 mt-8">
        <h2 class="font-semibold mb-4">Daily Sales (Last 7 Days)</h2>
        <canvas id="dailySalesChart" height="100"></canvas>
    </div>

    <!-- Popular foods and categories -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold mb-4">Popular Foods</h2>
            <canvas id="popularFoodsChart" height="150"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold mb-4">Category Performance</h2>
            <canvas id="categoryChart" height="150"></canvas>
        </div>
    </div>

    <!-- Cuisine and peak hours -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold mb-4">Cuisine Performance</h2>
            <canvas id="cuisineChart" height="150"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold mb-4">Peak Hours (Last 30 Days)</h2>
            <canvas id="peakHoursChart" height="150"></canvas>
        </div>
    </div>

    <!-- Table and game analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold mb-4">Table Performance</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-2">Table</th><th class="px-4 py-2">Orders</th><th class="px-4 py-2">Sales</th></tr></thead>
                    <tbody class="divide-y">
                        <?php foreach ($tableAnalytics as $row): ?>
                        <tr><td class="px-4 py-2"><?= e($row['table_number']) ?></td><td class="px-4 py-2"><?= (int)$row['order_count'] ?></td><td class="px-4 py-2">₹<?= number_format($row['total_sales'], 2) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold mb-4">Game Performance</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-2">Game</th><th class="px-4 py-2">Plays</th><th class="px-4 py-2">Coupons Generated</th></tr></thead>
                    <tbody class="divide-y">
                        <?php foreach ($gameAnalytics as $row): ?>
                        <tr><td class="px-4 py-2"><?= e($row['name']) ?></td><td class="px-4 py-2"><?= (int)$row['plays'] ?></td><td class="px-4 py-2"><?= (int)$row['coupons_generated'] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Coupon analytics -->
    <div class="bg-white rounded-xl shadow p-6 mt-8">
        <h2 class="font-semibold mb-4">Coupon Analytics</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div><p class="text-sm text-gray-500">Total Coupons</p><p class="text-2xl font-bold"><?= (int)$couponAnalytics['total_coupons'] ?></p></div>
            <div><p class="text-sm text-gray-500">Used Coupons</p><p class="text-2xl font-bold"><?= (int)$couponAnalytics['used_coupons'] ?></p></div>
            <div><p class="text-sm text-gray-500">Total % Discount Given</p><p class="text-2xl font-bold"><?= number_format((float)($couponAnalytics['total_percent_discount'] ?? 0), 2) ?>%<?= number_format((float)($couponAnalytics['total_fixed_discount'] ?? 0), 2) ?></p></div>
            <div><p class="text-sm text-gray-500">Total Fixed Discount Given</p><p class="text-2xl font-bold">₹<?= number_format((float)($couponAnalytics['total_fixed_discount'] ?? 0), 2) ?></p></div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily sales
new Chart(document.getElementById('dailySalesChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($dailyLabels) ?>,
        datasets: [{
            label: 'Sales (₹)',
            data: <?= json_encode($dailySales) ?>,
            borderColor: 'rgb(249,115,22)',
            tension: 0.3,
            fill: false
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});

// Popular foods
new Chart(document.getElementById('popularFoodsChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($popularFoods, 'name')) ?>,
        datasets: [{
            label: 'Quantity',
            data: <?= json_encode(array_map('intval', array_column($popularFoods, 'qty'))) ?>,
            backgroundColor: 'rgba(249,115,22,0.7)'
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});

// Categories
new Chart(document.getElementById('categoryChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($categoryPerformance, 'name')) ?>,
        datasets: [{
            label: 'Quantity',
            data: <?= json_encode(array_map('intval', array_column($categoryPerformance, 'qty'))) ?>,
            backgroundColor: 'rgba(59,130,246,0.7)'
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});

// Cuisines
new Chart(document.getElementById('cuisineChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($cuisinePerformance, 'name')) ?>,
        datasets: [{
            label: 'Quantity',
            data: <?= json_encode(array_map('intval', array_column($cuisinePerformance, 'qty'))) ?>,
            backgroundColor: 'rgba(16,185,129,0.7)'
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});

// Peak hours
const peakHourLabels = <?= json_encode(array_map(function($h){ return $h['hour'] . ':00'; }, $peakHours)) ?>;
const peakHourData = <?= json_encode(array_map('intval', array_column($peakHours, 'order_count'))) ?>;
new Chart(document.getElementById('peakHoursChart'), {
    type: 'bar',
    data: {
        labels: peakHourLabels,
        datasets: [{
            label: 'Orders',
            data: peakHourData,
            backgroundColor: 'rgba(139,92,246,0.7)'
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});
</script>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>