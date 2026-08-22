<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Settings';
$activePage = 'settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $key = sanitize_text($_POST['key'] ?? '', 100);
    $value = sanitize_text($_POST['value'] ?? '', 1000);
    if ($key) {
        $pdo->prepare("INSERT INTO settings (restaurant_id, `key`, `value`) VALUES (?,?,?)
                        ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)")->execute([$restaurantId, $key, $value]);
        $_SESSION['flash'] = 'Setting saved.';
    }
    redirect('/dinex/admin/owner/settings.php');
}

$settings = $pdo->prepare("SELECT * FROM settings WHERE restaurant_id=? ORDER BY `key`");
$settings->execute([$restaurantId]);
$settings = $settings->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Restaurant Settings</h1>
<div class="bg-white rounded-xl shadow-md p-6">
    <form method="POST" class="flex gap-4 mb-6">
        <?= csrf_field() ?>
        <input type="text" name="key" placeholder="Key" class="border rounded-lg px-4 py-2 flex-1">
        <input type="text" name="value" placeholder="Value" class="border rounded-lg px-4 py-2 flex-1">
        <button class="bg-slate-900 text-white px-6 py-2 rounded-lg">Add/Update</button>
    </form>
    <table class="w-full text-left">
        <thead><tr><th class="p-2">Key</th><th class="p-2">Value</th></tr></thead>
        <tbody>
            <?php foreach ($settings as $s): ?>
                <tr class="border-t"><td class="p-2"><?= e($s['key']) ?></td><td class="p-2"><?= e($s['value']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>