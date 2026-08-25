<?php
require_once __DIR__ . '/../../includes/auth.php';
restaurant_logout();
header('Location: ' . BASE_URL . '/admin/restaurant/login.php');
exit;