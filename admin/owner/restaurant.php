<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Restaurant Profile';
$activePage = 'restaurant';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $name = sanitize_text($_POST['name'] ?? '', 150);
    $tagline = sanitize_text($_POST['tagline'] ?? '', 255);
    $address = sanitize_text($_POST['address'] ?? '', 500);
    $phone = sanitize_text($_POST['phone'] ?? '', 50);
    $email = sanitize_text($_POST['email'] ?? '', 190);
    $taxRate = validate_price($_POST['tax_rate'] ?? 5);
    if ($name && $taxRate !== null) {
        $stmt = $pdo->prepare("UPDATE restaurants SET name=?, tagline=?, address=?, phone=?, email=?, tax_rate=? WHERE id=?");
        $stmt->execute([$name, $tagline, $address, $phone, $email, $taxRate, $restaurantId]);
        add_audit_log($pdo, $restaurantId, $user['id'], 'updated restaurant profile', 'restaurant', $restaurantId);
        $_SESSION['flash'] = 'Restaurant updated.';
        redirect('/dinex/admin/owner/restaurant.php');
    }
}

$restaurant = $pdo->prepare("SELECT * FROM restaurants WHERE id=? LIMIT 1");
$restaurant->execute([$restaurantId]);
$restaurant = $restaurant->fetch();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Restaurant Profile</h1>
<form method="POST" class="bg-white rounded-xl shadow-md p-6 space-y-4 max-w-2xl">
    <?= csrf_field() ?>
    <div><label class="block text-sm font-semibold">Name</label><input type="text" name="name" value="<?= e($restaurant['name']) ?>" class="w-full border rounded-lg px-4 py-2"></div>
    <div><label class="block text-sm font-semibold">Tagline</label><input type="text" name="tagline" value="<?= e($restaurant['tagline']) ?>" class="w-full border rounded-lg px-4 py-2"></div>
    <div><label class="block text-sm font-semibold">Address</label><textarea name="address" class="w-full border rounded-lg px-4 py-2"><?= e($restaurant['address']) ?></textarea></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-semibold">Phone</label><input type="text" name="phone" value="<?= e($restaurant['phone']) ?>" class="w-full border rounded-lg px-4 py-2"></div>
    <div><label class="block text-sm font-semibold">Email</label><input type="email" name="email" value="<?= e($restaurant['email']) ?>" class="w-full border rounded-lg px-4 py-2"></div></div>
    <div><label class="block text-sm font-semibold">Tax Rate (%)</label><input type="number" step="0.01" name="tax_rate" value="<?= e($restaurant['tax_rate']) ?>" class="w-full border rounded-lg px-4 py-2"></div>
    <button class="bg-slate-900 text-white px-6 py-2 rounded-lg">Save</button>
</form>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>