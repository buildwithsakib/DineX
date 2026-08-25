<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../../includes/security.php';   // <-- Add this
require_once __DIR__ . '/../../includes/csrf.php'; 
$founder = require_founder_access();
$pdo = db();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $billingCycle = sanitize_input($_POST['billing_cycle'] ?? '');
        $price = sanitize_input($_POST['price'] ?? '');
        $durationDays = (int)($_POST['duration_days'] ?? 0);
        $description = sanitize_input($_POST['description'] ?? '');
        $status = sanitize_input($_POST['status'] ?? 'ACTIVE');
        $maxTables = (int)($_POST['max_tables'] ?? 10);
        $maxStaff = (int)($_POST['max_staff'] ?? 3);

        $fieldErrors = validate_required($_POST, ['name', 'billing_cycle', 'price', 'duration_days']);
        if ($fieldErrors) {
            $errors = array_merge($errors, array_values($fieldErrors));
        }
        if (!in_array($billingCycle, ['MONTHLY', 'YEARLY'])) {
            $errors[] = 'Invalid billing cycle.';
        }
        if (!is_numeric($price) || $price < 0) {
            $errors[] = 'Price must be a positive number.';
        }
        if ($durationDays <= 0) {
            $errors[] = 'Duration must be greater than 0.';
        }
        if (!in_array($status, ['ACTIVE', 'INACTIVE'])) {
            $status = 'ACTIVE';
        }

        if (!$errors) {
            $slug = slugify($name);
            $stmt = $pdo->prepare('
                INSERT INTO subscription_plans (name, slug, billing_cycle, price, duration_days, description, status, max_tables, max_staff)
                VALUES (:name, :slug, :billing_cycle, :price, :duration_days, :description, :status, :max_tables, :max_staff)
            ');
            $stmt->execute([
                ':name' => $name,
                ':slug' => $slug,
                ':billing_cycle' => $billingCycle,
                ':price' => $price,
                ':duration_days' => $durationDays,
                ':description' => $description,
                ':status' => $status,
                ':max_tables' => $maxTables,
                ':max_staff' => $maxStaff,
            ]);
            $planId = (int)$pdo->lastInsertId();

            // Insert default features? We'll do that separately. Here we can insert basic features.
            $defaultFeatures = [
                FEATURE_QR_ORDERING => 1,
                FEATURE_DIGITAL_MENU => 1,
                FEATURE_KITCHEN => 1,
                FEATURE_BILLING => 1,
                FEATURE_GAMES => 0,
                FEATURE_COUPONS => 0,
                FEATURE_REVIEWS => 0,
                FEATURE_CAMPAIGNS => 0,
                FEATURE_ANALYTICS => 0,
                FEATURE_ADVANCED_ANALYTICS => 0,
            ];
            $featureStmt = $pdo->prepare('INSERT INTO subscription_plan_features (plan_id, feature_key, is_enabled) VALUES (:plan_id, :feature_key, :is_enabled)');
            foreach ($defaultFeatures as $key => $enabled) {
                $featureStmt->execute([':plan_id' => $planId, ':feature_key' => $key, ':is_enabled' => $enabled]);
            }

            audit_log('FOUNDER', $founder['id'], null, 'Subscription plan created', ['plan_id' => $planId]);
            $success = true;
        }
    }
}

$pageTitle = 'Create Plan';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <a href="plans.php" class="text-sm text-gray-500 hover:underline"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <h1 class="text-3xl font-bold text-gray-900 mt-4">Create Subscription Plan</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Plan created successfully.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="mt-6 bg-white rounded-xl shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium">Plan Name</label>
            <input type="text" name="name" required class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Billing Cycle</label>
            <select name="billing_cycle" required class="mt-1 w-full border rounded-lg px-3 py-2">
                <option value="MONTHLY">Monthly</option>
                <option value="YEARLY">Yearly</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Price (₹)</label>
            <input type="number" step="0.01" name="price" required class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Duration (days)</label>
            <input type="number" name="duration_days" required class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Max Tables</label>
            <input type="number" name="max_tables" value="20" required class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Max Staff</label>
            <input type="number" name="max_staff" value="5" required class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium">Description</label>
            <textarea name="description" class="mt-1 w-full border rounded-lg px-3 py-2"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium">Status</label>
            <select name="status" class="mt-1 w-full border rounded-lg px-3 py-2">
                <option value="ACTIVE" selected>Active</option>
                <option value="INACTIVE">Inactive</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Create Plan</button>
        </div>
    </form>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>