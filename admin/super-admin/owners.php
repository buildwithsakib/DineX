<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['super_admin']);
$pageTitle = 'Restaurant Owners';
$activePage = 'owners';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_csrf();
    if ($_POST['action'] === 'create_owner') {
        $name = sanitize_text($_POST['name'] ?? '', 100);
        $email = sanitize_text($_POST['email'] ?? '', 190);
        $restaurantId = validate_int($_POST['restaurant_id'] ?? 0, 1);
        $password = $_POST['password'] ?? '';
        if ($name && $email && $restaurantId && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (role_id, name, email, password_hash) VALUES (?,?,?,?)")->execute([ROLE_OWNER, $name, $email, $hash]);
            $userId = (int)$pdo->lastInsertId();
            $pdo->prepare("INSERT INTO restaurant_staff (user_id, restaurant_id, role_id) VALUES (?,?,?)")->execute([$userId, $restaurantId, ROLE_OWNER]);
            $_SESSION['flash'] = 'Owner created.';
            redirect('/dinex/admin/super-admin/owners.php');
        }
    }
}

$owners = $pdo->query("SELECT u.id, u.name, u.email, u.is_active, r.name AS restaurant_name
                       FROM users u
                       JOIN restaurant_staff rs ON rs.user_id = u.id
                       JOIN restaurants r ON r.id = rs.restaurant_id
                       WHERE u.role_id = 2
                       ORDER BY u.id DESC")->fetchAll();
$restaurants = $pdo->query("SELECT id, name FROM restaurants WHERE is_active=1")->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Owners</h1>
<div class="bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="font-bold text-lg mb-4">Create Owner</h2>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create_owner">
        <input type="text" name="name" required placeholder="Name" class="border rounded-lg px-4 py-2">
        <input type="email" name="email" required placeholder="Email" class="border rounded-lg px-4 py-2">
        <input type="password" name="password" required placeholder="Password" class="border rounded-lg px-4 py-2">
        <select name="restaurant_id" required class="border rounded-lg px-4 py-2">
            <?php foreach ($restaurants as $r): ?>
                <option value="<?= (int)$r['id'] ?>"><?= e($r['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="bg-slate-900 text-white px-6 py-2 rounded-lg">Create</button>
    </form>
</div>
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">ID</th><th>Name</th><th>Email</th><th>Restaurant</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($owners as $o): ?>
                <tr class="border-t"><td class="p-3"><?= (int)$o['id'] ?></td><td><?= e($o['name']) ?></td><td><?= e($o['email']) ?></td><td><?= e($o['restaurant_name']) ?></td><td><?= $o['is_active'] ? 'Active' : 'Inactive' ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>