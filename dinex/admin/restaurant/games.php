<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/feature-access.php';
$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

// Check feature access
if (!restaurant_has_feature($restaurantId, FEATURE_GAMES)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Games feature is not available in your subscription.');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'toggle') {
            $gameId = (int)($_POST['game_id'] ?? 0);
            $isActive = (int)($_POST['is_active'] ?? 0);
            $stmt = $pdo->prepare('UPDATE games SET is_active = :is_active WHERE id = :id AND restaurant_id = :rid');
            $stmt->execute([':is_active'=>$isActive, ':id'=>$gameId, ':rid'=>$restaurantId]);
            audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Game toggled', ['game_id'=>$gameId, 'active'=>$isActive]);
            $success = true;
        }
    }
}

$games = $pdo->query("SELECT * FROM games WHERE restaurant_id = $restaurantId ORDER BY id ASC")->fetchAll();

$pageTitle = 'Games';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Games</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Game updated.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($games as $game): ?>
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="font-semibold"><?= e($game['name']) ?></h2>
                <p class="text-sm text-gray-500 mt-1">Key: <?= e($game['game_key']) ?></p>
                <p class="mt-2">
                    <span class="px-2 py-1 text-xs rounded <?= $game['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= $game['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </p>
                <form method="POST" class="mt-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="game_id" value="<?= (int)$game['id'] ?>">
                    <input type="hidden" name="is_active" value="<?= $game['is_active'] ? 0 : 1 ?>">
                    <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">
                        <?= $game['is_active'] ? 'Deactivate' : 'Activate' ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>