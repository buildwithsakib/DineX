<?php
// Simple database-backed rate limiter

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function rate_limit_check(string $identifier, string $action, int $maxAttempts = RATE_LIMIT_MAX_ATTEMPTS, int $windowSeconds = RATE_LIMIT_WINDOW_SECONDS): bool
{
    $pdo = db();
    $now = now();
    $windowStart = date('Y-m-d H:i:s', time() - $windowSeconds);

    // Clean old records occasionally
    $pdo->prepare('DELETE FROM rate_limit WHERE window_start < :windowStart')->execute([':windowStart' => $windowStart]);

    $stmt = $pdo->prepare('
        SELECT attempts, window_start
        FROM rate_limit
        WHERE identifier = :identifier AND action = :action
        ORDER BY id DESC
        LIMIT 1
    ');
    $stmt->execute([':identifier' => $identifier, ':action' => $action]);
    $record = $stmt->fetch();

    if ($record && strtotime($record['window_start']) > time() - $windowSeconds) {
        if ((int)$record['attempts'] >= $maxAttempts) {
            return false;
        }

        $stmt = $pdo->prepare('
            UPDATE rate_limit
            SET attempts = attempts + 1, updated_at = NOW()
            WHERE identifier = :identifier AND action = :action AND window_start = :window_start
        ');
        $stmt->execute([
            ':identifier' => $identifier,
            ':action' => $action,
            ':window_start' => $record['window_start'],
        ]);
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO rate_limit (identifier, action, attempts, window_start)
            VALUES (:identifier, :action, 1, :window_start)
        ');
        $stmt->execute([
            ':identifier' => $identifier,
            ':action' => $action,
            ':window_start' => $windowStart,
        ]);
    }

    return true;
}

function rate_limit_clear(string $identifier, string $action): void
{
    $pdo = db();
    $stmt = $pdo->prepare('DELETE FROM rate_limit WHERE identifier = :identifier AND action = :action');
    $stmt->execute([':identifier' => $identifier, ':action' => $action]);
}