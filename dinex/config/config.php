<?php
// DineX configuration file

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'dinex');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application
define('APP_NAME', 'DineX');
define('APP_TAGLINE', 'SCAN. ORDER. PLAY. ENJOY.');
define('APP_ENV', 'development'); // development | production

// URLs
define('BASE_URL', '/dinex'); // <-- fixed base URL, only defined once
define('FULL_BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL);

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('CUSTOMER_PATH', ROOT_PATH . '/customer');
define('API_PATH', ROOT_PATH . '/api');
define('UPLOAD_PATH', ROOT_PATH . '/assets/uploads');
define('LOG_PATH', ROOT_PATH . '/logs');

// Sessions
define('SESSION_NAME', 'dinex_session');
define('SESSION_LIFETIME', 86400); // 1 day
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SECURE', false); // set true in production with HTTPS

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_BCRYPT_COST', 12);
define('RATE_LIMIT_MAX_ATTEMPTS', 5);
define('RATE_LIMIT_WINDOW_SECONDS', 300);

// Uploads
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_UPLOAD_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// Error reporting
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

date_default_timezone_set('Asia/Kolkata');
