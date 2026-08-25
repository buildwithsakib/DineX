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
        $ownerName = sanitize_input($_POST['owner_name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $planId = (int)($_POST['plan_id'] ?? 0);
        $status = sanitize_input($_POST['status'] ?? RESTAURANT_STATUS_ACTIVE);

        $fieldErrors = validate_required($_POST, ['name', 'owner_name', 'email', 'password']);
        if ($fieldErrors) {
            $errors = array_merge($errors, array_values($fieldErrors));
        }
        if (!validate_email($email)) {
            $errors[] = 'Invalid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($planId <= 0) {
            $errors[] = 'Please select a subscription plan.';
        }
        if (!in_array($status, [RESTAURANT_STATUS_PENDING, RESTAURANT_STATUS_ACTIVE, RESTAURANT_STATUS_SUSPENDED, RESTAURANT_STATUS_CANCELLED])) {
            $status = RESTAURANT_STATUS_ACTIVE;
        }

        if (!$errors) {
            try {
                $pdo->beginTransaction();
                $slug = slugify($name);

                $check = $pdo->prepare('SELECT COUNT(*) FROM restaurants WHERE email = :email OR slug = :slug');
                $check->execute([':email' => $email, ':slug' => $slug]);
                if ((int)$check->fetchColumn() > 0) {
                    $errors[] = 'A restaurant with this email or name already exists.';
                    $pdo->rollBack();
                } else {
                    $stmt = $pdo->prepare('
                        INSERT INTO restaurants (name, slug, owner_name, email, phone, status)
                        VALUES (:name, :slug, :owner_name, :email, :phone, :status)
                    ');
                    $stmt->execute([
                        ':name' => $name,
                        ':slug' => $slug,
                        ':owner_name' => $ownerName,
                        ':email' => $email,
                        ':phone' => $phone,
                        ':status' => $status,
                    ]);
                    $restaurantId = (int)$pdo->lastInsertId();

                    $staffStmt = $pdo->prepare('
                        INSERT INTO restaurant_staff (restaurant_id, name, email, password_hash, role)
                        VALUES (:restaurant_id, :name, :email, :password_hash, :role)
                    ');
                    $staffStmt->execute([
                        ':restaurant_id' => $restaurantId,
                        ':name' => $ownerName,
                        ':email' => $email,
                        ':password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_BCRYPT_COST]),
                        ':role' => ROLE_OWNER,
                    ]);

                    $subscriptionId = create_subscription($restaurantId, $planId, SUBSCRIPTION_STATUS_ACTIVE, SUBSCRIPTION_PAYMENT_PAID);

                    $pdo->commit();

                    audit_log('FOUNDER', $founder['id'], $restaurantId, 'Restaurant created by founder', ['plan_id' => $planId]);
                    $success = true;
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Error: ' . $e->getMessage();
            }
        }
    }
}

$stmt = db()->prepare('SELECT * FROM subscription_plans WHERE status = :active ORDER BY price ASC');
$stmt->execute([':active' => 'ACTIVE']);
$allPlans = $stmt->fetchAll();

$pageTitle = 'Create Restaurant';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <a href="restaurants.php" class="text-sm text-gray-500 hover:underline"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <h1 class="text-3xl font-bold text-gray-900 mt-4">Create Restaurant</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Restaurant created successfully.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="mt-6 bg-white rounded-xl shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium">Restaurant Name</label>
            <input type="text" name="name" required class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Owner Name</label>
            <input type="text" name="owner_name" required class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input type="email" name="email" required class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Phone</label>
            <input type="text" name="phone" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Password</label>
            <input type="password" name="password" required minlength="8" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Status</label>
            <select name="status" class="mt-1 w-full border rounded-lg px-3 py-2">
                <option value="<?= RESTAURANT_STATUS_ACTIVE ?>" selected>Active</option>
                <option value="<?= RESTAURANT_STATUS_PENDING ?>">Pending</option>
                <option value="<?= RESTAURANT_STATUS_SUSPENDED ?>">Suspended</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium">Subscription Plan</label>
            <select name="plan_id" required class="mt-1 w-full border rounded-lg px-3 py-2">
                <option value="">Select plan</option>
                <?php foreach ($allPlans as $plan): ?>
                    <option value="<?= (int)$plan['id'] ?>">
                        <?= e($plan['name']) ?> — ₹<?= e(number_format($plan['price'], 2)) ?> / <?= e(strtolower($plan['billing_cycle'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Create Restaurant</button>
        </div>
    </form>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>