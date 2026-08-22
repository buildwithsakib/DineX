<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/authorization.php';
require_once INCLUDES_PATH . '/csrf.php';

$user = require_role($pdo, ['owner']);
$restaurantId = $user['restaurant_id'];
$pageTitle = 'Reviews';
$activePage = 'reviews';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    $reviewId = validate_int($_POST['review_id'] ?? 0, 1);
    if ($reviewId) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE reviews SET status='APPROVED' WHERE id=? AND restaurant_id=?")->execute([$reviewId, $restaurantId]);
        } elseif ($action === 'hide') {
            $pdo->prepare("UPDATE reviews SET status='HIDDEN' WHERE id=? AND restaurant_id=?")->execute([$reviewId, $restaurantId]);
        }
        add_audit_log($pdo, $restaurantId, $user['id'], 'moderated review', 'review', $reviewId, $action);
    }
    redirect('/dinex/admin/owner/reviews.php');
}

$reviews = $pdo->prepare("SELECT r.*, t.table_number, o.order_number FROM reviews r
                          LEFT JOIN table_sessions ts ON ts.id = r.table_session_id
                          LEFT JOIN tables t ON t.id = ts.table_id
                          LEFT JOIN orders o ON o.id = r.order_id
                          WHERE r.restaurant_id=? ORDER BY r.id DESC LIMIT 100");
$reviews->execute([$restaurantId]);
$reviews = $reviews->fetchAll();

require_once INCLUDES_PATH . '/admin_header.php';
?>
<h1 class="text-2xl font-bold mb-6">Customer Reviews</h1>
<div class="bg-white rounded-xl shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50"><tr><th class="p-3">ID</th><th>Rating</th><th>Comment</th><th>Table</th><th>Order</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($reviews as $r): ?>
                <tr class="border-t">
                    <td class="p-3"><?= (int)$r['id'] ?></td>
                    <td><?= str_repeat('★', (int)$r['rating']) ?><?= str_repeat('☆', 5 - (int)$r['rating']) ?></td>
                    <td><?= e($r['comment']) ?></td>
                    <td><?= e($r['table_number'] ?? 'N/A') ?></td>
                    <td><?= e($r['order_number'] ?? 'N/A') ?></td>
                    <td><?= e($r['status']) ?></td>
                    <td class="flex gap-2">
                        <?php if ($r['status'] === 'PENDING' || $r['status'] === 'HIDDEN'): ?>
                            <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="approve"><input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>"><button class="text-emerald-600 hover:underline text-sm">Approve</button></form>
                        <?php endif; ?>
                        <?php if ($r['status'] !== 'HIDDEN'): ?>
                            <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="hide"><input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>"><button class="text-red-600 hover:underline text-sm">Hide</button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once INCLUDES_PATH . '/admin_footer.php'; ?>