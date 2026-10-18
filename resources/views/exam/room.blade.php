@extends('layouts.exam_secure')

@section('title', $package['title'] . ' - Ruang Ujian Bimbel Cemerlang')

@section('content')
<!-- Header Bar -->
<header class="bg-[#1F366A] text-white border-b-4 border-[#FFBE2A] sticky top-0 z-40 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 py-3 flex items-center justify-between">
        <!-- Logo & Package Title -->
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 bg-white rounded p-1 shadow-sm">
            <div>
                <span class="text-xs font-bold text-[#FFBE2A] uppercase tracking-wider block">{{ $package['category'] }}</span>
                <h1 class="text-base sm:text-lg font-black font-serif truncate max-w-xs sm:max-w-md">{{ $package['title'] }}</h1>
            </div>
        </div>

        <!-- Timer & Submit Action -->
        <div class="flex items-center space-x-3 sm:space-x-4">
            <!-- Real-time Countdown Timer -->
            <div class="flex items-center space-x-2 bg-[#132347] border border-amber-300/40 px-3.5 py-1.5 rounded-2xl shadow-inner">
                <i class="fa-solid fa-stopwatch text-[#FFBE2A] text-lg animate-pulse"></i>
                <div>
                    <span class="text-[9px] text-slate-300 uppercase font-extrabold block leading-tight">Sisa Waktu</span>
                    <span id="countdown-timer" class="font-mono text-sm sm:text-base font-black text-white tracking-widest">
                        00:00:00
                    </span>
                </div>
            </div>

            <!-- Finish Exam Button -->
            <button onclick="confirmSubmitExam()" class="btn-cemerlang-yellow text-xs font-black px-4 py-2.5 rounded-xl shadow flex items-center">
                <i class="fa-solid fa-flag-checkered mr-1.5"></i> Selesai Ujian
            </button>
        </div>
    </div>
</header>

