<?php
require_once __DIR__ . '/../../includes/auth.php';
founder_logout();
header('Location: ' . BASE_URL . '/admin/founder/login.php');
exit;