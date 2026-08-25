<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$founder = require_founder_access();
$pdo = db();

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$query = '
    SELECT r.*, 
           rs.status AS sub_status, 
           p.name AS plan_name,
           p.billing_cycle,
           rs.end_date AS expiry_date
    FROM restaurants r
    LEFT JOIN restaurant_subscriptions rs ON rs.id = (SELECT id FROM restaurant_subscriptions WHERE restaurant_id = r.id ORDER BY id DESC LIMIT 1)
    LEFT JOIN subscription_plans p ON p.id = rs.plan_id
    WHERE 1=1
';
$params = [];
if ($statusFilter) {
    $query .= ' AND r.status = :status';
    $params[':status'] = $statusFilter;
}
if ($search) {
    $query .= ' AND (r.name LIKE :q OR r.email LIKE :q OR r.owner_name LIKE :q)';
    $params[':q'] = "%$search%";
}
$query .= ' ORDER BY r.created_at DESC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$restaurants = $stmt->fetchAll();

$pageTitle = 'Restaurants';
include __DIR__ . '/../templates/founder-header.php';
?>
<main class="p-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Restaurants</h1>
        <a href="restaurant-create.php" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Add Restaurant</a>
    </div>

    <div class="mt-6 bg-white rounded-xl shadow p-4 flex flex-col md:flex-row gap-4">
        <form method="GET" class="flex gap-4 flex-1">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search name, email, owner..." class="border rounded-lg px-3 py-2 flex-1">
            <select name="status" class="border rounded-lg px-3 py-2">
                <option value="">All Statuses</option>
                <?php foreach ([RESTAURANT_STATUS_PENDING, RESTAURANT_STATUS_ACTIVE, RESTAURANT_STATUS_SUSPENDED, RESTAURANT_STATUS_CANCELLED] as $s): ?>
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
                    <th class="px-6 py-3">Name</th><th class="px-6 py-3">Owner</th><th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Plan</th><th class="px-6 py-3">Billing</th><th class="px-6 py-3">Expiry</th><th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($restaurants as $r): ?>
                <tr>
                    <td class="px-6 py-3 font-medium"><?= e($r['name']) ?></td>
                    <td class="px-6 py-3"><?= e($r['owner_name']) ?><br><span class="text-xs text-gray-500"><?= e($r['email']) ?></span></td>
                    <td class="px-6 py-3">
                        <?php $cls = ['ACTIVE'=>'bg-green-100 text-green-700','PENDING'=>'bg-amber-100 text-amber-700','SUSPENDED'=>'bg-red-100 text-red-700','CANCELLED'=>'bg-gray-100 text-gray-700']; ?>
                        <span class="px-2 py-1 text-xs rounded <?= $cls[$r['status']] ?? '' ?>"><?= e($r['status']) ?></span>
                    </td>
                    <td class="px-6 py-3"><?= e($r['plan_name'] ?? '—') ?></td>
                    <td class="px-6 py-3"><?= e($r['billing_cycle'] ?? '—') ?></td>
                    <td class="px-6 py-3"><?= $r['expiry_date'] ? e(date('d M Y', strtotime($r['expiry_date']))) : '—' ?></td>
                    <td class="px-6 py-3">
                        <a href="restaurant-view.php?id=<?= (int)$r['id'] ?>" class="text-orange-600 hover:underline">View</a>
                        <a href="restaurant-edit.php?id=<?= (int)$r['id'] ?>" class="ml-2 text-gray-600 hover:underline">Edit</a>
                        <a href="restaurant-features.php?id=<?= (int)$r['id'] ?>" class="ml-2 text-blue-600 hover:underline">Features</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../templates/founder-footer.php'; ?>