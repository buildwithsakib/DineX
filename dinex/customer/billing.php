<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-session.php';
require_once __DIR__ . '/../includes/feature-access.php';

$session = get_active_table_session();
if (!$session) {
    die('Session expired.');
}

$pdo = db();
$restaurantId = $session['restaurant_id'];
$sessionId = $session['id'];

// Find latest order for this session
$orderStmt = $pdo->prepare('SELECT * FROM orders WHERE restaurant_id = :rid AND session_id = :sid ORDER BY id DESC LIMIT 1');
$orderStmt->execute([':rid'=>$restaurantId, ':sid'=>$sessionId]);
$order = $orderStmt->fetch();
if (!$order) {
    die('No order found for this session.');
}

// Check if bill exists
$billStmt = $pdo->prepare('SELECT * FROM bills WHERE restaurant_id = :rid AND order_id = :oid LIMIT 1');
$billStmt->execute([':rid'=>$restaurantId, ':oid'=>$order['id']]);
$bill = $billStmt->fetch();
if (!$bill) {
    // Generate bill if missing
    $billNumber = 'BILL' . strtoupper(bin2hex(random_bytes(3)));
    $stmt = $pdo->prepare('INSERT INTO bills (restaurant_id, order_id, table_session_id, bill_number, subtotal, tax_amount, discount_amount, total_amount, status) VALUES (:rid, :oid, :tsid, :billno, :subtotal, :tax, :discount, :total, "PENDING")');
    $stmt->execute([
        ':rid'=>$restaurantId,
        ':oid'=>$order['id'],
        ':tsid'=>$sessionId,
        ':billno'=>$billNumber,
        ':subtotal'=>$order['subtotal'],
        ':tax'=>$order['tax_amount'],
        ':discount'=>$order['discount_amount'],
        ':total'=>$order['total_amount'],
    ]);
    $billId = (int)$pdo->lastInsertId();
    $billStmt = $pdo->prepare('SELECT * FROM bills WHERE id = :id');
    $billStmt->execute([':id'=>$billId]);
    $bill = $billStmt->fetch();
}

$payments = $pdo->prepare('SELECT * FROM payments WHERE bill_id = :bill_id');
$payments->execute([':bill_id'=>$bill['id']]);
$paymentList = $payments->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
            <h1 class="text-xl font-bold">Your Bill</h1>
            <a href="order-status.php?order_id=<?= (int)$order['id'] ?>" class="text-orange-600 text-sm">Back to Order</a>
        </div>
    </header>
    <main class="max-w-3xl mx-auto px-4 py-6">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm">Bill #: <?= e($bill['bill_number']) ?></p>
            <p class="text-sm">Order #: <?= e($order['order_number']) ?></p>
            <hr class="my-4">
            <div class="space-y-2">
                <p>Subtotal: ₹<?= e(number_format($bill['subtotal'], 2)) ?></p>
                <p>Tax: ₹<?= e(number_format($bill['tax_amount'], 2)) ?></p>
                <p>Discount: ₹<span id="discountDisplay"><?= e(number_format($bill['discount_amount'], 2)) ?></span></p>
                <p class="font-bold text-lg">Total: ₹<span id="totalDisplay"><?= e(number_format($bill['total_amount'], 2)) ?></span></p>
            </div>

            <!-- Coupon Section -->
            <?php if ((float)$bill['discount_amount'] <= 0 && $bill['status'] === 'PENDING'): ?>
                <div class="mt-6 border-t pt-4">
                    <h2 class="font-semibold">Have a coupon code?</h2>
                    <div class="mt-2 flex gap-2">
                        <input type="text" id="couponInput" placeholder="Enter coupon code" class="border rounded px-3 py-2 flex-1">
                        <button onclick="applyCoupon()" class="bg-blue-600 text-white px-4 py-2 rounded">Apply</button>
                    </div>
                    <p id="couponMessage" class="mt-2 text-sm"></p>
                </div>
            <?php else: ?>
                <div class="mt-6 border-t pt-4 text-sm text-gray-500">
                    <?php if ((float)$bill['discount_amount'] > 0): ?>
                        Coupon applied. Discount: ₹<?= e(number_format($bill['discount_amount'], 2)) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($bill['status'] === 'PENDING'): ?>
                <div class="mt-6">
                    <button onclick="payBill(<?= (int)$bill['id'] ?>)" class="w-full bg-green-600 text-white px-6 py-3 rounded-lg">Pay Now</button>
                </div>
            <?php else: ?>
                <p class="mt-6 text-green-600 font-semibold">Paid</p>
                <?php foreach ($paymentList as $p): ?>
                    <p class="text-sm">Transaction: <?= e($p['transaction_id'] ?? 'N/A') ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Pre-fill coupon if stored from game
        window.addEventListener('DOMContentLoaded', () => {
            const savedCoupon = localStorage.getItem('dinex_coupon');
            if (savedCoupon && document.getElementById('couponInput')) {
                document.getElementById('couponInput').value = savedCoupon;
            }
        });

        async function applyCoupon() {
            const code = document.getElementById('couponInput').value.trim();
            if (!code) {
                document.getElementById('couponMessage').innerText = 'Please enter a coupon code.';
                return;
            }
            const res = await fetch('<?= FULL_BASE_URL ?>/api/customer/redeem-coupon.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({coupon_code: code})
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('discountDisplay').innerText = Number(data.discount_amount).toFixed(2);
                document.getElementById('totalDisplay').innerText = Number(data.new_total).toFixed(2);
                document.getElementById('couponMessage').innerText = data.message;
                localStorage.removeItem('dinex_coupon'); // Clear saved coupon
                // Disable coupon section (simple reload)
                location.reload();
            } else {
                document.getElementById('couponMessage').innerText = data.message || 'Error applying coupon';
            }
        }

        async function payBill(billId) {
            const res = await fetch('<?= FULL_BASE_URL ?>/api/customer/pay-bill.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({bill_id: billId})
            });
            const data = await res.json();
            if (data.success) {
                alert('Payment successful!');
                location.reload();
            } else {
                alert(data.message || 'Payment failed');
            }
        }
    </script>
</body>
</html>