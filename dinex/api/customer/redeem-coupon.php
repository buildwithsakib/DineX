<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/customer-session.php';
require_once __DIR__ . '/../../includes/feature-access.php';

header('Content-Type: application/json');

$session = get_active_table_session();
if (!$session) {
    json_response(['success' => false, 'message' => 'Session expired'], 403);
}

$restaurantId = (int)$session['restaurant_id'];
$sessionId = (int)$session['id'];

if (!restaurant_has_feature($restaurantId, FEATURE_COUPONS)) {
    json_response(['success' => false, 'message' => 'Coupons are not available for this restaurant'], 403);
}

$input = json_decode(file_get_contents('php://input'), true);
$couponCode = strtoupper(trim($input['coupon_code'] ?? ''));

if (empty($couponCode)) {
    json_response(['success' => false, 'message' => 'Coupon code is required'], 400);
}

$pdo = db();

// Find the latest pending bill for this session
$billStmt = $pdo->prepare('
    SELECT * FROM bills
    WHERE restaurant_id = :rid AND table_session_id = :tsid AND status = "PENDING"
    ORDER BY id DESC LIMIT 1
');
$billStmt->execute([':rid' => $restaurantId, ':tsid' => $sessionId]);
$bill = $billStmt->fetch();

if (!$bill) {
    json_response(['success' => false, 'message' => 'No pending bill found'], 404);
}

// Check if discount already applied
if ((float)$bill['discount_amount'] > 0) {
    json_response(['success' => false, 'message' => 'A discount is already applied to this bill'], 400);
}

// Find the coupon
$couponStmt = $pdo->prepare('
    SELECT * FROM coupons
    WHERE restaurant_id = :rid AND code = :code AND table_session_id = :tsid
      AND is_used = 0 AND valid_from <= NOW() AND valid_until >= NOW()
    LIMIT 1
');
$couponStmt->execute([':rid' => $restaurantId, ':code' => $couponCode, ':tsid' => $sessionId]);
$coupon = $couponStmt->fetch();

if (!$coupon) {
    json_response(['success' => false, 'message' => 'Invalid or expired coupon code'], 400);
}

// Check minimum bill amount
$billSubtotal = (float)$bill['subtotal'];
if ($billSubtotal < (float)$coupon['min_bill_amount']) {
    json_response(['success' => false, 'message' => 'Minimum bill amount not met'], 400);
}

// Calculate discount
$discount = 0;
if ($coupon['discount_type'] === 'PERCENT') {
    $discount = $billSubtotal * ((float)$coupon['discount_value'] / 100);
    if ($coupon['max_discount'] !== null && $discount > (float)$coupon['max_discount']) {
        $discount = (float)$coupon['max_discount'];
    }
} else { // FIXED
    $discount = (float)$coupon['discount_value'];
    if ($discount > $billSubtotal) {
        $discount = $billSubtotal;
    }
}

$discount = round($discount, 2);
$newTotal = round((float)$bill['subtotal'] + (float)$bill['tax_amount'] - $discount, 2);

try {
    $pdo->beginTransaction();

    // Update bill
    $updateBill = $pdo->prepare('
        UPDATE bills
        SET discount_amount = :discount, total_amount = :total
        WHERE id = :bill_id
    ');
    $updateBill->execute([
        ':discount' => $discount,
        ':total' => $newTotal,
        ':bill_id' => $bill['id'],
    ]);

    // Mark coupon as used
    $updateCoupon = $pdo->prepare('
        UPDATE coupons
        SET is_used = 1, used_at = NOW()
        WHERE id = :coupon_id
    ');
    $updateCoupon->execute([':coupon_id' => $coupon['id']]);

    // Record redemption
    $insertRedemption = $pdo->prepare('
        INSERT INTO coupon_redemptions (coupon_id, bill_id, discount_amount)
        VALUES (:coupon_id, :bill_id, :discount)
    ');
    $insertRedemption->execute([
        ':coupon_id' => $coupon['id'],
        ':bill_id' => $bill['id'],
        ':discount' => $discount,
    ]);

    $pdo->commit();

    audit_log('CUSTOMER', null, $restaurantId, 'Coupon redeemed', [
        'coupon_id' => $coupon['id'],
        'bill_id' => $bill['id'],
        'discount' => $discount,
    ]);

    json_response([
        'success' => true,
        'message' => 'Coupon applied successfully',
        'discount_amount' => $discount,
        'new_total' => $newTotal,
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['success' => false, 'message' => 'Error applying coupon'], 500);
}