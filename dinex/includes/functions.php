<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/security-headers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function today(): string
{
    return date('Y-m-d');
}

function generate_random_token(int $length = 32): string
{
    return bin2hex(random_bytes($length / 2));
}

function generate_unique_code(string $prefix, int $length = 16): string
{
    return $prefix . bin2hex(random_bytes($length / 2));
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'item';
}

function get_client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function audit_log(string $actorType, ?int $actorId, ?int $restaurantId, string $action, $details = null): void
{
    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (actor_type, actor_id, restaurant_id, action, details, ip_address)
            VALUES (:actor_type, :actor_id, :restaurant_id, :action, :details, :ip)
        ");
        $stmt->execute([
            ':actor_type' => $actorType,
            ':actor_id' => $actorId,
            ':restaurant_id' => $restaurantId,
            ':action' => $action,
            ':details' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            ':ip' => get_client_ip(),
        ]);
    } catch (PDOException $e) {
        error_log('Audit log failed: ' . $e->getMessage());
    }
}

function get_setting(string $key, $default = null)
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $stmt->execute([':key' => $key]);
    $row = $stmt->fetch();
    return $row['setting_value'] ?? $default;
}

function set_setting(string $key, $value): void
{
    $pdo = db();
    $stmt = $pdo->prepare('
        INSERT INTO settings (setting_key, setting_value)
        VALUES (:key, :value_insert)
        ON DUPLICATE KEY UPDATE setting_value = :value_update, updated_at = NOW()
    ');
    $stmt->execute([
        ':key' => $key,
        ':value_insert' => (string)$value,
        ':value_update' => (string)$value,
    ]);
}