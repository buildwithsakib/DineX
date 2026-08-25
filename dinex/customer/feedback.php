<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-session.php';
require_once __DIR__ . '/../includes/feature-access.php';

$session = get_active_table_session();
if (!$session) {
    die('Session expired.');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $rating = (int)($_POST['rating'] ?? 5);
        $feedback = sanitize_input($_POST['feedback'] ?? '');
        if ($rating < 1 || $rating > 5) $rating = 5;
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO reviews (restaurant_id, table_session_id, rating, feedback, status) VALUES (:rid, :tsid, :rating, :feedback, "PENDING")');
        $stmt->execute([
            ':rid' => $session['restaurant_id'],
            ':tsid' => $session['id'],
            ':rating' => $rating,
            ':feedback' => $feedback,
        ]);
        audit_log('CUSTOMER', null, $session['restaurant_id'], 'Feedback submitted', ['rating'=>$rating]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm">
        <div class="max-w-3xl mx-auto px-4 py-3">
            <h1 class="text-xl font-bold">Anonymous Feedback</h1>
        </div>
    </header>
    <main class="max-w-3xl mx-auto px-4 py-6">
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Thank you for your feedback!</div>
        <?php else: ?>
            <?php if ($errors): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"><?= implode('<br>', array_map('e', $errors)) ?></div>
            <?php endif; ?>
            <form method="POST" class="bg-white rounded-xl shadow p-6">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-medium">Rating</label>
                    <select name="rating" class="mt-1 w-full border rounded px-3 py-2">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>"><?= $i ?> Star</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium">Feedback</label>
                    <textarea name="feedback" rows="4" class="mt-1 w-full border rounded px-3 py-2"></textarea>
                </div>
                <button type="submit" class="mt-4 bg-orange-600 text-white px-4 py-2 rounded">Submit</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>