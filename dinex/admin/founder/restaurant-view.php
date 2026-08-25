<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$founder = require_founder_access();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect(BASE_URL . '/admin/founder/restaurants.php');
}

$stmt = $pdo->prepare('SELECT * FROM restaurants WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$restaurant = $stmt->fetch();
if (!$restaurant) {
    redirect(BASE_URL . '/admin/founder/restaurants.php');
}

$subscription = get_current_subscription($id);
$history = get_subscription_history($id);
$features = get_effective_features($id);
$staffCount = (int)$pdo->prepare('SELECT COUNT(*) FROM restaurant_staff WHERE restaurant_id = :rid')->execute([':rid' => $id]) ? $pdo->query('SELECT COUNT(*) FROM restaurant_staff WHERE restaurant_id = ' . $id)->fetchColumn() : 0;
$tableCount = (int)$pdo->query('SELECT COUNT(*) FROM tables WHERE restaurant_id = ' . $id)->fetchColumn();
$foodCount = (int)$pdo->query('SELECT COUNT(*) FROM foods WHERE restaurant_id = ' . $id)->fetchColumn();
$orderCount = (int)$pdo->query('SELECT COUNT(*) FROM orders WHERE restaurant_id = ' . $id)->fetchColumn();

$pageTitle = 'Restaurant View';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <a href="restaurants.php" class="text-sm text-gray-500 hover:underline"><i class="fa-solid fa-arrow-left"></i> Back to restaurants</a>
    <div class="mt-4 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?= e($restaurant['name']) ?></h1>
            <p class="text-gray-500"><?= e($restaurant['city'] ?? '') ?> <?= e($restaurant['state'] ?? '') ?></p>
        </div>
        <span class="px-3 py-1 text-xs rounded <?= $restaurant['status'] === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>"><?= e($restaurant['status']) ?></span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold">Contact</h2>
            <p class="text-sm mt-2">Owner: <?= e($restaurant['owner_name']) ?></p>
            <p class="text-sm">Email: <?= e($restaurant['email']) ?></p>
            <p class="text-sm">Phone: <?= e($restaurant['phone'] ?? '—') ?></p>
            <p class="text-sm">Address: <?= e($restaurant['address'] ?? '—') ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold">Subscription</h2>
            <?php if ($subscription): ?>
                <p class="text-sm mt-2">Plan: <?= e($subscription['plan_name']) ?></p>
                <p class="text-sm">Billing: <?= e($subscription['billing_cycle']) ?></p>
                <p class="text-sm">Status: <?= e($subscription['status']) ?></p>
                <p class="text-sm">Start: <?= e(date('d M Y', strtotime($subscription['start_date']))) ?></p>
                <p class="text-sm">End: <?= e(date('d M Y', strtotime($subscription['end_date']))) ?></p>
            <?php else: ?>
                <p class="text-sm text-gray-500 mt-2">No subscription assigned.</p>
            <?php endif; ?>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold">Operational Snapshot</h2>
            <p class="text-sm mt-2">Staff: <?= $staffCount ?></p>
            <p class="text-sm">Tables: <?= $tableCount ?></p>
            <p class="text-sm">Foods: <?= $foodCount ?></p>
            <p class="text-sm">Orders: <?= $orderCount ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow mt-8">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Effective Features</h2>
        </div>
        <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($features as $key => $enabled): ?>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full <?= $enabled ? 'bg-green-500' : 'bg-red-500' ?>"></span>
                    <span class="text-sm"><?= e($key) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow mt-8">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Subscription History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-6 py-3">Plan</th><th class="px-6 py-3">Start</th><th class="px-6 py-3">End</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Payment</th></tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($history as $sub): ?>
                    <tr>
                        <td class="px-6 py-3"><?= e($sub['plan_name']) ?></td>
                        <td class="px-6 py-3"><?= e($sub['start_date']) ?></td>
                        <td class="px-6 py-3"><?= e($sub['end_date']) ?></td>
                        <td class="px-6 py-3"><?= e($sub['status']) ?></td>
                        <td class="px-6 py-3"><?= e($sub['payment_status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>