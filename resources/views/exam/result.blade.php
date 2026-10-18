@extends('layouts.cemerlang')

@section('title', 'Hasil Evaluasi & Pembahasan Try Out - Bimbel Cemerlang')

@section('content')
<div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-6 sm:space-y-8">

    <!-- 1. Score & Passing Grade Evaluation Banner -->
    @php
        $isPassed = !empty($session['is_passed']);
        $score = $session['score'] ?? 0;
        $passingGrade = $session['passing_grade'] ?? ($package['passing_grade'] ?? 70);
    @endphp

    <div class="rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-2xl text-white relative overflow-hidden border-2 sm:border-4 {{ $isPassed ? 'bg-gradient-to-r from-emerald-800 via-teal-900 to-[#1F366A] border-[#FFBE2A]' : 'bg-gradient-to-r from-rose-900 via-slate-900 to-[#1F366A] border-rose-400' }}">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
            <!-- Evaluation Status -->
            <div class="space-y-2.5 sm:space-y-3 text-center md:text-left">
                <span class="inline-block px-3.5 py-1 rounded-full text-[10px] sm:text-xs font-black uppercase tracking-wider {{ $isPassed ? 'bg-[#FFBE2A] text-[#1F366A]' : 'bg-rose-500 text-white' }}">
                    Hasil Evaluasi Instan
                </span>
                <h1 class="text-2xl sm:text-4xl font-black font-serif">
                    @if($isPassed)
                        <i class="fa-solid fa-trophy mr-1.5 sm:mr-2 text-[#FFBE2A]"></i> SELAMAT, ANDA LULUS!
                    @else
                        <i class="fa-solid fa-circle-exclamation mr-1.5 sm:mr-2 text-rose-400"></i> BELUM LULUS
                    @endif
                </h1>
                <p class="text-xs sm:text-sm text-slate-200 max-w-lg">
                    @if($isPassed)
                        Skor akhir Anda berhasil melampaui ambang batas Passing Grade untuk <b>{{ $package['title'] }}</b>. Pertahankan dan tingkatkan prestasimu!
                    @else
                        Skor akhir Anda belum mencapai target Passing Grade. Pelajari pembahasan soal di bawah ini untuk memperdalam pemahaman konsep materi.
                    @endif
                </p>
                <div class="text-[11px] sm:text-xs text-slate-300 font-semibold pt-1">
                    Peserta: <b>{{ $session['student_name'] }}</b> | Selesai: <b>{{ $session['finished_at'] ?? date('Y-m-d H:i') }}</b>
                </div>
            </div>

            <!-- Score Display Badge -->
            <div class="w-full md:w-auto bg-white/10 backdrop-blur-md border-2 border-white/20 p-4 sm:p-6 rounded-2xl sm:rounded-3xl text-center min-w-[180px] sm:min-w-[200px] shadow-lg">
                <span class="text-[10px] sm:text-xs text-slate-200 font-bold uppercase tracking-wider block">Skor Akhir</span>
                <div class="text-4xl sm:text-5xl font-black font-serif my-1 {{ $isPassed ? 'text-[#FFBE2A]' : 'text-white' }}">
                    {{ $score }}
                </div>
                <div class="text-[11px] sm:text-xs text-slate-300 font-semibold border-t border-white/20 pt-2 mt-2">
                    Passing Grade: <b class="text-white">{{ $passingGrade }}%</b>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Statistics Breakdown Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Benar -->
        <div class="bg-white rounded-2xl p-5 shadow-md border-2 border-emerald-300 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-check"></i>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Jawaban Benar</span>
                <span class="text-2xl font-black text-emerald-700">{{ $session['correct_count'] ?? 0 }}</span>
                <span class="text-xs text-slate-400 font-semibold">/ {{ $session['total_questions'] ?? count($questions) }}</span>
            </div>
        </div>

        <!-- Salah -->
        <div class="bg-white rounded-2xl p-5 shadow-md border-2 border-rose-300 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Jawaban Salah</span>
                <span class="text-2xl font-black text-rose-700">{{ $session['wrong_count'] ?? 0 }}</span>
                <span class="text-xs text-slate-400 font-semibold">Butir</span>
            </div>
        </div>

        <!-- Kosong -->
        <div class="bg-white rounded-2xl p-5 shadow-md border-2 border-slate-300 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-minus"></i>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Tidak Dijawab</span>
                <span class="text-2xl font-black text-slate-700">{{ $session['empty_count'] ?? 0 }}</span>
                <span class="text-xs text-slate-400 font-semibold">Butir</span>
            </div>
        </div>

        <!-- Akurasi -->
        @php
            $totalQ = $session['total_questions'] ?: count($questions);
            $accuracy = $totalQ > 0 ? round((($session['correct_count'] ?? 0) / $totalQ) * 100, 1) : 0;
        @endphp
        <div class="bg-white rounded-2xl p-5 shadow-md border-2 border-amber-300 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-[#1F366A] flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-percent"></i>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Tingkat Akurasi</span>
                <span class="text-2xl font-black text-[#1F366A]">{{ $accuracy }}%</span>
                <span class="text-xs text-slate-400 font-semibold">Rasio</span>
            </div>
        </div>
    </div>

    <!-- Quick Navigation & Filter for Discussion -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center space-x-2">
            <span class="text-xs font-bold text-slate-600">Filter Pembahasan:</span>
            <button onclick="filterExplanations('all')" class="btn-filter active px-3 py-1.5 rounded-xl text-xs font-bold bg-[#1F366A] text-white" id="filter-all">
                Semua Soal ({{ count($questions) }})
            </button>
            <button onclick="filterExplanations('correct')" class="btn-filter px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-emerald-800 hover:bg-emerald-50" id="filter-correct">
                Benar ({{ $session['correct_count'] ?? 0 }})
            </button>
            <button onclick="filterExplanations('wrong')" class="btn-filter px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-rose-800 hover:bg-rose-50" id="filter-wrong">
                Salah / Kosong ({{ ($session['wrong_count'] ?? 0) + ($session['empty_count'] ?? 0) }})
            </button>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-3.5 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-print mr-1"></i> Cetak Hasil
            </button>
            <a href="{{ url('/student/dashboard') }}" class="btn-cemerlang-navy px-4 py-1.5 rounded-xl text-xs font-bold">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- 3. Rincian & Pembahasan Singkat per Nomor -->
    <div class="space-y-6">
        <h2 class="text-xl font-black font-serif text-[#1F366A] flex items-center">
            <i class="fa-solid fa-book-open-reader text-[#FFBE2A] mr-2"></i> Rincian & Pembahasan Soal
        </h2>

        @foreach($questions as $index => $q)
            @php
                $eval = $evaluations[$q['id']] ?? null;
                $isCorrect = $eval['is_correct'] ?? false;
                $isEmpty = $eval['is_empty'] ?? true;
                $studentAnswer = $eval['student_answer'] ?? '-';
                $correctAnswer = $eval['correct_answer'] ?? ($q['correct_answer'] ?? '-');
            @endphp

            <div class="question-card bg-white rounded-3xl p-6 sm:p-8 shadow-md border-2 {{ $isCorrect ? 'border-emerald-300 status-correct' : 'border-rose-300 status-wrong' }} transition hover:shadow-lg" data-status="{{ $isCorrect ? 'correct' : 'wrong' }}">
                <!-- Card Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div class="flex items-center space-x-3">
                        <span class="bg-[#1F366A] text-[#FFBE2A] text-xs font-black px-3.5 py-1 rounded-full shadow-sm">
                            Nomor {{ $index + 1 }}
                        </span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700">
                            {{ $q['type'] === 'pg' ? 'Pilihan Ganda' : ($q['type'] === 'mcma' ? 'MCMA (Multi-Select)' : 'Benar / Salah') }}
                        </span>
                    </div>

                    <!-- Result Status Badge -->
                    @if($isCorrect)
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 flex items-center">
                            <i class="fa-solid fa-circle-check mr-1.5 text-emerald-600"></i> Jawaban Benar
                        </span>
                    @elseif($isEmpty)
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-600 flex items-center">
                            <i class="fa-solid fa-circle-minus mr-1.5 text-slate-400"></i> Tidak Dijawab
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 flex items-center">
                            <i class="fa-solid fa-circle-xmark mr-1.5 text-rose-600"></i> Jawaban Salah
                        </span>
                    @endif
                </div>

                <!-- Question Text -->
                <div class="text-base text-slate-900 font-medium leading-relaxed mb-6">
                    @if(!empty($q['image']))
                        <div class="mb-4 max-w-lg p-2.5 bg-slate-50 border border-slate-200 rounded-2xl shadow-sm">
                            <img src="{{ asset($q['image']) }}" alt="Ilustrasi Soal {{ $index + 1 }}" class="w-full max-h-80 object-contain rounded-xl bg-white">
                        </div>
                    @endif
                    {!! nl2br(e($q['text'])) !!}
                </div>

                <!-- Answer Options & Choices -->
                <div class="space-y-2 mb-6">
                    @if($q['type'] === 'pg' || $q['type'] === 'mcma')
                        @foreach($q['options'] as $optKey => $optVal)
                            @php
                                $isThisStudentAns = false;
                                if (is_array($studentAnswer)) {
                                    $isThisStudentAns = in_array($optKey, $studentAnswer);
                                } else {
                                    $isThisStudentAns = (strtoupper(trim((string)$studentAnswer)) === strtoupper(trim($optKey)));
                                }

                                $isThisCorrectKey = false;
                                if (is_array($correctAnswer)) {
                                    $isThisCorrectKey = in_array($optKey, $correctAnswer);
                                } else {
                                    $isThisCorrectKey = (strtoupper(trim((string)$correctAnswer)) === strtoupper(trim($optKey)));
                                }
                            @endphp

                            <div class="flex items-center space-x-3 p-3 rounded-xl border text-xs sm:text-sm font-medium
                                {{ $isThisCorrectKey ? 'bg-emerald-50 border-emerald-400 text-emerald-950 font-bold' : ($isThisStudentAns ? 'bg-rose-50 border-rose-300 text-rose-950' : 'bg-slate-50/60 border-slate-200 text-slate-700') }}">
                                <span class="w-6 h-6 rounded-md flex items-center justify-center font-bold text-xs
                                    {{ $isThisCorrectKey ? 'bg-emerald-600 text-white' : ($isThisStudentAns ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-700') }}">
                                    {{ $optKey }}
                                </span>
                                <span class="flex-1">{{ $optVal }}</span>

                                @if($isThisCorrectKey)
                                    <span class="text-[11px] font-black text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">
                                        <i class="fa-solid fa-check mr-1"></i> Kunci Benar
                                    </span>
                                @endif
                                @if($isThisStudentAns && !$isThisCorrectKey)
                                    <span class="text-[11px] font-black text-rose-700 bg-rose-100 px-2 py-0.5 rounded">
                                        <i class="fa-solid fa-xmark mr-1"></i> Jawaban Anda
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    @elseif($q['type'] === 'tf')
                        <div class="grid grid-cols-2 gap-3 text-xs sm:text-sm">
                            @foreach(['Benar', 'Salah'] as $tfChoice)
                                @php
                                    $isThisStudentAns = (strcasecmp(trim((string)$studentAnswer), $tfChoice) === 0);
                                    $isThisCorrectKey = (strcasecmp(trim((string)$correctAnswer), $tfChoice) === 0);
                                @endphp
                                <div class="p-3 rounded-xl border flex items-center justify-between font-bold
                                    {{ $isThisCorrectKey ? 'bg-emerald-50 border-emerald-400 text-emerald-950' : ($isThisStudentAns ? 'bg-rose-50 border-rose-300 text-rose-950' : 'bg-slate-50 border-slate-200 text-slate-600') }}">
                                    <span>{{ $tfChoice }} ({{ $tfChoice === 'Benar' ? 'True' : 'False' }})</span>
                                    @if($isThisCorrectKey)
                                        <span class="text-[10px] bg-emerald-200 text-emerald-900 px-2 py-0.5 rounded font-black">Kunci Benar</span>
                                    @endif
                                    @if($isThisStudentAns && !$isThisCorrectKey)
                                        <span class="text-[10px] bg-rose-200 text-rose-900 px-2 py-0.5 rounded font-black">Jawaban Anda</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Kotak Pembahasan Lengkap & Rumus per Nomor -->
                <div class="bg-[#FFF7E9] rounded-2xl p-4 sm:p-5 border-2 border-amber-300 text-xs sm:text-sm text-[#1F366A]">
                    <div class="flex items-center space-x-2 font-black text-[#1F366A] uppercase tracking-wider text-xs mb-2">
                        <i class="fa-solid fa-lightbulb text-[#FFBE2A] text-base"></i>
                        <span>Pembahasan Lengkap & Rumus:</span>
                    </div>
                    <div class="text-slate-800 leading-relaxed font-normal math-content space-y-2">
                        {!! nl2br(preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', e($q['explanation'] ?? 'Pembahasan belum tersedia untuk soal ini.'))) !!}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    function filterExplanations(type) {
        const cards = document.querySelectorAll('.question-card');
        const btns = document.querySelectorAll('.btn-filter');

        btns.forEach(b => {
            b.classList.remove('bg-[#1F366A]', 'text-white');
            b.classList.add('bg-slate-100');
        });

        const activeBtn = document.getElementById('filter-' + type);
        if (activeBtn) {
            activeBtn.classList.add('bg-[#1F366A]', 'text-white');
            activeBtn.classList.remove('bg-slate-100');
        }

        cards.forEach(card => {
            const status = card.getAttribute('data-status');
            if (type === 'all') {
                card.classList.remove('hidden');
            } else if (type === 'correct') {
                card.classList.toggle('hidden', status !== 'correct');
            } else if (type === 'wrong') {
                card.classList.toggle('hidden', status !== 'wrong');
            }
        });
    }
</script>
@endpush
@endsection
