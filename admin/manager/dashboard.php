<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['manager']);
require_permission($pdo, $user, 'dashboard');

$restaurantId = $user['restaurant_id'];
$pageTitle = 'Manager Dashboard';
$activePage = 'dashboard';

$todayOrders = (int)$pdo->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id=? AND DATE(created_at)=?")->execute([$restaurantId, date('Y-m-d')]) ?: 0;
require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Manager Dashboard</h1>
<div class="bg-white rounded-xl shadow-md p-6"><p class="text-3xl font-bold"><?= $todayOrders ?></p><p class="text-gray-500">Today's Orders</p></div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>