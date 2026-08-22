<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Audit Logs';
$activePage = 'audit_logs';

$logs = $pdo->prepare("SELECT a.*, u.name AS user_name, u.email AS user_email
                       FROM audit_logs a
                       LEFT JOIN users u ON u.id = a.user_id
                       WHERE a.restaurant_id=? ORDER BY a.id DESC LIMIT 200");
$logs->execute([$restaurantId]);
$logs = $logs->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Audit Logs</h1>
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">Time</th><th>User</th><th>Action</th><th>Entity</th><th>Details</th><th>IP</th></tr></thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr class="border-t">
                    <td class="p-3 text-sm"><?= e($log['created_at']) ?></td>
                    <td><?= e($log['user_name'] ?? 'System') ?></td>
                    <td><?= e($log['action']) ?></td>
                    <td><?= e($log['entity_type'] ?? '') ?> #<?= (int)$log['entity_id'] ?></td>
                    <td><?= e($log['details'] ?? '') ?></td>
                    <td><?= e($log['ip_address'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>