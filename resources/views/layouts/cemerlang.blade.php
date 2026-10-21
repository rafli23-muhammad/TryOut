<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Platform Try Out Online Bimbel Cemerlang')</title>

    <!-- Tailwind CSS with Cemerlang Color Palette -->
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
                        sans: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            background-color: #F4F8FD;
            color: #1F366A;
        }
        .btn-cemerlang-yellow {
            background-color: #FFBE2A;
            color: #1F366A;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        .btn-cemerlang-yellow:hover {
            background-color: #E5A817;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 190, 42, 0.4);
        }
        .btn-cemerlang-navy {
            background-color: #1F366A;
            color: #FFF7E9;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        .btn-cemerlang-navy:hover {
            background-color: #132347;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(31, 54, 106, 0.4);
        }
        @keyframes gentleFloat {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-8px);
            }
        }
        .animate-float {
            animation: gentleFloat 3.5s ease-in-out infinite;
        }

        /* Scroll Pop-Up Animations */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(28px) scale(0.96);
            transition: opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1), transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .reveal-on-scroll.revealed {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        /* Pop-up with a bounce/spring feel */
        .reveal-pop {
            opacity: 0;
            transform: scale(0.88) translateY(20px);
            transition: opacity 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            will-change: opacity, transform;
        }
        .reveal-pop.revealed {
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        /* Stagger delays */
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-[#F4F8FD] overflow-x-hidden">

    <!-- Responsive Navbar -->
    <header class="bg-white text-slate-800 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Brand / Logo -->
                <a href="{{ url('/') }}" class="flex items-center space-x-2.5 sm:space-x-3.5 group py-1 min-w-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Bimbel Cemerlang" class="h-10 sm:h-14 md:h-16 w-auto object-contain transition duration-200 group-hover:scale-105 drop-shadow-sm flex-shrink-0">
                    <div class="min-w-0">
                        <span class="block text-base sm:text-xl lg:text-2xl font-black tracking-tight text-[#1F366A] font-serif leading-none truncate">BIMBEL CEMERLANG</span>
                        <span class="hidden sm:block text-[9px] sm:text-[11px] font-bold text-slate-500 tracking-wider uppercase mt-1 truncate">Platform Try Out & Bimbingan Belajar</span>
                    </div>
                </a>

                <!-- Desktop Navigation Links in Center Pill Style -->
                <nav class="hidden lg:flex items-center space-x-2 text-xs font-bold">
                    <a href="{{ url('/') }}" class="px-4 py-2 rounded-full transition flex items-center {{ request()->is('/') ? 'bg-[#EEF5FF] text-[#1E75FF] font-extrabold' : 'text-slate-600 hover:text-[#1F366A] hover:bg-slate-100' }}">
                        <i class="fa-solid fa-house mr-1.5 text-xs"></i>Beranda
                    </a>
                    <a href="{{ url('/student/catalog') }}" class="px-4 py-2 rounded-full transition flex items-center {{ request()->is('student/catalog*') ? 'bg-[#EEF5FF] text-[#1E75FF] font-extrabold' : 'text-slate-600 hover:text-[#1F366A] hover:bg-slate-100' }}">
                        <i class="fa-solid fa-book-open mr-1.5 text-xs"></i>Katalog Try Out
                    </a>

                    @if(session('student_id'))
                        <a href="{{ url('/student/dashboard') }}" class="px-4 py-2 rounded-full transition flex items-center {{ request()->is('student/dashboard*') ? 'bg-[#EEF5FF] text-[#1E75FF] font-extrabold' : 'text-slate-600 hover:text-[#1F366A] hover:bg-slate-100' }}">
                            <i class="fa-solid fa-graduation-cap mr-1.5 text-xs"></i>Dashboard Siswa
                        </a>
                    @endif

                    @if(session('admin_id'))
                        <a href="{{ url('/admin/dashboard') }}" class="px-4 py-2 rounded-full transition flex items-center {{ request()->is('admin*') ? 'bg-emerald-50 text-emerald-700 font-extrabold' : 'text-slate-600 hover:text-emerald-700 hover:bg-slate-100' }}">
                            <i class="fa-solid fa-gear mr-1.5 text-xs"></i>Panel Admin
                        </a>
                    @endif
                </nav>

                <!-- Desktop Auth Buttons -->
                <div class="hidden lg:flex items-center space-x-2.5">
                    @if(session('student_id'))
                        <div class="flex items-center space-x-2 bg-slate-100 py-1.5 px-3 rounded-full border border-slate-200 shadow-sm">
                            <a href="{{ url('/student/profile') }}" title="Edit Profil Saya" class="flex items-center space-x-2.5 group">
                                @if(session('student_avatar'))
                                    <img src="{{ asset(session('student_avatar')) }}" alt="Foto Profil" class="w-8 h-8 rounded-full object-cover border border-[#FFBE2A] group-hover:scale-105 transition shadow-inner">
                                @else
                                    <span class="w-8 h-8 rounded-full bg-[#1F366A] text-white font-bold flex items-center justify-center text-xs shadow-inner group-hover:scale-105 transition">
                                        {{ strtoupper(substr(session('student_name', 'S'), 0, 1)) }}
                                    </span>
                                @endif
                                <span class="text-xs font-bold text-slate-800 max-w-[120px] truncate group-hover:text-[#1E75FF] transition">{{ session('student_name') }}</span>
                            </a>
                            <span class="text-slate-400">|</span>
                            <a href="{{ url('/student/profile') }}" title="Edit Profil" class="text-slate-500 hover:text-[#1E75FF] text-xs">
                                <i class="fa-solid fa-user-pen"></i>
                            </a>
                            <a href="{{ url('/logout') }}" title="Keluar" class="text-rose-500 hover:text-rose-700 text-xs pl-1">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </a>
                        </div>
                    @elseif(session('admin_id'))
                        <div class="flex items-center space-x-3 bg-emerald-50 py-1.5 px-3 rounded-full border border-emerald-300">
                            <span class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-xs">
                                <i class="fa-solid fa-user-gear"></i>
                            </span>
                            <span class="text-xs font-bold text-emerald-900">{{ session('admin_username') }} (Admin)</span>
                            <a href="{{ url('/logout') }}" title="Keluar" class="text-rose-500 hover:text-rose-700 text-xs pl-1">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </a>
                        </div>
                    @else
                        <!-- 1. Admin Pill Button -->
                        <a href="{{ url('/admin/login') }}" class="inline-flex items-center text-xs font-bold text-slate-700 hover:text-[#1F366A] hover:bg-slate-50 px-4 py-2 rounded-full border border-slate-300 transition shadow-sm" title="Login Administrator">
                            <i class="fa-solid fa-user-shield mr-1.5 text-xs text-slate-600"></i>Admin
                        </a>

                        <!-- 2. Masuk Siswa Pill Button -->
                        <a href="{{ url('/login') }}" class="inline-flex items-center text-xs font-bold text-slate-700 hover:text-[#1F366A] hover:bg-slate-50 px-4 py-2 rounded-full border border-slate-300 transition shadow-sm">
                            <i class="fa-solid fa-arrow-right-to-bracket mr-1.5 text-xs text-slate-600"></i>Masuk Siswa
                        </a>

                        <!-- 3. Daftar Siswa (Yellow Pill Button) -->
                        <a href="{{ url('/register') }}" class="inline-flex items-center text-xs font-black text-[#1F366A] bg-[#FFBE2A] hover:bg-[#E5A817] px-4 py-2 rounded-full shadow-sm transition">
                            <i class="fa-solid fa-user-plus mr-1.5 text-xs"></i>Daftar Siswa
                        </a>
                    @endif
                </div>

                <!-- Mobile Header Right Actions (Hamburger + Quick Button) -->
                <div class="flex lg:hidden items-center space-x-2">
                    @if(session('student_id'))
                        <a href="{{ url('/student/profile') }}" class="flex items-center space-x-1.5 bg-slate-100 p-1.5 rounded-full border border-slate-200">
                            @if(session('student_avatar'))
                                <img src="{{ asset(session('student_avatar')) }}" alt="Foto Profil" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <span class="w-7 h-7 rounded-full bg-[#1F366A] text-white font-bold flex items-center justify-center text-[10px]">
                                    {{ strtoupper(substr(session('student_name', 'S'), 0, 1)) }}
                                </span>
                            @endif
                        </a>
                    @elseif(!session('admin_id'))
                        <a href="{{ url('/login') }}" class="inline-flex items-center text-[11px] font-black text-[#1F366A] bg-[#FFBE2A] hover:bg-[#E5A817] px-3 py-1.5 rounded-full shadow-xs">
                            Masuk
                        </a>
                    @endif

                    <!-- Hamburger Button -->
                    <button type="button" id="mobile-menu-btn" onclick="toggleMobileMenu()" class="w-9 h-9 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-[#1F366A] flex items-center justify-center text-base focus:outline-none transition" aria-label="Buka Menu">
                        <i id="mobile-menu-icon" class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu (Expandable on small screens) -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-200 bg-white/95 backdrop-blur-md px-4 pt-3 pb-6 space-y-2 shadow-lg animate-fadeIn">
            <a href="{{ url('/') }}" class="flex items-center px-4 py-2.5 rounded-xl text-xs font-bold {{ request()->is('/') ? 'bg-[#EEF5FF] text-[#1E75FF] font-extrabold' : 'text-slate-700 hover:bg-slate-50' }}">
                <i class="fa-solid fa-house w-6 text-xs text-[#FFBE2A]"></i>Beranda
            </a>
            <a href="{{ url('/student/catalog') }}" class="flex items-center px-4 py-2.5 rounded-xl text-xs font-bold {{ request()->is('student/catalog*') ? 'bg-[#EEF5FF] text-[#1E75FF] font-extrabold' : 'text-slate-700 hover:bg-slate-50' }}">
                <i class="fa-solid fa-book-open w-6 text-xs text-[#FFBE2A]"></i>Katalog Try Out
            </a>

            @if(session('student_id'))
                <a href="{{ url('/student/dashboard') }}" class="flex items-center px-4 py-2.5 rounded-xl text-xs font-bold {{ request()->is('student/dashboard*') ? 'bg-[#EEF5FF] text-[#1E75FF] font-extrabold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-graduation-cap w-6 text-xs text-[#FFBE2A]"></i>Dashboard Siswa
                </a>
                <a href="{{ url('/student/profile') }}" class="flex items-center px-4 py-2.5 rounded-xl text-xs font-bold {{ request()->is('student/profile*') ? 'bg-[#EEF5FF] text-[#1E75FF] font-extrabold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-user-pen w-6 text-xs text-sky-600"></i>Profil Saya
                </a>
                <div class="pt-2 border-t border-slate-100">
                    <a href="{{ url('/logout') }}" class="flex items-center px-4 py-2.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50">
                        <i class="fa-solid fa-right-from-bracket w-6 text-xs"></i>Keluar (Logout)
                    </a>
                </div>
            @elseif(session('admin_id'))
                <a href="{{ url('/admin/dashboard') }}" class="flex items-center px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800">
                    <i class="fa-solid fa-gauge w-6 text-xs text-emerald-600"></i>Dashboard Admin
                </a>
                <div class="pt-2 border-t border-slate-100">
                    <a href="{{ url('/logout') }}" class="flex items-center px-4 py-2.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50">
                        <i class="fa-solid fa-right-from-bracket w-6 text-xs"></i>Keluar Admin
                    </a>
                </div>
            @else
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <a href="{{ url('/register') }}" class="flex items-center justify-center space-x-2 w-full py-3 rounded-xl bg-[#FFBE2A] hover:bg-[#E5A817] text-[#1F366A] font-black text-xs shadow-sm">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Daftar Akun Siswa</span>
                    </a>
                    <a href="{{ url('/login') }}" class="flex items-center justify-center space-x-2 w-full py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50">
                        <i class="fa-solid fa-arrow-right-to-bracket text-slate-500"></i>
                        <span>Masuk Siswa</span>
                    </a>
                    <a href="{{ url('/admin/login') }}" class="flex items-center justify-center space-x-1.5 w-full py-2 rounded-xl text-[11px] font-bold text-slate-500 hover:text-slate-800">
                        <i class="fa-solid fa-user-shield text-xs"></i>
                        <span>Login Administrator</span>
                    </a>
                </div>
            @endif
        </div>
    </header>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('mobile-menu-icon');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                menu.classList.add('hidden');
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        }
    </script>


    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="flex items-center p-4 mb-4 text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200 shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-600 text-xl mr-3"></i>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center p-4 mb-4 text-rose-800 rounded-xl bg-rose-50 border border-rose-200 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-xl mr-3"></i>
                <div class="text-sm font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="flex items-center p-4 mb-4 text-sky-800 rounded-xl bg-sky-50 border border-sky-200 shadow-sm">
                <i class="fa-solid fa-circle-info text-sky-600 text-xl mr-3"></i>
                <div class="text-sm font-medium">{{ session('info') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 mb-4 text-rose-800 rounded-xl bg-rose-50 border border-rose-200 shadow-sm">
                <div class="flex items-center mb-1">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 mr-2"></i>
                    <span class="font-bold text-sm">Harap periksa kesalahan berikut:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 mt-1 text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 w-full">
        @yield('content')
    </main>

    <!-- Footer matching reference image -->
    <footer class="bg-[#0B1E48] text-slate-300 mt-16 border-t-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10">
                <!-- Brand & Description -->
                <div class="md:col-span-5 space-y-4">
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Bimbel Cemerlang" class="h-16 sm:h-20 w-auto object-contain drop-shadow-md">
                        <span class="text-xl sm:text-2xl font-black tracking-wider text-white font-serif leading-none">BIMBEL CEMERLANG</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed pr-6 max-w-md">
                        Pusat Bimbingan Belajar dan Try Out Online terdepan dengan simulasi ujian real-time, sistem pembobotan IRT (Item Response Theory), dan pembahasan komprehensif untuk persiapan SNBT dan TKA.
                    </p>
                    <!-- Social Media Icons (Instagram, YouTube, Telegram, Globe) -->
                    <div class="flex items-center space-x-3 pt-2">
                        <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-[#FFBE2A] hover:text-[#1F366A] text-white flex items-center justify-center text-xs transition">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-[#FFBE2A] hover:text-[#1F366A] text-white flex items-center justify-center text-xs transition">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-[#FFBE2A] hover:text-[#1F366A] text-white flex items-center justify-center text-xs transition">
                            <i class="fa-brands fa-telegram"></i>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-[#FFBE2A] hover:text-[#1F366A] text-white flex items-center justify-center text-xs transition">
                            <i class="fa-solid fa-globe"></i>
                        </a>
                    </div>
                </div>

                <!-- Kategori Ujian -->
                <div class="md:col-span-3">
                    <h4 class="text-xs font-black text-white uppercase tracking-wider mb-2">FOKUS PROGRAM</h4>
                    <div class="w-8 h-1 bg-[#FFBE2A] rounded-full mb-4"></div>
                    <ul class="text-xs space-y-3 text-slate-300">
                        <li>
                            <a href="{{ url('/student/catalog') }}" class="hover:text-white transition flex items-center">
                                <i class="fa-solid fa-graduation-cap w-5 text-[#FFBE2A]"></i>
                                <span>UTBK - SNBT (TPS & Literasi)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/student/catalog') }}" class="hover:text-white transition flex items-center">
                                <i class="fa-solid fa-atom w-5 text-[#FFBE2A]"></i>
                                <span>TKA Saintek (Kemampuan Akademik)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/student/catalog') }}" class="hover:text-white transition flex items-center">
                                <i class="fa-solid fa-landmark w-5 text-[#FFBE2A]"></i>
                                <span>TKA Soshum (Kemampuan Akademik)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/student/catalog') }}" class="hover:text-white transition flex items-center">
                                <i class="fa-solid fa-chart-line w-5 text-[#FFBE2A]"></i>
                                <span>Penilaian IRT (Item Response Theory)</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Keunggulan Platform -->
                <div class="md:col-span-4">
                    <h4 class="text-xs font-black text-white uppercase tracking-wider mb-2">KEUNGGULAN PLATFORM</h4>
                    <div class="w-8 h-1 bg-[#FFBE2A] rounded-full mb-4"></div>
                    <ul class="text-xs space-y-3 text-slate-300">
                        <li class="flex items-center">
                            <i class="fa-solid fa-stopwatch w-5 text-slate-400"></i>
                            <span>Timer Real-Time Auto-Submit</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fa-solid fa-list-check w-5 text-slate-400"></i>
                            <span>3 Tipe Soal (PG, MCMA, TF)</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fa-solid fa-users-gear w-5 text-slate-400"></i>
                            <span>Evaluasi Passing Grade</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fa-solid fa-book-bookmark w-5 text-slate-400"></i>
                            <span>Rincian Pembahasan Lengkap</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Line -->
            <div class="border-t border-slate-700/60 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-400">
                <p>&copy; {{ date('Y') }} Bimbel Cemerlang. All rights reserved. Dilindungi oleh Cipta.</p>
                <div class="flex items-center space-x-2 mt-2 sm:mt-0">
                    <i class="fa-solid fa-graduation-cap text-[#FFBE2A]"></i>
                    <span class="text-white font-semibold">Bimbel Cemerlang Online</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')

    <!-- Subtle Audio Feedback Script -->
    <script>
        (function() {
            let audioCtx = null;

            function playSoftClick() {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) return;
                    if (!audioCtx) audioCtx = new AudioContext();
                    if (audioCtx.state === 'suspended') audioCtx.resume();

                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();

                    // Soft woodblock / gentle pop frequency
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(620, audioCtx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(380, audioCtx.currentTime + 0.045);

                    // Gentle volume curve that decays softly
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
                const target = e.target.closest('button, a, input[type="submit"], input[type="button"], .btn-cemerlang-yellow, .btn-cemerlang-navy, [role="button"], label, select');
                if (target) {
                    playSoftClick();
                }
            }, { passive: true, capture: true });

            // Scroll Pop-Up Animation Observer
            function initScrollReveal() {
                const observerOptions = {
                    root: null,
                    rootMargin: '0px 0px -40px 0px',
                    threshold: 0.12
                };

                const scrollObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('revealed');
                        }
                    });
                }, observerOptions);

                // Observe all elements with reveal classes
                document.querySelectorAll('.reveal-on-scroll, .reveal-pop').forEach(el => {
                    scrollObserver.observe(el);
                });

                // Auto-attach to common card & section elements if not explicitly marked
                const autoTargetSelectors = [
                    'main section',
                    '.max-w-7xl > .grid > div',
                    '.max-w-7xl > div[class*="bg-white rounded"]',
                    '.max-w-7xl > div[class*="bg-gradient"]',
                    '.grid > .bg-white.rounded-3xl',
                    '.grid > .bg-white.rounded-2xl',
                    '.grid > .bg-white.rounded-xl'
                ];

                document.querySelectorAll(autoTargetSelectors.join(', ')).forEach((el, idx) => {
                    if (!el.classList.contains('reveal-on-scroll') && !el.classList.contains('reveal-pop')) {
                        el.classList.add('reveal-pop');
                        // Add staggered animation delay based on grid order
                        const delayClass = `reveal-delay-${(idx % 4) + 1}`;
                        el.classList.add(delayClass);
                        scrollObserver.observe(el);
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initScrollReveal);
            } else {
                initScrollReveal();
            }
        })();
    </script>
</body>
</html>
