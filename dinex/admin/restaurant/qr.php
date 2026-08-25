<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';


$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

$tableId = (int)($_GET['table_id'] ?? 0);
$errors = [];
$success = false;
$generatedToken = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $tableId = (int)($_POST['table_id'] ?? 0);
        if ($tableId <= 0) {
            $errors[] = 'Invalid table.';
        } else {
            // Check table belongs to restaurant
            $stmt = $pdo->prepare('SELECT * FROM tables WHERE id = :id AND restaurant_id = :rid LIMIT 1');
            $stmt->execute([':id' => $tableId, ':rid' => $restaurantId]);
            $table = $stmt->fetch();
            if (!$table) {
                $errors[] = 'Table not found or unauthorized.';
            } else {
                // Check if QR exists; if yes, reactivate or generate new token
                $token = generate_unique_code('dinex_' . $staff['restaurant_slug'] . '_' . strtolower($table['table_number']) . '_');
                // Delete existing QR for table
                $del = $pdo->prepare('DELETE FROM qr_codes WHERE table_id = :tid AND restaurant_id = :rid');
                $del->execute([':tid' => $tableId, ':rid' => $restaurantId]);
                $stmt = $pdo->prepare('INSERT INTO qr_codes (restaurant_id, table_id, token) VALUES (:rid, :tid, :token)');
                $stmt->execute([':rid' => $restaurantId, ':tid' => $tableId, ':token' => $token]);
                $generatedToken = $token;
                audit_log('RESTAURANT', $staff['id'], $restaurantId, 'QR generated', ['table_id' => $tableId]);
                $success = true;
            }
        }
    }
}

$tables = $pdo->query("SELECT * FROM tables WHERE restaurant_id = $restaurantId ORDER BY table_number ASC")->fetchAll();
$qrCodes = $pdo->query("
    SELECT q.*, t.table_number
    FROM qr_codes q
    JOIN tables t ON t.id = q.table_id
    WHERE q.restaurant_id = $restaurantId
    ORDER BY t.table_number ASC
")->fetchAll();

$pageTitle = 'QR Codes';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">QR Codes</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            QR generated. Customer URL: <span class="font-mono"><?= e(FULL_BASE_URL . '/customer/menu.php?token=' . $generatedToken) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold">Generate QR for Table</h2>
            <form method="POST" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="generate" value="1">
                <div>
                    <label class="block text-sm">Table</label>
                    <select name="table_id" required class="mt-1 w-full border rounded px-3 py-2">
                        <option value="">Select Table</option>
                        <?php foreach ($tables as $t): ?>
                            <option value="<?= (int)$t['id'] ?>" <?= $tableId === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['table_number']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded">Generate QR</button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold">Existing QR Codes</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <?php foreach ($qrCodes as $qr): ?>
                <div class="border rounded-lg p-4 text-center">
                    <p class="font-medium">Table <?= e($qr['table_number']) ?></p>
                    <div id="qr-<?= (int)$qr['id'] ?>" class="mt-2"></div>
                    <p class="text-xs text-gray-500 mt-2">Token: <?= e(substr($qr['token'], 0, 12)) ?>...</p>
                    <p class="text-xs text-gray-500">URL: <?= e(FULL_BASE_URL . '/customer/menu.php?token=' . $qr['token']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
<?php foreach ($qrCodes as $qr): ?>
new QRCode(document.getElementById("qr-<?= (int)$qr['id'] ?>"), {
    text: "<?= e(FULL_BASE_URL . '/customer/menu.php?token=' . $qr['token']) ?>",
    width: 100,
    height: 100
});
<?php endforeach; ?>
</script>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>