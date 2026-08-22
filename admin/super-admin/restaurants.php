<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['super_admin']);
$pageTitle = 'Restaurants';
$activePage = 'restaurants';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_csrf();
    $action = $_POST['action'];
    if ($action === 'create') {
        $name = sanitize_text($_POST['name'] ?? '', 150);
        $email = sanitize_text($_POST['email'] ?? '', 190);
        $phone = sanitize_text($_POST['phone'] ?? '', 50);
        $address = sanitize_text($_POST['address'] ?? '', 500);
        $slug = slugify($name);
        $stmt = $pdo->prepare("INSERT INTO restaurants (name, slug, email, phone, address) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $slug, $email, $phone, $address]);
        add_audit_log($pdo, (int)$pdo->lastInsertId(), $user['id'], 'created restaurant', 'restaurant', (int)$pdo->lastInsertId());
        $_SESSION['flash'] = 'Restaurant created.';
        redirect('/dinex/admin/super-admin/restaurants.php');
    } elseif ($action === 'toggle_active') {
        $id = validate_int($_POST['id'] ?? 0, 1);
        $pdo->prepare("UPDATE restaurants SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
        redirect('/dinex/admin/super-admin/restaurants.php');
    }
}

$restaurants = $pdo->query("SELECT * FROM restaurants ORDER BY id DESC")->fetchAll();
require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Restaurants</h1>
<div class="bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="font-bold text-lg mb-4">Add Restaurant</h2>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <input type="text" name="name" required placeholder="Restaurant Name" class="border rounded-lg px-4 py-2">
        <input type="email" name="email" placeholder="Email" class="border rounded-lg px-4 py-2">
        <input type="text" name="phone" placeholder="Phone" class="border rounded-lg px-4 py-2">
        <textarea name="address" placeholder="Address" class="border rounded-lg px-4 py-2"></textarea>
        <button class="bg-slate-900 text-white px-6 py-2 rounded-lg">Create</button>
    </form>
</div>
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50 text-gray-600">
            <tr><th class="p-3">ID</th><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php foreach ($restaurants as $r): ?>
                <tr class="border-t">
                    <td class="p-3"><?= (int)$r['id'] ?></td>
                    <td><?= e($r['name']) ?></td>
                    <td><?= e($r['email']) ?></td>
                    <td><?= $r['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td>
                        <form method="POST" data-confirm="Toggle restaurant status?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle_active">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button class="text-blue-600 hover:underline">Toggle</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>