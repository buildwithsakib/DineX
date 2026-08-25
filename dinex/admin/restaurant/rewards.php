<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/feature-access.php';

$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

if (!restaurant_has_feature($restaurantId, FEATURE_GAMES)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Games feature is not available.');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'add') {
            $gameId = (int)($_POST['game_id'] ?? 0);
            $rewardType = sanitize_input($_POST['reward_type'] ?? '');
            $value = sanitize_input($_POST['value'] ?? '');
            if ($gameId <= 0) {
                $errors[] = 'Select a game.';
            }
            if (!in_array($rewardType, ['COUPON','DISCOUNT','FREE_ITEM','NONE'])) {
                $errors[] = 'Invalid reward type.';
            }
            if (!$errors) {
                $stmt = $pdo->prepare('INSERT INTO game_rewards (game_id, reward_type, value, is_active) VALUES (:gid, :rtype, :val, 1)');
                $stmt->execute([
                    ':gid' => $gameId,
                    ':rtype' => $rewardType,
                    ':val' => $value !== '' ? $value : null,
                ]);
                audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Reward added', ['game_id'=>$gameId]);
                $success = true;
            }
        } elseif ($action === 'delete') {
            $rewardId = (int)($_POST['reward_id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM game_rewards WHERE id = :id');
            $stmt->execute([':id'=>$rewardId]);
            audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Reward deleted', ['reward_id'=>$rewardId]);
            $success = true;
        }
    }
}

$games = $pdo->query("SELECT * FROM games WHERE restaurant_id = $restaurantId ORDER BY id ASC")->fetchAll();
$rewards = $pdo->query("
    SELECT gr.*, g.name AS game_name
    FROM game_rewards gr
    JOIN games g ON g.id = gr.game_id
    WHERE g.restaurant_id = $restaurantId
    ORDER BY gr.id DESC
")->fetchAll();

$pageTitle = 'Rewards';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Game Rewards</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Action completed.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold">Add Reward</h2>
            <form method="POST" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div>
                    <label class="block text-sm">Game</label>
                    <select name="game_id" required class="mt-1 w-full border rounded px-3 py-2">
                        <option value="">Select Game</option>
                        <?php foreach ($games as $game): ?>
                            <option value="<?= (int)$game['id'] ?>"><?= e($game['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm">Reward Type</label>
                    <select name="reward_type" required class="mt-1 w-full border rounded px-3 py-2">
                        <option value="COUPON">Coupon</option>
                        <option value="DISCOUNT">Discount (%)</option>
                        <option value="FREE_ITEM">Free Item</option>
                        <option value="NONE">None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm">Value (for Coupon/Discount)</label>
                    <input type="text" name="value" class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g., 10 for 10% or fixed amount">
                </div>
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded">Add</button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-6 py-3">Game</th><th class="px-6 py-3">Type</th><th class="px-6 py-3">Value</th><th class="px-6 py-3">Actions</th></tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($rewards as $reward): ?>
                    <tr>
                        <td class="px-6 py-3"><?= e($reward['game_name']) ?></td>
                        <td class="px-6 py-3"><?= e($reward['reward_type']) ?></td>
                        <td class="px-6 py-3"><?= e($reward['value'] ?? '—') ?></td>
                        <td class="px-6 py-3">
                            <form method="POST" class="inline" onsubmit="return confirm('Delete?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="reward_id" value="<?= (int)$reward['id'] ?>">
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>