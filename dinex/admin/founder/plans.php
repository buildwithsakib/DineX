<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$founder = require_founder_access();
$pdo = db();

$plans = $pdo->query('SELECT * FROM subscription_plans ORDER BY id ASC')->fetchAll();

$pageTitle = 'Subscription Plans';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Subscription Plans</h1>
        <a href="plan-create.php" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Add Plan</a>
    </div>

    <div class="mt-6 bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3">Name</th><th class="px-6 py-3">Billing Cycle</th><th class="px-6 py-3">Price</th>
                    <th class="px-6 py-3">Duration</th><th class="px-6 py-3">Max Tables</th><th class="px-6 py-3">Max Staff</th>
                    <th class="px-6 py-3">Status</th><th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($plans as $plan): ?>
                <tr>
                    <td class="px-6 py-3 font-medium"><?= e($plan['name']) ?></td>
                    <td class="px-6 py-3"><?= e($plan['billing_cycle']) ?></td>
                    <td class="px-6 py-3">₹<?= e(number_format($plan['price'], 2)) ?></td>
                    <td class="px-6 py-3"><?= (int)$plan['duration_days'] ?> days</td>
                    <td class="px-6 py-3"><?= (int)$plan['max_tables'] ?></td>
                    <td class="px-6 py-3"><?= (int)$plan['max_staff'] ?></td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 text-xs rounded <?= $plan['status'] === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>"><?= e($plan['status']) ?></span>
                    </td>
                    <td class="px-6 py-3">
                        <a href="plan-edit.php?id=<?= (int)$plan['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>