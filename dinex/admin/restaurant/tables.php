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
            $tableNumber = sanitize_input($_POST['table_number'] ?? '');
            $capacity = (int)($_POST['capacity'] ?? 2);
            if (empty($tableNumber)) {
                $errors[] = 'Table number is required.';
            } else {
                // Check duplicate
                $check = $pdo->prepare('SELECT COUNT(*) FROM tables WHERE restaurant_id = :rid AND table_number = :tn');
                $check->execute([':rid' => $restaurantId, ':tn' => $tableNumber]);
                if ((int)$check->fetchColumn() > 0) {
                    $errors[] = 'Table number already exists.';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO tables (restaurant_id, table_number, capacity) VALUES (:rid, :tn, :cap)');
                    $stmt->execute([':rid' => $restaurantId, ':tn' => $tableNumber, ':cap' => $capacity]);
                    audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Table added', ['table' => $tableNumber]);
                    $success = true;
                }
            }
        } elseif ($action === 'delete') {
            $tableId = (int)($_POST['table_id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM tables WHERE id = :id AND restaurant_id = :rid');
            $stmt->execute([':id' => $tableId, ':rid' => $restaurantId]);
            audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Table deleted', ['table_id' => $tableId]);
            $success = true;
        }
    }
}

$tables = $pdo->query("SELECT * FROM tables WHERE restaurant_id = $restaurantId ORDER BY table_number ASC")->fetchAll();

$pageTitle = 'Tables';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Tables</h1>

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
            <h2 class="font-semibold">Add Table</h2>
            <form method="POST" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div>
                    <label class="block text-sm">Table Number</label>
                    <input type="text" name="table_number" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Capacity</label>
                    <input type="number" name="capacity" min="1" max="50" value="2" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded">Add Table</button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-6 py-3">Number</th><th class="px-6 py-3">Capacity</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Actions</th></tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($tables as $table): ?>
                    <tr>
                        <td class="px-6 py-3 font-medium"><?= e($table['table_number']) ?></td>
                        <td class="px-6 py-3"><?= (int)$table['capacity'] ?></td>
                        <td class="px-6 py-3"><span class="px-2 py-1 text-xs rounded <?= $table['status'] === 'AVAILABLE' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>"><?= e($table['status']) ?></span></td>
                        <td class="px-6 py-3">
                            <a href="qr.php?table_id=<?= (int)$table['id'] ?>" class="text-blue-600 hover:underline">QR</a>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete this table?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="table_id" value="<?= (int)$table['id'] ?>">
                                <button type="submit" class="ml-2 text-red-600 hover:underline">Delete</button>
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