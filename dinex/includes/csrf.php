<?php
// CSRF token helpers

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';   // needed for e()

function generate_csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_field(): string
{
    $token = generate_csrf_token();
    return '<input type="hidden" name="' . e(CSRF_TOKEN_NAME) . '" value="' . e($token) . '">';
}

function verify_csrf_token(?string $token): bool
{
    start_secure_session();
    return is_string($token) && isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}