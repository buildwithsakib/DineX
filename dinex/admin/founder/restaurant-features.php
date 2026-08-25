<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../../includes/security.php';   // <-- Add this
require_once __DIR__ . '/../../includes/csrf.php'; 

$founder = require_founder_access();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect(BASE_URL . '/admin/founder/restaurants.php');
}

$stmt = $pdo->prepare('SELECT * FROM restaurants WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$restaurant = $stmt->fetch();
if (!$restaurant) {
    redirect(BASE_URL . '/admin/founder/restaurants.php');
}

$subscription = get_current_subscription($id);
$effectiveFeatures = get_effective_features($id);

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
        try {
            $pdo->beginTransaction();
            foreach ($featureKeys as $key) {
                $override = $_POST['override'][$key] ?? null; // 'inherit', '1', '0'
                if ($override === 'inherit' || $override === '') {
                    // Remove override
                    $stmt = $pdo->prepare('DELETE FROM restaurant_features WHERE restaurant_id = :rid AND feature_key = :key');
                    $stmt->execute([':rid' => $id, ':key' => $key]);
                } else {
                    $enabled = ($override === '1') ? 1 : 0;
                    set_restaurant_feature_override($id, $key, $enabled);
                }
            }
            $pdo->commit();
            audit_log('FOUNDER', $founder['id'], $id, 'Restaurant feature overrides updated');
            $success = true;
            $effectiveFeatures = get_effective_features($id);
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Restaurant Features';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <a href="restaurant-view.php?id=<?= (int)$restaurant['id'] ?>" class="text-sm text-gray-500 hover:underline"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <h1 class="text-3xl font-bold text-gray-900 mt-4">Feature Overrides for <?= e($restaurant['name']) ?></h1>

    <?php if ($subscription && is_subscription_active($subscription)): ?>
        <p class="text-sm text-gray-500 mt-1">Plan: <?= e($subscription['plan_name']) ?> (<?= e($subscription['billing_cycle']) ?>)</p>
    <?php else: ?>
        <p class="text-sm text-red-500 mt-1">No active subscription.</p>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Overrides saved.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="mt-6 bg-white rounded-xl shadow p-6 max-w-2xl">
        <?= csrf_field() ?>
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr><th class="px-4 py-3">Feature</th><th class="px-4 py-3">Plan Entitlement</th><th class="px-4 py-3">Effective</th><th class="px-4 py-3">Override</th></tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($featureKeys as $key): ?>
                    <tr>
                        <td class="px-4 py-3 font-medium"><?= e(str_replace('_', ' ', $key)) ?></td>
                        <td class="px-4 py-3">
                            <?php
                            $planEnabled = false;
                            if ($subscription && is_subscription_active($subscription)) {
                                $stmt = $pdo->prepare('SELECT is_enabled FROM subscription_plan_features WHERE plan_id = :plan_id AND feature_key = :key');
                                $stmt->execute([':plan_id' => $subscription['plan_id'], ':key' => $key]);
                                $row = $stmt->fetch();
                                $planEnabled = $row ? (bool)$row['is_enabled'] : false;
                            }
                            ?>
                            <span class="<?= $planEnabled ? 'text-green-600' : 'text-red-600' ?>"><?= $planEnabled ? 'Enabled' : 'Disabled' ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="<?= !empty($effectiveFeatures[$key]) ? 'text-green-600' : 'text-red-600' ?>">
                                <?= !empty($effectiveFeatures[$key]) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <select name="override[<?= e($key) ?>]" class="border rounded px-2 py-1 text-sm">
                                <option value="inherit">Inherit from Plan</option>
                                <option value="1">Force Enable</option>
                                <option value="0">Force Disable</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="mt-6 bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Save Overrides</button>
    </form>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>