<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/rate-limit.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!rate_limit_check(get_client_ip(), 'founder_login')) {
            $errors[] = 'Too many login attempts. Try again later.';
        } else {
            if (empty($email) || empty($password)) {
                $errors[] = 'Email and password are required.';
            } else {
                $result = founder_login($email, $password);
                if ($result['success']) {
                    rate_limit_clear(get_client_ip(), 'founder_login');
                    redirect(BASE_URL . '/admin/founder/dashboard.php');
                }
                $errors[] = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Founder Login — DineX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-2xl font-bold text-gray-900">DineX Founder</h1>
        <p class="text-sm text-gray-500 mt-1">Platform Administration</p>

        <?php if ($errors): ?>
            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="mt-6 space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium">Email</label>
                <input type="email" name="email" required class="mt-1 w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium">Password</label>
                <input type="password" name="password" required class="mt-1 w-full border rounded-lg px-3 py-2">
            </div>
            <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-700">Login</button>
        </form>
        <p class="mt-4 text-xs text-gray-500 text-center">Default demo: founder@dinex.local / password</p>
    </div>
</body>
</html>
