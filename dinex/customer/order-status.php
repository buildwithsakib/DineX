<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-session.php';
require_once __DIR__ . '/../includes/feature-access.php';

$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
    die('Invalid order.');
}
$session = get_active_table_session();
if (!$session) {
    die('Session expired.');
}
$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id AND restaurant_id = :rid AND session_id = :sid LIMIT 1');
$stmt->execute([':id'=>$orderId, ':rid'=>$session['restaurant_id'], ':sid'=>$session['id']]);
$order = $stmt->fetch();
if (!$order) {
    die('Order not found.');
}
$statusHistory = $pdo->query("SELECT * FROM order_status_history WHERE order_id = $orderId ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-50 min-h-screen">
   <header class="bg-white shadow-sm">
    <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
        <h1 class="text-xl font-bold">Order #<?= e($order['order_number']) ?></h1>
        <div class="flex gap-2">
            <a href="games.php" class="bg-orange-600 text-white px-4 py-2 rounded-lg text-sm">Play Games</a>
            <a href="billing.php" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm">View Bill / Pay</a>
            <a href="menu.php?token=<?= e($_SESSION['session_token'] ?? '') ?>" class="text-orange-600 border border-orange-600 px-4 py-2 rounded-lg text-sm">Back to Menu</a>
        </div>
    </div>
</header>
    <main class="max-w-3xl mx-auto px-4 py-6">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold">Current Status</span>
                <span class="px-3 py-1 rounded bg-blue-100 text-blue-700"><?= e($order['status']) ?></span>
            </div>
            <p class="mt-2 text-sm text-gray-500">Placed at <?= e(date('d M H:i', strtotime($order['created_at']))) ?></p>
            <div class="mt-4">
                <h2 class="font-semibold">Items</h2>
                <?php
                $items = $pdo->prepare('SELECT oi.quantity, f.name, oi.total_price FROM order_items oi JOIN foods f ON f.id = oi.food_id WHERE oi.order_id = :oid');
                $items->execute([':oid'=>$orderId]);
                ?>
                <ul class="mt-2 space-y-1 text-sm">
                    <?php foreach ($items->fetchAll() as $item): ?>
                        <li><?= e($item['quantity']) ?>x <?= e($item['name']) ?> — ₹<?= e(number_format($item['total_price'], 2)) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="mt-4">
                <p class="text-sm">Subtotal: ₹<?= e(number_format($order['subtotal'], 2)) ?></p>
                <p class="text-sm">Tax: ₹<?= e(number_format($order['tax_amount'], 2)) ?></p>
                <p class="text-sm font-bold">Total: ₹<?= e(number_format($order['total_amount'], 2)) ?></p>
            </div>
            <div class="mt-6">
                <h2 class="font-semibold">Status Updates</h2>
                <ul class="mt-2 space-y-1 text-sm">
                    <?php foreach ($statusHistory as $hist): ?>
                        <li><?= e(date('H:i', strtotime($hist['created_at']))) ?> — <?= e($hist['status']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </main>
</body>
</html>