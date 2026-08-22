<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Tables';
$activePage = 'tables';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $tableNumber = sanitize_text($_POST['table_number'] ?? '', 20);
        $capacity = validate_int($_POST['capacity'] ?? 4, 1, 50);
        if ($tableNumber && $capacity) {
            $pdo->prepare("INSERT INTO tables (restaurant_id, table_number, capacity) VALUES (?,?,?)")->execute([$restaurantId, $tableNumber, $capacity]);
            $tableId = (int)$pdo->lastInsertId();
            // Generate QR token
            $token = generate_token(16);
            $pdo->prepare("INSERT INTO qr_codes (restaurant_id, table_id, token) VALUES (?,?,?)")->execute([$restaurantId, $tableId, $token]);
            add_audit_log($pdo, $restaurantId, $user['id'], 'created table', 'table', $tableId);
            $_SESSION['flash'] = 'Table created with QR.';
            redirect('/dinex/admin/owner/tables.php');
        }
    } elseif ($action === 'toggle_active') {
        $id = validate_int($_POST['id'] ?? 0, 1);
        $pdo->prepare("UPDATE tables SET is_active=1-is_active WHERE id=? AND restaurant_id=?")->execute([$id, $restaurantId]);
        redirect('/dinex/admin/owner/tables.php');
    }
}

$tables = $pdo->prepare("SELECT t.*, q.token AS qr_token FROM tables t LEFT JOIN qr_codes q ON q.table_id = t.id WHERE t.restaurant_id=? ORDER BY t.id");
$tables->execute([$restaurantId]);
$tables = $tables->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Tables</h1>
<div class="bg-white rounded-xl shadow-md p-6 mb-6">
    <form method="POST" class="flex gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <input type="text" name="table_number" required placeholder="Table Number" class="border rounded-lg px-4 py-2">
        <input type="number" name="capacity" value="4" min="1" class="border rounded-lg px-4 py-2 w-24">
        <button class="bg-slate-900 text-white px-6 py-2 rounded-lg">Add Table</button>
    </form>
</div>
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">ID</th><th>Table</th><th>Capacity</th><th>Status</th><th>QR Token</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($tables as $t): ?>
                <tr class="border-t"><td class="p-3"><?= (int)$t['id'] ?></td><td><?= e($t['table_number']) ?></td><td><?= (int)$t['capacity'] ?></td><td><?= e($t['status']) ?></td><td class="font-mono text-xs"><?= e($t['qr_token']) ?></td>
                <td><form method="POST" data-confirm="Toggle table active?"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_active"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="text-blue-600 hover:underline">Toggle</button></form></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>