<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$founder = require_founder_access();
$pdo = db();

$logs = $pdo->query('SELECT * FROM audit_logs ORDER BY id DESC LIMIT 200')->fetchAll();

$pageTitle = 'Audit Logs';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Audit Logs</h1>

    <div class="mt-6 bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3">Timestamp</th><th class="px-6 py-3">Actor Type</th><th class="px-6 py-3">Actor ID</th>
                    <th class="px-6 py-3">Restaurant ID</th><th class="px-6 py-3">Action</th><th class="px-6 py-3">Details</th><th class="px-6 py-3">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="px-6 py-3 text-sm"><?= e(date('d M Y H:i:s', strtotime($log['created_at']))) ?></td>
                    <td class="px-6 py-3"><?= e($log['actor_type']) ?></td>
                    <td class="px-6 py-3"><?= $log['actor_id'] ?: '—' ?></td>
                    <td class="px-6 py-3"><?= $log['restaurant_id'] ?: '—' ?></td>
                    <td class="px-6 py-3"><?= e($log['action']) ?></td>
                    <td class="px-6 py-3 text-sm"><?= e($log['details'] ?? '') ?></td>
                    <td class="px-6 py-3 text-sm"><?= e($log['ip_address']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>