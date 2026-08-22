<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'QR Codes';
$activePage = 'qr';

$qrs = $pdo->prepare("SELECT q.*, t.table_number FROM qr_codes q JOIN tables t ON t.id=q.table_id WHERE q.restaurant_id=? ORDER BY q.id");
$qrs->execute([$restaurantId]);
$qrs = $qrs->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">QR Codes</h1>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach ($qrs as $qr): ?>
        <div class="bg-white rounded-xl shadow-md p-6 text-center">
            <h3 class="font-bold mb-2"><?= e($qr['table_number']) ?></h3>
            <div id="qr-<?= (int)$qr['id'] ?>" class="flex justify-center"></div>
            <p class="font-mono text-xs mt-2">Token: <?= e($qr['token']) ?></p>
            <p class="text-xs text-gray-500">URL: /dinex/customer/menu.php?token=<?= e($qr['token']) ?></p>
        </div>
    <?php endforeach; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    <?php foreach ($qrs as $qr): ?>
        new QRCode(document.getElementById('qr-<?= (int)$qr['id'] ?>'), {
            text: '/dinex/customer/menu.php?token=<?= e($qr['token']) ?>',
            width: 120,
            height: 120,
        });
    <?php endforeach; ?>
</script>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>