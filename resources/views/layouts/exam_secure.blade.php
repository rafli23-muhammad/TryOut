<!DOCTYPE html>
<html lang="id" class="h-full select-none">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ruang Ujian Try Out - Bimbel Cemerlang')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cemerlang: {
                            cream: '#FFF7E9',
                            yellow: '#FFBE2A',
                            gold: '#E5A817',
                            navy: '#1F366A',
                            dark: '#132347',
                            lightnavy: '#2A4B8D',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- KaTeX for Rich Math Formula Rendering -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"
        onload="renderMathInElement(document.body, {
            delimiters: [
                {left: '$$', right: '$$', display: true},
                {left: '$', right: '$', display: false},
                {left: '\\(', right: '\\)', display: false},
                {left: '\\[', right: '\\]', display: true}
            ],
            throwOnError: false
        });"></script>

    <style>
        /* Strict Anti-Copy & Anti-Selection CSS */
        *, *::before, *::after {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
            -webkit-touch-callout: none !important;
        }

        /* Prevent image drag */
        img {
            pointer-events: none;
            -webkit-user-drag: none;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FFF7E9;
            color: #1F366A;
            overflow-x: hidden;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #FFF7E9;
        }
        ::-webkit-scrollbar-thumb {
            background: #1F366A;
            border-radius: 4px;
        }

        /* Anti-Screenshot CSS: Blackout on Print & Save to PDF */
        @media print {
            html, body {
                display: none !important;
                visibility: hidden !important;
            }
        }

        /* Obscuring curtain when window loses focus (Snipping tool / screenshot app trigger) */
        .window-obscured {
            filter: blur(28px) grayscale(100%) !important;
            pointer-events: none !important;
            user-select: none !important;
        }

        /* Translucent Dynamic Anti-Leak Watermark Overlay */
        .exam-watermark-pattern {
            pointer-events: none;
            user-select: none;
            background-image: repeating-linear-gradient(
                -45deg,
                rgba(31, 54, 106, 0.035),
                rgba(31, 54, 106, 0.035) 20px,
                transparent 20px,
                transparent 80px
            );
        }
    </style>
    @stack('styles')
