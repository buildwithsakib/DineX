<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

if ($staff['role'] !== ROLE_OWNER) {
    header('HTTP/1.1 403 Forbidden');
    exit('Only owner can manage staff.');
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
            $email = sanitize_input($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = sanitize_input($_POST['role'] ?? ROLE_CASHIER);

            if (empty($name) || empty($email) || strlen($password) < 8) {
                $errors[] = 'Name, valid email and password (min 8) are required.';
            }
            if (!in_array($role, [ROLE_OWNER, ROLE_MANAGER, ROLE_CASHIER])) {
                $errors[] = 'Invalid role.';
            }
            if (!$errors) {
                // Prevent creating another owner unless owner wants to transfer? We'll allow for demo.
                $check = $pdo->prepare('SELECT COUNT(*) FROM restaurant_staff WHERE restaurant_id = :rid AND email = :email');
                $check->execute([':rid'=>$restaurantId, ':email'=>$email]);
                if ((int)$check->fetchColumn() > 0) {
                    $errors[] = 'Email already exists.';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO restaurant_staff (restaurant_id, name, email, password_hash, role) VALUES (:rid, :name, :email, :hash, :role)');
                    $stmt->execute([
                        ':rid'=>$restaurantId,
                        ':name'=>$name,
                        ':email'=>$email,
                        ':hash'=>password_hash($password, PASSWORD_BCRYPT),
                        ':role'=>$role,
                    ]);
                    audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Staff created', ['email'=>$email, 'role'=>$role]);
                    $success = true;
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id === (int)$staff['id']) {
                $errors[] = 'You cannot delete yourself.';
            } else {
                $stmt = $pdo->prepare('DELETE FROM restaurant_staff WHERE id = :id AND restaurant_id = :rid');
                $stmt->execute([':id'=>$id, ':rid'=>$restaurantId]);
                audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Staff deleted', ['staff_id'=>$id]);
                $success = true;
            }
        }
    }
}

$staffList = $pdo->query("SELECT id, name, email, role, status, created_at FROM restaurant_staff WHERE restaurant_id = $restaurantId ORDER BY id ASC")->fetchAll();

$pageTitle = 'Staff Management';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Staff</h1>

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
            <h2 class="font-semibold">Add Staff</h2>
            <form method="POST" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div>
                    <label class="block text-sm">Name</label>
                    <input type="text" name="name" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Email</label>
                    <input type="email" name="email" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Password</label>
                    <input type="password" name="password" required minlength="8" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Role</label>
                    <select name="role" class="mt-1 w-full border rounded px-3 py-2">
                        <option value="<?= ROLE_MANAGER ?>">Manager</option>
                        <option value="<?= ROLE_CASHIER ?>" selected>Cashier</option>
                        <option value="<?= ROLE_OWNER ?>">Owner</option>
                    </select>
                </div>
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded">Add</button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-6 py-3">Name</th><th class="px-6 py-3">Email</th><th class="px-6 py-3">Role</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Actions</th></tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($staffList as $s): ?>
                    <tr>
                        <td class="px-6 py-3"><?= e($s['name']) ?></td>
                        <td class="px-6 py-3"><?= e($s['email']) ?></td>
                        <td class="px-6 py-3"><span class="px-2 py-1 text-xs rounded bg-gray-100"><?= e($s['role']) ?></span></td>
                        <td class="px-6 py-3"><?= e($s['status']) ?></td>
                        <td class="px-6 py-3">
                            <?php if ($s['id'] != $staff['id']): ?>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>