<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$founder = require_founder_access();
$pdo = db();

// Platform aggregate stats
$totalRestaurants = (int)$pdo->query('SELECT COUNT(*) FROM restaurants')->fetchColumn();
$activeRestaurants = (int)$pdo->query("SELECT COUNT(*) FROM restaurants WHERE status = 'ACTIVE'")->fetchColumn();
$pendingRestaurants = (int)$pdo->query("SELECT COUNT(*) FROM restaurants WHERE status = 'PENDING'")->fetchColumn();
$suspendedRestaurants = (int)$pdo->query("SELECT COUNT(*) FROM restaurants WHERE status = 'SUSPENDED'")->fetchColumn();

$activeSubscriptions = (int)$pdo->query("SELECT COUNT(*) FROM restaurant_subscriptions WHERE status = 'ACTIVE' AND end_date >= CURDATE()")->fetchColumn();
$expiredSubscriptions = (int)$pdo->query("SELECT COUNT(*) FROM restaurant_subscriptions WHERE status = 'EXPIRED' OR end_date < CURDATE()")->fetchColumn();

$monthlyRevenue = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0) FROM subscription_payments
    WHERE status = 'SUCCESS' AND MONTH(paid_at) = MONTH(CURDATE()) AND YEAR(paid_at) = YEAR(CURDATE())
")->fetchColumn();
$yearlyRevenue = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0) FROM subscription_payments
    WHERE status = 'SUCCESS' AND YEAR(paid_at) = YEAR(CURDATE())
")->fetchColumn();

$recentRestaurants = $pdo->query('
    SELECT r.*, rs.status AS sub_status, p.name AS plan_name
    FROM restaurants r
    LEFT JOIN restaurant_subscriptions rs ON rs.id = (SELECT id FROM restaurant_subscriptions WHERE restaurant_id = r.id ORDER BY id DESC LIMIT 1)
    LEFT JOIN subscription_plans p ON p.id = rs.plan_id
    ORDER BY r.created_at DESC
    LIMIT 5
')->fetchAll();

$pageTitle = 'Founder Dashboard';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-500 mt-1">Welcome, <?= e($founder['name']) ?>.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Total Restaurants</p>
            <p class="text-3xl font-bold text-gray-900"><?= $totalRestaurants ?></p>
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
            <p class="text-sm text-gray-500">Active Subscriptions</p>
            <p class="text-3xl font-bold text-blue-600"><?= $activeSubscriptions ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Expired Subscriptions</p>
            <p class="text-3xl font-bold text-red-600"><?= $expiredSubscriptions ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Yearly Revenue</p>
            <p class="text-3xl font-bold text-gray-900">₹<?= number_format($yearlyRevenue, 2) ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow mt-8">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Recent Restaurant Registrations</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-6 py-3">Name</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Plan</th><th class="px-6 py-3">Created</th></tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($recentRestaurants as $r): ?>
                    <tr>
                        <td class="px-6 py-3"><a class="text-orange-600 hover:underline" href="restaurant-view.php?id=<?= (int)$r['id'] ?>"><?= e($r['name']) ?></a></td>
                        <td class="px-6 py-3">
                            <?php $statusClasses = ['ACTIVE'=>'bg-green-100 text-green-700','PENDING'=>'bg-amber-100 text-amber-700','SUSPENDED'=>'bg-red-100 text-red-700','CANCELLED'=>'bg-gray-100 text-gray-700']; ?>
                            <span class="px-2 py-1 text-xs rounded <?= $statusClasses[$r['status']] ?? 'bg-gray-100 text-gray-700' ?>"><?= e($r['status']) ?></span>
                        </td>
                        <td class="px-6 py-3"><?= e($r['plan_name'] ?? '—') ?></td>
                        <td class="px-6 py-3 text-sm"><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>