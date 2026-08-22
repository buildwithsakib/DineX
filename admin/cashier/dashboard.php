<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';

$user = require_role($pdo, ['cashier']);
require_permission($pdo, $user, 'dashboard');
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Cashier Dashboard';
$activePage = 'dashboard';

$pending = (int)$pdo->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id=? AND status IN ('READY','SERVED')")->execute([$restaurantId]) ?: 0;
require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Cashier Dashboard</h1>
<div class="bg-white rounded-xl shadow-md p-6"><p class="text-3xl font-bold"><?= $pending ?></p><p class="text-gray-500">Orders Ready for Billing</p></div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>