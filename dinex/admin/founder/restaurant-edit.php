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
        $address = sanitize_input($_POST['address'] ?? '');
        $city = sanitize_input($_POST['city'] ?? '');
        $state = sanitize_input($_POST['state'] ?? '');
        $postalCode = sanitize_input($_POST['postal_code'] ?? '');
        $country = sanitize_input($_POST['country'] ?? 'India');
        $description = sanitize_input($_POST['description'] ?? '');
        $status = sanitize_input($_POST['status'] ?? $restaurant['status']);

        $fieldErrors = validate_required($_POST, ['name', 'owner_name', 'email']);
        if ($fieldErrors) {
            $errors = array_merge($errors, array_values($fieldErrors));
        }
        if (!validate_email($email)) {
            $errors[] = 'Invalid email address.';
        }
        if (!in_array($status, [RESTAURANT_STATUS_PENDING, RESTAURANT_STATUS_ACTIVE, RESTAURANT_STATUS_SUSPENDED, RESTAURANT_STATUS_CANCELLED])) {
            $errors[] = 'Invalid status.';
        }

        if (!$errors) {
            $stmt = $pdo->prepare('
                UPDATE restaurants SET
                    name = :name,
                    owner_name = :owner_name,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    city = :city,
                    state = :state,
                    postal_code = :postal_code,
                    country = :country,
                    description = :description,
                    status = :status
                WHERE id = :id
            ');
            $stmt->execute([
                ':name' => $name,
                ':owner_name' => $ownerName,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address,
                ':city' => $city,
                ':state' => $state,
                ':postal_code' => $postalCode,
                ':country' => $country,
                ':description' => $description,
                ':status' => $status,
                ':id' => $id,
            ]);

            audit_log('FOUNDER', $founder['id'], $id, 'Restaurant updated by founder', ['status' => $status]);
            $success = true;
            // Refresh data
            $stmt = $pdo->prepare('SELECT * FROM restaurants WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $restaurant = $stmt->fetch();
        }
    }
}

$pageTitle = 'Edit Restaurant';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <a href="restaurant-view.php?id=<?= (int)$restaurant['id'] ?>" class="text-sm text-gray-500 hover:underline"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <h1 class="text-3xl font-bold text-gray-900 mt-4">Edit Restaurant</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Changes saved.</div>
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
            <input type="text" name="name" required value="<?= e($restaurant['name']) ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Owner Name</label>
            <input type="text" name="owner_name" required value="<?= e($restaurant['owner_name']) ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input type="email" name="email" required value="<?= e($restaurant['email']) ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Phone</label>
            <input type="text" name="phone" value="<?= e($restaurant['phone'] ?? '') ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium">Address</label>
            <textarea name="address" class="mt-1 w-full border rounded-lg px-3 py-2"><?= e($restaurant['address'] ?? '') ?></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium">City</label>
            <input type="text" name="city" value="<?= e($restaurant['city'] ?? '') ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">State</label>
            <input type="text" name="state" value="<?= e($restaurant['state'] ?? '') ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Postal Code</label>
            <input type="text" name="postal_code" value="<?= e($restaurant['postal_code'] ?? '') ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Country</label>
            <input type="text" name="country" value="<?= e($restaurant['country'] ?? 'India') ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium">Description</label>
            <textarea name="description" class="mt-1 w-full border rounded-lg px-3 py-2"><?= e($restaurant['description'] ?? '') ?></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium">Status</label>
            <select name="status" class="mt-1 w-full border rounded-lg px-3 py-2">
                <?php foreach ([RESTAURANT_STATUS_PENDING, RESTAURANT_STATUS_ACTIVE, RESTAURANT_STATUS_SUSPENDED, RESTAURANT_STATUS_CANCELLED] as $s): ?>
                    <option value="<?= e($s) ?>" <?= $restaurant['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Save Changes</button>
        </div>
    </form>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>