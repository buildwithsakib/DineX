<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/customer-session.php';
require_once __DIR__ . '/../../includes/feature-access.php';

header('Content-Type: application/json');

$session = get_active_table_session();
if (!$session) {
    json_response(['success' => false, 'message' => 'Session expired'], 403);
}

$restaurantId = $session['restaurant_id'];
if (!restaurant_has_feature($restaurantId, FEATURE_GAMES)) {
    json_response(['success' => false, 'message' => 'Games not available'], 403);
}

$input = json_decode(file_get_contents('php://input'), true);
$gameId = (int)($input['game_id'] ?? 0);
if ($gameId <= 0) {
    json_response(['success' => false, 'message' => 'Invalid game'], 400);
}

$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM games WHERE id = :id AND restaurant_id = :rid AND is_active = 1 LIMIT 1');
$stmt->execute([':id'=>$gameId, ':rid'=>$restaurantId]);
$game = $stmt->fetch();
if (!$game) {
    json_response(['success' => false, 'message' => 'Game not found'], 404);
}

// Fetch random reward for this game
$rewardStmt = $pdo->prepare('SELECT * FROM game_rewards WHERE game_id = :gid AND is_active = 1 ORDER BY RAND() LIMIT 1');
$rewardStmt->execute([':gid'=>$gameId]);
$reward = $rewardStmt->fetch();

if (!$reward) {
    json_response(['success' => false, 'message' => 'No rewards configured'], 500);
}

// Record game session
$sessionStmt = $pdo->prepare('INSERT INTO game_sessions (restaurant_id, table_session_id, game_id, result_payload, reward_id) VALUES (:rid, :tsid, :gid, :payload, :rewardid)');
$payload = json_encode(['game' => $game['game_key']]);
$sessionStmt->execute([
    ':rid' => $restaurantId,
    ':tsid' => $session['id'],
    ':gid' => $gameId,
    ':payload' => $payload,
    ':rewardid' => $reward['id'],
]);

// If reward is coupon, generate coupon
$couponCode = null;
if ($reward['reward_type'] === 'COUPON') {
    $code = strtoupper(bin2hex(random_bytes(4)));
    $discountValue = (float)$reward['value'];
    $validUntil = date('Y-m-d H:i:s', strtotime('+1 day'));
    $couponStmt = $pdo->prepare('
        INSERT INTO coupons (restaurant_id, table_session_id, code, discount_type, discount_value, min_bill_amount, valid_from, valid_until)
        VALUES (:rid, :tsid, :code, "PERCENT", :dvalue, 0, NOW(), :validuntil)
    ');
    $couponStmt->execute([
        ':rid' => $restaurantId,
        ':tsid' => $session['id'],
        ':code' => $code,
        ':dvalue' => $discountValue,
        ':validuntil' => $validUntil,
    ]);
    $couponCode = $code;
} elseif ($reward['reward_type'] === 'DISCOUNT') {
    // For discount, return value directly
    $discountValue = (float)$reward['value'];
    $couponCode = null;
} else {
    $couponCode = null;
}

audit_log('CUSTOMER', null, $restaurantId, 'Game played', ['game_id'=>$gameId, 'reward_id'=>$reward['id']]);

json_response([
    'success' => true,
    'reward_type' => $reward['reward_type'],
    'value' => $reward['value'],
    'coupon_code' => $couponCode,
    'message' => 'You won a reward!'
]);