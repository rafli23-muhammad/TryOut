<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - Bimbel Cemerlang')</title>

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
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC;
        }
        .btn-cemerlang-yellow {
            background-color: #FFBE2A;
            color: #1F366A;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        .btn-cemerlang-yellow:hover {
            background-color: #E5A817;
        }
        .btn-cemerlang-navy {
            background-color: #1F366A;
            color: #FFF7E9;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        .btn-cemerlang-navy:hover {
            background-color: #132347;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col md:flex-row bg-slate-50 text-slate-900">

    <!-- Admin Sidebar -->
    <aside class="w-full md:w-64 bg-[#1F366A] text-white flex-shrink-0 flex flex-col justify-between border-r-4 border-[#FFBE2A]">
        <div>
            <!-- Sidebar Header / Brand -->
            <div class="p-5 border-b border-blue-900/60 flex items-center space-x-3.5">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Bimbel Cemerlang" class="w-14 h-14 bg-white rounded-2xl p-1.5 shadow-md object-contain shrink-0">
                <div>
                    <h2 class="text-sm sm:text-base font-black font-serif text-white tracking-wide leading-tight">BIMBEL CEMERLANG</h2>
                    <span class="text-[10px] font-bold text-[#FFBE2A] uppercase tracking-wider block mt-0.5">Admin Control Center</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav id="admin-sidebar-nav" class="p-4 space-y-1.5 text-xs font-bold relative">
                <!-- Single Floating Animated Yellow Indicator -->
                <div id="sidebar-indicator" class="absolute rounded-xl bg-[#FFBE2A] shadow pointer-events-none z-0" style="opacity: 0; transition: top 0.32s cubic-bezier(0.25, 1, 0.5, 1), height 0.32s ease, width 0.32s ease;"></div>

                <a href="{{ url('/admin/dashboard') }}" class="sidebar-nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors duration-200 relative z-10 {{ request()->is('admin/dashboard') ? 'is-active text-[#1F366A] font-extrabold' : 'text-slate-200 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-chart-pie text-base"></i>
                    <span>Dashboard Utama</span>
                </a>

                <a href="{{ url('/admin/users') }}" class="sidebar-nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors duration-200 relative z-10 {{ request()->is('admin/users*') ? 'is-active text-[#1F366A] font-extrabold' : 'text-slate-200 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-users-gear text-base"></i>
                    <span>Kelola Akun</span>
                </a>

                <a href="{{ url('/admin/packages') }}" class="sidebar-nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors duration-200 relative z-10 {{ request()->is('admin/packages*') ? 'is-active text-[#1F366A] font-extrabold' : 'text-slate-200 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-box-archive text-base"></i>
                    <span>Paket Try Out</span>
                </a>

                <a href="{{ url('/admin/orders') }}" class="sidebar-nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors duration-200 relative z-10 {{ request()->is('admin/orders*') ? 'is-active text-[#1F366A] font-extrabold' : 'text-slate-200 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-receipt text-base"></i>
                    <span>Verifikasi Pembayaran</span>
                </a>

                <a href="{{ url('/admin/payment-settings') }}" class="sidebar-nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors duration-200 relative z-10 {{ request()->is('admin/payment-settings*') ? 'is-active text-[#1F366A] font-extrabold' : 'text-slate-200 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-wallet text-base"></i>
                    <span>Rekening & QRIS</span>
                </a>

                <a href="{{ url('/admin/proctoring') }}" class="sidebar-nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors duration-200 relative z-10 {{ request()->is('admin/proctoring*') ? 'is-active text-[#1F366A] font-extrabold' : 'text-slate-200 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-shield-halved text-base"></i>
                    <span>Monitoring Ujian</span>
                </a>

                <a href="{{ url('/admin/reports') }}" class="sidebar-nav-item flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors duration-200 relative z-10 {{ request()->is('admin/reports*') ? 'is-active text-[#1F366A] font-extrabold' : 'text-slate-200 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-file-excel text-base"></i>
                    <span>Laporan & Rekap Nilai</span>
                </a>

                <div class="pt-4 border-t border-blue-900/40 my-2"></div>

                <a href="{{ url('/') }}" target="_blank" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition relative z-10">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Lihat Web Peserta</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer / Logout -->
        <div class="p-4 border-t border-blue-900/60 bg-[#132347]">
            <div class="flex items-center justify-between">
                <a href="{{ url('/admin/profile') }}" title="Kelola Akun Admin" class="flex items-center space-x-2.5 hover:opacity-90 transition group">
                    <div class="w-8 h-8 rounded-full bg-emerald-400 text-[#1F366A] flex items-center justify-center font-bold text-xs group-hover:scale-105 transition">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div class="text-xs">
                        <span class="block font-bold text-white truncate max-w-[120px]">{{ session('admin_username', 'Administrator') }}</span>
                        <span class="text-[10px] text-emerald-300 group-hover:underline">Kelola Akun Admin</span>
                    </div>
                </a>
                <a href="{{ url('/logout') }}" title="Keluar" class="text-red-300 hover:text-red-100 p-2">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

        <!-- Top Header Bar -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
            <div>
                <h2 class="text-lg font-black font-serif text-[#1F366A]">@yield('header_title', 'Admin Dashboard')</h2>
                <span class="text-xs text-slate-500">Panel Pengelolaan Bimbel Cemerlang Try Out</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full">
                    <i class="fa-regular fa-clock mr-1 text-[#FFBE2A]"></i> {{ date('d M Y') }}
                </span>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="flex items-center p-3.5 mb-3 text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base mr-2.5"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="flex items-center p-3.5 mb-3 text-rose-800 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-base mr-2.5"></i>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <!-- Subtle Audio Feedback Script (Click SFX) -->
    <script>
        (function() {
            let audioCtx = null;
            let lastPlayTime = 0;

            function playSoftClick() {
                const now = Date.now();
                if (now - lastPlayTime < 60) return;
                lastPlayTime = now;

                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) return;
                    if (!audioCtx) audioCtx = new AudioContext();
                    if (audioCtx.state === 'suspended') audioCtx.resume();

                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();

                    // Soft woodblock / gentle modern tap pop
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(640, audioCtx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(360, audioCtx.currentTime + 0.045);

                    // Gentle volume curve
                    gain.gain.setValueAtTime(0.06, audioCtx.currentTime);
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
                const target = e.target.closest('button, a, input[type="submit"], input[type="button"], .btn-cemerlang-yellow, .btn-cemerlang-navy, [role="button"], label, select');
                if (target) {
                    playSoftClick();
                }
            }, { passive: true, capture: true });
        })();
    </script>

    <!-- Sliding Sidebar Active Indicator Script -->
    <script>
        (function() {
            function initSidebarIndicator() {
                const nav = document.getElementById('admin-sidebar-nav');
                if (!nav) return;

                const indicator = document.getElementById('sidebar-indicator');
                const items = nav.querySelectorAll('.sidebar-nav-item');
                const activeItem = nav.querySelector('.sidebar-nav-item.is-active');

                if (!indicator || !items.length) return;

                if (activeItem) {
                    const prevTop = sessionStorage.getItem('admin_sidebar_prev_top');
                    const targetTop = activeItem.offsetTop;
                    const targetLeft = activeItem.offsetLeft;
                    const targetWidth = activeItem.offsetWidth;
                    const targetHeight = activeItem.offsetHeight;

                    indicator.style.left = targetLeft + 'px';
                    indicator.style.width = targetWidth + 'px';
                    indicator.style.height = targetHeight + 'px';

                    if (prevTop !== null && Math.abs(parseFloat(prevTop) - targetTop) > 6) {
                        // Start indicator from previous item position without transition
                        indicator.style.transition = 'none';
                        indicator.style.top = prevTop + 'px';
                        indicator.style.opacity = '1';

                        // Force DOM reflow
                        void indicator.offsetHeight;

                        // Smoothly glide into target position
                        indicator.style.transition = 'top 0.32s cubic-bezier(0.25, 1, 0.5, 1), height 0.32s ease, width 0.32s ease, opacity 0.2s ease';
                        indicator.style.top = targetTop + 'px';
                    } else {
                        indicator.style.transition = 'none';
                        indicator.style.top = targetTop + 'px';
                        indicator.style.opacity = '1';
                    }

                    sessionStorage.setItem('admin_sidebar_prev_top', targetTop);
                } else {
                    indicator.style.opacity = '0';
                }

                // Animate instantly when clicking/pressing another menu item
                items.forEach(function(item) {
                    item.addEventListener('pointerdown', function() {
                        const currentActive = nav.querySelector('.sidebar-nav-item.is-active');
                        if (!currentActive || currentActive === this) return;

                        // Save target position for incoming page
                        sessionStorage.setItem('admin_sidebar_prev_top', this.offsetTop);

                        // Cross-fade text colors
                        currentActive.classList.remove('is-active', 'text-[#1F366A]', 'font-extrabold');
                        currentActive.classList.add('text-slate-200');

                        this.classList.add('is-active', 'text-[#1F366A]', 'font-extrabold');
                        this.classList.remove('text-slate-200');

                        // Glide indicator to clicked element immediately
                        indicator.style.transition = 'top 0.32s cubic-bezier(0.25, 1, 0.5, 1), height 0.32s ease, width 0.32s ease, opacity 0.2s ease';
                        indicator.style.top = this.offsetTop + 'px';
                        indicator.style.height = this.offsetHeight + 'px';
                        indicator.style.opacity = '1';
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSidebarIndicator);
            } else {
                initSidebarIndicator();
            }
        })();
    </script>
</body>
</html>
