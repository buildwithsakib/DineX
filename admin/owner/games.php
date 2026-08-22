<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';
require_once INCLUDES_PATH . '/validation.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Game Management';
$activePage = 'games';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = sanitize_text($_POST['name'] ?? '', 100);
        $slug = slugify($name);
        $type = $_POST['type'] ?? '';
        $minOrder = validate_price($_POST['min_order_value'] ?? 0) ?? 0;
        $dailyLimit = validate_int($_POST['daily_limit'] ?? 0, 0, 10000) ?? 0;
        $onePlay = (int)($_POST['one_play_per_order'] ?? 0);
        $probability = validate_price($_POST['reward_probability'] ?? 50) ?? 50;
        $expiryDays = validate_int($_POST['coupon_expiry_days'] ?? 1, 1, 365) ?? 1;
        $status = $_POST['status'] ?? 'INACTIVE';

        $allowedTypes = ['spin-wheel','lottery','slot-machine','catch-win','snakes-ladders','tap-speed'];
        if ($name && in_array($type, $allowedTypes, true)) {
            $pdo->prepare("INSERT INTO games (restaurant_id, name, slug, type, status, min_order_value, daily_limit, one_play_per_order, reward_probability, coupon_expiry_days)
                            VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$restaurantId, $name, $slug, $type, $status, $minOrder, $dailyLimit, $onePlay, $probability, $expiryDays]);
            $gameId = (int)$pdo->lastInsertId();
            add_audit_log($pdo, $restaurantId, $user['id'], 'created game', 'game', $gameId);
            $_SESSION['flash'] = 'Game created.';
            redirect('/dinex/admin/owner/games.php');
        }
    } elseif ($action === 'update_status') {
        $gameId = validate_int($_POST['game_id'] ?? 0, 1);
        $newStatus = $_POST['status'] ?? '';
        $allowed = ['ACTIVE','INACTIVE','SCHEDULED'];
        if ($gameId && in_array($newStatus, $allowed, true)) {
            $check = $pdo->prepare("SELECT id FROM games WHERE id=? AND restaurant_id=?");
            $check->execute([$gameId, $restaurantId]);
            if ($check->fetch()) {
                $pdo->prepare("UPDATE games SET status=? WHERE id=?")->execute([$newStatus, $gameId]);
                add_audit_log($pdo, $restaurantId, $user['id'], 'updated game status', 'game', $gameId, $newStatus);
                $_SESSION['flash'] = 'Game status updated.';
            }
        }
        redirect('/dinex/admin/owner/games.php');
    }
}

$games = $pdo->prepare("SELECT * FROM games WHERE restaurant_id=? ORDER BY id DESC");
$games->execute([$restaurantId]);
$games = $games->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Game Management</h1>

<!-- Create Game Form -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="font-bold text-lg mb-4">Create New Game</h2>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <input type="text" name="name" required placeholder="Game Name" class="border rounded-lg px-4 py-2">
        <select name="type" required class="border rounded-lg px-4 py-2">
            <option value="spin-wheel">Spin Wheel</option>
            <option value="lottery">Instant Lottery</option>
            <option value="slot-machine">Slot Machine</option>
            <option value="catch-win">Catch & Win</option>
            <option value="snakes-ladders">Snakes & Ladders</option>
            <option value="tap-speed">Tap Speed</option>
        </select>
        <select name="status" class="border rounded-lg px-4 py-2">
            <option value="INACTIVE">Inactive</option>
            <option value="ACTIVE">Active</option>
            <option value="SCHEDULED">Scheduled</option>
        </select>
        <input type="number" step="0.01" name="min_order_value" value="100" placeholder="Min Order Value" class="border rounded-lg px-4 py-2">
        <input type="number" name="daily_limit" value="0" placeholder="Daily Limit (0=unlimited)" class="border rounded-lg px-4 py-2">
        <select name="one_play_per_order" class="border rounded-lg px-4 py-2">
            <option value="0">Multiple Plays Per Order</option>
            <option value="1">One Play Per Order</option>
        </select>
        <input type="number" step="0.01" name="reward_probability" value="50" placeholder="Reward Probability %" class="border rounded-lg px-4 py-2">
        <input type="number" name="coupon_expiry_days" value="1" min="1" placeholder="Coupon Expiry Days" class="border rounded-lg px-4 py-2">
        <button class="bg-slate-900 text-white px-6 py-2 rounded-lg md:col-span-3">Create Game</button>
    </form>
</div>

<!-- Game List -->
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">ID</th><th>Name</th><th>Type</th><th>Status</th><th>Min Order</th><th>Daily Limit</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($games as $g): ?>
                <tr class="border-t">
                    <td class="p-3"><?= (int)$g['id'] ?></td>
                    <td><?= e($g['name']) ?></td>
                    <td><?= e($g['type']) ?></td>
                    <td><?= e($g['status']) ?></td>
                    <td>₹<?= number_format($g['min_order_value'],2) ?></td>
                    <td><?= (int)$g['daily_limit'] ?: 'Unlimited' ?></td>
                    <td>
                        <form method="POST" class="flex gap-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="game_id" value="<?= (int)$g['id'] ?>">
                            <select name="status" class="border rounded px-2 py-1 text-sm">
                                <option value="ACTIVE" <?= $g['status']==='ACTIVE'?'selected':'' ?>>Active</option>
                                <option value="INACTIVE" <?= $g['status']==='INACTIVE'?'selected':'' ?>>Inactive</option>
                                <option value="SCHEDULED" <?= $g['status']==='SCHEDULED'?'selected':'' ?>>Scheduled</option>
                            </select>
                            <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>