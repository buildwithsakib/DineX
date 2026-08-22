<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['super_admin']);
$pageTitle = 'Subscriptions';
$activePage = 'subscriptions';

$restaurants = $pdo->query("SELECT id, name, is_active, created_at FROM restaurants ORDER BY id DESC")->fetchAll();
require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Subscriptions</h1>
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">ID</th><th>Restaurant</th><th>Created</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($restaurants as $r): ?>
                <tr class="border-t"><td class="p-3"><?= (int)$r['id'] ?></td><td><?= e($r['name']) ?></td><td><?= e($r['created_at']) ?></td><td><?= $r['is_active'] ? 'Active' : 'Inactive' ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>