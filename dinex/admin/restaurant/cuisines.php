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
        $action = $_POST['action'] ?? '';
        if ($action === 'add') {
            $name = sanitize_input($_POST['name'] ?? '');
            if (empty($name)) {
                $errors[] = 'Name is required.';
            } else {
                $slug = slugify($name);
                $stmt = $pdo->prepare('INSERT INTO cuisines (restaurant_id, name, slug) VALUES (:rid, :name, :slug)');
                $stmt->execute([':rid' => $restaurantId, ':name' => $name, ':slug' => $slug]);
                audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Cuisine added', ['name' => $name]);
                $success = true;
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM cuisines WHERE id = :id AND restaurant_id = :rid');
            $stmt->execute([':id' => $id, ':rid' => $restaurantId]);
            audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Cuisine deleted', ['id' => $id]);
            $success = true;
        }
    }
}

$cuisines = $pdo->query("SELECT * FROM cuisines WHERE restaurant_id = $restaurantId ORDER BY name ASC")->fetchAll();

$pageTitle = 'Cuisines';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Cuisines</h1>

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
            <h2 class="font-semibold">Add Cuisine</h2>
            <form method="POST" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div>
                    <label class="block text-sm">Name</label>
                    <input type="text" name="name" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded">Add</button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-6 py-3">Name</th><th class="px-6 py-3">Slug</th><th class="px-6 py-3">Actions</th></tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($cuisines as $cuisine): ?>
                    <tr>
                        <td class="px-6 py-3"><?= e($cuisine['name']) ?></td>
                        <td class="px-6 py-3"><?= e($cuisine['slug']) ?></td>
                        <td class="px-6 py-3">
                            <form method="POST" class="inline" onsubmit="return confirm('Delete?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$cuisine['id'] ?>">
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