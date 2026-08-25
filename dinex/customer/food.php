<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-session.php';
require_once __DIR__ . '/../includes/feature-access.php';

$session = get_active_table_session();
if (!$session) die('Session expired.');
$foodId = (int)($_GET['id'] ?? 0);
$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM foods WHERE id = :id AND restaurant_id = :rid AND is_available = 1');
$stmt->execute([':id'=>$foodId, ':rid'=>$session['restaurant_id']]);
$food = $stmt->fetch();
if (!$food) die('Food not found.');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($food['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-lg mx-auto bg-white rounded-xl shadow p-6 mt-10">
        <h1 class="text-2xl font-bold"><?= e($food['name']) ?></h1>
        <p class="text-gray-600 mt-2"><?= e($food['description'] ?? '') ?></p>
        <p class="text-xl font-bold mt-4">₹<?= e(number_format($food['price'], 2)) ?></p>
        <a href="menu.php?token=<?= e($_SESSION['session_token'] ?? '') ?>" class="mt-4 inline-block text-orange-600">Back to Menu</a>
    </div>
</body>
</html>