<!-- Mandatory Fullscreen Gate Modal (Compact & Pixel-Perfect) -->
<div id="fullscreen-gate-modal" class="fixed inset-0 z-[160] bg-[#0A1D44]/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-[2rem] max-w-[390px] w-full p-5 sm:p-6 text-center shadow-2xl border border-slate-100 animate-scaleIn relative overflow-hidden my-auto">
        
        <!-- Computer Monitor Illustration with Golden Focus Brackets & Shield (Compact) -->
        <div class="relative w-36 h-24 mx-auto mb-2 flex items-center justify-center">
            <!-- Focus Brackets (Yellow / Gold Corners) -->
            <!-- Top Left -->
            <div class="absolute top-0.5 left-2 w-4 h-4 border-t-[3px] border-l-[3px] border-[#FFBE2A] rounded-tl-lg pointer-events-none"></div>
            <!-- Top Right -->
            <div class="absolute top-0.5 right-2 w-4 h-4 border-t-[3px] border-r-[3px] border-[#FFBE2A] rounded-tr-lg pointer-events-none"></div>
            <!-- Bottom Left -->
            <div class="absolute bottom-0.5 left-2 w-4 h-4 border-b-[3px] border-l-[3px] border-[#FFBE2A] rounded-bl-lg pointer-events-none"></div>
            <!-- Bottom Right -->
            <div class="absolute bottom-0.5 right-2 w-4 h-4 border-b-[3px] border-r-[3px] border-[#FFBE2A] rounded-br-lg pointer-events-none"></div>

            <!-- Monitor Unit SVG / CSS Illustration -->
            <div class="relative flex flex-col items-center">
                <!-- Monitor Screen Box -->
                <div class="w-24 h-16 bg-white rounded-xl border-[3px] border-[#122A5C] p-1.5 flex flex-col justify-between shadow-sm relative">
                    <!-- Blue Header line -->
                    <div class="h-1 bg-[#CBDCF8] rounded-full w-2/5"></div>
                    <!-- Blue Content lines -->
                    <div class="space-y-0.5">
                        <div class="h-0.5 bg-[#E2ECFA] rounded-full w-4/5"></div>
                        <div class="h-0.5 bg-[#E2ECFA] rounded-full w-3/5"></div>
                    </div>
                    <!-- Yellow pill button inside screen -->
                    <div class="w-6 h-2 bg-[#FFBE2A] rounded-full"></div>

                    <!-- Security Shield Badge Bottom Right of Screen -->
                    <div class="absolute -bottom-1.5 -right-1.5 w-5 h-5 rounded-full bg-[#FFBE2A] border border-white text-[#122A5C] flex items-center justify-center text-[8px] shadow-sm">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
                <!-- Monitor Stand Neck -->
                <div class="w-2.5 h-2 bg-[#122A5C]"></div>
                <!-- Monitor Stand Base -->
                <div class="w-10 h-1 bg-[#122A5C] rounded-full"></div>
            </div>
        </div>

        <!-- Pill Badge: MODE FOKUS UJIAN with Lock Icon -->
        <div class="inline-flex items-center space-x-1.5 bg-[#FFF6E5] text-[#C07E00] px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider mb-2 border border-[#FFE19E]">
            <i class="fa-solid fa-lock text-[9px]"></i>
            <span>MODE FOKUS UJIAN</span>
        </div>

        <!-- Heading Title -->
        <h2 class="text-lg sm:text-xl font-black text-[#132A5A] leading-tight mb-2">
            Ujian Dilakukan Dalam Mode Layar Penuh
        </h2>

        <!-- Description Paragraph -->
        <p class="text-[10px] sm:text-[11px] text-slate-500 leading-relaxed max-w-xs mx-auto mb-3.5 font-normal">
            Untuk menjaga konsentrasi dan integritas pengerjaan, seluruh tampilan layar akan dikunci dalam mode penuh (fullscreen). Siswa dilarang berpindah aplikasi, membuka tab baru, atau keluar dari mode layar penuh selama sesi berlangsung.
        </p>

        <!-- 3 Feature Instruction Rows inside Light Grey Card Box -->
        <div class="bg-[#F8FAFD] border border-slate-200/70 rounded-xl p-2.5 mb-4 text-left space-y-2">
            <!-- Row 1: Green Check -->
            <div class="flex items-center space-x-2.5">
                <div class="w-5 h-5 rounded-md bg-[#E6F8F0] text-[#00A86B] flex items-center justify-center text-[10px] flex-shrink-0">
                    <i class="fa-solid fa-check text-[10px]"></i>
                </div>
                <span class="text-[10px] sm:text-[11px] font-semibold text-slate-700 leading-tight">
                    Layar ujian memenuhi seluruh layar monitor
                </span>
            </div>

            <div class="border-t border-slate-100"></div>

            <!-- Row 2: Warning Yellow Triangle -->
            <div class="flex items-center space-x-2.5">
                <div class="w-5 h-5 rounded-md bg-[#FFF6E5] text-[#C07E00] flex items-center justify-center text-[10px] flex-shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                </div>
                <span class="text-[10px] sm:text-[11px] font-semibold text-slate-700 leading-tight">
                    Keluar dari layar penuh atau beralih jendela akan dicatat sebagai peringatan
                </span>
            </div>

            <div class="border-t border-slate-100"></div>

            <!-- Row 3: Blue Shield -->
            <div class="flex items-center space-x-2.5">
                <div class="w-5 h-5 rounded-md bg-[#EDF3FC] text-[#1E75FF] flex items-center justify-center text-[10px] flex-shrink-0">
                    <i class="fa-regular fa-shield text-[10px]"></i>
                </div>
                <span class="text-[10px] sm:text-[11px] font-semibold text-slate-700 leading-tight">
                    Jika batas toleransi habis, ujian otomatis tersimpan dan dikumpulkan
                </span>
            </div>
        </div>

        <!-- Solid Yellow Button: Mulai Ujian & Masuk Layar Penuh -->
        <button id="btn-enter-fullscreen" onclick="requestExamFullscreen()" class="w-full py-3 bg-[#FFB81C] hover:bg-[#E5A515] text-[#132A5A] font-black rounded-xl shadow-md text-xs sm:text-sm transition transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
            <i class="fa-solid fa-expand text-xs"></i>
            <span>Mulai Ujian & Masuk Layar Penuh</span>
        </button>
    </div>
