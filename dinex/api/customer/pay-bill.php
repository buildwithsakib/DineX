<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/customer-session.php';

header('Content-Type: application/json');

$session = get_active_table_session();
if (!$session) {
    json_response(['success' => false, 'message' => 'Session expired'], 403);
}

$input = json_decode(file_get_contents('php://input'), true);
$billId = (int)($input['bill_id'] ?? 0);
if ($billId <= 0) {
    json_response(['success' => false, 'message' => 'Invalid bill'], 400);
}

$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM bills WHERE id = :id AND restaurant_id = :rid AND table_session_id = :tsid LIMIT 1');
$stmt->execute([':id'=>$billId, ':rid'=>$session['restaurant_id'], ':tsid'=>$session['id']]);
$bill = $stmt->fetch();
if (!$bill) {
    json_response(['success' => false, 'message' => 'Bill not found'], 404);
}
if ($bill['status'] === 'PAID') {
    json_response(['success' => false, 'message' => 'Bill already paid'], 400);
}

// Simulate payment
$txnId = 'TXN' . strtoupper(bin2hex(random_bytes(5)));
$stmt = $pdo->prepare('INSERT INTO payments (restaurant_id, bill_id, amount, payment_method, transaction_id, status, paid_at) VALUES (:rid, :bid, :amount, :method, :txn, "SUCCESS", NOW())');
$stmt->execute([
    ':rid'=>$session['restaurant_id'],
    ':bid'=>$billId,
    ':amount'=>$bill['total_amount'],
    ':method'=>'Simulated',
    ':txn'=>$txnId,
]);
$pdo->prepare('UPDATE bills SET status = "PAID" WHERE id = :id')->execute([':id'=>$billId]);

audit_log('CUSTOMER', null, $session['restaurant_id'], 'Bill paid', ['bill_id'=>$billId, 'txn'=>$txnId]);

json_response(['success' => true, 'message' => 'Payment successful', 'transaction_id' => $txnId]);