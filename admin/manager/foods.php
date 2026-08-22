<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['manager']);
require_permission($pdo, $user, 'foods.manage');
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Food Availability';
$activePage = 'foods';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $foodId = validate_int($_POST['id'] ?? 0, 1);
    $pdo->prepare("UPDATE foods SET is_available=1-is_available WHERE id=? AND restaurant_id=?")->execute([$foodId, $restaurantId]);
    redirect('/dinex/admin/manager/foods.php');
}

$foods = $pdo->prepare("SELECT f.*, c.name AS category_name FROM foods f LEFT JOIN categories c ON c.id=f.category_id WHERE f.restaurant_id=? ORDER BY f.id DESC");
$foods->execute([$restaurantId]);
$foods = $foods->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Food Availability</h1>
<table class="w-full bg-white rounded-xl shadow-md overflow-hidden">
    <thead class="bg-slate-50"><tr><th class="p-3">Name</th><th>Category</th><th>Availability</th><th>Action</th></tr></thead>
    <tbody>
        <?php foreach ($foods as $f): ?>
            <tr class="border-t"><td class="p-3"><?= e($f['name']) ?></td><td><?= e($f['category_name']) ?></td><td><?= $f['is_available'] ? 'Available' : 'Unavailable' ?></td>
            <td><form method="POST"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$f['id'] ?>"><button class="text-blue-600 hover:underline">Toggle</button></form></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>