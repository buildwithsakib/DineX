<?php
// Secure session initialization

require_once __DIR__ . '/../config/config.php';

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }
    $_SESSION['last_activity'] = time();
}