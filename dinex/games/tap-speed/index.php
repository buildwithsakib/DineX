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
    <title>Tap Speed | DineX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>const FULL_BASE_URL = "<?= FULL_BASE_URL ?>";</script>
    <script src="<?= FULL_BASE_URL ?>/games/common/play.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800;900&display=swap');
        body { font-family: 'Poppins', sans-serif; overflow: hidden; overscroll-behavior-y: none; }
        .bg-animated { background: radial-gradient(circle at 50% 50%, #2e094b, #0f0518); position: relative; z-index: 0; }
        .blob { position: absolute; border-radius: 50%; filter: blur(60px); opacity: 0.6; z-index: -1; animation: float 10s infinite alternate ease-in-out; }
        .blob-1 { top: -10%; left: -10%; width: 300px; height: 300px; background: #9d4edd; animation-delay: 0s; }
        .blob-2 { bottom: -10%; right: -10%; width: 250px; height: 250px; background: #ff006e; animation-delay: -3s; }
        .blob-3 { top: 40%; left: 60%; width: 200px; height: 200px; background: #ffbe0b; animation-delay: -6s; }
        @keyframes float { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(30px,50px) scale(1.1); } }
        .glass-panel { background: rgba(255,255,255,0.08); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.2); }
        #confetti-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999; }
        .modal-enter { animation: modalPop 0.5s cubic-bezier(0.34,1.56,0.64,1) forwards; }
        @keyframes modalPop { 0% { opacity:0; transform:scale(0.8) translateY(20px); } 100% { opacity:1; transform:scale(1) translateY(0); } }
        .spin-btn { background: linear-gradient(135deg,#ff006e,#ffbe0b); background-size:200% 200%; transition:all 0.3s ease; box-shadow:0 10px 20px -5px rgba(255,0,110,0.5); }
        .spin-btn:hover:not(:disabled) { transform:translateY(-2px) scale(1.02); box-shadow:0 15px 25px -5px rgba(255,0,110,0.6); }
        .spin-btn:active:not(:disabled) { transform:translateY(2px) scale(0.98); }
        .spin-btn:disabled { background:#4b5563; box-shadow:none; cursor:not-allowed; transform:none; opacity:0.7; }
        .tap-area { background: rgba(255,255,255,0.1); border: 2px dashed rgba(255,255,255,0.3); transition: all 0.1s ease; }
        .tap-area:active { transform: scale(0.96); }
    </style>
</head>
<body class="bg-animated min-h-[100dvh] flex flex-col items-center justify-between py-6 px-4 text-white">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
    <canvas id="confetti-canvas"></canvas>

    <div class="w-full max-w-md flex justify-between items-center z-10 px-2">
        <a href="<?= FULL_BASE_URL ?>/customer/menu.php?token=<?= e($_SESSION['session_token'] ?? '') ?>" class="glass-panel px-4 py-2 rounded-full text-sm font-semibold hover:bg-white/20 transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Menu
        </a>
        <button id="soundToggle" onclick="toggleSound()" class="glass-panel w-10 h-10 rounded-full flex items-center justify-center text-lg hover:bg-white/20 transition" aria-label="Toggle Sound">🔊</button>
    </div>

    <div class="text-center z-10 mt-4">
        <div class="inline-block bg-white/10 px-3 py-1 rounded-full text-xs font-bold tracking-wider mb-3 text-pink-300 border border-pink-300/30 uppercase">
            <i class="fa-solid fa-gift mr-1"></i> Daily Reward
        </div>
        <h1 class="text-4xl font-black mb-1 bg-clip-text text-transparent bg-gradient-to-r from-yellow-300 to-pink-500 tracking-tight" style="text-shadow:0 4px 10px rgba(0,0,0,0.3);">
            TAP SPEED
        </h1>
        <p class="text-gray-300 text-sm font-medium">Tap as fast as you can for 7 seconds!</p>
    </div>

    <div class="z-10 my-8 w-full max-w-sm">
        <div id="tapArea" class="tap-area rounded-2xl p-10 cursor-pointer select-none text-center" onclick="tap()">
            <span id="tapText" class="text-6xl font-black text-white">TAP</span>
        </div>
        <p id="tapCounter" class="mt-4 text-center text-2xl font-bold text-yellow-300"></p>
    </div>

    <div class="z-10 w-full max-w-sm text-center mb-4">
        <button id="actionBtn" onclick="startGame()" class="spin-btn w-full text-white font-black text-xl py-4 rounded-2xl flex items-center justify-center gap-3 tracking-wide">
            <i class="fa-solid fa-hand-pointer"></i> <span>START TAPPING</span>
        </button>
        <p id="statusMsg" class="mt-3 text-sm text-gray-300 hidden"></p>
    </div>

    <div id="winModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden bg-black/60 backdrop-blur-sm">
        <div class="glass-panel w-full max-w-sm rounded-3xl p-8 text-center relative overflow-hidden transform scale-90 opacity-0 transition-all duration-300" id="winModalContent">
            <div class="absolute -top-16 -left-16 w-32 h-32 bg-yellow-400 rounded-full mix-blend-overlay filter blur-2xl opacity-50"></div>
            <div class="absolute -bottom-16 -right-16 w-32 h-32 bg-pink-500 rounded-full mix-blend-overlay filter blur-2xl opacity-50"></div>
            <div class="text-6xl mb-4 bounce">🎉</div>
            <h2 class="text-3xl font-black text-white mb-2 tracking-tight">YOU WON!</h2>
            <div id="winTitle" class="text-2xl font-bold text-yellow-300 mb-4 bg-black/20 py-2 rounded-xl border border-yellow-300/20"></div>
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
            <button onclick="closeModal()" class="w-full bg-white text-purple-900 font-bold py-3 rounded-xl hover:bg-gray-100 transition shadow-lg">Awesome!</button>
        </div>
    </div>

    <div id="loseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden bg-black/60 backdrop-blur-sm">
        <div class="glass-panel w-full max-w-sm rounded-3xl p-8 text-center relative transform scale-90 opacity-0 transition-all duration-300" id="loseModalContent">
            <div class="text-6xl mb-4">😅</div>
            <h2 class="text-2xl font-black text-white mb-2">Oh no!</h2>
            <p id="loseMessage" class="text-gray-300 mb-8">Better luck next time. Give it another try tomorrow!</p>
            <button onclick="closeModal()" class="w-full bg-white/20 text-white font-bold py-3 rounded-xl border border-white/30 hover:bg-white/30 transition">Close</button>
        </div>
    </div>

    <script>
        const GAME_ID = <?= $gameId ?>;
        let taps = 0;
        let isPlaying = false;
        let timer = null;
        let soundEnabled = true;
        let audioCtx = null;

        function initAudio() {
            if (!audioCtx) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) audioCtx = new AudioContext();
            }
        }

        function playTone(freq, type, duration, vol = 0.1) {
            if (!soundEnabled || !audioCtx) return;
            try {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = type;
                osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
                gain.gain.setValueAtTime(vol, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
                osc.connect(gain); gain.connect(audioCtx.destination);
                osc.start(); osc.stop(audioCtx.currentTime + duration);
            } catch(e) {}
        }

        function toggleSound() {
            soundEnabled = !soundEnabled;
            document.getElementById('soundToggle').innerHTML = soundEnabled ? '🔊' : '🔇';
            if (soundEnabled) { initAudio(); playTone(600, 'sine', 0.1); }
        }

        function hasPlayedToday() {
            const key = 'dinex_played_' + GAME_ID;
            const today = new Date().toISOString().slice(0,10);
            return localStorage.getItem(key) === today;
        }

        function markPlayedToday() {
            const key = 'dinex_played_' + GAME_ID;
            const today = new Date().toISOString().slice(0,10);
            localStorage.setItem(key, today);
        }

        function updateDailyLockUI() {
            if (hasPlayedToday()) {
                const btn = document.getElementById('actionBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>PLAYED TODAY</span>';
                document.getElementById('statusMsg').classList.remove('hidden');
                document.getElementById('statusMsg').innerText = "You've already played today. Come back tomorrow!";
                document.getElementById('tapArea').classList.add('opacity-50','pointer-events-none');
            }
        }

        const canvas = document.getElementById('confetti-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];
        let confettiAnimationId = null;
        function resizeCanvas() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
        window.addEventListener('resize', resizeCanvas); resizeCanvas();
        function createConfetti() {
            particles = [];
            const colors = ['#ffbe0b','#fb5607','#ff006e','#8338ec','#3a86ff','#00f5d4'];
            for (let i=0; i<150; i++) {
                particles.push({ x: canvas.width/2, y: canvas.height/2, r: Math.random()*6+4, dx: Math.random()*20-10, dy: Math.random()*-20-5, color: colors[Math.floor(Math.random()*colors.length)], tilt: Math.random()*10 });
            }
            if (!confettiAnimationId) animateConfetti();
        }
        function animateConfetti() {
            if (particles.length === 0) { ctx.clearRect(0,0,canvas.width,canvas.height); confettiAnimationId = null; return; }
            ctx.clearRect(0,0,canvas.width,canvas.height);
            particles.forEach((p,i) => {
                p.x += p.dx; p.y += p.dy; p.dy += 0.4; p.tilt += 0.1;
                ctx.beginPath(); ctx.lineWidth = p.r; ctx.strokeStyle = p.color;
                ctx.moveTo(p.x + p.tilt + p.r, p.y); ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r); ctx.stroke();
                if (p.y > canvas.height) particles.splice(i,1);
            });
            confettiAnimationId = requestAnimationFrame(animateConfetti);
        }

        function openWinModal(data) {
            let displayTitle = data.reward_type;
            if (data.value) displayTitle += ` (${data.value})`;
            document.getElementById('winTitle').innerText = displayTitle;
            document.getElementById('winMessage').innerText = data.message || 'Congratulations! Your reward has been added to your account.';
            const couponContainer = document.getElementById('couponContainer');
            if (data.coupon_code) {
                couponContainer.classList.remove('hidden');
                document.getElementById('couponCode').innerText = data.coupon_code;
            } else {
                couponContainer.classList.add('hidden');
            }
            const modal = document.getElementById('winModal');
            const content = document.getElementById('winModalContent');
            modal.classList.remove('hidden');
            requestAnimationFrame(() => { content.classList.remove('opacity-0','scale-90'); content.classList.add('modal-enter'); });
        }

        function openLoseModal(message) {
            document.getElementById('loseMessage').innerText = message || 'Better luck next time!';
            const modal = document.getElementById('loseModal');
            const content = document.getElementById('loseModalContent');
            modal.classList.remove('hidden');
            requestAnimationFrame(() => { content.classList.remove('opacity-0','scale-90'); content.classList.add('modal-enter'); });
        }

        function closeModal() {
            ['winModal','loseModal'].forEach(id => {
                const modal = document.getElementById(id);
                const content = document.getElementById(id + 'Content');
                content.classList.remove('modal-enter');
                content.classList.add('opacity-0','scale-90');
                setTimeout(() => modal.classList.add('hidden'), 300);
            });
            const copyIcon = document.getElementById('copyIcon');
            if (copyIcon) copyIcon.className = "fa-regular fa-copy text-white";
        }

        function copyCoupon() {
            const code = document.getElementById('couponCode').innerText;
            navigator.clipboard.writeText(code).then(() => {
                const icon = document.getElementById('copyIcon');
                icon.className = "fa-solid fa-check text-green-400";
                setTimeout(() => icon.className = "fa-regular fa-copy text-white", 2000);
            }).catch(err => console.error('Could not copy text: ', err));
        }

        function tap() {
            if (!isPlaying || hasPlayedToday()) return;
            taps++;
            document.getElementById('tapText').innerText = taps;
            document.getElementById('tapCounter').innerText = taps + ' taps';
            playTone(700, 'square', 0.03, 0.05);
        }

        function startGame() {
            if (isPlaying || hasPlayedToday()) return;
            initAudio();
            isPlaying = true;
            taps = 0;
            const btn = document.getElementById('actionBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>TAP NOW!</span>';
            document.getElementById('tapText').innerText = 'GO!';
            document.getElementById('tapCounter').innerText = '';
            playTone(500, 'sine', 0.1);

            timer = setTimeout(async () => {
                document.getElementById('tapText').innerText = "TIME'S UP!";
                try {
                    const data = await playGame(GAME_ID);
                    markPlayedToday();
                    if (data.success) {
                        playTone(400, 'sine', 0.2);
                        setTimeout(() => playTone(600, 'sine', 0.2), 150);
                        setTimeout(() => playTone(1000, 'sine', 0.6), 300);
                        createConfetti();
                        openWinModal(data);
                    } else {
                        playTone(300, 'sawtooth', 0.3);
                        setTimeout(() => playTone(200, 'sawtooth', 0.5), 250);
                        openLoseModal(data.message);
                    }
                } catch (error) {
                    openLoseModal('Network error. Please try again.');
                } finally {
                    isPlaying = false;
                    btn.innerHTML = '<i class="fa-solid fa-hand-pointer"></i> <span>START TAPPING</span>';
                    updateDailyLockUI();
                }
            }, 7000);
        }

        updateDailyLockUI();
    </script>
</body>
</html>
