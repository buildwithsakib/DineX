<?php
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineX – Scan. Order. Play. Enjoy.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-50 text-gray-900">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-2xl font-extrabold text-orange-600">DineX</span>
                <span class="hidden md:inline text-sm text-gray-500 font-medium">SCAN. ORDER. PLAY. ENJOY.</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="business-login.php" class="text-gray-700 hover:text-orange-600 text-sm font-medium">Sign In</a>
                <a href="register.php" class="bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-700 transition">Get Started Free</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="bg-gradient-to-br from-orange-50 via-white to-orange-50">
        <div class="max-w-6xl mx-auto px-4 py-20 text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">
                Gamified QR Ordering for<br class="hidden md:block"> Restaurants, Cafés & Hotels
            </h1>
            <p class="mt-6 text-lg md:text-xl text-gray-600 max-w-2xl mx-auto">
                Turn every table visit into a game. Customers scan, order, play, and win — no app, no hardware, no registration.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="register.php" class="bg-orange-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-orange-700 transition">
                    Get Started Free
                </a>
                <a href="business-login.php" class="bg-white border border-orange-600 text-orange-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-orange-50 transition">
                    Sign In
                </a>
            </div>
            <div class="mt-12 flex flex-wrap justify-center gap-6 text-sm text-gray-500">
                <span><i class="fa-solid fa-qrcode mr-1"></i> No hardware needed</span>
                <span><i class="fa-solid fa-mobile-screen mr-1"></i> Works on any phone</span>
                <span><i class="fa-solid fa-language mr-1"></i> Multi-language</span>
            </div>
        </div>
    </header>

    <!-- Games Marquee / Ticker -->
    <div class="bg-white border-y border-gray-200 py-4 overflow-hidden">
        <div class="flex whitespace-nowrap animate-marquee">
            <span class="mx-4 text-lg font-bold text-gray-800">🎡 Spin the Wheel</span>
            <span class="mx-4 text-lg font-bold text-gray-800">🎫 Instant Lottery</span>
            <span class="mx-4 text-lg font-bold text-gray-800">🎰 Slot Machine</span>
            <span class="mx-4 text-lg font-bold text-gray-800">🧺 Catch & Win</span>
            <span class="mx-4 text-lg font-bold text-gray-800">🐍 Snakes & Ladders</span>
            <span class="mx-4 text-lg font-bold text-gray-800">👆 Tap Speed</span>
            <!-- Duplicate for seamless loop -->
            <span class="mx-4 text-lg font-bold text-gray-800">🎡 Spin the Wheel</span>
            <span class="mx-4 text-lg font-bold text-gray-800">🎫 Instant Lottery</span>
            <span class="mx-4 text-lg font-bold text-gray-800">🎰 Slot Machine</span>
            <span class="mx-4 text-lg font-bold text-gray-800">🧺 Catch & Win</span>
            <span class="mx-4 text-lg font-bold text-gray-800">🐍 Snakes & Ladders</span>
            <span class="mx-4 text-lg font-bold text-gray-800">👆 Tap Speed</span>
        </div>
    </div>

    <!-- How It Works -->
    <section class="py-20">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center">
                <h2 class="text-3xl md:text-4xl font-bold">Up and running in minutes</h2>
                <p class="mt-4 text-gray-600">No tech skills required.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 mt-12">
                <div class="bg-white rounded-2xl shadow p-8 text-center">
                    <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mx-auto text-xl font-bold">1</div>
                    <h3 class="mt-4 font-semibold text-lg">Create an event</h3>
                    <p class="mt-2 text-gray-600 text-sm">Pick a game, set your prizes, and publish. Your QR code activates immediately.</p>
                </div>
                <div class="bg-white rounded-2xl shadow p-8 text-center">
                    <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mx-auto text-xl font-bold">2</div>
                    <h3 class="mt-4 font-semibold text-lg">Share your QR code</h3>
                    <p class="mt-2 text-gray-600 text-sm">Print it, display it at your counter, or share the link on social media.</p>
                </div>
                <div class="bg-white rounded-2xl shadow p-8 text-center">
                    <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mx-auto text-xl font-bold">3</div>
                    <h3 class="mt-4 font-semibold text-lg">Reward your customers</h3>
                    <p class="mt-2 text-gray-600 text-sm">Winners get a coupon code on their phone. You mark it redeemed when they visit.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="bg-white py-16 border-y border-gray-200">
        <div class="max-w-6xl mx-auto px-4 grid grid-cols-3 gap-8 text-center">
            <div>
                <p class="text-4xl font-extrabold text-orange-600">6</p>
                <p class="mt-2 text-gray-600">Ready-to-play games</p>
            </div>
            <div>
                <p class="text-4xl font-extrabold text-orange-600">&lt;5</p>
                <p class="mt-2 text-gray-600">Minutes to launch</p>
            </div>
            <div>
                <p class="text-4xl font-extrabold text-orange-600">0</p>
                <p class="mt-2 text-gray-600">Hardware required</p>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-20">
        <div class="max-w-6xl mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center">Everything you need</h2>
            <p class="mt-4 text-center text-gray-600">From setup to redemption, DineX handles it all.</p>
            <div class="grid md:grid-cols-2 gap-8 mt-12">
                <div class="bg-white rounded-2xl shadow p-8">
                    <div class="text-orange-600 text-2xl font-bold">01</div>
                    <h3 class="mt-2 font-semibold text-lg">Permanent QR Code</h3>
                    <p class="mt-2 text-gray-600">Print it once, use it forever. Your QR code never changes — just update the active game.</p>
                </div>
                <div class="bg-white rounded-2xl shadow p-8">
                    <div class="text-orange-600 text-2xl font-bold">02</div>
                    <h3 class="mt-2 font-semibold text-lg">6 Built-in Games</h3>
                    <p class="mt-2 text-gray-600">Spin the wheel, scratch cards, slot machine, and more. Customers love them.</p>
                </div>
                <div class="bg-white rounded-2xl shadow p-8">
                    <div class="text-orange-600 text-2xl font-bold">03</div>
                    <h3 class="mt-2 font-semibold text-lg">Smart Coupons</h3>
                    <p class="mt-2 text-gray-600">Winners get unique codes. Track issuance, redemption, and expiry from your dashboard.</p>
                </div>
                <div class="bg-white rounded-2xl shadow p-8">
                    <div class="text-orange-600 text-2xl font-bold">04</div>
                    <h3 class="mt-2 font-semibold text-lg">Real-time Analytics</h3>
                    <p class="mt-2 text-gray-600">See scans, plays, wins, and redemption rates — updated live as customers engage.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Games Showcase -->
    <section class="py-20 bg-white border-t border-gray-200">
        <div class="max-w-6xl mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center">Choose your game</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-8 mt-12">
                <div class="text-center">
                    <div class="text-6xl">🎡</div>
                    <h3 class="mt-4 font-semibold">Spin the Wheel</h3>
                    <p class="mt-2 text-sm text-gray-600">Customers spin a carnival wheel to land on a prize segment</p>
                </div>
                <div class="text-center">
                    <div class="text-6xl">🎫</div>
                    <h3 class="mt-4 font-semibold">Instant Lottery</h3>
                    <p class="mt-2 text-sm text-gray-600">Scratch three cards to reveal matching symbols and win</p>
                </div>
                <div class="text-center">
                    <div class="text-6xl">🎰</div>
                    <h3 class="mt-4 font-semibold">Slot Machine</h3>
                    <p class="mt-2 text-sm text-gray-600">Pull the lever — matching symbols mean a coupon reward</p>
                </div>
                <div class="text-center">
                    <div class="text-6xl">🧺</div>
                    <h3 class="mt-4 font-semibold">Catch & Win</h3>
                    <p class="mt-2 text-sm text-gray-600">Move the basket to catch falling items and collect coupons</p>
                </div>
                <div class="text-center">
                    <div class="text-6xl">🐍</div>
                    <h3 class="mt-4 font-semibold">Snakes & Ladders</h3>
                    <p class="mt-2 text-sm text-gray-600">Roll dice and race to the finish — land on opponents to eliminate them!</p>
                </div>
                <div class="text-center">
                    <div class="text-6xl">👆</div>
                    <h3 class="mt-4 font-semibold">Tap Speed</h3>
                    <p class="mt-2 text-sm text-gray-600">Tap as fast as possible in 7 seconds for better rewards</p>
                </div>
            </div>
            <p class="mt-12 text-center text-gray-600 max-w-2xl mx-auto">
                Each customer scans once, plays instantly — no account, no download, just delight.
            </p>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 bg-gradient-to-br from-orange-600 to-orange-500 text-white">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold">Ready to delight your customers?</h2>
            <p class="mt-4 text-orange-100">Join business owners already using DineX to drive repeat visits.</p>
            <a href="register.php" class="mt-8 inline-block bg-white text-orange-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transition">
                Get Started Free
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-10">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="text-lg font-semibold text-white">© <?= date('Y') ?> DineX. Built for local businesses.</p>
            <p class="mt-2 text-sm text-gray-400">Designed and developed by DineX Team</p>
            <div class="mt-4 text-sm">
                <a href="#" class="hover:text-white">Terms & Conditions</a>
            </div>
        </div>
    </footer>

    <!-- Marquee animation -->
    <style>
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee {
            animation: marquee 20s linear infinite;
        }
    </style>
</body>
</html>
