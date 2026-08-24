<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

function start_customer_session(): void
{
    start_secure_session();
}

function get_or_create_table_session(string $token): array
{
    start_customer_session();
    $pdo = db();

    $stmt = $pdo->prepare('
        SELECT q.*, t.restaurant_id, t.table_number, t.id AS table_id, r.name AS restaurant_name
        FROM qr_codes q
        JOIN tables t ON t.id = q.table_id
        JOIN restaurants r ON r.id = t.restaurant_id
        WHERE q.token = :token AND q.is_active = 1
        LIMIT 1
    ');
    $stmt->execute([':token' => $token]);
    $qr = $stmt->fetch();

    if (!$qr) {
        return ['success' => false, 'message' => 'Invalid QR code or table inactive.'];
    }

    $sessionStmt = $pdo->prepare('
        SELECT * FROM table_sessions
        WHERE restaurant_id = :rid AND table_id = :tid AND status = :active
        ORDER BY id DESC LIMIT 1
    ');
    $sessionStmt->execute([':rid'=>$qr['restaurant_id'], ':tid'=>$qr['table_id'], ':active'=>TABLE_SESSION_ACTIVE]);
    $existingSession = $sessionStmt->fetch();

    if ($existingSession) {
        $_SESSION['table_session_id'] = (int)$existingSession['id'];
        $_SESSION['restaurant_id'] = (int)$qr['restaurant_id'];
        $_SESSION['table_id'] = (int)$qr['table_id'];
        $_SESSION['session_token'] = $qr['token'];
        return [
            'success' => true,
            'session_id' => (int)$existingSession['id'],
            'restaurant_id' => (int)$qr['restaurant_id'],
            'table_id' => (int)$qr['table_id'],
            'table_number' => $qr['table_number'],
            'restaurant_name' => $qr['restaurant_name'],
        ];
    }

    $sessionToken = generate_random_token(32);
    $insert = $pdo->prepare('
        INSERT INTO table_sessions (restaurant_id, table_id, session_token, status)
        VALUES (:rid, :tid, :token, :status)
    ');
    $insert->execute([
        ':rid' => $qr['restaurant_id'],
        ':tid' => $qr['table_id'],
        ':token' => $sessionToken,
        ':status' => TABLE_SESSION_ACTIVE,
    ]);
    $sessionId = (int)$pdo->lastInsertId();

    $_SESSION['table_session_id'] = $sessionId;
    $_SESSION['restaurant_id'] = (int)$qr['restaurant_id'];
    $_SESSION['table_id'] = (int)$qr['table_id'];
    $_SESSION['session_token'] = $qr['token'];

    return [
        'success' => true,
        'session_id' => $sessionId,
        'restaurant_id' => (int)$qr['restaurant_id'],
        'table_id' => (int)$qr['table_id'],
        'table_number' => $qr['table_number'],
        'restaurant_name' => $qr['restaurant_name'],
    ];
}

function get_active_table_session(): ?array
{
    start_customer_session();
    if (empty($_SESSION['table_session_id'])) {
        return null;
    }
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM table_sessions WHERE id = :id AND status = :active LIMIT 1');
    $stmt->execute([':id'=>$_SESSION['table_session_id'], ':active'=>TABLE_SESSION_ACTIVE]);
    return $stmt->fetch() ?: null;
}