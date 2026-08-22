<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';
require_once INCLUDES_PATH . '/security.php';
require_once INCLUDES_PATH . '/validation.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Food Management';
$activePage = 'foods';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $name = sanitize_text($_POST['name'] ?? '', 150);
        $categoryId = validate_int($_POST['category_id'] ?? 0, 1);
        $cuisineId = validate_int($_POST['cuisine_id'] ?? 0, 1);
        $description = sanitize_text($_POST['description'] ?? '', 1000);
        $price = validate_price($_POST['price'] ?? 0);
        $discountPrice = isset($_POST['discount_price']) && $_POST['discount_price'] !== '' ? validate_price($_POST['discount_price']) : null;
        $foodType = validate_food_type($_POST['food_type'] ?? 'veg');
        $spicyLevel = validate_int($_POST['spicy_level'] ?? 0, 0, 3);
        $preparationTime = validate_int($_POST['preparation_time'] ?? 15, 1, 180);
        $isAvailable = (int)($_POST['is_available'] ?? 1);
        $isFeatured = (int)($_POST['is_featured'] ?? 0);
        $isBestSeller = (int)($_POST['is_best_seller'] ?? 0);
        $isChefChoice = (int)($_POST['is_chef_choice'] ?? 0);
        $isSignature = (int)($_POST['is_signature'] ?? 0);
        $tags = sanitize_text($_POST['tags'] ?? '', 255);

        if (!$name || !$categoryId || !$cuisineId || $price === null || !$foodType) {
            $_SESSION['flash'] = 'Invalid food data.';
        } else {
            $slug = slugify($name);
            $imagePath = $_POST['existing_image'] ?? null;
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploaded = handle_food_image_upload($_FILES['image']);
                if ($uploaded) {
                    if ($imagePath) delete_food_image($imagePath);
                    $imagePath = $uploaded;
                }
            }

            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO foods (restaurant_id, category_id, cuisine_id, name, slug, description, price, discount_price, image, food_type, spicy_level, preparation_time, is_available, is_featured, is_best_seller, is_chef_choice, is_signature, tags)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$restaurantId, $categoryId, $cuisineId, $name, $slug, $description, $price, $discountPrice, $imagePath, $foodType, $spicyLevel, $preparationTime, $isAvailable, $isFeatured, $isBestSeller, $isChefChoice, $isSignature, $tags]);
                add_audit_log($pdo, $restaurantId, $user['id'], 'created food', 'food', (int)$pdo->lastInsertId());
                $_SESSION['flash'] = 'Food created.';
            } else {
                $id = validate_int($_POST['id'] ?? 0, 1);
                $stmt = $pdo->prepare("UPDATE foods SET category_id=?, cuisine_id=?, name=?, slug=?, description=?, price=?, discount_price=?, image=?, food_type=?, spicy_level=?, preparation_time=?, is_available=?, is_featured=?, is_best_seller=?, is_chef_choice=?, is_signature=?, tags=? WHERE id=? AND restaurant_id=?");
                $stmt->execute([$categoryId, $cuisineId, $name, $slug, $description, $price, $discountPrice, $imagePath, $foodType, $spicyLevel, $preparationTime, $isAvailable, $isFeatured, $isBestSeller, $isChefChoice, $isSignature, $tags, $id, $restaurantId]);
                $_SESSION['flash'] = 'Food updated.';
            }
            redirect('/dinex/admin/owner/foods.php');
        }
    } elseif ($action === 'toggle_available') {
        $id = validate_int($_POST['id'] ?? 0, 1);
        $pdo->prepare("UPDATE foods SET is_available=1-is_available WHERE id=? AND restaurant_id=?")->execute([$id, $restaurantId]);
        redirect('/dinex/admin/owner/foods.php');
    }
}

$foods = $pdo->prepare("SELECT f.*, c.name AS category_name, cu.name AS cuisine_name FROM foods f
                        LEFT JOIN categories c ON c.id=f.category_id
                        LEFT JOIN cuisines cu ON cu.id=f.cuisine_id
                        WHERE f.restaurant_id=? ORDER BY f.id DESC");
$foods->execute([$restaurantId]);
$foods = $foods->fetchAll();
$categories = $pdo->prepare("SELECT * FROM categories WHERE restaurant_id=? AND is_active=1 ORDER BY display_order");
$categories->execute([$restaurantId]);
$categories = $categories->fetchAll();
$cuisines = $pdo->prepare("SELECT * FROM cuisines WHERE restaurant_id=? AND is_active=1 ORDER BY name");
$cuisines->execute([$restaurantId]);
$cuisines = $cuisines->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Food Management</h1>

<!-- Create Form -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h2 class="font-bold text-lg mb-4">Add Food</h2>
    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <input type="text" name="name" required placeholder="Food Name" class="border rounded-lg px-4 py-2">
        <select name="category_id" required class="border rounded-lg px-4 py-2">
            <?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
        <select name="cuisine_id" required class="border rounded-lg px-4 py-2">
            <?php foreach ($cuisines as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
        <textarea name="description" placeholder="Description" class="border rounded-lg px-4 py-2 md:col-span-2"></textarea>
        <input type="number" step="0.01" name="price" required placeholder="Price" class="border rounded-lg px-4 py-2">
        <input type="number" step="0.01" name="discount_price" placeholder="Discount Price (optional)" class="border rounded-lg px-4 py-2">
        <select name="food_type" class="border rounded-lg px-4 py-2">
            <option value="veg">Veg</option><option value="non_veg">Non-Veg</option><option value="egg">Egg</option>
        </select>
        <select name="spicy_level" class="border rounded-lg px-4 py-2">
            <option value="0">0 🌶</option><option value="1">1 🌶</option><option value="2">2 🌶</option><option value="3">3 🌶</option>
        </select>
        <input type="number" name="preparation_time" value="15" min="1" class="border rounded-lg px-4 py-2">
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="border rounded-lg px-4 py-2">
        <input type="text" name="tags" placeholder="Tags comma separated" class="border rounded-lg px-4 py-2">
        <div class="flex items-center gap-2 md:col-span-2">
            <label class="text-sm"><input type="checkbox" name="is_available" value="1" checked> Available</label>
            <label class="text-sm"><input type="checkbox" name="is_featured" value="1"> Featured</label>
            <label class="text-sm"><input type="checkbox" name="is_best_seller" value="1"> Best Seller</label>
            <label class="text-sm"><input type="checkbox" name="is_chef_choice" value="1"> Chef's Choice</label>
            <label class="text-sm"><input type="checkbox" name="is_signature" value="1"> Signature</label>
        </div>
        <button class="bg-slate-900 text-white px-6 py-2 rounded-lg md:col-span-3">Add Food</button>
    </form>
</div>

<!-- Food List -->
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">ID</th><th>Name</th><th>Category</th><th>Cuisine</th><th>Price</th><th>Type</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($foods as $f): ?>
                <tr class="border-t">
                    <td class="p-3"><?= (int)$f['id'] ?></td>
                    <td><?= e($f['name']) ?></td>
                    <td><?= e($f['category_name']) ?></td>
                    <td><?= e($f['cuisine_name']) ?></td>
                    <td>₹<?= number_format($f['price'], 2) ?></td>
                    <td><?= strtoupper(e($f['food_type'])) ?></td>
                    <td><?= $f['is_available'] ? 'Available' : 'Unavailable' ?></td>
                    <td>
                        <form method="POST" data-confirm="Toggle availability?" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle_available">
                            <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                            <button class="text-blue-600 hover:underline text-sm">Toggle</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>