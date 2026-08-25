<?php
// This file expects $staff and $pageTitle
$currentRestaurantId = $staff['restaurant_id'] ?? ($_SESSION['restaurant_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — DineX Restaurant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <aside class="w-64 bg-white border-r flex-shrink-0 min-h-screen">
        <div class="p-5 border-b">
            <h1 class="text-2xl font-bold text-orange-600">DineX</h1>
            <p class="text-xs text-gray-500"><?= e($_SESSION['restaurant_name'] ?? '') ?></p>
        </div>
        <nav class="p-4 space-y-1">
            <a href="<?= BASE_URL ?>/admin/restaurant/dashboard.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-orange-100 text-orange-700' : 'text-gray-700' ?>"><i class="fa-solid fa-gauge mr-2"></i>Dashboard</a>
            <?php if (has_permission($staff['role'], 'restaurant')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/restaurant.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-store mr-2"></i>Restaurant</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'tables')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/tables.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-chair mr-2"></i>Tables</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'qr')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/qr.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-qrcode mr-2"></i>QR Codes</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'categories')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/categories.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-tags mr-2"></i>Categories</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'cuisines')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/cuisines.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-earth-americas mr-2"></i>Cuisines</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'foods')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/foods.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-pizza-slice mr-2"></i>Foods</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'orders')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/orders.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-receipt mr-2"></i>Orders</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'kitchen') && restaurant_has_feature($currentRestaurantId, FEATURE_KITCHEN)): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/kitchen.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-fire-burner mr-2"></i>Kitchen</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'games') && restaurant_has_feature($currentRestaurantId, FEATURE_GAMES)): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/games.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-gamepad mr-2"></i>Games</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'rewards') && restaurant_has_feature($currentRestaurantId, FEATURE_GAMES)): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/rewards.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-gift mr-2"></i>Rewards</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'coupons') && restaurant_has_feature($currentRestaurantId, FEATURE_COUPONS)): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/coupons.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-ticket mr-2"></i>Coupons</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'campaigns') && restaurant_has_feature($currentRestaurantId, FEATURE_CAMPAIGNS)): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/campaigns.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-bullhorn mr-2"></i>Campaigns</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'reviews')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/reviews.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-star mr-2"></i>Reviews</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'analytics')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/analytics.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-chart-line mr-2"></i>Analytics</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'staff')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/staff.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-users mr-2"></i>Staff</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'billing')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/billing.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-file-invoice-dollar mr-2"></i>Billing</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'payments')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/payments.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-credit-card mr-2"></i>Payments</a>
            <?php endif; ?>
            <?php if (has_permission($staff['role'], 'settings')): ?>
                <a href="<?= BASE_URL ?>/admin/restaurant/settings.php" class="block px-4 py-2 rounded-lg hover:bg-orange-50 text-gray-700"><i class="fa-solid fa-cog mr-2"></i>Settings</a>
            <?php endif; ?>
            <div class="pt-4 mt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/restaurant/logout.php" class="block px-4 py-2 rounded-lg hover:bg-red-50 text-red-600"><i class="fa-solid fa-right-from-bracket mr-2"></i>Logout</a>
            </div>
        </nav>
    </aside>
    <div class="flex-1 flex flex-col">