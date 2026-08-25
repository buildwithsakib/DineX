<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-session.php';
require_once __DIR__ . '/../includes/feature-access.php';

$session = get_active_table_session();
if (!$session) {
    die('Session expired or invalid. Please scan QR again.');
}

// We'll use client-side localStorage cart, so cart page just reads it via JS.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
            <h1 class="text-xl font-bold">Your Cart</h1>
            <a href="menu.php?token=<?= e($_SESSION['session_token'] ?? '') ?>" class="text-orange-600">Back to Menu</a>
        </div>
    </header>
    <main class="max-w-3xl mx-auto px-4 py-6">
        <div id="cartItems" class="space-y-4"></div>
        <div class="mt-6 bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold">Order Summary</h2>
            <div id="cartSummary" class="mt-2 text-sm"></div>
            <button onclick="placeOrder()" class="mt-4 w-full bg-orange-600 text-white px-4 py-3 rounded-lg">Place Order</button>
        </div>
    </main>
    <script>
        let cart = JSON.parse(localStorage.getItem('dinex_cart') || '{}');
        function renderCart() {
            const container = document.getElementById('cartItems');
            container.innerHTML = '';
            let total = 0;
            for (const id in cart) {
                const item = cart[id];
                const subtotal = item.price * item.qty;
                total += subtotal;
                container.innerHTML += `
                    <div class="bg-white rounded-xl shadow p-4 flex justify-between items-center">
                        <div>
                            <p class="font-medium">${item.name}</p>
                            <p class="text-sm text-gray-500">₹${item.price.toFixed(2)} x ${item.qty}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="changeQty('${id}', -1)" class="w-8 h-8 bg-gray-200 rounded">-</button>
                            <span>${item.qty}</span>
                            <button onclick="changeQty('${id}', 1)" class="w-8 h-8 bg-gray-200 rounded">+</button>
                        </div>
                    </div>`;
            }
            document.getElementById('cartSummary').innerHTML = `<p>Total: <span class="font-bold">₹${total.toFixed(2)}</span></p>`;
        }
        function changeQty(id, delta) {
            if (cart[id]) {
                cart[id].qty += delta;
                if (cart[id].qty <= 0) delete cart[id];
                localStorage.setItem('dinex_cart', JSON.stringify(cart));
                renderCart();
            }
        }
        function placeOrder() {
            if (Object.keys(cart).length === 0) {
                alert('Cart is empty');
                return;
            }
            // Send to checkout API
            fetch('<?= FULL_BASE_URL ?>/api/customer/place-order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart: cart })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    localStorage.removeItem('dinex_cart');
                    window.location.href = 'order-status.php?order_id=' + data.order_id;
                } else {
                    alert(data.message || 'Error placing order');
                }
            });
        }
        renderCart();
    </script>
</body>
</html>
