<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/customer-session.php';

$gameId = (int)($_GET['game_id'] ?? 0);
if ($gameId <= 0) die('Invalid game.');
$session = get_active_table_session();
if (!$session) die('Session expired.');
$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM games WHERE id = :id AND restaurant_id = :rid AND is_active = 1 LIMIT 1');
$stmt->execute([':id'=>$gameId, ':rid'=>$session['restaurant_id']]);
$game = $stmt->fetch();
if (!$game) die('Game not available.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Spin & Win | DineX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>const FULL_BASE_URL = "<?= FULL_BASE_URL ?>";</script>
    <script src="<?= FULL_BASE_URL ?>/games/common/play.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800;900&display=swap');
        
        :root {
            --wheel-size: 320px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow: hidden; /* Prevent scrolling, full app-like experience */
            overscroll-behavior-y: none;
        }

        /* Animated Background */
        .bg-animated {
            background: radial-gradient(circle at 50% 50%, #2e094b, #0f0518);
            position: relative;
            z-index: 0;
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.6;
            z-index: -1;
            animation: float 10s infinite alternate ease-in-out;
        }
        .blob-1 { top: -10%; left: -10%; width: 300px; height: 300px; background: #9d4edd; animation-delay: 0s; }
        .blob-2 { bottom: -10%; right: -10%; width: 250px; height: 250px; background: #ff006e; animation-delay: -3s; }
        .blob-3 { top: 40%; left: 60%; width: 200px; height: 200px; background: #ffbe0b; animation-delay: -6s; }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 50px) scale(1.1); }
        }

        /* Wheel Styles */
        .wheel-container {
            position: relative;
            width: var(--wheel-size);
            height: var(--wheel-size);
            margin: 0 auto;
            filter: drop-shadow(0 0 20px rgba(157, 78, 221, 0.4));
        }
        
        .wheel {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            transition: transform 4s cubic-bezier(0.15, 0.85, 0.25, 1);
            transform-origin: center center;
            will-change: transform;
        }

        .wheel-center-hub {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70px;
            height: 70px;
            background: radial-gradient(circle, #fff, #e0e0e0);
            border: 4px solid #3c096c;
            border-radius: 50%;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: #3c096c;
            box-shadow: 0 0 15px rgba(0,0,0,0.5), inset 0 0 10px rgba(0,0,0,0.2);
            text-shadow: 0 1px 2px rgba(255,255,255,0.8);
        }

        .pulse-idle {
            animation: pulse-glow 2s infinite alternate;
        }

        @keyframes pulse-glow {
            0% { box-shadow: 0 0 15px rgba(255, 0, 110, 0.4); }
            100% { box-shadow: 0 0 35px rgba(255, 0, 110, 0.8); }
        }

        /* Pointer */
        .wheel-pointer {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            z-index: 20;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4));
        }

        /* Modals & Effects */
        .glass-panel {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        #confetti-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        }

        .modal-enter {
            animation: modalPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes modalPop {
            0% { opacity: 0; transform: scale(0.8) translateY(20px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        
        .spin-btn {
            background: linear-gradient(135deg, #ff006e, #ffbe0b);
            background-size: 200% 200%;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px rgba(255, 0, 110, 0.5);
        }
        
        .spin-btn:hover:not(:disabled) {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 15px 25px -5px rgba(255, 0, 110, 0.6);
        }
        
        .spin-btn:active:not(:disabled) {
            transform: translateY(2px) scale(0.98);
        }

        .spin-btn:disabled {
            background: #4b5563;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
            opacity: 0.7;
        }

        /* Responsive adjustments */
        @media (max-width: 380px) {
            :root { --wheel-size: 280px; }
            h1 { font-size: 1.75rem !important; }
        }
    </style>
</head>
<body class="bg-animated min-h-[100dvh] flex flex-col items-center justify-between py-6 px-4 text-white">

    <!-- Background Elements -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
    <canvas id="confetti-canvas"></canvas>

    <!-- Top Navigation / Controls -->
    <div class="w-full max-w-md flex justify-between items-center z-10 px-2">
        <a href="<?= FULL_BASE_URL ?>/customer/menu.php?token=<?= e($_SESSION['session_token'] ?? '') ?>" 
           class="glass-panel px-4 py-2 rounded-full text-sm font-semibold hover:bg-white/20 transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Menu
        </a>
        <button id="soundToggle" onclick="toggleSound()" class="glass-panel w-10 h-10 rounded-full flex items-center justify-center text-lg hover:bg-white/20 transition" aria-label="Toggle Sound">
            🔊
        </button>
    </div>

    <!-- Header Section -->
    <div class="text-center z-10 mt-4">
        <div class="inline-block bg-white/10 px-3 py-1 rounded-full text-xs font-bold tracking-wider mb-3 text-pink-300 border border-pink-300/30 uppercase">
            <i class="fa-solid fa-gift mr-1"></i> Daily Reward
        </div>
        <h1 class="text-4xl font-black mb-1 bg-clip-text text-transparent bg-gradient-to-r from-yellow-300 to-pink-500 tracking-tight" style="text-shadow: 0 4px 10px rgba(0,0,0,0.3);">
            SPIN & WIN
        </h1>
        <p class="text-gray-300 text-sm font-medium">Try your luck for amazing treats!</p>
    </div>

    <!-- The Wheel -->
    <div class="wheel-container z-10 my-8">
        <!-- Pointer (SVG) -->
        <svg class="wheel-pointer" viewBox="0 0 100 100">
            <path d="M50 90 L20 20 L80 20 Z" fill="#ffbe0b" stroke="#fff" stroke-width="4" filter="drop-shadow(0 5px 5px rgba(0,0,0,0.5))" />
            <circle cx="50" cy="30" r="8" fill="#fff" />
        </svg>

        <!-- Dynamic SVG Wheel -->
        <svg id="wheelSvg" class="wheel" viewBox="0 0 400 400">
            <defs>
                <filter id="shadow">
                    <feDropShadow dx="0" dy="0" stdDeviation="4" flood-opacity="0.5"/>
                </filter>
            </defs>
            <g id="wheelGroup">
                <!-- Slices will be generated via JS -->
            </g>
            <!-- Outer decorative ring -->
            <circle cx="200" cy="200" r="196" fill="none" stroke="#ffffff" stroke-width="8" opacity="0.3"/>
            <circle cx="200" cy="200" r="190" fill="none" stroke="#ffbe0b" stroke-width="4" filter="url(#shadow)"/>
        </svg>

        <div class="wheel-center-hub pulse-idle" id="centerHub">
            SPIN
        </div>
    </div>

    <!-- Action Section -->
    <div class="z-10 w-full max-w-sm text-center mb-4">
        <button id="spinBtn" onclick="startSpin()" class="spin-btn w-full text-white font-black text-xl py-4 rounded-2xl flex items-center justify-center gap-3 tracking-wide">
            <i class="fa-solid fa-rotate"></i> 
            <span>SPIN NOW</span>
        </button>
    </div>

    <!-- WIN MODAL (Hidden by default) -->
    <div id="winModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden bg-black/60 backdrop-blur-sm">
        <div class="glass-panel w-full max-w-sm rounded-3xl p-8 text-center relative overflow-hidden transform scale-90 opacity-0 transition-all duration-300" id="winModalContent">
            <!-- Decorative burst -->
            <div class="absolute -top-16 -left-16 w-32 h-32 bg-yellow-400 rounded-full mix-blend-overlay filter blur-2xl opacity-50"></div>
            <div class="absolute -bottom-16 -right-16 w-32 h-32 bg-pink-500 rounded-full mix-blend-overlay filter blur-2xl opacity-50"></div>
            
            <div class="text-6xl mb-4 bounce">🎉</div>
            <h2 class="text-3xl font-black text-white mb-2 tracking-tight">YOU WON!</h2>
            <div id="winTitle" class="text-2xl font-bold text-yellow-300 mb-4 bg-black/20 py-2 rounded-xl border border-yellow-300/20">
                <!-- Reward Name Here -->
            </div>
            
            <div id="couponContainer" class="hidden mb-6">
                <p class="text-sm text-gray-300 mb-2">Your exclusive coupon code:</p>
                <div class="flex items-center bg-white/10 rounded-xl p-2 border border-white/20">
                    <code id="couponCode" class="flex-1 text-lg font-bold text-white tracking-wider"></code>
                    <button onclick="copyCoupon()" class="bg-white/20 hover:bg-white/30 p-3 rounded-lg transition" aria-label="Copy Code">
                        <i id="copyIcon" class="fa-regular fa-copy text-white"></i>
                    </button>
                </div>
            </div>

            <p id="winMessage" class="text-sm text-gray-200 mb-8"></p>

            <button onclick="closeModal()" class="w-full bg-white text-purple-900 font-bold py-3 rounded-xl hover:bg-gray-100 transition shadow-lg">
                Awesome!
            </button>
        </div>
    </div>

    <!-- LOSE / TRY AGAIN MODAL (Hidden by default) -->
    <div id="loseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden bg-black/60 backdrop-blur-sm">
        <div class="glass-panel w-full max-w-sm rounded-3xl p-8 text-center relative transform scale-90 opacity-0 transition-all duration-300" id="loseModalContent">
            <div class="text-6xl mb-4">😅</div>
            <h2 class="text-2xl font-black text-white mb-2">Oh no!</h2>
            <p id="loseMessage" class="text-gray-300 mb-8">Better luck next time. Give it another try tomorrow!</p>
            <button onclick="closeModal()" class="w-full bg-white/20 text-white font-bold py-3 rounded-xl border border-white/30 hover:bg-white/30 transition">
                Close
            </button>
        </div>
    </div>

    <script>
        // Wheel Configuration
        const slices = [
            { label: "10% OFF", color: "#8b5cf6" },
            { label: "FREE DRINK", color: "#ec4899" },
            { label: "50 POINTS", color: "#f59e0b" },
            { label: "TRY AGAIN", color: "#10b981" },
            { label: "20% OFF", color: "#6366f1" },
            { label: "DESSERT", color: "#f43f5e" },
            { label: "100 POINTS", color: "#14b8a6" },
            { label: "MYSTERY", color: "#3b82f6" }
        ];

        // Draw SVG Wheel
        const wheelGroup = document.getElementById('wheelGroup');
        const numSlices = slices.length;
        const sliceAngle = 360 / numSlices;

        slices.forEach((slice, index) => {
            const angle = index * sliceAngle;
            
            // SVG Math for 1 slice (0 to 45 deg)
            // Center is 200,200. Radius is 200.
            // Start point: x=200, y=0.
            // End point (45deg): x = 200 + 200 * sin(45), y = 200 - 200 * cos(45)
            // sin(45) = 0.7071, cos(45) = 0.7071 -> 341.42, 58.58
            
            const g = document.createElementNS("http://www.w3.org/2000/svg", "g");
            g.setAttribute("transform", `rotate(${angle}, 200, 200)`);

            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
            path.setAttribute("d", "M200,200 L200,0 A200,200 0 0,1 341.42,58.58 Z");
            path.setAttribute("fill", slice.color);
            path.setAttribute("stroke", "rgba(0,0,0,0.1)");
            path.setAttribute("stroke-width", "2");

            const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
            // Center text inside the 45-degree wedge (at 22.5 degrees)
            text.setAttribute("x", "200");
            text.setAttribute("y", "40");
            text.setAttribute("fill", "#ffffff");
            text.setAttribute("font-size", "18");
            text.setAttribute("font-weight", "800");
            text.setAttribute("text-anchor", "middle");
            text.setAttribute("transform", "rotate(22.5, 200, 200)");
            text.textContent = slice.label;
            
            // Text shadow for readability
            text.style.textShadow = "1px 1px 3px rgba(0,0,0,0.5)";

            g.appendChild(path);
            g.appendChild(text);
            wheelGroup.appendChild(g);
        });

        // State & Audio
        let isSpinning = false;
        let currentRotation = 0;
        let soundEnabled = true;
        let audioCtx = null;
        let tickTimeout = null;

        function initAudio() {
            if (!audioCtx) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) audioCtx = new AudioContext();
            }
        }

        function playTone(freq, type, duration, vol = 0.1) {
            if (!soundEnabled || !audioCtx) return;
            try {
                if(audioCtx.state === 'suspended') audioCtx.resume();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = type;
                osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
                
                gain.gain.setValueAtTime(vol, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
                
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + duration);
            } catch(e) {}
        }

        function toggleSound() {
            soundEnabled = !soundEnabled;
            document.getElementById('soundToggle').innerHTML = soundEnabled ? '🔊' : '🔇';
            if (soundEnabled) {
                initAudio();
                playTone(600, 'sine', 0.1); // test sound
            }
        }

        function scheduleTicks(duration, baseInterval) {
            if (!soundEnabled) return;
            let elapsed = 0;
            
            function tick() {
                if (elapsed >= duration) return;
                playTone(800, 'triangle', 0.05, 0.03);
                
                // Slow down the ticking exponentially based on how much time has passed
                let progress = elapsed / duration;
                let nextInterval = baseInterval + (Math.pow(progress, 3) * 300); 
                
                elapsed += nextInterval;
                if (elapsed < duration) {
                    tickTimeout = setTimeout(tick, nextInterval);
                }
            }
            tick();
        }

        // Confetti System
        const canvas = document.getElementById('confetti-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];
        let confettiAnimationId = null;

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        function createConfetti() {
            particles = [];
            const colors = ['#ffbe0b', '#fb5607', '#ff006e', '#8338ec', '#3a86ff', '#00f5d4'];
            for (let i = 0; i < 150; i++) {
                particles.push({
                    x: canvas.width / 2,
                    y: canvas.height / 2,
                    r: Math.random() * 6 + 4,
                    dx: Math.random() * 20 - 10,
                    dy: Math.random() * -20 - 5,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    tilt: Math.random() * 10
                });
            }
            if (!confettiAnimationId) animateConfetti();
        }

        function animateConfetti() {
            if (particles.length === 0) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                confettiAnimationId = null;
                return;
            }
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach((p, i) => {
                p.x += p.dx;
                p.y += p.dy;
                p.dy += 0.4; // Gravity
                p.tilt += 0.1;
                
                ctx.beginPath();
                ctx.lineWidth = p.r;
                ctx.strokeStyle = p.color;
                ctx.moveTo(p.x + p.tilt + p.r, p.y);
                ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r);
                ctx.stroke();
                
                if (p.y > canvas.height) particles.splice(i, 1);
            });
            confettiAnimationId = requestAnimationFrame(animateConfetti);
        }

        // Main Game Logic
        async function startSpin() {
            if (isSpinning) return;
            initAudio(); // Required for iOS to unlock audio context on user interaction
            
            isSpinning = true;
            const btn = document.getElementById('spinBtn');
            const wheel = document.getElementById('wheelSvg');
            const centerHub = document.getElementById('centerHub');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>SPINNING...</span>';
            centerHub.classList.remove('pulse-idle');

            // Add base rotation to make it feel fast instantly, we will adjust landing via API response
            const extraSpins = 5 * 360; 
            const randomOffset = Math.floor(Math.random() * 360);
            currentRotation += extraSpins + randomOffset;
            
            wheel.style.transform = `rotate(${currentRotation}deg)`;
            
            // Start audio ticking (approx 4 seconds duration to match CSS transition)
            scheduleTicks(4000, 30);

            try {
                // Call actual backend - Authoritative Source
                const data = await playGame(<?= $gameId ?>);
                
                // Wait for the physical CSS animation to finish (4s)
                setTimeout(() => {
                    clearTimeout(tickTimeout);
                    handleResult(data);
                }, 4100);

            } catch (error) {
                setTimeout(() => {
                    clearTimeout(tickTimeout);
                    handleResult({ success: false, message: 'Network error. Please try again.' });
                }, 4100);
            }
        }

        function handleResult(data) {
            const btn = document.getElementById('spinBtn');
            const centerHub = document.getElementById('centerHub');
            
            btn.innerHTML = '<i class="fa-solid fa-rotate"></i> <span>SPIN AGAIN</span>';
            btn.disabled = false;
            isSpinning = false;
            centerHub.classList.add('pulse-idle');

            if (data.success) {
                // Play Win Sound
                playTone(400, 'sine', 0.2);
                setTimeout(() => playTone(600, 'sine', 0.2), 150);
                setTimeout(() => playTone(1000, 'sine', 0.6), 300);
                
                createConfetti();

                // Populate Win Modal
                let displayTitle = data.reward_type;
                if (data.value) displayTitle += ` (${data.value})`;
                
                document.getElementById('winTitle').innerText = displayTitle;
                document.getElementById('winMessage').innerText = data.message || "Congratulations! Your reward has been added to your account.";
                
                const couponContainer = document.getElementById('couponContainer');
                if (data.coupon_code) {
                    couponContainer.classList.remove('hidden');
                    document.getElementById('couponCode').innerText = data.coupon_code;
                } else {
                    couponContainer.classList.add('hidden');
                }

                // Show Win Modal
                const modal = document.getElementById('winModal');
                const content = document.getElementById('winModalContent');
                modal.classList.remove('hidden');
                requestAnimationFrame(() => {
                    content.classList.remove('opacity-0', 'scale-90');
                    content.classList.add('modal-enter');
                });

            } else {
                // Play Lose Sound
                playTone(300, 'sawtooth', 0.3);
                setTimeout(() => playTone(200, 'sawtooth', 0.5), 250);

                // Populate Lose Modal
                document.getElementById('loseMessage').innerText = data.message || 'Give it another try!';
                
                // Show Lose Modal
                const modal = document.getElementById('loseModal');
                const content = document.getElementById('loseModalContent');
                modal.classList.remove('hidden');
                requestAnimationFrame(() => {
                    content.classList.remove('opacity-0', 'scale-90');
                    content.classList.add('modal-enter');
                });
            }
        }

        function closeModal() {
            // Hide Win
            const winModal = document.getElementById('winModal');
            const winContent = document.getElementById('winModalContent');
            winContent.classList.remove('modal-enter');
            winContent.classList.add('opacity-0', 'scale-90');
            setTimeout(() => winModal.classList.add('hidden'), 300);

            // Hide Lose
            const loseModal = document.getElementById('loseModal');
            const loseContent = document.getElementById('loseModalContent');
            loseContent.classList.remove('modal-enter');
            loseContent.classList.add('opacity-0', 'scale-90');
            setTimeout(() => loseModal.classList.add('hidden'), 300);
            
            // Reset Copy Button
            const copyIcon = document.getElementById('copyIcon');
            if(copyIcon) copyIcon.className = "fa-regular fa-copy text-white";
        }

        function copyCoupon() {
            const code = document.getElementById('couponCode').innerText;
            navigator.clipboard.writeText(code).then(() => {
                const icon = document.getElementById('copyIcon');
                icon.className = "fa-solid fa-check text-green-400";
                setTimeout(() => {
                    icon.className = "fa-regular fa-copy text-white";
                }, 2000);
            }).catch(err => {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</body>
</html>
