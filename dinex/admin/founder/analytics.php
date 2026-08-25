<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$founder = require_founder_access();
$pdo = db();

// Platform-wide metrics
$totalRestaurants = (int)$pdo->query('SELECT COUNT(*) FROM restaurants')->fetchColumn();
$activeRestaurants = (int)$pdo->query("SELECT COUNT(*) FROM restaurants WHERE status = 'ACTIVE'")->fetchColumn();
$pendingRestaurants = (int)$pdo->query("SELECT COUNT(*) FROM restaurants WHERE status = 'PENDING'")->fetchColumn();
$suspendedRestaurants = (int)$pdo->query("SELECT COUNT(*) FROM restaurants WHERE status = 'SUSPENDED'")->fetchColumn();

$activeSubscriptions = (int)$pdo->query("SELECT COUNT(*) FROM restaurant_subscriptions WHERE status = 'ACTIVE' AND end_date >= CURDATE()")->fetchColumn();
$expiredSubscriptions = (int)$pdo->query("SELECT COUNT(*) FROM restaurant_subscriptions WHERE status = 'EXPIRED' OR end_date < CURDATE()")->fetchColumn();
$monthlySubscriptions = (int)$pdo->query("SELECT COUNT(*) FROM restaurant_subscriptions WHERE status = 'ACTIVE' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();
$yearlySubscriptions = (int)$pdo->query("SELECT COUNT(*) FROM restaurant_subscriptions WHERE status = 'ACTIVE' AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();

$monthlyRevenue = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM subscription_payments WHERE status = 'SUCCESS' AND MONTH(paid_at) = MONTH(CURDATE()) AND YEAR(paid_at) = YEAR(CURDATE())")->fetchColumn();
$yearlyRevenue = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM subscription_payments WHERE status = 'SUCCESS' AND YEAR(paid_at) = YEAR(CURDATE())")->fetchColumn();
$totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM subscription_payments WHERE status = 'SUCCESS'")->fetchColumn();

// Revenue trend for last 6 months
$revenueTrend = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM subscription_payments WHERE status = 'SUCCESS' AND DATE_FORMAT(paid_at, '%Y-%m') = :month");
    $stmt->execute([':month' => $month]);
    $revenueTrend[] = (float)$stmt->fetchColumn();
}
$revenueLabels = [];
for ($i = 5; $i >= 0; $i--) {
    $revenueLabels[] = date('M Y', strtotime("-$i months"));
}

// Restaurant growth (last 6 months)
$growthData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM restaurants WHERE DATE_FORMAT(created_at, '%Y-%m') = :month");
    $stmt->execute([':month' => $month]);
    $growthData[] = (int)$stmt->fetchColumn();
}

$pageTitle = 'Platform Analytics';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Analytics</h1>
    <p class="text-gray-500 mt-1">Comprehensive platform performance</p>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Total Restaurants</p>
            <p class="text-3xl font-bold"><?= $totalRestaurants ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Active Restaurants</p>
            <p class="text-3xl font-bold text-green-600"><?= $activeRestaurants ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Pending Approvals</p>
            <p class="text-3xl font-bold text-amber-600"><?= $pendingRestaurants ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Suspended</p>
            <p class="text-3xl font-bold text-red-600"><?= $suspendedRestaurants ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Active Subscriptions</p>
            <p class="text-3xl font-bold text-blue-600"><?= $activeSubscriptions ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Expired Subscriptions</p>
            <p class="text-3xl font-bold text-red-600"><?= $expiredSubscriptions ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Monthly Revenue</p>
            <p class="text-3xl font-bold">₹<?= number_format($monthlyRevenue, 2) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Yearly Revenue</p>
            <p class="text-3xl font-bold">₹<?= number_format($yearlyRevenue, 2) ?></p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold mb-4">Revenue Trend (Last 6 Months)</h2>
            <canvas id="revenueChart" height="150"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold mb-4">Restaurant Registrations (Last 6 Months)</h2>
            <canvas id="growthChart" height="150"></canvas>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue chart
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($revenueLabels) ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?= json_encode($revenueTrend) ?>,
            borderColor: 'rgb(249,115,22)',
            backgroundColor: 'rgba(249,115,22,0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});

// Growth chart
new Chart(document.getElementById('growthChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($revenueLabels) ?>,
        datasets: [{
            label: 'New Restaurants',
            data: <?= json_encode($growthData) ?>,
            backgroundColor: 'rgba(59,130,246,0.7)'
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});
</script>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>