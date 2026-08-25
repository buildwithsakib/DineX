<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/feature-access.php';

$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

if (!restaurant_has_feature($restaurantId, FEATURE_CAMPAIGNS)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Campaigns feature is not available.');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'add') {
            $name = sanitize_input($_POST['name'] ?? '');
            $type = sanitize_input($_POST['type'] ?? '');
            $startDate = sanitize_input($_POST['start_date'] ?? '');
            $endDate = sanitize_input($_POST['end_date'] ?? '');
            if (empty($name)) $errors[] = 'Name required.';
            if (!$errors) {
                $stmt = $pdo->prepare('INSERT INTO campaigns (restaurant_id, name, type, start_date, end_date, is_active) VALUES (:rid, :name, :type, :start, :end, 1)');
                $stmt->execute([':rid'=>$restaurantId, ':name'=>$name, ':type'=>$type, ':start'=>$startDate, ':end'=>$endDate]);
                audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Campaign added', ['name'=>$name]);
                $success = true;
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM campaigns WHERE id = :id AND restaurant_id = :rid');
            $stmt->execute([':id'=>$id, ':rid'=>$restaurantId]);
            audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Campaign deleted', ['id'=>$id]);
            $success = true;
        }
    }
}

$campaigns = $pdo->query("SELECT * FROM campaigns WHERE restaurant_id = $restaurantId ORDER BY id DESC")->fetchAll();

$pageTitle = 'Campaigns';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Campaigns</h1>

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
            <h2 class="font-semibold">Add Campaign</h2>
            <form method="POST" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div>
                    <label class="block text-sm">Name</label>
                    <input type="text" name="name" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Type</label>
                    <input type="text" name="type" placeholder="e.g., Festive" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Start Date</label>
                    <input type="date" name="start_date" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">End Date</label>
                    <input type="date" name="end_date" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded">Add</button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-6 py-3">Name</th><th class="px-6 py-3">Type</th><th class="px-6 py-3">Start</th><th class="px-6 py-3">End</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Actions</th></tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($campaigns as $campaign): ?>
                    <tr>
                        <td class="px-6 py-3"><?= e($campaign['name']) ?></td>
                        <td class="px-6 py-3"><?= e($campaign['type'] ?? '—') ?></td>
                        <td class="px-6 py-3"><?= $campaign['start_date'] ? e($campaign['start_date']) : '—' ?></td>
                        <td class="px-6 py-3"><?= $campaign['end_date'] ? e($campaign['end_date']) : '—' ?></td>
                        <td class="px-6 py-3"><?= $campaign['is_active'] ? 'Active' : 'Inactive' ?></td>
                        <td class="px-6 py-3">
                            <form method="POST" class="inline" onsubmit="return confirm('Delete?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$campaign['id'] ?>">
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