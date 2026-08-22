<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Cuisines';
$activePage = 'cuisines';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $name = sanitize_text($_POST['name'] ?? '', 100);
    if ($name) {
        $slug = slugify($name);
        $pdo->prepare("INSERT INTO cuisines (restaurant_id, name, slug) VALUES (?,?,?)")->execute([$restaurantId, $name, $slug]);
        $_SESSION['flash'] = 'Cuisine created.';
    }
    redirect('/dinex/admin/owner/cuisines.php');
}

$cuisines = $pdo->prepare("SELECT * FROM cuisines WHERE restaurant_id=? ORDER BY name");
$cuisines->execute([$restaurantId]);
$cuisines = $cuisines->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Cuisines</h1>
<div class="bg-white rounded-xl shadow-md p-6 mb-6">
    <form method="POST" class="flex gap-4">
        <?= csrf_field() ?>
        <input type="text" name="name" placeholder="Cuisine Name" class="border rounded-lg px-4 py-2 flex-1">
        <button class="bg-slate-900 text-white px-6 py-2 rounded-lg">Add</button>
    </form>
</div>
<table class="w-full bg-white rounded-xl shadow-md overflow-hidden">
    <thead class="bg-slate-50"><tr><th class="p-3 text-left">ID</th><th>Name</th><th>Slug</th></tr></thead>
    <tbody>
        <?php foreach ($cuisines as $c): ?>
            <tr class="border-t"><td class="p-3"><?= (int)$c['id'] ?></td><td><?= e($c['name']) ?></td><td><?= e($c['slug']) ?></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>