<?php
// Security helper functions

require_once __DIR__ . '/functions.php';

function csrf_token(): string
{
    return $_SESSION[CSRF_TOKEN_NAME] ?? '';
}

function sanitize_input($data)
{
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return trim($data);
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
    }
}

function require_csrf(): void
{
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $_POST[CSRF_TOKEN_NAME])) {
        json_response(['success' => false, 'message' => 'Invalid CSRF token.'], 419);
    }
}

function validate_uploaded_image(array $file): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error.'];
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File too large.'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_UPLOAD_EXTENSIONS, true)) {
        return ['success' => false, 'message' => 'Invalid file type.'];
    }
    return ['success' => true, 'extension' => $ext];
}

function secure_upload_image(array $file, string $folder = 'uploads'): array
{
    $check = validate_uploaded_image($file);
    if (!$check['success']) {
        return $check;
    }

    $targetDir = UPLOAD_PATH . '/' . $folder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $check['extension'];
    $target = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['success' => false, 'message' => 'Could not save file.'];
    }

    return ['success' => true, 'path' => 'assets/uploads/' . $folder . '/' . $filename];
}

function require_https(): void
{
    if (APP_ENV === 'production' && empty($_SERVER['HTTPS'])) {
        header('HTTP/1.1 403 Forbidden');
        exit('HTTPS required.');
    }
}