<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function cleanup_expired_sessions(): void
{
    $pdo = db();
    $retentionHours = (int)get_setting('session_retention_hours', '24');
    $cutoff = date('Y-m-d H:i:s', time() - $retentionHours * 3600);

    // Find expired sessions (status ACTIVE but old, or status CLOSED/EXPIRED)
    $stmt = $pdo->prepare('
        SELECT id FROM table_sessions
        WHERE (status = "ACTIVE" AND updated_at < :cutoff)
           OR status IN ("CLOSED","EXPIRED")
    ');
    $stmt->execute([':cutoff' => $cutoff]);
    $sessionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($sessionIds as $sessionId) {
        // Delete related data in order to comply with anonymous retention
        $tables = ['game_sessions', 'coupons', 'reviews'];
        foreach ($tables as $table) {
            $pdo->prepare("DELETE FROM $table WHERE table_session_id = :sid")->execute([':sid'=>$sessionId]);
        }
        // We keep orders and bills for accounting, but remove session linkage
        $pdo->prepare('UPDATE orders SET session_id = NULL WHERE session_id = :sid')->execute([':sid'=>$sessionId]);
        $pdo->prepare('UPDATE bills SET table_session_id = NULL WHERE table_session_id = :sid')->execute([':sid'=>$sessionId]);
        // Delete session
        $pdo->prepare('DELETE FROM table_sessions WHERE id = :sid')->execute([':sid'=>$sessionId]);
        audit_log('SYSTEM', null, null, 'Session purged', ['session_id'=>$sessionId]);
    }
}

// If run directly via CLI or web
if (php_sapi_name() === 'cli' || isset($_GET['run'])) {
    cleanup_expired_sessions();
    echo 'Cleanup completed.';
}