<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['manager']);
require_permission($pdo, $user, 'tables.manage');
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Tables';
$activePage = 'tables';

$tables = $pdo->prepare("SELECT * FROM tables WHERE restaurant_id=? ORDER BY id");
$tables->execute([$restaurantId]);
$tables = $tables->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Tables</h1>
<table class="w-full bg-white rounded-xl shadow-md overflow-hidden">
    <thead class="bg-slate-50"><tr><th class="p-3">Table</th><th>Capacity</th><th>Status</th></tr></thead>
    <tbody><?php foreach ($tables as $t): ?><tr class="border-t"><td class="p-3"><?= e($t['table_number']) ?></td><td><?= (int)$t['capacity'] ?></td><td><?= e($t['status']) ?></td></tr><?php endforeach; ?></tbody>
</table>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>