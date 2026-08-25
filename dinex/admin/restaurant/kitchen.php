<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';   // if not already present
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
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = sanitize_input($_POST['status'] ?? '');
        if (!in_array($newStatus, [ORDER_STATUS_ACCEPTED, ORDER_STATUS_PREPARING, ORDER_STATUS_READY])) {
            $errors[] = 'Invalid status.';
        } else {
            // Verify ownership
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id AND restaurant_id = :rid LIMIT 1');
            $stmt->execute([':id'=>$orderId, ':rid'=>$restaurantId]);
            $order = $stmt->fetch();
            if (!$order) {
                $errors[] = 'Order not found.';
            } else {
                $update = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
                $update->execute([':status'=>$newStatus, ':id'=>$orderId]);
                $hist = $pdo->prepare('INSERT INTO order_status_history (order_id, status) VALUES (:oid, :status)');
                $hist->execute([':oid'=>$orderId, ':status'=>$newStatus]);
                audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Kitchen status update', ['order_id'=>$orderId, 'status'=>$newStatus]);
                $success = true;
            }
        }
    }
}

$activeOrders = $pdo->query("
    SELECT o.*, t.table_number
    FROM orders o
    JOIN tables t ON t.id = o.table_id
    WHERE o.restaurant_id = $restaurantId
      AND o.status IN ('PLACED','ACCEPTED','PREPARING')
    ORDER BY o.created_at ASC
")->fetchAll();

$readyOrders = $pdo->query("
    SELECT o.*, t.table_number
    FROM orders o
    JOIN tables t ON t.id = o.table_id
    WHERE o.restaurant_id = $restaurantId
      AND o.status = 'READY'
    ORDER BY o.updated_at ASC
")->fetchAll();

$pageTitle = 'Kitchen Display';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Kitchen Display</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Status updated.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div>
            <h2 class="font-semibold text-lg">New / In Progress</h2>
            <div class="space-y-4 mt-4">
                <?php foreach ($activeOrders as $order): ?>
                <div class="bg-white rounded-xl shadow p-4 border-l-4 border-orange-500">
                    <div class="flex justify-between">
                        <span class="font-bold"><?= e($order['order_number']) ?></span>
                        <span class="text-sm text-gray-500">Table <?= e($order['table_number']) ?></span>
                    </div>
                    <ul class="mt-2 text-sm">
                        <?php
                        $items = $pdo->prepare('SELECT oi.quantity, f.name FROM order_items oi JOIN foods f ON f.id = oi.food_id WHERE oi.order_id = :oid');
                        $items->execute([':oid'=>$order['id']]);
                        foreach ($items->fetchAll() as $item) {
                            echo '<li>' . e($item['quantity']) . 'x ' . e($item['name']) . '</li>';
                        }
                        ?>
                    </ul>
                    <div class="mt-3 flex gap-2">
                        <?php if ($order['status'] === ORDER_STATUS_PLACED): ?>
                            <form method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                <input type="hidden" name="status" value="<?= ORDER_STATUS_ACCEPTED ?>">
                                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm">Accept</button>
                            </form>
                        <?php endif; ?>
                        <?php if (in_array($order['status'], [ORDER_STATUS_ACCEPTED, ORDER_STATUS_PREPARING])): ?>
                            <form method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                <input type="hidden" name="status" value="<?= $order['status'] === ORDER_STATUS_ACCEPTED ? ORDER_STATUS_PREPARING : ORDER_STATUS_READY ?>">
                                <button type="submit" class="bg-amber-600 text-white px-3 py-1 rounded text-sm"><?= $order['status'] === ORDER_STATUS_ACCEPTED ? 'Start Preparing' : 'Mark Ready' ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (!$activeOrders): ?><p class="text-gray-500">No active orders.</p><?php endif; ?>
            </div>
        </div>

        <div>
            <h2 class="font-semibold text-lg">Ready for Serving</h2>
            <div class="space-y-4 mt-4">
                <?php foreach ($readyOrders as $order): ?>
                <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-500">
                    <div class="flex justify-between">
                        <span class="font-bold"><?= e($order['order_number']) ?></span>
                        <span class="text-sm text-gray-500">Table <?= e($order['table_number']) ?></span>
                    </div>
                    <ul class="mt-2 text-sm">
                        <?php
                        $items = $pdo->prepare('SELECT oi.quantity, f.name FROM order_items oi JOIN foods f ON f.id = oi.food_id WHERE oi.order_id = :oid');
                        $items->execute([':oid'=>$order['id']]);
                        foreach ($items->fetchAll() as $item) {
                            echo '<li>' . e($item['quantity']) . 'x ' . e($item['name']) . '</li>';
                        }
                        ?>
                    </ul>
                </div>
                <?php endforeach; ?>
                <?php if (!$readyOrders): ?><p class="text-gray-500">No ready orders.</p><?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>