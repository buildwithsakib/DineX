<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-session.php';
require_once __DIR__ . '/../includes/feature-access.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    die('Invalid link.');
}

$sessionInfo = get_or_create_table_session($token);
if (!$sessionInfo['success']) {
    die($sessionInfo['message']);
}

// Check if restaurant has QR ordering feature
if (!restaurant_has_feature($sessionInfo['restaurant_id'], FEATURE_QR_ORDERING)) {
    die('QR ordering is not available for this restaurant.');
}

// Fetch menu data
$pdo = db();
$categories = $pdo->query("SELECT * FROM categories WHERE restaurant_id = {$sessionInfo['restaurant_id']} AND is_active = 1 ORDER BY name ASC")->fetchAll();
$cuisines = $pdo->query("SELECT * FROM cuisines WHERE restaurant_id = {$sessionInfo['restaurant_id']} AND is_active = 1 ORDER BY name ASC")->fetchAll();

// Default list all available foods
$foods = $pdo->query("
    SELECT f.*, c.name AS category_name, cu.name AS cuisine_name
    FROM foods f
    LEFT JOIN categories c ON c.id = f.category_id
    LEFT JOIN cuisines cu ON cu.id = f.cuisine_id
    WHERE f.restaurant_id = {$sessionInfo['restaurant_id']} AND f.is_available = 1
    ORDER BY f.name ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu — <?= e($sessionInfo['restaurant_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900"><?= e($sessionInfo['restaurant_name']) ?></h1>
                <p class="text-xs text-gray-500">Table <?= e($sessionInfo['table_number']) ?></p>
            </div>
            <a href="cart.php" class="relative bg-orange-600 text-white px-4 py-2 rounded-lg">
                <i class="fa-solid fa-cart-shopping"></i>
                <span id="cartCount" class="absolute -top-2 -right-2 bg-red-600 text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
            </a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row gap-4">
            <input type="text" id="searchInput" placeholder="Search foods..." class="border rounded-lg px-4 py-2 flex-1">
            <select id="categoryFilter" class="border rounded-lg px-4 py-2">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="cuisineFilter" class="border rounded-lg px-4 py-2">
                <option value="">All Cuisines</option>
                <?php foreach ($cuisines as $cuisine): ?>
                    <option value="<?= (int)$cuisine['id'] ?>"><?= e($cuisine['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="typeFilter" class="border rounded-lg px-4 py-2">
                <option value="">All Types</option>
                <option value="VEG">Veg</option>
                <option value="NON_VEG">Non-Veg</option>
                <option value="EGG">Egg</option>
                <option value="VEGAN">Vegan</option>
            </select>
        </div>

        <div id="foodList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            <?php foreach ($foods as $food): ?>
                <div class="bg-white rounded-xl shadow p-4 flex flex-col" data-category="<?= (int)$food['category_id'] ?>" data-cuisine="<?= (int)$food['cuisine_id'] ?>" data-type="<?= e($food['food_type']) ?>" data-name="<?= e(strtolower($food['name'])) ?>">
                    <div class="flex justify-between">
                        <h3 class="font-semibold"><?= e($food['name']) ?></h3>
                        <span class="text-xs px-2 py-1 rounded <?= $food['food_type'] === 'VEG' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"><?= e($food['food_type']) ?></span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1"><?= e($food['description'] ?? '') ?></p>
                    <div class="mt-auto pt-3 flex items-center justify-between">
                        <span class="font-bold">₹<?= e(number_format($food['price'], 2)) ?></span>
                        <button onclick="addToCart(<?= (int)$food['id'] ?>, '<?= e($food['name']) ?>', <?= (float)$food['price'] ?>)" class="bg-orange-600 text-white px-3 py-1 rounded">Add</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Cart stored in session via JS and backend? We'll store in localStorage for demo, but for real backend we need API. Simpler: use session via PHP? We'll use client-side localStorage for now; checkout will send cart data to server. -->
    <script>
        // Simple cart in localStorage
        let cart = JSON.parse(localStorage.getItem('dinex_cart') || '{}');
        function updateCartCount() {
            const count = Object.values(cart).reduce((a,b) => a + b.qty, 0);
            document.getElementById('cartCount').textContent = count;
        }
        function addToCart(id, name, price) {
            if (cart[id]) {
                cart[id].qty++;
            } else {
                cart[id] = {id, name, price, qty:1};
            }
            localStorage.setItem('dinex_cart', JSON.stringify(cart));
            updateCartCount();
        }
        updateCartCount();
        // Filtering
        function applyFilters() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const cat = document.getElementById('categoryFilter').value;
            const cuis = document.getElementById('cuisineFilter').value;
            const type = document.getElementById('typeFilter').value;
            document.querySelectorAll('#foodList > div').forEach(card => {
                const name = card.dataset.name;
                const category = card.dataset.category;
                const cuisine = card.dataset.cuisine;
                const foodType = card.dataset.type;
                let show = true;
                if (search && !name.includes(search)) show = false;
                if (cat && category !== cat) show = false;
                if (cuis && cuisine !== cuis) show = false;
                if (type && foodType !== type) show = false;
                card.style.display = show ? '' : 'none';
            });
        }
        document.getElementById('searchInput').addEventListener('input', applyFilters);
        document.getElementById('categoryFilter').addEventListener('change', applyFilters);
        document.getElementById('cuisineFilter').addEventListener('change', applyFilters);
        document.getElementById('typeFilter').addEventListener('change', applyFilters);
    </script>
</body>
</html>