<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../../includes/security.php';   // <-- Add this
require_once __DIR__ . '/../../includes/csrf.php'; 
$founder = require_founder_access();
$pdo = db();

$settings = [];
$stmt = $pdo->query('SELECT * FROM settings');
foreach ($stmt->fetchAll() as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        set_setting('platform_name', sanitize_input($_POST['platform_name'] ?? 'DineX'));
        set_setting('tagline', sanitize_input($_POST['tagline'] ?? ''));
        set_setting('session_retention_hours', sanitize_input($_POST['session_retention_hours'] ?? '24'));
        set_setting('default_tax_rate', sanitize_input($_POST['default_tax_rate'] ?? '5.00'));
        audit_log('FOUNDER', $founder['id'], null, 'Platform settings updated');
        $success = true;
        // Reload
        $settings = [];
        $stmt = $pdo->query('SELECT * FROM settings');
        foreach ($stmt->fetchAll() as $s) {
            $settings[$s['setting_key']] = $s['setting_value'];
        }
    }
}

$pageTitle = 'Platform Settings';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Settings</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Settings saved.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="mt-6 bg-white rounded-xl shadow p-6 max-w-2xl space-y-4">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium">Platform Name</label>
            <input type="text" name="platform_name" value="<?= e($settings['platform_name'] ?? 'DineX') ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Tagline</label>
            <input type="text" name="tagline" value="<?= e($settings['tagline'] ?? '') ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Session Retention (hours)</label>
            <input type="number" name="session_retention_hours" value="<?= e($settings['session_retention_hours'] ?? '24') ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Default Tax Rate (%)</label>
            <input type="number" step="0.01" name="default_tax_rate" value="<?= e($settings['default_tax_rate'] ?? '5.00') ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Save Settings</button>
    </form>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>