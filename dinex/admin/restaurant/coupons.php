<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/feature-access.php';

$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

if (!restaurant_has_feature($restaurantId, FEATURE_COUPONS)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Coupons feature is not available.');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'delete') {
            $couponId = (int)($_POST['coupon_id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM coupons WHERE id = :id AND restaurant_id = :rid');
            $stmt->execute([':id'=>$couponId, ':rid'=>$restaurantId]);
            audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Coupon deleted', ['coupon_id'=>$couponId]);
            $success = true;
        } elseif ($action === 'generate') {
            // Manual generation for demo/manager
            $code = strtoupper(bin2hex(random_bytes(4)));
            $discountType = sanitize_input($_POST['discount_type'] ?? 'PERCENT');
            $discountValue = (float)($_POST['discount_value'] ?? 0);
            $minBill = (float)($_POST['min_bill'] ?? 0);
            $maxDiscount = isset($_POST['max_discount']) && $_POST['max_discount'] !== '' ? (float)$_POST['max_discount'] : null;
            $validUntil = date('Y-m-d H:i:s', strtotime('+7 days'));

            if ($discountValue <= 0) {
                $errors[] = 'Discount value must be positive.';
            }
            if (!$errors) {
                $stmt = $pdo->prepare('
                    INSERT INTO coupons (restaurant_id, code, discount_type, discount_value, min_bill_amount, max_discount, valid_from, valid_until)
                    VALUES (:rid, :code, :dtype, :dvalue, :minbill, :maxdisc, NOW(), :validuntil)
                ');
                $stmt->execute([
                    ':rid'=>$restaurantId,
                    ':code'=>$code,
                    ':dtype'=>$discountType,
                    ':dvalue'=>$discountValue,
                    ':minbill'=>$minBill,
                    ':maxdisc'=>$maxDiscount,
                    ':validuntil'=>$validUntil,
                ]);
                audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Coupon generated', ['code'=>$code]);
                $success = true;
            }
        }
    }
}

$coupons = $pdo->query("SELECT * FROM coupons WHERE restaurant_id = $restaurantId ORDER BY id DESC LIMIT 200")->fetchAll();

$pageTitle = 'Coupons';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Coupons</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Action completed.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold">Generate Coupon</h2>
            <form method="POST" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="generate">
                <div>
                    <label class="block text-sm">Discount Type</label>
                    <select name="discount_type" class="mt-1 w-full border rounded px-3 py-2">
                        <option value="PERCENT">Percent</option>
                        <option value="FIXED">Fixed Amount</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm">Discount Value</label>
                    <input type="number" step="0.01" name="discount_value" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Min Bill Amount</label>
                    <input type="number" step="0.01" name="min_bill" value="0" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Max Discount (optional)</label>
                    <input type="number" step="0.01" name="max_discount" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded">Generate</button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-3">Code</th><th class="px-6 py-3">Type</th><th class="px-6 py-3">Value</th>
                        <th class="px-6 py-3">Min Bill</th><th class="px-6 py-3">Max Discount</th><th class="px-6 py-3">Used</th><th class="px-6 py-3">Expiry</th><th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($coupons as $coupon): ?>
                    <tr>
                        <td class="px-6 py-3 font-mono"><?= e($coupon['code']) ?></td>
                        <td class="px-6 py-3"><?= e($coupon['discount_type']) ?></td>
                        <td class="px-6 py-3"><?= e($coupon['discount_value']) ?></td>
                        <td class="px-6 py-3">₹<?= e($coupon['min_bill_amount']) ?></td>
                        <td class="px-6 py-3"><?= $coupon['max_discount'] !== null ? '₹'.e($coupon['max_discount']) : '—' ?></td>
                        <td class="px-6 py-3"><?= $coupon['is_used'] ? 'Yes' : 'No' ?></td>
                        <td class="px-6 py-3"><?= e(date('d M Y', strtotime($coupon['valid_until']))) ?></td>
                        <td class="px-6 py-3">
                            <form method="POST" class="inline" onsubmit="return confirm('Delete?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="coupon_id" value="<?= (int)$coupon['id'] ?>">
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>