<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

$payments = $pdo->query("
    SELECT p.*, b.bill_number
    FROM payments p
    JOIN bills b ON b.id = p.bill_id
    WHERE p.restaurant_id = $restaurantId
    ORDER BY p.id DESC LIMIT 200
")->fetchAll();

$pageTitle = 'Payments';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Payments</h1>

    <div class="mt-6 bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3">Transaction ID</th><th class="px-6 py-3">Bill #</th><th class="px-6 py-3">Amount</th>
                    <th class="px-6 py-3">Method</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td class="px-6 py-3"><?= e($payment['transaction_id'] ?? 'N/A') ?></td>
                    <td class="px-6 py-3"><?= e($payment['bill_number']) ?></td>
                    <td class="px-6 py-3">₹<?= e(number_format($payment['amount'], 2)) ?></td>
                    <td class="px-6 py-3"><?= e($payment['payment_method'] ?? '—') ?></td>
                    <td class="px-6 py-3"><?= e($payment['status']) ?></td>
                    <td class="px-6 py-3"><?= $payment['paid_at'] ? e(date('d M H:i', strtotime($payment['paid_at']))) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>