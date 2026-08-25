<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Founder') ?> — DineX Founder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <aside class="w-64 bg-gray-900 text-white flex-shrink-0 min-h-screen">
        <div class="p-5 border-b border-gray-800">
            <h1 class="text-2xl font-bold text-orange-500">DineX</h1>
            <p class="text-xs text-gray-400">Founder Panel</p>
        </div>
        <nav class="p-4 space-y-1">
            <a href="<?= BASE_URL ?>/admin/founder/dashboard.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800 <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-gray-800' : '' ?>"><i class="fa-solid fa-gauge mr-2"></i>Dashboard</a>
            <a href="<?= BASE_URL ?>/admin/founder/restaurants.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800"><i class="fa-solid fa-utensils mr-2"></i>Restaurants</a>
            <a href="<?= BASE_URL ?>/admin/founder/subscriptions.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800"><i class="fa-solid fa-calendar-check mr-2"></i>Subscriptions</a>
            <a href="<?= BASE_URL ?>/admin/founder/plans.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800"><i class="fa-solid fa-box mr-2"></i>Plans</a>
            <a href="<?= BASE_URL ?>/admin/founder/features.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800"><i class="fa-solid fa-toggle-on mr-2"></i>Features</a>
            <a href="<?= BASE_URL ?>/admin/founder/payments.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800"><i class="fa-solid fa-credit-card mr-2"></i>Payments</a>
            <a href="<?= BASE_URL ?>/admin/founder/analytics.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800"><i class="fa-solid fa-chart-line mr-2"></i>Analytics</a>
            <a href="<?= BASE_URL ?>/admin/founder/audit-logs.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800"><i class="fa-solid fa-clipboard-list mr-2"></i>Audit Logs</a>
            <a href="<?= BASE_URL ?>/admin/founder/settings.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800"><i class="fa-solid fa-cog mr-2"></i>Settings</a>
            <div class="pt-4 mt-4 border-t border-gray-800">
                <a href="<?= BASE_URL ?>/admin/founder/logout.php" class="block px-4 py-2 rounded-lg hover:bg-red-700 text-red-300"><i class="fa-solid fa-right-from-bracket mr-2"></i>Logout</a>
            </div>
        </nav>
    </aside>
    <div class="flex-1 flex flex-col">