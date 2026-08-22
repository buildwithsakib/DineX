<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';

logout_user();
redirect('/dinex/admin/' . ROLE_NAMES[$_SESSION['role_id'] ?? ROLE_SUPER_ADMIN] . '/login.php');