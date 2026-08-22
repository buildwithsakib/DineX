<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Categories';
$activePage = 'categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    $name = sanitize_text($_POST['name'] ?? '', 100);
    $displayOrder = validate_int($_POST['display_order'] ?? 0, 0, 999);
    if ($action === 'create' && $name) {
        $slug = slugify($name);
        $pdo->prepare("INSERT INTO categories (restaurant_id, name, slug, display_order) VALUES (?,?,?,?)")->execute([$restaurantId, $name, $slug, $displayOrder]);
        $_SESSION['flash'] = 'Category created.';
    } elseif ($action === 'update') {
        $id = validate_int($_POST['id'] ?? 0, 1);
        $pdo->prepare("UPDATE categories SET name=?, display_order=? WHERE id=? AND restaurant_id=?")->execute([$name, $displayOrder, $id, $restaurantId]);
        $_SESSION['flash'] = 'Category updated.';
    }
    redirect('/dinex/admin/owner/categories.php');
}

$categories = $pdo->prepare("SELECT * FROM categories WHERE restaurant_id=? ORDER BY display_order, name");
$categories->execute([$restaurantId]);
$categories = $categories->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Categories</h1>
<div class="bg-white rounded-xl shadow-md p-6 mb-6">
    <form method="POST" class="flex gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <input type="text" name="name" placeholder="Category Name" class="border rounded-lg px-4 py-2 flex-1">
        <input type="number" name="display_order" value="0" class="border rounded-lg px-4 py-2 w-24">
        <button class="bg-slate-900 text-white px-6 py-2 rounded-lg">Add</button>
    </form>
</div>
<table class="w-full bg-white rounded-xl shadow-md overflow-hidden">
    <thead class="bg-slate-50"><tr><th class="p-3 text-left">ID</th><th>Name</th><th>Slug</th><th>Order</th><th>Action</th></tr></thead>
    <tbody>
        <?php foreach ($categories as $c): ?>
            <tr class="border-t">
                <td class="p-3"><?= (int)$c['id'] ?></td>
                <td><?= e($c['name']) ?></td>
                <td><?= e($c['slug']) ?></td>
                <td><?= (int)$c['display_order'] ?></td>
                <td>
                    <form method="POST" class="flex gap-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <input type="text" name="name" value="<?= e($c['name']) ?>" class="border rounded px-2 py-1">
                        <input type="number" name="display_order" value="<?= (int)$c['display_order'] ?>" class="border rounded px-2 py-1 w-20">
                        <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>