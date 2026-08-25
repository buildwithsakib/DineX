<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';   // <-- Add
require_once __DIR__ . '/../../includes/csrf.php';

$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'update_status') {
            $orderId = (int)($_POST['order_id'] ?? 0);
            $newStatus = sanitize_input($_POST['status'] ?? '');
            $allowedStatuses = [ORDER_STATUS_PLACED, ORDER_STATUS_ACCEPTED, ORDER_STATUS_PREPARING, ORDER_STATUS_READY, ORDER_STATUS_SERVED, ORDER_STATUS_COMPLETED, ORDER_STATUS_CANCELLED];
            if (!in_array($newStatus, $allowedStatuses)) {
                $errors[] = 'Invalid status.';
            } else {
                // Verify order belongs to restaurant
                $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id AND restaurant_id = :rid LIMIT 1');
                $stmt->execute([':id'=>$orderId, ':rid'=>$restaurantId]);
                $order = $stmt->fetch();
                if (!$order) {
                    $errors[] = 'Order not found or unauthorized.';
                } else {
                    $update = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
                    $update->execute([':status'=>$newStatus, ':id'=>$orderId]);
                    // Insert status history
                    $hist = $pdo->prepare('INSERT INTO order_status_history (order_id, status) VALUES (:oid, :status)');
                    $hist->execute([':oid'=>$orderId, ':status'=>$newStatus]);
                    audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Order status updated', ['order_id'=>$orderId, 'status'=>$newStatus]);
                    $success = true;
                }
            }
        }
    }
}

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$query = '
    SELECT o.*, t.table_number
    FROM orders o
    JOIN tables t ON t.id = o.table_id
    WHERE o.restaurant_id = :rid
';
$params = [':rid'=>$restaurantId];
if ($statusFilter) {
    $query .= ' AND o.status = :status';
    $params[':status'] = $statusFilter;
}
if ($search) {
    $query .= ' AND (o.order_number LIKE :q OR t.table_number LIKE :q)';
    $params[':q'] = "%$search%";
}
$query .= ' ORDER BY o.id DESC LIMIT 100';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$pageTitle = 'Orders';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Orders</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Order updated.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="mt-6 bg-white rounded-xl shadow p-4 flex flex-col md:flex-row gap-4">
        <form method="GET" class="flex gap-4 flex-1">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search order # or table..." class="border rounded px-3 py-2 flex-1">
            <select name="status" class="border rounded px-3 py-2">
                <option value="">All Statuses</option>
                <?php foreach ([ORDER_STATUS_PLACED,ORDER_STATUS_ACCEPTED,ORDER_STATUS_PREPARING,ORDER_STATUS_READY,ORDER_STATUS_SERVED,ORDER_STATUS_COMPLETED,ORDER_STATUS_CANCELLED] as $s): ?>
                    <option value="<?= e($s) ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded">Filter</button>
        </form>
    </div>

    <div class="mt-6 bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3">Order #</th><th class="px-6 py-3">Table</th><th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Items</th><th class="px-6 py-3">Total</th><th class="px-6 py-3">Placed</th><th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td class="px-6 py-3"><?= e($order['order_number']) ?></td>
                    <td class="px-6 py-3"><?= e($order['table_number']) ?></td>
                    <td class="px-6 py-3">
                        <form method="POST" class="inline-flex gap-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                            <select name="status" class="border rounded px-2 py-1 text-sm">
                                <?php foreach ([ORDER_STATUS_PLACED,ORDER_STATUS_ACCEPTED,ORDER_STATUS_PREPARING,ORDER_STATUS_READY,ORDER_STATUS_SERVED,ORDER_STATUS_COMPLETED,ORDER_STATUS_CANCELLED] as $s): ?>
                                    <option value="<?= e($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="text-xs bg-blue-600 text-white px-2 py-1 rounded">Update</button>
                        </form>
                    </td>
                    <td class="px-6 py-3">
                        <?php
                        $items = $pdo->prepare('SELECT oi.quantity, f.name FROM order_items oi JOIN foods f ON f.id = oi.food_id WHERE oi.order_id = :oid');
                        $items->execute([':oid'=>$order['id']]);
                        $itemList = $items->fetchAll();
                        foreach ($itemList as $i) {
                            echo e($i['quantity']) . 'x ' . e($i['name']) . '<br>';
                        }
                        ?>
                    </td>
                    <td class="px-6 py-3">₹<?= number_format($order['total_amount'], 2) ?></td>
                    <td class="px-6 py-3 text-sm"><?= e(date('d M H:i', strtotime($order['created_at']))) ?></td>
                    <td class="px-6 py-3">
                        <a href="<?= BASE_URL ?>/admin/restaurant/order-detail.php?id=<?= (int)$order['id'] ?>" class="text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>