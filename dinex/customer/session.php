<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-session.php';
require_once __DIR__ . '/../includes/feature-access.php';

$session = get_active_table_session();
if (!$session) {
    echo 'No active session.';
} else {
    echo 'Session active for Table ID ' . $session['table_id'];
}