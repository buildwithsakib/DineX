<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['super_admin']);
$pageTitle = 'Super Admin Dashboard';
$activePage = 'dashboard';

// Aggregate stats
$totalRestaurants = (int)$pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
$totalOwners = (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM restaurant_staff WHERE role_id = 2")->fetchColumn();
$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Super Admin Dashboard</h1>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center gap-4">
            <div class="bg-slate-900 text-white rounded-full h-14 w-14 flex items-center justify-center"><i class="fas fa-utensils"></i></div>
            <div>
                <p class="text-2xl font-bold"><?= $totalRestaurants ?></p>
                <p class="text-gray-500">Restaurants</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center gap-4">
            <div class="bg-slate-900 text-white rounded-full h-14 w-14 flex items-center justify-center"><i class="fas fa-user-tie"></i></div>
            <div>
                <p class="text-2xl font-bold"><?= $totalOwners ?></p>
                <p class="text-gray-500">Owners</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center gap-4">
            <div class="bg-slate-900 text-white rounded-full h-14 w-14 flex items-center justify-center"><i class="fas fa-receipt"></i></div>
            <div>
                <p class="text-2xl font-bold"><?= $totalOrders ?></p>
                <p class="text-gray-500">Orders</p>
            </div>
        </div>
    </div>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>