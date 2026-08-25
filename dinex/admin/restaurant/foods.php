<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';

$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

$errors = [];
$success = false;
$editFood = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM foods WHERE id = :id AND restaurant_id = :rid');
            $stmt->execute([':id' => $id, ':rid' => $restaurantId]);
            audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Food deleted', ['id' => $id]);
            $success = true;
        } else {
            // Add or update
            $id = (int)($_POST['id'] ?? 0);
            $name = sanitize_input($_POST['name'] ?? '');
            $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
            $cuisineId = (int)($_POST['cuisine_id'] ?? 0) ?: null;
            $description = sanitize_input($_POST['description'] ?? '');
            $foodType = sanitize_input($_POST['food_type'] ?? 'VEG');
            $price = sanitize_input($_POST['price'] ?? '');
            $taxRate = sanitize_input($_POST['tax_rate'] ?? '5.00');
            $isAvailable = isset($_POST['is_available']) ? 1 : 0;
            $isChefSpecial = isset($_POST['is_chef_special']) ? 1 : 0;
            $isSignature = isset($_POST['is_signature']) ? 1 : 0;
            $isBestSeller = isset($_POST['is_best_seller']) ? 1 : 0;
            $isTrending = isset($_POST['is_trending']) ? 1 : 0;

            $fieldErrors = validate_required($_POST, ['name', 'price']);
            if ($fieldErrors) {
                $errors = array_merge($errors, array_values($fieldErrors));
            }
            if (!in_array($foodType, ['VEG','NON_VEG','EGG','VEGAN'])) {
                $errors[] = 'Invalid food type.';
            }
            if (!is_numeric($price) || $price < 0) {
                $errors[] = 'Price must be a positive number.';
            }
            if (!is_numeric($taxRate) || $taxRate < 0 || $taxRate > 100) {
                $errors[] = 'Tax rate must be between 0 and 100.';
            }

            if (!$errors) {
                $slug = slugify($name);
                if ($id > 0) {
                    // Update
                    $stmt = $pdo->prepare('
                        UPDATE foods SET
                            name=:name, slug=:slug, category_id=:category_id, cuisine_id=:cuisine_id,
                            description=:description, food_type=:food_type, price=:price, tax_rate=:tax_rate,
                            is_available=:is_available, is_chef_special=:is_chef_special, is_signature=:is_signature,
                            is_best_seller=:is_best_seller, is_trending=:is_trending
                        WHERE id=:id AND restaurant_id=:rid
                    ');
                    $stmt->execute([
                        ':name'=>$name, ':slug'=>$slug, ':category_id'=>$categoryId, ':cuisine_id'=>$cuisineId,
                        ':description'=>$description, ':food_type'=>$foodType, ':price'=>$price, ':tax_rate'=>$taxRate,
                        ':is_available'=>$isAvailable, ':is_chef_special'=>$isChefSpecial, ':is_signature'=>$isSignature,
                        ':is_best_seller'=>$isBestSeller, ':is_trending'=>$isTrending, ':id'=>$id, ':rid'=>$restaurantId
                    ]);
                    audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Food updated', ['id'=>$id]);
                } else {
                    $stmt = $pdo->prepare('
                        INSERT INTO foods (restaurant_id, name, slug, category_id, cuisine_id, description, food_type, price, tax_rate,
                            is_available, is_chef_special, is_signature, is_best_seller, is_trending)
                        VALUES (:rid, :name, :slug, :category_id, :cuisine_id, :description, :food_type, :price, :tax_rate,
                            :is_available, :is_chef_special, :is_signature, :is_best_seller, :is_trending)
                    ');
                    $stmt->execute([
                        ':rid'=>$restaurantId, ':name'=>$name, ':slug'=>$slug, ':category_id'=>$categoryId, ':cuisine_id'=>$cuisineId,
                        ':description'=>$description, ':food_type'=>$foodType, ':price'=>$price, ':tax_rate'=>$taxRate,
                        ':is_available'=>$isAvailable, ':is_chef_special'=>$isChefSpecial, ':is_signature'=>$isSignature,
                        ':is_best_seller'=>$isBestSeller, ':is_trending'=>$isTrending
                    ]);
                    audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Food added', ['name'=>$name]);
                }
                $success = true;
                // Clear edit mode
                $editFood = null;
            }
        }
    }
}

