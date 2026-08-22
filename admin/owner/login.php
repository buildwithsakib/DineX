<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/csrf.php';
require_once INCLUDES_PATH . '/rate-limit.php';
require_once INCLUDES_PATH . '/validation.php';

if (current_user($pdo)) {
    redirect('/dinex/admin/owner/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    if (!check_rate_limit('owner_login', 5, 300)) {
        $error = 'Too many login attempts. Try again later.';
    } else {
        $email = sanitize_text((string)($_POST['email'] ?? ''), 190);
        $password = (string)($_POST['password'] ?? '');

        if (!validate_email($email) || $password === '') {
            $error = 'Invalid email or password.';
        } else {
            $user = attempt_login($pdo, $email, $password, ROLE_OWNER);
            if ($user && !empty($user['restaurant_id'])) {
                redirect('/dinex/admin/owner/dashboard.php');
            } else {
                record_attempt('owner_login');
                $error = 'Invalid credentials or owner access denied.';
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
    <title>Owner Login - DineX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/dinex/assets/css/admin.css">
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">DineX</h1>
            <p class="text-slate-500">Restaurant Owner Login</p>
        </div>
        <?php if ($error): ?><div class="bg-red-50 text-red-700 p-3 rounded-lg mb-4"><?= e($error) ?></div><?php endif; ?>
        <form method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <input type="email" name="email" required placeholder="Email" class="w-full border rounded-lg px-4 py-2">
            <input type="password" name="password" required placeholder="Password" class="w-full border rounded-lg px-4 py-2">
            <button class="w-full bg-slate-900 text-white py-2 rounded-lg hover:bg-slate-700">Login</button>
        </form>
    </div>
</body>
</html>
