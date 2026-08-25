<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$staff = require_restaurant_auth();
if ($staff['role'] !== ROLE_OWNER) {
    header('HTTP/1.1 403 Forbidden');
    exit('Only owner can manage permissions.');
}
$pdo = db();
$roles = $pdo->query('SELECT * FROM roles')->fetchAll();
$permissions = $pdo->query('SELECT * FROM permissions ORDER BY `group` ASC, `name` ASC')->fetchAll();
$rolePerms = [];
$stmt = $pdo->query('SELECT rp.role_id, p.key FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id');
foreach ($stmt->fetchAll() as $row) {
    $rolePerms[$row['role_id']][] = $row['key'];
}

$pageTitle = 'Permissions';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Role Permissions</h1>
    <div class="mt-6 bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3">Permission</th>
                    <?php foreach ($roles as $role): ?>
                        <th class="px-6 py-3"><?= e($role['name']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($permissions as $perm): ?>
                <tr>
                    <td class="px-6 py-3"><?= e($perm['name']) ?></td>
                    <?php foreach ($roles as $role): ?>
                        <td class="px-6 py-3">
                            <?php if (in_array($perm['key'], $rolePerms[$role['id']] ?? [])): ?>
                                <span class="text-green-600"><i class="fa-solid fa-check"></i></span>
                            <?php else: ?>
                                <span class="text-red-400"><i class="fa-solid fa-xmark"></i></span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>