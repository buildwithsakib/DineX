<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-session.php';
require_once __DIR__ . '/../includes/feature-access.php';

$session = get_active_table_session();
if (!$session) {
    die('Session expired. Please scan QR again.');
}

$restaurantId = $session['restaurant_id'];
if (!restaurant_has_feature($restaurantId, FEATURE_GAMES)) {
    die('Games are not available for this restaurant.');
}

$pdo = db();
$games = $pdo->query("SELECT * FROM games WHERE restaurant_id = $restaurantId AND is_active = 1 ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
            <h1 class="text-xl font-bold">Play & Win</h1>
            <a href="menu.php?token=<?= e($_SESSION['session_token'] ?? '') ?>" class="text-orange-600">Back to Menu</a>
        </div>
    </header>
    <main class="max-w-3xl mx-auto px-4 py-6">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <?php foreach ($games as $game): ?>
                <a href="play.php?game_id=<?= (int)$game['id'] ?>&token=<?= e($_SESSION['session_token'] ?? '') ?>" class="bg-white rounded-xl shadow p-6 text-center hover:shadow-lg transition">
                    <i class="fa-solid fa-gamepad text-3xl text-orange-500"></i>
                    <h3 class="mt-2 font-semibold"><?= e($game['name']) ?></h3>
                    <p class="text-xs text-gray-500">Tap to play</p>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>