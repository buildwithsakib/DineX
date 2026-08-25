<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-session.php';
require_once __DIR__ . '/../includes/feature-access.php';

$gameId = (int)($_GET['game_id'] ?? 0);
if ($gameId <= 0) {
    die('Invalid game.');
}
$session = get_active_table_session();
if (!$session) {
    die('Session expired.');
}
$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM games WHERE id = :id AND restaurant_id = :rid AND is_active = 1 LIMIT 1');
$stmt->execute([':id'=>$gameId, ':rid'=>$session['restaurant_id']]);
$game = $stmt->fetch();
if (!$game) {
    die('Game not available.');
}
// Redirect to game folder
$gameKey = $game['game_key'];
$gameBase = FULL_BASE_URL . '/games/' . $gameKey . '/index.php';
header('Location: ' . $gameBase . '?game_id=' . $gameId);
exit;