// Handle edit GET
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM foods WHERE id = :id AND restaurant_id = :rid LIMIT 1');
    $stmt->execute([':id'=>$editId, ':rid'=>$restaurantId]);
    $editFood = $stmt->fetch();
    if (!$editFood) {
        redirect(BASE_URL . '/admin/restaurant/foods.php');
    }
}

$categories = $pdo->query("SELECT * FROM categories WHERE restaurant_id = $restaurantId ORDER BY name ASC")->fetchAll();
$cuisines = $pdo->query("SELECT * FROM cuisines WHERE restaurant_id = $restaurantId ORDER BY name ASC")->fetchAll();
$foods = $pdo->query("
    SELECT f.*, c.name AS category_name, cu.name AS cuisine_name
    FROM foods f
    LEFT JOIN categories c ON c.id = f.category_id
    LEFT JOIN cuisines cu ON cu.id = f.cuisine_id
    WHERE f.restaurant_id = $restaurantId
    ORDER BY f.name ASC
")->fetchAll();

$pageTitle = 'Foods';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Foods</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Food saved.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold"><?= $editFood ? 'Edit Food' : 'Add Food' ?></h2>
            <form method="POST" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= $editFood ? (int)$editFood['id'] : 0 ?>">
                <div>
                    <label class="block text-sm">Name</label>
                    <input type="text" name="name" required value="<?= $editFood ? e($editFood['name']) : '' ?>" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Category</label>
                    <select name="category_id" class="mt-1 w-full border rounded px-3 py-2">
                        <option value="">None</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= $editFood && $editFood['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm">Cuisine</label>
                    <select name="cuisine_id" class="mt-1 w-full border rounded px-3 py-2">
                        <option value="">None</option>
                        <?php foreach ($cuisines as $cuisine): ?>
                            <option value="<?= (int)$cuisine['id'] ?>" <?= $editFood && $editFood['cuisine_id'] == $cuisine['id'] ? 'selected' : '' ?>><?= e($cuisine['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm">Description</label>
                    <textarea name="description" class="mt-1 w-full border rounded px-3 py-2"><?= $editFood ? e($editFood['description']) : '' ?></textarea>
                </div>
                <div>
                    <label class="block text-sm">Food Type</label>
                    <select name="food_type" class="mt-1 w-full border rounded px-3 py-2">
                        <?php foreach (['VEG','NON_VEG','EGG','VEGAN'] as $type): ?>
                            <option value="<?= $type ?>" <?= $editFood && $editFood['food_type'] === $type ? 'selected' : '' ?>><?= $type ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm">Price (₹)</label>
                    <input type="number" step="0.01" name="price" required value="<?= $editFood ? e($editFood['price']) : '' ?>" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Tax Rate (%)</label>
                    <input type="number" step="0.01" name="tax_rate" value="<?= $editFood ? e($editFood['tax_rate']) : '5.00' ?>" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_available" <?= $editFood && $editFood['is_available'] ? 'checked' : '' ?>> Available</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_chef_special" <?= $editFood && $editFood['is_chef_special'] ? 'checked' : '' ?>> Chef Special</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_signature" <?= $editFood && $editFood['is_signature'] ? 'checked' : '' ?>> Signature</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_best_seller" <?= $editFood && $editFood['is_best_seller'] ? 'checked' : '' ?>> Best Seller</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_trending" <?= $editFood && $editFood['is_trending'] ? 'checked' : '' ?>> Trending</label>
                </div>
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded"><?= $editFood ? 'Update' : 'Add' ?></button>
                <?php if ($editFood): ?><a href="foods.php" class="ml-2 text-gray-500">Cancel</a><?php endif; ?>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-3">Name</th><th class="px-6 py-3">Category</th><th class="px-6 py-3">Cuisine</th>
                        <th class="px-6 py-3">Type</th><th class="px-6 py-3">Price</th><th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($foods as $food): ?>
                    <tr>
                        <td class="px-6 py-3 font-medium"><?= e($food['name']) ?></td>
                        <td class="px-6 py-3"><?= e($food['category_name'] ?? '—') ?></td>
                        <td class="px-6 py-3"><?= e($food['cuisine_name'] ?? '—') ?></td>
                        <td class="px-6 py-3"><span class="px-2 py-1 text-xs rounded <?= $food['food_type'] === 'VEG' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"><?= e($food['food_type']) ?></span></td>
                        <td class="px-6 py-3">₹<?= e(number_format($food['price'], 2)) ?></td>
                        <td class="px-6 py-3">
                            <a href="foods.php?edit=<?= (int)$food['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$food['id'] ?>">
                                <button type="submit" class="ml-2 text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>