</div>

<!-- Main Exam Body -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-1 flex flex-col md:flex-row gap-6 items-start">

    <!-- Left / Center: Question Workspace -->
    <div class="relative flex-1 w-full bg-white rounded-3xl p-6 sm:p-8 shadow-xl border-2 border-slate-200 flex flex-col justify-between min-h-[580px] overflow-hidden">
        <!-- Discreet Security Watermark Pattern (Student ID & Anti-Leak Stamp) -->
        <div class="absolute inset-0 exam-watermark-pattern pointer-events-none select-none z-0"></div>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none z-0 opacity-[0.035] rotate-[-25deg]">
            <span class="text-3xl sm:text-5xl font-black text-[#1F366A] tracking-widest uppercase">
                {{ $session['student_name'] ?? 'Bimbel Cemerlang' }} • {{ $session['student_id'] ?? 'Try Out' }}
            </span>
        </div>

        <div class="relative z-10">
            <!-- Question Header / Badge -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-6">
                <div class="flex items-center space-x-3">
                    <span id="current-question-badge" class="bg-[#1F366A] text-[#FFBE2A] text-sm font-black px-4 py-1.5 rounded-full shadow">
                        Soal No. 1
                    </span>
                    <span id="question-type-badge" class="bg-amber-100 text-amber-900 border border-amber-300 text-[11px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
                        Pilihan Ganda
                    </span>
                </div>
                <div class="text-xs text-slate-500 font-semibold">
                    Total: <b>{{ count($safeQuestions) }}</b> Soal
                </div>
            </div>

            <!-- Question Text -->
            <div id="question-text-container" class="text-base sm:text-lg font-medium text-slate-900 leading-relaxed mb-8 select-none">
                <!-- Injected via JS -->
            </div>

            <!-- Options Area (Dynamic for PG, MCMA, TF) -->
            <div id="options-container" class="space-y-3.5 mb-8">
                <!-- Injected via JS -->
            </div>
        </div>

        <!-- Question Navigation Bottom Bar -->
        <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <button id="btn-prev" onclick="prevQuestion()" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border-2 border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-xs flex items-center justify-center transition">
                <i class="fa-solid fa-arrow-left mr-2"></i> Sebelumnya
            </button>

            <!-- Raguragu Button -->
            <button id="btn-doubt" onclick="toggleDoubt()" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border-2 border-amber-400 bg-amber-50 text-amber-900 hover:bg-amber-100 font-extrabold text-xs flex items-center justify-center transition">
                <i id="doubt-icon" class="fa-regular fa-bookmark mr-2 text-amber-600"></i>
                <span id="doubt-label">Tandai Ragu-ragu</span>
            </button>

            <button id="btn-next" onclick="nextQuestion()" class="w-full sm:w-auto btn-cemerlang-navy px-6 py-2.5 rounded-xl font-bold text-xs flex items-center justify-center shadow transition">
                Selanjutnya <i class="fa-solid fa-arrow-right ml-2 text-[#FFBE2A]"></i>
            </button>
        </div>
    </div>

    <!-- Right Sidebar: Interactive Number Grid & Status -->
    <div class="w-full md:w-80 bg-white rounded-3xl p-5 shadow-xl border-2 border-slate-200 flex flex-col space-y-5">
        <div>
            <h3 class="text-sm font-black font-serif text-[#1F366A] uppercase tracking-wider mb-2 flex items-center">
                <i class="fa-solid fa-table-cells mr-2 text-[#FFBE2A]"></i> Navigasi Nomor Soal
            </h3>
            <p class="text-[11px] text-slate-500">Klik nomor untuk berpindah langsung ke soal yang diinginkan.</p>
        </div>

        <!-- Legend -->
        <div class="grid grid-cols-3 gap-1.5 text-[10px] font-bold bg-[#FFF7E9] p-2.5 rounded-2xl border border-amber-200/80">
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded-md bg-[#1F366A] text-white flex items-center justify-center text-[8px]">✓</span>
                <span class="text-slate-700">Terjawab</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded-md bg-[#FFBE2A] border border-amber-500"></span>
                <span class="text-amber-900">Ragu-ragu</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded-md bg-white border border-slate-300"></span>
                <span class="text-slate-500">Kosong</span>
            </div>
        </div>

        <!-- Grid Buttons -->
        <div class="grid grid-cols-5 gap-2 max-h-64 overflow-y-auto p-1" id="question-grid">
            <!-- Injected via JS -->
        </div>

        <!-- Summary Statistics -->
        <div class="border-t border-slate-200 pt-3 space-y-1.5 text-xs">
            <div class="flex justify-between items-center text-slate-600">
                <span>Sudah Dijawab:</span>
                <b id="stat-answered" class="text-[#1F366A] font-extrabold">0</b>
            </div>
            <div class="flex justify-between items-center text-amber-700">
                <span>Ragu-ragu:</span>
                <b id="stat-doubt" class="font-extrabold">0</b>
            </div>
            <div class="flex justify-between items-center text-slate-400">
                <span>Belum Dijawab:</span>
                <b id="stat-unanswered" class="font-extrabold">0</b>
            </div>
        </div>
    </div>
</div>

<!-- Submit Confirmation Modal -->
<div id="submit-confirm-modal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border-4 border-[#FFBE2A] text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 text-[#1F366A] mx-auto flex items-center justify-center text-3xl mb-4">
            <i class="fa-solid fa-flag-checkered"></i>
        </div>
        <h3 class="text-xl font-black font-serif text-[#1F366A] mb-1">Kumpulkan Ujian Sekarang?</h3>
        <p class="text-xs text-slate-500 mb-4">Pastikan Anda telah memeriksa kembali seluruh jawaban sebelum mengirim.</p>

        <div class="bg-[#FFF7E9] p-4 rounded-2xl border border-amber-200 mb-6 text-xs grid grid-cols-3 gap-2">
            <div>
                <span class="block text-slate-500 text-[10px]">Terjawab</span>
                <b id="modal-summary-answered" class="text-base text-emerald-700">0</b>
            </div>
            <div>
                <span class="block text-slate-500 text-[10px]">Ragu-ragu</span>
                <b id="modal-summary-doubt" class="text-base text-amber-600">0</b>
            </div>
            <div>
                <span class="block text-slate-500 text-[10px]">Kosong</span>
                <b id="modal-summary-empty" class="text-base text-rose-600">0</b>
            </div>
        </div>

        <form id="form-submit-exam" method="POST" action="{{ url('/exam/' . $session['id'] . '/submit') }}">
            @csrf
            <input type="hidden" name="answers" id="input-final-answers" value="">
            <div class="flex items-center space-x-3">
                <button type="button" onclick="closeSubmitConfirmModal()" class="w-1/2 py-3 rounded-xl border border-slate-300 font-bold text-xs text-slate-600 hover:bg-slate-50">
                    Periksa Kembali
                </button>
                <button type="submit" class="w-1/2 btn-cemerlang-yellow py-3 rounded-xl font-black text-xs shadow">
                    Ya, Kumpulkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Time Expired In-Page Modal -->
<div id="time-expired-modal" class="fixed inset-0 z-[200] hidden bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border-4 border-[#FFBE2A] text-center transform transition-all scale-100 animate-pop">
        <div class="w-20 h-20 rounded-3xl bg-amber-50 text-amber-500 border-2 border-amber-200 mx-auto flex items-center justify-center text-4xl mb-4 shadow-inner animate-pulse">
            <i class="fa-solid fa-hourglass-end text-[#1F366A]"></i>
        </div>
        <h3 class="text-2xl font-black font-serif text-[#1F366A] mb-2">Waktu Ujian Berakhir!</h3>
        <p class="text-xs sm:text-sm text-slate-600 font-medium mb-5 leading-relaxed">
            Waktu pengerjaan try out telah habis. Seluruh jawaban Anda telah tersimpan aman dan sedang dikumpulkan ke sistem.
        </p>

        <div class="bg-[#FFF7E9] border border-amber-200 rounded-2xl p-4 mb-6">
            <div class="flex items-center justify-center gap-2 text-xs font-bold text-[#1F366A]">
                <i class="fa-solid fa-circle-notch fa-spin text-[#FFBE2A] text-sm"></i>
                <span>Mengumpulkan otomatis dalam <strong id="timeout-countdown-number" class="text-base font-black text-amber-600">3</strong> detik...</span>
            </div>
            <div class="w-full bg-amber-200/50 h-2 rounded-full overflow-hidden mt-3">
                <div id="timeout-progress-bar" class="bg-[#1F366A] h-full w-full transition-all duration-1000 ease-linear"></div>
            </div>
        </div>

        <button type="button" onclick="submitExamNow()" class="btn-cemerlang-yellow w-full py-3.5 rounded-xl font-extrabold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
            <span>Kumpulkan Sekarang</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>
    </div>
</div>

@push('scripts')
<script>
    // Global context passed from controller
    window.examSessionId = "{{ $session['id'] }}";
    window.examSubmitted = false;
    let isFullscreenMode = false;

    // Fullscreen Gate Control
    function isDocFullscreen() {
        return !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
    }

    function updateFullscreenUI() {
        const gate = document.getElementById('fullscreen-gate-modal');
        const isFS = isDocFullscreen();

        if (isFS) {
            if (gate) gate.classList.add('hidden');
            isFullscreenMode = true;
        } else {
            isFullscreenMode = false;
        }
    }

    function requestExamFullscreen() {
        const elem = document.documentElement;
        const req = elem.requestFullscreen || elem.webkitRequestFullscreen || elem.mozRequestFullScreen || elem.msRequestFullscreen;
        if (req) {
            req.call(elem).then(() => {
                document.getElementById('fullscreen-gate-modal')?.classList.add('hidden');
                updateFullscreenUI();
            }).catch(() => {
                // If browser denies (e.g. iframe or permission), hide gate and continue with standard secure mode
                document.getElementById('fullscreen-gate-modal')?.classList.add('hidden');
            });
        } else {
            document.getElementById('fullscreen-gate-modal')?.classList.add('hidden');
        }
    }

    function toggleFullscreenMode() {
        if (!isDocFullscreen()) {
            requestExamFullscreen();
        } else {
            const exit = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
            if (exit) exit.call(document);
        }
    }

    // Monitor Fullscreen Exit during exam
    function handleFullscreenChange() {
        updateFullscreenUI();
        if (!window.examSubmitted && !isDocFullscreen()) {
            // Student exited fullscreen mode: trigger security violation alert and show gate again
            const gate = document.getElementById('fullscreen-gate-modal');
            if (gate) gate.classList.remove('hidden');

            if (typeof window.recordExamViolation === 'function') {
                window.recordExamViolation('fullscreen_exit', 'Anda keluar dari mode layar penuh. Harap kembali ke mode layar penuh untuk melanjutkan.');
            }
        }
    }

    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('mozfullscreenchange', handleFullscreenChange);
    document.addEventListener('MSFullscreenChange', handleFullscreenChange);

    const questions = @json($safeQuestions);
    const savedAnswers = @json($savedAnswers);
    const savedDoubts = @json($savedDoubts);
    let remainingSeconds = {{ $remainingSeconds }};

    let currentIndex = 0;
    let answers = savedAnswers || {};
    let doubts = new Set(savedDoubts || []);

    // 1. COUNTDOWN TIMER
    function updateTimerDisplay() {
        if (remainingSeconds <= 0) {
            document.getElementById('countdown-timer').innerText = "00:00:00";
            autoSubmitOnTimeout();
            return;
        }

        const h = Math.floor(remainingSeconds / 3600);
        const m = Math.floor((remainingSeconds % 3600) / 60);
        const s = remainingSeconds % 60;

        const str = [
            String(h).padStart(2, '0'),
            String(m).padStart(2, '0'),
            String(s).padStart(2, '0')
        ].join(':');

        const el = document.getElementById('countdown-timer');
        if (el) el.innerText = str;

        remainingSeconds--;
    }

    const timerInterval = setInterval(updateTimerDisplay, 1000);
    updateTimerDisplay();

    function submitExamNow() {
        window.examSubmitted = true;
        document.getElementById('input-final-answers').value = JSON.stringify(answers);
        document.getElementById('form-submit-exam').submit();
    }

    function autoSubmitOnTimeout() {
        clearInterval(timerInterval);
        window.examSubmitted = true;
        document.getElementById('input-final-answers').value = JSON.stringify(answers);

        // Show elegant in-page modal instead of native browser alert
        const modal = document.getElementById('time-expired-modal');
        if (modal) {
            modal.classList.remove('hidden');

            let countdown = 3;
            const countdownEl = document.getElementById('timeout-countdown-number');
            const progressEl = document.getElementById('timeout-progress-bar');

            if (progressEl) progressEl.style.width = '100%';

            const submitTimer = setInterval(() => {
                countdown--;
                if (countdownEl) countdownEl.innerText = countdown;
                if (progressEl) progressEl.style.width = ((countdown / 3) * 100) + '%';

                if (countdown <= 0) {
                    clearInterval(submitTimer);
                    document.getElementById('form-submit-exam').submit();
                }
            }, 1000);
        } else {
            document.getElementById('form-submit-exam').submit();
        }
    }

    // 2. RENDER QUESTION
    function renderQuestion(index) {
        if (index < 0 || index >= questions.length) return;
        currentIndex = index;
        const q = questions[currentIndex];

        // Header info
        document.getElementById('current-question-badge').innerText = 'Soal No. ' + (currentIndex + 1);
        const typeBadge = document.getElementById('question-type-badge');
        if (q.type === 'pg') {
            typeBadge.innerText = 'Pilihan Ganda';
            typeBadge.className = 'bg-blue-100 text-blue-900 border border-blue-300 text-[11px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider';
        } else if (q.type === 'mcma') {
            typeBadge.innerText = 'MCMA (Pilih > 1 Jawaban)';
            typeBadge.className = 'bg-purple-100 text-purple-900 border border-purple-300 text-[11px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider';
        } else if (q.type === 'tf') {
            typeBadge.innerText = 'Benar / Salah';
            typeBadge.className = 'bg-amber-100 text-amber-900 border border-amber-300 text-[11px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider';
        }

        // Text & Image
        let questionHtml = '';
        if (q.image) {
            questionHtml += `<div class="mb-5 max-w-lg p-2.5 bg-slate-50 border border-slate-200 rounded-2xl shadow-sm">
                <img src="${q.image}" alt="Ilustrasi Soal" class="w-full max-h-80 object-contain rounded-xl bg-white">
            </div>`;
        }
        questionHtml += `<div class="leading-relaxed">${q.text.replace(/\n/g, '<br>')}</div>`;
        document.getElementById('question-text-container').innerHTML = questionHtml;

        // Options
        const container = document.getElementById('options-container');
        container.innerHTML = '';

        const currentAns = answers[q.id];

        if (q.type === 'pg') {
            // Radio options
            for (const [key, val] of Object.entries(q.options)) {
                const isChecked = (currentAns === key);
                const optEl = document.createElement('label');
                optEl.className = 'flex items-center space-x-3.5 p-4 rounded-2xl border-2 cursor-pointer transition ' +
                    (isChecked ? 'border-[#1F366A] bg-[#FFF7E9]' : 'border-slate-200 hover:border-slate-300 bg-white');

                optEl.innerHTML = `
                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center font-bold text-xs ${isChecked ? 'bg-[#1F366A] text-[#FFBE2A] border-[#1F366A]' : 'border-slate-300 text-slate-600'}">
                        ${key}
                    </div>
                    <span class="text-sm sm:text-base font-medium text-slate-800 flex-1">${val}</span>
                    <input type="radio" name="option_choice" value="${key}" ${isChecked ? 'checked' : ''} class="hidden">
                `;

                optEl.onclick = () => selectSingleAnswer(q.id, key);
                container.appendChild(optEl);
            }
        } else if (q.type === 'mcma') {
            // Checkbox options
            const currentArr = Array.isArray(currentAns) ? currentAns : (currentAns ? String(currentAns).split(',') : []);
            for (const [key, val] of Object.entries(q.options)) {
                const isChecked = currentArr.includes(key);
                const optEl = document.createElement('label');
                optEl.className = 'flex items-center space-x-3.5 p-4 rounded-2xl border-2 cursor-pointer transition ' +
                    (isChecked ? 'border-[#1F366A] bg-[#FFF7E9]' : 'border-slate-200 hover:border-slate-300 bg-white');

                optEl.innerHTML = `
                    <div class="w-7 h-7 rounded-lg border-2 flex items-center justify-center font-bold text-xs ${isChecked ? 'bg-[#1F366A] text-[#FFBE2A] border-[#1F366A]' : 'border-slate-300 text-slate-600'}">
                        ${isChecked ? '✓' : key}
                    </div>
                    <span class="text-sm sm:text-base font-medium text-slate-800 flex-1">${val}</span>
                `;

                optEl.onclick = (e) => {
                    e.preventDefault();
                    toggleMcmaAnswer(q.id, key);
                };
                container.appendChild(optEl);
            }
        } else if (q.type === 'tf') {
            // True / False options
            const choices = ['Benar', 'Salah'];
            choices.forEach(choice => {
                const isChecked = (currentAns === choice);
                const optEl = document.createElement('div');
                optEl.className = 'p-4 rounded-2xl border-2 cursor-pointer flex items-center space-x-3 transition ' +
                    (isChecked ? 'border-[#1F366A] bg-[#FFF7E9]' : 'border-slate-200 hover:border-slate-300 bg-white');

                optEl.innerHTML = `
                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center font-bold text-xs ${isChecked ? 'bg-[#1F366A] text-[#FFBE2A] border-[#1F366A]' : 'border-slate-300 text-slate-600'}">
                        ${choice === 'Benar' ? 'B' : 'S'}
                    </div>
                    <span class="text-sm sm:text-base font-extrabold ${choice === 'Benar' ? 'text-emerald-700' : 'text-rose-700'}">${choice}</span>
                `;

                optEl.onclick = () => selectSingleAnswer(q.id, choice);
                container.appendChild(optEl);
            });
        }

        // Doubt button state
        const doubtBtn = document.getElementById('btn-doubt');
        const doubtIcon = document.getElementById('doubt-icon');
        const doubtLabel = document.getElementById('doubt-label');
        if (doubts.has(q.id)) {
            doubtBtn.className = 'w-full sm:w-auto px-5 py-2.5 rounded-xl border-2 border-amber-500 bg-[#FFBE2A] text-[#1F366A] font-black text-xs flex items-center justify-center shadow transition';
            doubtIcon.className = 'fa-solid fa-bookmark mr-2 text-[#1F366A]';
            doubtLabel.innerText = 'Ragu-ragu (Ditandai)';
        } else {
            doubtBtn.className = 'w-full sm:w-auto px-5 py-2.5 rounded-xl border-2 border-amber-400 bg-amber-50 text-amber-900 hover:bg-amber-100 font-extrabold text-xs flex items-center justify-center transition';
            doubtIcon.className = 'fa-regular fa-bookmark mr-2 text-amber-600';
            doubtLabel.innerText = 'Tandai Ragu-ragu';
        }

        // Prev/Next state
        document.getElementById('btn-prev').disabled = (currentIndex === 0);
        document.getElementById('btn-prev').classList.toggle('opacity-40', currentIndex === 0);

        renderGrid();
        updateStats();
    }

    // 3. ANSWERS HANDLERS
    function selectSingleAnswer(questionId, value) {
        answers[questionId] = value;
        persistAnswer(questionId, value);
        renderQuestion(currentIndex);
    }

    function toggleMcmaAnswer(questionId, value) {
        let current = answers[questionId];
        if (!Array.isArray(current)) {
            current = current ? [current] : [];
        }
        if (current.includes(value)) {
            current = current.filter(v => v !== value);
        } else {
            current.push(value);
        }
        answers[questionId] = current;
        persistAnswer(questionId, current);
        renderQuestion(currentIndex);
    }

    function toggleDoubt() {
        const q = questions[currentIndex];
        if (doubts.has(q.id)) {
            doubts.delete(q.id);
        } else {
            doubts.add(q.id);
        }
        persistAnswer(q.id, answers[q.id]);
        renderQuestion(currentIndex);
    }

    function persistAnswer(questionId, answer) {
        axios.post('/api/exam/' + window.examSessionId + '/save-answer', {
            question_id: questionId,
            answer: answer,
            is_doubt: doubts.has(questionId)
        }).catch(() => {});
    }

    function nextQuestion() {
        if (currentIndex < questions.length - 1) {
            renderQuestion(currentIndex + 1);
        } else {
            confirmSubmitExam();
        }
    }

    function prevQuestion() {
        if (currentIndex > 0) {
            renderQuestion(currentIndex - 1);
        }
    }

    // 4. GRID RENDERING
    function renderGrid() {
        const grid = document.getElementById('question-grid');
        grid.innerHTML = '';

        questions.forEach((q, idx) => {
            const isCurrent = (idx === currentIndex);
            const ans = answers[q.id];
            const hasAns = (ans !== undefined && ans !== null && ans !== '' && (!Array.isArray(ans) || ans.length > 0));
            const isDoubt = doubts.has(q.id);

            let bgClass = 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50';
            if (isDoubt) {
                bgClass = 'bg-[#FFBE2A] border-amber-500 text-[#1F366A] font-black';
            } else if (hasAns) {
                bgClass = 'bg-[#1F366A] border-[#1F366A] text-white font-extrabold';
            }

            const currentRing = isCurrent ? 'ring-4 ring-amber-400 scale-105 shadow-md' : '';

            const btn = document.createElement('button');
            btn.className = `h-10 rounded-xl border-2 flex items-center justify-center text-xs transition font-bold ${bgClass} ${currentRing}`;
            btn.innerText = idx + 1;
            btn.onclick = () => renderQuestion(idx);
            grid.appendChild(btn);
        });
    }

    function updateStats() {
        let answered = 0;
        let doubtCount = doubts.size;

        questions.forEach(q => {
            const ans = answers[q.id];
            if (ans !== undefined && ans !== null && ans !== '' && (!Array.isArray(ans) || ans.length > 0)) {
                answered++;
            }
        });

        const unanswered = questions.length - answered;

        document.getElementById('stat-answered').innerText = answered;
        document.getElementById('stat-doubt').innerText = doubtCount;
        document.getElementById('stat-unanswered').innerText = unanswered;
    }

    // 5. SUBMIT CONFIRMATION
    function confirmSubmitExam() {
        let answered = 0;
        let doubtCount = doubts.size;

        questions.forEach(q => {
            const ans = answers[q.id];
            if (ans !== undefined && ans !== null && ans !== '' && (!Array.isArray(ans) || ans.length > 0)) {
                answered++;
            }
        });

        const empty = questions.length - answered;

        document.getElementById('modal-summary-answered').innerText = answered;
        document.getElementById('modal-summary-doubt').innerText = doubtCount;
        document.getElementById('modal-summary-empty').innerText = empty;

        document.getElementById('input-final-answers').value = JSON.stringify(answers);
        document.getElementById('submit-confirm-modal').classList.remove('hidden');
    }

    function closeSubmitConfirmModal() {
        document.getElementById('submit-confirm-modal').classList.add('hidden');
    }

    document.getElementById('form-submit-exam').addEventListener('submit', () => {
        window.examSubmitted = true;
    });

    // Initial render
    renderQuestion(0);
</script>
@endpush
@endsection
