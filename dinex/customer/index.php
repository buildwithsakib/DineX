<?php
require_once __DIR__ . '/../includes/functions.php';
$token = $_GET['token'] ?? '';
if ($token) {
    header('Location: menu.php?token=' . urlencode($token));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineX Customer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Welcome to DineX</h1>
        <p class="text-gray-600 mt-2">Please scan a table QR code to start ordering.</p>
    </div>
</body>
</html>