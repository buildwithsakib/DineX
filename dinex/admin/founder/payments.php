<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$founder = require_founder_access();
$pdo = db();

$payments = $pdo->query('
    SELECT sp.*, r.name AS restaurant_name, p.name AS plan_name, rs.status AS sub_status
    FROM subscription_payments sp
    JOIN restaurant_subscriptions rs ON rs.id = sp.restaurant_subscription_id
    JOIN restaurants r ON r.id = rs.restaurant_id
    JOIN subscription_plans p ON p.id = rs.plan_id
    ORDER BY sp.id DESC
')->fetchAll();

$pageTitle = 'Subscription Payments';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Subscription Payments</h1>

    <div class="mt-6 bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3">Transaction ID</th><th class="px-6 py-3">Restaurant</th><th class="px-6 py-3">Plan</th>
                    <th class="px-6 py-3">Amount</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td class="px-6 py-3"><?= e($payment['transaction_id'] ?? 'N/A') ?></td>
                    <td class="px-6 py-3"><?= e($payment['restaurant_name']) ?></td>
                    <td class="px-6 py-3"><?= e($payment['plan_name']) ?></td>
                    <td class="px-6 py-3">₹<?= e(number_format($payment['amount'], 2)) ?></td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 text-xs rounded <?= $payment['status'] === 'SUCCESS' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>"><?= e($payment['status']) ?></span>
                    </td>
                    <td class="px-6 py-3"><?= $payment['paid_at'] ? e(date('d M Y H:i', strtotime($payment['paid_at']))) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>