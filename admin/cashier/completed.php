<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['cashier']);
require_permission($pdo, $user, 'orders.view');
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Completed Bills';
$activePage = 'completed';

$bills = $pdo->prepare("SELECT b.*, o.order_number, t.table_number FROM bills b
                        JOIN orders o ON o.id = b.order_id
                        JOIN tables t ON t.id = b.table_id
                        WHERE b.restaurant_id=? AND b.status='PAID' ORDER BY b.id DESC LIMIT 200");
$bills->execute([$restaurantId]);
$bills = $bills->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Completed Bills</h1>
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">Bill #</th><th>Order #</th><th>Table</th><th>Total</th><th>Date</th></tr></thead>
        <tbody>
            <?php foreach ($bills as $b): ?>
                <tr class="border-t"><td class="p-3 font-mono"><?= e($b['bill_number']) ?></td><td><?= e($b['order_number']) ?></td><td><?= e($b['table_number']) ?></td><td>₹<?= number_format($b['total'],2) ?></td><td><?= e($b['updated_at']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>