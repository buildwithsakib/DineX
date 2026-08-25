<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';

$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        // Update restaurant-specific settings if any; here we just log or save a welcome message
        set_setting('restaurant_' . $restaurantId . '_welcome_message', sanitize_input($_POST['welcome_message'] ?? ''));
        audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Restaurant settings updated');
        $success = true;
    }
}
$welcomeMessage = get_setting('restaurant_' . $restaurantId . '_welcome_message', 'Welcome to our restaurant!');

$pageTitle = 'Settings';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Restaurant Settings</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Settings saved.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="mt-6 bg-white rounded-xl shadow p-6 max-w-2xl">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium">Customer Welcome Message</label>
            <textarea name="welcome_message" class="mt-1 w-full border rounded-lg px-3 py-2"><?= e($welcomeMessage) ?></textarea>
        </div>
        <button type="submit" class="mt-4 bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Save Settings</button>
    </form>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>