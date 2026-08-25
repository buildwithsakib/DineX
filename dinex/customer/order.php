<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-session.php';
require_once __DIR__ . '/../includes/feature-access.php';

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) die('Invalid order.');
$session = get_active_table_session();
if (!$session) die('Session expired.');
$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id AND restaurant_id = :rid AND session_id = :sid LIMIT 1');
$stmt->execute([':id'=>$orderId, ':rid'=>$session['restaurant_id'], ':sid'=>$session['id']]);
$order = $stmt->fetch();
if (!$order) die('Order not found.');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-lg mx-auto bg-white rounded-xl shadow p-6 mt-10">
        <h1 class="text-xl font-bold">Order #<?= e($order['order_number']) ?></h1>
        <p>Status: <?= e($order['status']) ?></p>
        <p>Total: ₹<?= e(number_format($order['total_amount'], 2)) ?></p>
        <a href="menu.php?token=<?= e($_SESSION['session_token'] ?? '') ?>" class="mt-4 inline-block text-orange-600">Back to Menu</a>
    </div>
</body>
</html>