<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';  
require_once __DIR__ . '/../../includes/csrf.php'; 
require_once __DIR__ . '/../../includes/validation.php';   // <-- Add this


$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

$stmt = $pdo->prepare('SELECT * FROM restaurants WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $restaurantId]);
$restaurant = $stmt->fetch();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $ownerName = sanitize_input($_POST['owner_name'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $address = sanitize_input($_POST['address'] ?? '');
        $city = sanitize_input($_POST['city'] ?? '');
        $state = sanitize_input($_POST['state'] ?? '');
        $postalCode = sanitize_input($_POST['postal_code'] ?? '');
        $country = sanitize_input($_POST['country'] ?? 'India');
        $description = sanitize_input($_POST['description'] ?? '');

        $fieldErrors = validate_required($_POST, ['name', 'owner_name']);
        if ($fieldErrors) {
            $errors = array_merge($errors, array_values($fieldErrors));
        }
        if (!$errors) {
            $stmt = $pdo->prepare('UPDATE restaurants SET name=:name, owner_name=:owner_name, phone=:phone, address=:address, city=:city, state=:state, postal_code=:postal_code, country=:country, description=:description WHERE id=:id');
            $stmt->execute([
                ':name' => $name,
                ':owner_name' => $ownerName,
                ':phone' => $phone,
                ':address' => $address,
                ':city' => $city,
                ':state' => $state,
                ':postal_code' => $postalCode,
                ':country' => $country,
                ':description' => $description,
                ':id' => $restaurantId,
            ]);
            audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Restaurant profile updated');
            $success = true;
            // Refresh
            $stmt = $pdo->prepare('SELECT * FROM restaurants WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $restaurantId]);
            $restaurant = $stmt->fetch();
        }
    }
}

$pageTitle = 'Restaurant Profile';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Restaurant Profile</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Profile updated.</div>
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
            <label class="block text-sm font-medium">Phone</label>
            <input type="text" name="phone" value="<?= e($restaurant['phone'] ?? '') ?>" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
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
        <div class="md:col-span-2">
            <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Save</button>
        </div>
    </form>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>