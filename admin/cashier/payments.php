<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['cashier']);
require_permission($pdo, $user, 'billing.view');
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Payments';
$activePage = 'payments';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'record_payment') {
        $billId = validate_int($_POST['bill_id'] ?? 0, 1);
        $method = sanitize_text($_POST['method'] ?? '', 50);
        $transactionRef = sanitize_text($_POST['transaction_ref'] ?? '', 150);
        $amount = validate_price($_POST['amount'] ?? 0);
        $status = $_POST['status'] ?? 'PAID';

        if ($billId && $method && $amount !== null) {
            // Verify bill belongs to restaurant
            $bill = $pdo->prepare("SELECT * FROM bills WHERE id=? AND restaurant_id=? LIMIT 1");
            $bill->execute([$billId, $restaurantId]);
            $billData = $bill->fetch();

            if ($billData) {
                $pdo->beginTransaction();
                try {
                    $pdo->prepare("INSERT INTO payments (restaurant_id, bill_id, order_id, amount, method, transaction_ref, status)
                                   VALUES (?,?,?,?,?,?,?)")
                        ->execute([$restaurantId, $billId, $billData['order_id'], $amount, $method, $transactionRef, $status]);

                    // Update bill status
                    $pdo->prepare("UPDATE bills SET status=? WHERE id=?")->execute([$status, $billId]);

                    // Update order status to COMPLETED if paid
                    if ($status === 'PAID') {
                        $pdo->prepare("UPDATE orders SET status='COMPLETED' WHERE id=?")->execute([$billData['order_id']]);
                        // Close table session if all orders completed
                        $pdo->prepare("UPDATE table_sessions SET status='CLOSED', closed_at=NOW() WHERE id=? AND status='ACTIVE'")
                            ->execute([$billData['order_id']]);
                    }

                    add_audit_log($pdo, $restaurantId, $user['id'], 'recorded payment', 'bill', $billId, $status);
                    $pdo->commit();
                    $message = 'Payment recorded.';
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $message = 'Error: ' . $e->getMessage();
                }
            } else {
                $message = 'Bill not found.';
            }
        }
    }
}

// List bills with payment status
$bills = $pdo->prepare("SELECT b.*, o.order_number, t.table_number,
                        (SELECT p.status FROM payments p WHERE p.bill_id = b.id ORDER BY p.id DESC LIMIT 1) AS payment_status
                        FROM bills b
                        JOIN orders o ON o.id = b.order_id
                        JOIN tables t ON t.id = b.table_id
                        WHERE b.restaurant_id=? ORDER BY b.id DESC LIMIT 100");
$bills->execute([$restaurantId]);
$bills = $bills->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Payments</h1>

<?php if ($message): ?><div class="mb-4 bg-blue-50 text-blue-800 p-4 rounded-lg"><?= e($message) ?></div><?php endif; ?>

<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">Bill #</th><th>Order #</th><th>Table</th><th>Total</th><th>Payment Status</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($bills as $b): ?>
                <tr class="border-t">
                    <td class="p-3 font-mono"><?= e($b['bill_number']) ?></td>
                    <td><?= e($b['order_number']) ?></td>
                    <td><?= e($b['table_number']) ?></td>
                    <td>₹<?= number_format($b['total'],2) ?></td>
                    <td><?= e($b['payment_status'] ?? 'PENDING') ?></td>
                    <td>
                        <button onclick="openPaymentModal(<?= (int)$b['id'] ?>, '<?= e($b['bill_number']) ?>', <?= (float)$b['total'] ?>)" class="text-blue-600 hover:underline">Record Payment</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Payment Modal (simple JS prompt alternative) -->
<div id="payment-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Record Payment</h2>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="record_payment">
            <input type="hidden" name="bill_id" id="payment-bill-id">
            <div class="mb-3">
                <label class="block text-sm font-semibold">Method</label>
                <select name="method" class="w-full border rounded-lg px-3 py-2">
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="UPI">UPI</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-semibold">Amount</label>
                <input type="number" step="0.01" name="amount" id="payment-amount" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-semibold">Transaction Ref (optional)</label>
                <input type="text" name="transaction_ref" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-semibold">Status</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2">
                    <option value="PAID">Paid</option>
                    <option value="FAILED">Failed</option>
                    <option value="PENDING">Pending</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('payment-modal').classList.add('hidden')" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button class="bg-emerald-600 text-white px-4 py-2 rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal(billId, billNumber, amount) {
    document.getElementById('payment-bill-id').value = billId;
    document.getElementById('payment-amount').value = amount.toFixed(2);
    document.getElementById('payment-modal').classList.remove('hidden');
}
</script>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>