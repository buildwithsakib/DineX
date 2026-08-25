<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/authorization.php';

$staff = require_restaurant_auth();
$pdo = db();
$restaurantId = $staff['restaurant_id'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        $reviewId = (int)($_POST['review_id'] ?? 0);
        if ($action === 'approve') {
            $stmt = $pdo->prepare('UPDATE reviews SET status = :status WHERE id = :id AND restaurant_id = :rid');
            $stmt->execute([':status'=>'APPROVED', ':id'=>$reviewId, ':rid'=>$restaurantId]);
            audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Review approved', ['review_id'=>$reviewId]);
            $success = true;
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare('UPDATE reviews SET status = :status WHERE id = :id AND restaurant_id = :rid');
            $stmt->execute([':status'=>'REJECTED', ':id'=>$reviewId, ':rid'=>$restaurantId]);
            audit_log('RESTAURANT', $staff['id'], $restaurantId, 'Review rejected', ['review_id'=>$reviewId]);
            $success = true;
        }
    }
}

$reviews = $pdo->query("SELECT * FROM reviews WHERE restaurant_id = $restaurantId ORDER BY id DESC LIMIT 200")->fetchAll();

$pageTitle = 'Reviews & Feedback';
include __DIR__ . '/../templates/restaurant-header.php';
?>
<main class="p-6">
    <h1 class="text-3xl font-bold text-gray-900">Anonymous Feedback</h1>

    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">Updated.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="mt-6 bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3">ID</th><th class="px-6 py-3">Rating</th><th class="px-6 py-3">Feedback</th>
                    <th class="px-6 py-3">Status</th><th class="px-6 py-3">Date</th><th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($reviews as $review): ?>
                <tr>
                    <td class="px-6 py-3"><?= (int)$review['id'] ?></td>
                    <td class="px-6 py-3"><?= (int)$review['rating'] ?>/5</td>
                    <td class="px-6 py-3"><?= e($review['feedback'] ?? '') ?></td>
                    <td class="px-6 py-3"><?= e($review['status']) ?></td>
                    <td class="px-6 py-3"><?= e(date('d M Y', strtotime($review['created_at']))) ?></td>
                    <td class="px-6 py-3">
                        <?php if ($review['status'] === 'PENDING'): ?>
                            <form method="POST" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                                <button type="submit" class="text-green-600 hover:underline">Approve</button>
                            </form>
                            <form method="POST" class="inline ml-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                                <button type="submit" class="text-red-600 hover:underline">Reject</button>
                            </form>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../templates/restaurant-footer.php'; ?>