<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

$bills = $pdo->query("
    SELECT b.*, t.table_number, o.order_number
    FROM bills b
    LEFT JOIN table_sessions ts ON ts.id = b.table_session_id
    LEFT JOIN tables t ON t.id = ts.table_id
    LEFT JOIN orders o ON o.id = b.order_id
    WHERE b.restaurant_id = $restaurantId
    ORDER BY b.id DESC LIMIT 200
")->fetchAll();

$pageTitle = 'Billing';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Bills</h1>

    <div class="mt-6 bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3">Bill #</th><th class="px-6 py-3">Order #</th><th class="px-6 py-3">Table</th>
                    <th class="px-6 py-3">Subtotal</th><th class="px-6 py-3">Tax</th><th class="px-6 py-3">Discount</th>
                    <th class="px-6 py-3">Total</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($bills as $bill): ?>
                <tr>
                    <td class="px-6 py-3"><?= e($bill['bill_number']) ?></td>
                    <td class="px-6 py-3"><?= e($bill['order_number'] ?? '—') ?></td>
                    <td class="px-6 py-3"><?= e($bill['table_number'] ?? '—') ?></td>
                    <td class="px-6 py-3">₹<?= e(number_format($bill['subtotal'], 2)) ?></td>
                    <td class="px-6 py-3">₹<?= e(number_format($bill['tax_amount'], 2)) ?></td>
                    <td class="px-6 py-3">₹<?= e(number_format($bill['discount_amount'], 2)) ?></td>
                    <td class="px-6 py-3 font-bold">₹<?= e(number_format($bill['total_amount'], 2)) ?></td>
                    <td class="px-6 py-3"><span class="px-2 py-1 text-xs rounded <?= $bill['status'] === 'PAID' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>"><?= e($bill['status']) ?></span></td>
                    <td class="px-6 py-3"><?= e(date('d M H:i', strtotime($bill['created_at']))) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>