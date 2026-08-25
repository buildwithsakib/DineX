<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../../includes/security.php';   // <-- Add this
require_once __DIR__ . '/../../includes/csrf.php'; 

$founder = require_founder_access();
$pdo = db();

$stmt = db()->prepare('SELECT * FROM subscription_plans WHERE status = :active ORDER BY id ASC');
$stmt->execute([':active' => 'ACTIVE']);
$allPlans = $stmt->fetchAll();

$featureKeys = [
    FEATURE_QR_ORDERING,
    FEATURE_DIGITAL_MENU,
    FEATURE_KITCHEN,
    FEATURE_BILLING,
    FEATURE_GAMES,
    FEATURE_COUPONS,
    FEATURE_REVIEWS,
    FEATURE_CAMPAIGNS,
    FEATURE_ANALYTICS,
    FEATURE_ADVANCED_ANALYTICS,
];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $planId = (int)($_POST['plan_id'] ?? 0);
        if ($planId <= 0) {
            $errors[] = 'Please select a plan.';
        } else {
            $features = $_POST['features'] ?? [];
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('DELETE FROM subscription_plan_features WHERE plan_id = :plan_id');
                $stmt->execute([':plan_id' => $planId]);

                $insertStmt = $pdo->prepare('INSERT INTO subscription_plan_features (plan_id, feature_key, is_enabled) VALUES (:plan_id, :feature_key, :is_enabled)');
                foreach ($featureKeys as $key) {
                    $isEnabled = isset($features[$key]) ? 1 : 0;
                    $insertStmt->execute([':plan_id' => $planId, ':feature_key' => $key, ':is_enabled' => $isEnabled]);
                }
                $pdo->commit();
                audit_log('FOUNDER', $founder['id'], null, 'Plan features updated', ['plan_id' => $planId]);
                $success = true;
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Error: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Plan Features';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Plan Features</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Features updated successfully.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="mt-6 bg-white rounded-xl shadow p-6 max-w-2xl">
        <form method="POST">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium">Select Plan</label>
                <select name="plan_id" id="planSelect" class="mt-1 w-full border rounded-lg px-3 py-2">
                    <option value="">-- Select --</option>
                    <?php foreach ($allPlans as $plan): ?>
                        <option value="<?= (int)$plan['id'] ?>"><?= e($plan['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="featureList" class="mt-6 hidden">
                <p class="text-sm font-medium mb-2">Features</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach ($featureKeys as $key): ?>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="features[<?= e($key) ?>]" value="1" class="rounded">
                            <span class="text-sm"><?= e(str_replace('_', ' ', $key)) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="mt-4 bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Save Features</button>
            </div>
        </form>
    </div>
</main>
<script>
document.getElementById('planSelect').addEventListener('change', function() {
    const val = this.value;
    const list = document.getElementById('featureList');
    if (val) {
        list.classList.remove('hidden');
        // Load existing features for selected plan via AJAX (optional; we'll just show all unchecked by default)
    } else {
        list.classList.add('hidden');
    }
});
</script>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>