</head>
<body class="h-full min-h-screen bg-[#FFF7E9] text-[#1F366A] flex flex-col antialiased select-none" oncontextmenu="return false;" oncopy="return false;" oncut="return false;" onpaste="return false;" ondragstart="return false;" onselectstart="return false;">

    @yield('content')

    <!-- Anti-Screenshot / Blur Veil Shield (Protects against Snipping Tool / Screen Capture) -->
    <div id="screen-blur-shield" class="fixed inset-0 z-[140] hidden bg-[#132347]/95 backdrop-blur-2xl flex flex-col items-center justify-center p-6 text-center text-white transition-opacity duration-200">
        <div class="w-20 h-20 rounded-full bg-[#FFBE2A] text-[#1F366A] flex items-center justify-center text-3xl mb-4 shadow-xl">
            <i class="fa-solid fa-eye-slash"></i>
        </div>
        <h3 class="text-xl font-bold font-serif text-[#FFBE2A] mb-2">Layar Soal Disamarkan</h3>
        <p class="text-xs sm:text-sm text-slate-200 max-w-md leading-relaxed mb-4">
            Jendela ujian sedang tidak aktif atau terdeteksi upaya tangkapan layar. Klik kembali lembar ujian ini untuk melanjutkan pengerjaan soal.
        </p>
        <span class="inline-flex items-center px-4 py-2 bg-white/10 text-amber-200 text-xs font-semibold rounded-lg border border-amber-300/30">
            <i class="fa-solid fa-arrow-pointer mr-2"></i> Klik di area ini untuk kembali fokus
        </span>
    </div>

    <!-- Security Toast Alert -->
    <div id="security-toast" class="fixed top-6 left-1/2 -translate-x-1/2 z-[100] hidden bg-[#1F366A] text-white text-xs sm:text-sm font-bold px-6 py-3 rounded-xl shadow-2xl border-2 border-amber-300 transition-all flex items-center space-x-3">
        <i class="fa-solid fa-circle-info text-lg text-[#FFBE2A]"></i>
        <span id="security-toast-message">Informasi Pengerjaan Ujian</span>
    </div>

    <!-- Violation Modal Warning -->
    <div id="violation-modal" class="fixed inset-0 z-[110] hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 text-center border-2 border-amber-400 shadow-2xl animate-scaleIn">
            <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-700 mx-auto flex items-center justify-center text-3xl mb-4">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-xl font-extrabold text-[#1F366A] mb-2 font-serif">Peringatan Pengerjaan Ujian</h3>
            <p id="violation-modal-text" class="text-sm text-slate-600 mb-4 leading-relaxed">
                Anda terdeteksi beralih dari jendela ujian. Harap tetap fokus pada lembar pengerjaan soal.
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-5 text-sm font-semibold text-amber-900">
                Peringatan: <span id="modal-violation-current" class="text-lg font-bold text-amber-700">1</span> dari <span id="modal-violation-max">3</span> batas toleransi
            </div>
            <button id="btn-ack-violation" class="w-full py-3 bg-[#1F366A] hover:bg-[#132347] text-[#FFF7E9] font-bold rounded-xl shadow transition">
                Saya Mengerti & Lanjutkan Pengerjaan
            </button>
        </div>
    </div>

    <!-- Blocked Fullscreen Screen (If Max Violations Exceeded) -->
    <div id="blocked-screen" class="fixed inset-0 z-[200] hidden bg-[#132347] text-white flex flex-col items-center justify-center p-6 text-center">
        <div class="w-24 h-24 rounded-full bg-[#FFBE2A] text-[#1F366A] flex items-center justify-center text-5xl mb-6 shadow-xl">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <h1 class="text-3xl font-extrabold mb-3 font-serif text-[#FFBE2A]">Sesi Ujian Telah Berakhir</h1>
        <p class="max-w-lg text-slate-200 text-base mb-6 leading-relaxed">
            Batas pengerjaan lembar soal Anda telah selesai. Seluruh jawaban Anda telah disimpan dan otomatis terkirim untuk dievaluasi.
        </p>
        <form id="form-auto-submit-blocked" method="POST" action="">
            @csrf
            <button type="submit" class="bg-[#FFBE2A] hover:bg-[#E5A817] text-[#1F366A] font-extrabold px-8 py-3.5 rounded-xl shadow-lg transition">
                Lihat Hasil Evaluasi & Nilai <i class="fa-solid fa-arrow-right ml-2"></i>
            </button>
        </form>
    </div>

    @stack('scripts')

    <!-- Anti-Cheat and Integrity Protection Scripts -->
    <script>
        (function() {
            // Setup Axios CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

            // 1. ANTI COPY-PASTE & ANTI CONTEXT MENU
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                showSecurityToast('Fitur klik kanan tidak aktif pada halaman ujian');
                return false;
            });

            document.addEventListener('copy', function(e) {
                e.preventDefault();
                showSecurityToast('Fitur salin teks tidak aktif pada halaman ujian');
                handleViolation('copy_attempt', 'Mencoba menyalin teks soal');
                return false;
            });

            document.addEventListener('cut', function(e) {
                e.preventDefault();
                showSecurityToast('Fitur cut teks tidak aktif pada halaman ujian');
                return false;
            });

            document.addEventListener('paste', function(e) {
                e.preventDefault();
                showSecurityToast('Fitur tempel teks tidak aktif pada halaman ujian');
                return false;
            });

            // 2. KEYBOARD SHORTCUT & SCREENSHOT BLOCKING
            function wipeClipboard() {
                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText('');
                    }
                } catch (e) {}
            }

            function triggerTemporaryShield(msg = 'Tangkapan layar dinonaktifkan pada lembar ujian') {
                const shield = document.getElementById('screen-blur-shield');
                if (shield) {
                    shield.classList.remove('hidden');
                    wipeClipboard();
                    showSecurityToast(msg);
                    setTimeout(() => {
                        // Keep hidden only if window has focus
                        if (document.hasFocus()) {
                            shield.classList.add('hidden');
                        }
                    }, 1200);
                }
            }

            // Detect PrintScreen on keydown & keyup
            const isPrtScn = (e) => e.key === 'PrintScreen' || e.code === 'PrintScreen' || e.keyCode === 44;

            window.addEventListener('keyup', function(e) {
                if (isPrtScn(e)) {
                    wipeClipboard();
                    triggerTemporaryShield();
                    handleViolation('screenshot_attempt', 'Mencoba melakukan tangkapan layar (PrintScreen)');
                }
            });

            window.addEventListener('keydown', function(e) {
                // PrintScreen key
                if (isPrtScn(e)) {
                    wipeClipboard();
                    triggerTemporaryShield();
                    handleViolation('screenshot_attempt', 'Mencoba melakukan tangkapan layar (PrintScreen)');
                    e.preventDefault();
                    return false;
                }

                // Win + Shift + S (on Windows, often manifests as shift + s with meta/alt or blur)
                if ((e.metaKey || e.ctrlKey) && e.shiftKey && (e.key === 'S' || e.key === 's')) {
                    e.preventDefault();
                    wipeClipboard();
                    triggerTemporaryShield();
                    handleViolation('screenshot_attempt', 'Mencoba shortcut snipping tool / tangkapan layar');
                    return false;
                }

                // F12
                if (e.key === 'F12' || e.keyCode === 123) {
                    e.preventDefault();
                    showSecurityToast('Developer tools dinonaktifkan!');
                    handleViolation('devtools_attempt', 'Mencoba membuka inspect element / devtools (F12)');
                    return false;
                }

                // Ctrl+Shift+I / J / C
                if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) {
                    e.preventDefault();
                    showSecurityToast('Developer tools dinonaktifkan!');
                    handleViolation('devtools_attempt', 'Mencoba membuka inspect element (Ctrl+Shift+I/J/C)');
                    return false;
                }

                // Ctrl+U (View Source)
                if (e.ctrlKey && (e.key === 'u' || e.key === 'U')) {
                    e.preventDefault();
                    showSecurityToast('View source dinonaktifkan!');
                    return false;
                }

                // Ctrl+S (Save Page)
                if (e.ctrlKey && (e.key === 's' || e.key === 'S')) {
                    e.preventDefault();
                    return false;
                }

                // Ctrl+P (Print Page)
                if (e.ctrlKey && (e.key === 'p' || e.key === 'P')) {
                    e.preventDefault();
                    wipeClipboard();
                    showSecurityToast('Fitur cetak halaman dinonaktifkan');
                    return false;
                }

                // Ctrl+C, Ctrl+V
                if (e.ctrlKey && (e.key === 'c' || e.key === 'C')) {
                    e.preventDefault();
                    showSecurityToast('Fitur salin teks tidak aktif pada halaman ujian');
                    handleViolation('copy_attempt', 'Mencoba shortcut copy (Ctrl+C)');
                    return false;
                }
                if (e.ctrlKey && (e.key === 'v' || e.key === 'V')) {
                    e.preventDefault();
                    showSecurityToast('Fitur tempel teks tidak aktif pada halaman ujian');
                    return false;
                }
            });

            function showSecurityToast(msg) {
                const toast = document.getElementById('security-toast');
                const toastMsg = document.getElementById('security-toast-message');
                if (toast && toastMsg) {
                    toastMsg.innerText = msg;
                    toast.classList.remove('hidden');
                    clearTimeout(window.toastTimer);
                    window.toastTimer = setTimeout(() => {
                        toast.classList.add('hidden');
                    }, 3500);
                }
            }

            // 3. TAB SWITCH / WINDOW BLUR & ANTI-SNIPPING CURTAIN
            function handleViolation(type, message) {
                if (!window.examSessionId || window.examSubmitted) return;

                axios.post('/api/exam/' + window.examSessionId + '/violation', {
                    violation_type: type,
                    violation_message: message
                }).then(res => {
                    const data = res.data;
                    const currentCount = data.violation_count;
                    const maxViolations = data.max_violations;

                    if (data.is_blocked) {
                        const blockedScreen = document.getElementById('blocked-screen');
                        const blockedForm = document.getElementById('form-auto-submit-blocked');
                        if (blockedForm) {
                            blockedForm.action = '/exam/' + window.examSessionId + '/submit';
                        }
                        if (blockedScreen) {
                            blockedScreen.classList.remove('hidden');
                        }
                    } else {
                        const modal = document.getElementById('violation-modal');
                        const modalText = document.getElementById('violation-modal-text');
                        const modalCur = document.getElementById('modal-violation-current');
                        const modalMax = document.getElementById('modal-violation-max');

                        if (modalCur) modalCur.innerText = currentCount;
                        if (modalMax) modalMax.innerText = maxViolations;
                        if (modalText) modalText.innerText = message + '. Sistem mencatat waktu kejadian ini.';
                        if (modal) modal.classList.remove('hidden');
                    }
                }).catch(() => {});
            }

            window.recordExamViolation = handleViolation;

            // Show blur curtain whenever window loses focus (e.g. Snipping tool, alt-tab, dual monitor screen recording)
            const shieldEl = document.getElementById('screen-blur-shield');
            shieldEl?.addEventListener('click', function() {
                window.focus();
                shieldEl.classList.add('hidden');
            });

            window.addEventListener('blur', function() {
                if (window.examSubmitted) return;
                wipeClipboard();
                if (shieldEl) {
                    shieldEl.classList.remove('hidden');
                }
                handleViolation('window_blur', 'Jendela ujian kehilangan fokus / Anda membuka aplikasi lain');
            });

            window.addEventListener('focus', function() {
                if (shieldEl && !document.getElementById('violation-modal')?.classList.contains('hidden') === false) {
                    shieldEl.classList.add('hidden');
                }
            });

            document.addEventListener('visibilitychange', function() {
                if (window.examSubmitted) return;
                if (document.visibilityState === 'hidden') {
                    wipeClipboard();
                    if (shieldEl) {
                        shieldEl.classList.remove('hidden');
                    }
                    handleViolation('tab_switch', 'Anda berpindah tab browser saat ujian berlangsung');
                } else {
                    if (shieldEl) {
                        shieldEl.classList.add('hidden');
                    }
                }
            });

            document.getElementById('btn-ack-violation')?.addEventListener('click', function() {
                document.getElementById('violation-modal')?.classList.add('hidden');
                shieldEl?.classList.add('hidden');
                window.focus();
            });

            // Soft click sound for exam interaction
            let audioCtx = null;
            function playExamClick() {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) return;
                    if (!audioCtx) audioCtx = new AudioContext();
                    if (audioCtx.state === 'suspended') audioCtx.resume();

                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(620, audioCtx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(380, audioCtx.currentTime + 0.045);

                    gain.gain.setValueAtTime(0.04, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.045);

                    osc.connect(gain);
                    gain.connect(audioCtx.destination);

                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.05);
                } catch (e) {}
            }

            // Unlock audio on first user gesture
            const unlockAudio = function() {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (AudioContext && !audioCtx) audioCtx = new AudioContext();
                    if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
                } catch (e) {}
            };
            document.addEventListener('pointerdown', unlockAudio, { once: true, passive: true });
            document.addEventListener('keydown', unlockAudio, { once: true, passive: true });

            document.addEventListener('pointerdown', function(e) {
                const target = e.target.closest('button, a, input[type="radio"], input[type="checkbox"], .exam-opt-label, [role="button"], label');
                if (target) {
                    playExamClick();
                }
            }, { passive: true, capture: true });
        })();
    </script>
</body>
</html>
