<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/subscription.php';

$founder = require_founder_access();
$pdo = db();

$errors = [];
$success = false;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = sanitize_input($_POST['action'] ?? '');
        $subscriptionId = (int)($_POST['subscription_id'] ?? 0);

        if ($subscriptionId <= 0) {
            $errors[] = 'Invalid subscription ID.';
        } else {
            // Fetch subscription to get restaurant_id
            $subStmt = $pdo->prepare('SELECT * FROM restaurant_subscriptions WHERE id = :id LIMIT 1');
            $subStmt->execute([':id' => $subscriptionId]);
            $subscription = $subStmt->fetch();
            if (!$subscription) {
                $errors[] = 'Subscription not found.';
            } else {
                $restaurantId = (int)$subscription['restaurant_id'];

                switch ($action) {
                    case 'activate':
                        // Activate subscription and mark payment as paid
                        activate_subscription($subscriptionId);
                        // Also set restaurant status to ACTIVE
                        $pdo->prepare('UPDATE restaurants SET status = :status WHERE id = :rid')
                            ->execute([':status' => RESTAURANT_STATUS_ACTIVE, ':rid' => $restaurantId]);
                        audit_log('FOUNDER', $founder['id'], $restaurantId, 'Subscription activated', ['subscription_id' => $subscriptionId]);
                        $success = true;
                        break;

                    case 'suspend':
                        suspend_subscription($subscriptionId);
                        audit_log('FOUNDER', $founder['id'], $restaurantId, 'Subscription suspended', ['subscription_id' => $subscriptionId]);
                        $success = true;
                        break;

                    case 'expire':
                        expire_subscription($subscriptionId);
                        audit_log('FOUNDER', $founder['id'], $restaurantId, 'Subscription expired', ['subscription_id' => $subscriptionId]);
                        $success = true;
                        break;

                    case 'cancel':
                        cancel_subscription($subscriptionId);
                        audit_log('FOUNDER', $founder['id'], $restaurantId, 'Subscription cancelled', ['subscription_id' => $subscriptionId]);
                        $success = true;
                        break;

                    default:
                        $errors[] = 'Invalid action.';
                }
            }
        }
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$query = '
    SELECT rs.*, r.name AS restaurant_name, r.email AS restaurant_email, r.status AS restaurant_status,
           p.name AS plan_name, p.billing_cycle, p.price
    FROM restaurant_subscriptions rs
    JOIN restaurants r ON r.id = rs.restaurant_id
    JOIN subscription_plans p ON p.id = rs.plan_id
    WHERE 1=1
';
$params = [];
if ($statusFilter) {
    $query .= ' AND rs.status = :status';
    $params[':status'] = $statusFilter;
}
if ($search) {
    $query .= ' AND (r.name LIKE :q OR r.email LIKE :q OR p.name LIKE :q)';
    $params[':q'] = "%$search%";
}
$query .= ' ORDER BY rs.id DESC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$subscriptions = $stmt->fetchAll();

$pageTitle = 'Subscriptions';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Subscriptions</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Subscription updated successfully.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="mt-6 bg-white rounded-xl shadow p-4 flex flex-col md:flex-row gap-4">
        <form method="GET" class="flex gap-4 flex-1">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search restaurant or plan..." class="border rounded-lg px-3 py-2 flex-1">
            <select name="status" class="border rounded-lg px-3 py-2">
                <option value="">All Statuses</option>
                <?php foreach ([SUBSCRIPTION_STATUS_PENDING, SUBSCRIPTION_STATUS_ACTIVE, SUBSCRIPTION_STATUS_EXPIRED, SUBSCRIPTION_STATUS_SUSPENDED, SUBSCRIPTION_STATUS_CANCELLED] as $s): ?>
                    <option value="<?= e($s) ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg">Filter</button>
        </form>
    </div>

    <div class="mt-6 bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3">Restaurant</th>
                    <th class="px-6 py-3">Plan</th>
                    <th class="px-6 py-3">Billing</th>
                    <th class="px-6 py-3">Start</th>
                    <th class="px-6 py-3">End</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Payment</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($subscriptions as $sub): ?>
                <tr>
                    <td class="px-6 py-3">
                        <?= e($sub['restaurant_name']) ?><br>
                        <span class="text-xs text-gray-500"><?= e($sub['restaurant_email']) ?></span>
                    </td>
                    <td class="px-6 py-3"><?= e($sub['plan_name']) ?></td>
                    <td class="px-6 py-3"><?= e($sub['billing_cycle']) ?></td>
                    <td class="px-6 py-3"><?= e(date('d M Y', strtotime($sub['start_date']))) ?></td>
                    <td class="px-6 py-3"><?= e(date('d M Y', strtotime($sub['end_date']))) ?></td>
                    <td class="px-6 py-3">
                        <?php $cls = [SUBSCRIPTION_STATUS_ACTIVE=>'bg-green-100 text-green-700', SUBSCRIPTION_STATUS_PENDING=>'bg-amber-100 text-amber-700', SUBSCRIPTION_STATUS_EXPIRED=>'bg-red-100 text-red-700', SUBSCRIPTION_STATUS_SUSPENDED=>'bg-orange-100 text-orange-700', SUBSCRIPTION_STATUS_CANCELLED=>'bg-gray-100 text-gray-700']; ?>
                        <span class="px-2 py-1 text-xs rounded <?= $cls[$sub['status']] ?? '' ?>"><?= e($sub['status']) ?></span>
                    </td>
                    <td class="px-6 py-3"><?= e($sub['payment_status']) ?></td>
                    <td class="px-6 py-3">
                        <?php if ($sub['status'] === SUBSCRIPTION_STATUS_PENDING): ?>
                            <form method="POST" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="activate">
                                <input type="hidden" name="subscription_id" value="<?= (int)$sub['id'] ?>">
                                <button type="submit" class="text-green-600 hover:underline">Activate</button>
                            </form>
                        <?php elseif ($sub['status'] === SUBSCRIPTION_STATUS_ACTIVE): ?>
                            <form method="POST" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="suspend">
                                <input type="hidden" name="subscription_id" value="<?= (int)$sub['id'] ?>">
                                <button type="submit" class="text-orange-600 hover:underline">Suspend</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($sub['status'] !== SUBSCRIPTION_STATUS_CANCELLED && $sub['status'] !== SUBSCRIPTION_STATUS_EXPIRED): ?>
                            <form method="POST" class="inline ml-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="subscription_id" value="<?= (int)$sub['id'] ?>">
                                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Cancel this subscription?');">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>