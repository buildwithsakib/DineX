<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';
require_once INCLUDES_PATH . '/staff-functions.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Staff Management';
$activePage = 'staff';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = sanitize_text($_POST['name'] ?? '', 100);
        $email = sanitize_text($_POST['email'] ?? '', 190);
        $roleId = validate_int($_POST['role_id'] ?? 0, 3, 4);
        $password = $_POST['password'] ?? '';
        $permissionIds = $_POST['permissions'] ?? [];

        if ($name && $email && $roleId && $password) {
            $createdUserId = create_staff_member($pdo, $restaurantId, $roleId, $name, $email, $password, array_map('intval', $permissionIds));
            if ($createdUserId) {
                add_audit_log($pdo, $restaurantId, $user['id'], 'created staff', 'user', $createdUserId);
                $_SESSION['flash'] = 'Staff member created.';
            } else {
                $message = 'Error creating staff. Email may already exist.';
            }
        } else {
            $message = 'All fields are required.';
        }
        redirect('/dinex/admin/owner/staff.php');
    } elseif ($action === 'toggle_active') {
        $userId = validate_int($_POST['user_id'] ?? 0, 1);
        toggle_staff_active($pdo, $userId, $restaurantId);
        add_audit_log($pdo, $restaurantId, $user['id'], 'toggled staff active', 'user', $userId);
        redirect('/dinex/admin/owner/staff.php');
    } elseif ($action === 'update_permissions') {
        $userId = validate_int($_POST['user_id'] ?? 0, 1);
        $permissionIds = array_map('intval', $_POST['permissions'] ?? []);
        // Ensure user belongs to restaurant and is manager
        $check = $pdo->prepare("SELECT u.id, u.role_id FROM users u JOIN restaurant_staff rs ON rs.user_id = u.id WHERE u.id=? AND rs.restaurant_id=? AND u.role_id = ? LIMIT 1");
        $check->execute([$userId, $restaurantId, ROLE_MANAGER]);
        $staff = $check->fetch();
        if ($staff) {
            update_manager_permissions($pdo, $userId, $permissionIds);
            add_audit_log($pdo, $restaurantId, $user['id'], 'updated staff permissions', 'user', $userId);
            $_SESSION['flash'] = 'Permissions updated.';
        }
        redirect('/dinex/admin/owner/staff.php');
    }
}

$staff = get_restaurant_staff($pdo, $restaurantId);
$allPermissions = get_all_permissions($pdo);

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Staff Management</h1>

<?php if ($message): ?><div class="mb-4 bg-red-50 text-red-700 p-4 rounded-lg"><?= e($message) ?></div><?php endif; ?>

<!-- Create Staff Form -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="font-bold text-lg mb-4">Add Staff</h2>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <input type="text" name="name" required placeholder="Full Name" class="border rounded-lg px-4 py-2">
        <input type="email" name="email" required placeholder="Email" class="border rounded-lg px-4 py-2">
        <input type="password" name="password" required placeholder="Password" class="border rounded-lg px-4 py-2">
        <select name="role_id" required class="border rounded-lg px-4 py-2">
            <option value="<?= ROLE_MANAGER ?>">Manager</option>
            <option value="<?= ROLE_CASHIER ?>">Cashier</option>
        </select>
        <div class="md:col-span-3">
            <p class="text-sm font-semibold mb-2">Manager Permissions (optional)</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <?php foreach ($allPermissions as $perm): ?>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="permissions[]" value="<?= (int)$perm['id'] ?>">
                        <?= e($perm['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="bg-slate-900 text-white px-6 py-2 rounded-lg md:col-span-3">Add Staff</button>
    </form>
</div>

<!-- Staff List -->
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($staff as $s): ?>
                <tr class="border-t">
                    <td class="p-3"><?= e($s['name']) ?></td>
                    <td><?= e($s['email']) ?></td>
                    <td><?= e($s['role_name']) ?></td>
                    <td><?= $s['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td class="flex gap-2">
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle_active">
                            <input type="hidden" name="user_id" value="<?= (int)$s['id'] ?>">
                            <button class="text-blue-600 hover:underline text-sm">Toggle</button>
                        </form>
                        <?php if ($s['role_id'] == ROLE_MANAGER): ?>
                            <button onclick="openPermissionModal(<?= (int)$s['id'] ?>, '<?= e($s['name']) ?>')" class="text-emerald-600 hover:underline text-sm">Permissions</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Permission Modal -->
<div id="permission-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg">
        <h2 class="text-xl font-bold mb-4">Edit Permissions</h2>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_permissions">
            <input type="hidden" name="user_id" id="perm-user-id">
            <div class="grid grid-cols-2 gap-2 mb-4">
                <?php foreach ($allPermissions as $perm): ?>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="permissions[]" value="<?= (int)$perm['id'] ?>" class="perm-checkbox" data-perm-id="<?= (int)$perm['id'] ?>">
                        <?= e($perm['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('permission-modal').classList.add('hidden')" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button class="bg-emerald-600 text-white px-4 py-2 rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPermissionModal(userId, name) {
    document.getElementById('perm-user-id').value = userId;
    // Reset checkboxes
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
    // Fetch current permissions via AJAX (simplified: just open, no preload; user will check)
    document.getElementById('permission-modal').classList.remove('hidden');
}
</script>

<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>