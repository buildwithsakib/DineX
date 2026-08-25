<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-session.php';
require_once __DIR__ . '/../includes/feature-access.php';

$session = get_active_table_session();
if (!$session) {
    die('Session expired.');
}

$pdo = db();
$coupons = $pdo->query("SELECT * FROM coupons WHERE restaurant_id = {$session['restaurant_id']} AND table_session_id = {$session['id']} AND is_used = 0 AND valid_until > NOW() ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Coupons</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm">
        <div class="max-w-3xl mx-auto px-4 py-3">
            <h1 class="text-xl font-bold">My Coupons</h1>
        </div>
    </header>
    <main class="max-w-3xl mx-auto px-4 py-6">
        <?php if ($coupons): ?>
            <div class="space-y-4">
                <?php foreach ($coupons as $coupon): ?>
                    <div class="bg-white rounded-xl shadow p-4">
                        <p class="font-mono text-lg"><?= e($coupon['code']) ?></p>
                        <p class="text-sm text-gray-500"><?= e($coupon['discount_type']) ?>: <?= e($coupon['discount_value']) ?> off</p>
                        <p class="text-xs text-gray-400">Valid until <?= e(date('d M Y', strtotime($coupon['valid_until']))) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No coupons yet.</p>
        <?php endif; ?>
    </main>
</body>
</html>