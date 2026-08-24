<?php
// Authentication functions

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

function founder_login(string $email, string $password): array
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM platform_users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid founder credentials.'];
    }
    if ($user['status'] !== 'ACTIVE') {
        return ['success' => false, 'message' => 'Founder account is not active.'];
    }

    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['platform_user_id'] = (int)$user['id'];
    $_SESSION['platform_user_name'] = $user['name'];
    $_SESSION['platform_user_email'] = $user['email'];

    audit_log('FOUNDER', $user['id'], null, 'Founder logged in');

    return ['success' => true, 'user' => $user];
}

function founder_logout(): void
{
    start_secure_session();
    unset($_SESSION['platform_user_id'], $_SESSION['platform_user_name'], $_SESSION['platform_user_email']);
    session_regenerate_id(true);
}

function require_founder_auth(): array
{
    start_secure_session();
    if (empty($_SESSION['platform_user_id'])) {
        header('Location: ' . BASE_URL . '/admin/founder/login.php');
        exit;
    }

    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM platform_users WHERE id = :id AND status = :status LIMIT 1');
    $stmt->execute([':id' => $_SESSION['platform_user_id'], ':status' => 'ACTIVE']);
    $user = $stmt->fetch();

    if (!$user) {
        founder_logout();
        header('Location: ' . BASE_URL . '/admin/founder/login.php?error=session');
        exit;
    }

    return $user;
}

function restaurant_login(string $email, string $password): array
{
    $pdo = db();
    $stmt = $pdo->prepare('
        SELECT s.*, r.status AS restaurant_status, r.name AS restaurant_name
        FROM restaurant_staff s
        JOIN restaurants r ON r.id = s.restaurant_id
        WHERE s.email = :email
        LIMIT 1
    ');
    $stmt->execute([':email' => $email]);
    $staff = $stmt->fetch();

    if (!$staff || !password_verify($password, $staff['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid restaurant credentials.'];
    }
    if ($staff['status'] !== 'ACTIVE') {
        return ['success' => false, 'message' => 'Your staff account is not active.'];
    }
    if ($staff['restaurant_status'] === 'SUSPENDED') {
        return ['success' => false, 'message' => 'Restaurant account is currently suspended.'];
    }
    if ($staff['restaurant_status'] === 'PENDING') {
        return ['success' => false, 'message' => 'Restaurant account is pending approval. Please contact DineX.'];
    }
    if ($staff['restaurant_status'] === 'CANCELLED') {
        return ['success' => false, 'message' => 'Restaurant account has been cancelled.'];
    }

    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['restaurant_staff_id'] = (int)$staff['id'];
    $_SESSION['restaurant_id'] = (int)$staff['restaurant_id'];
    $_SESSION['restaurant_staff_role'] = $staff['role'];
    $_SESSION['restaurant_staff_name'] = $staff['name'];
    $_SESSION['restaurant_name'] = $staff['restaurant_name'];

    audit_log('RESTAURANT', $staff['id'], $staff['restaurant_id'], 'Restaurant staff logged in');

    return ['success' => true, 'staff' => $staff];
}

function restaurant_logout(): void
{
    start_secure_session();
    unset(
        $_SESSION['restaurant_staff_id'],
        $_SESSION['restaurant_id'],
        $_SESSION['restaurant_staff_role'],
        $_SESSION['restaurant_staff_name'],
        $_SESSION['restaurant_name']
    );
    session_regenerate_id(true);
}

function require_restaurant_auth(): array
{
    start_secure_session();
    if (empty($_SESSION['restaurant_staff_id']) || empty($_SESSION['restaurant_id'])) {
        header('Location: ' . BASE_URL . '/admin/restaurant/login.php');
        exit;
    }

    $pdo = db();
    $stmt = $pdo->prepare('
        SELECT s.*, r.status AS restaurant_status, r.name AS restaurant_name, r.slug AS restaurant_slug
        FROM restaurant_staff s
        JOIN restaurants r ON r.id = s.restaurant_id
        WHERE s.id = :staff_id AND s.restaurant_id = :restaurant_id
        LIMIT 1
    ');
    $stmt->execute([
        ':staff_id' => $_SESSION['restaurant_staff_id'],
        ':restaurant_id' => $_SESSION['restaurant_id'],
    ]);
    $staff = $stmt->fetch();

    if (!$staff || $staff['status'] !== 'ACTIVE') {
        restaurant_logout();
        header('Location: ' . BASE_URL . '/admin/restaurant/login.php?error=session');
        exit;
    }
    if ($staff['restaurant_status'] === 'SUSPENDED') {
        restaurant_logout();
        header('Location: ' . BASE_URL . '/admin/restaurant/login.php?error=suspended');
        exit;
    }

    return $staff;
}
