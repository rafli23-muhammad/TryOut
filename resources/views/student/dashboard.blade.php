@extends('layouts.cemerlang')

@section('title', 'Dashboard Siswa - Bimbel Cemerlang')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

    <!-- Profile & Welcome Banner -->
    <div class="bg-gradient-to-r from-[#1F366A] to-[#132347] text-white rounded-3xl p-6 sm:p-8 shadow-xl border-4 border-[#FFBE2A] flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center space-x-5">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl overflow-hidden bg-[#FFBE2A] text-[#1F366A] font-black text-2xl sm:text-3xl flex items-center justify-center shadow-lg border-2 border-white/20">
                @if(session('student_avatar'))
                    <img src="{{ asset(session('student_avatar')) }}" alt="Foto Siswa" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr(session('student_name', 'S'), 0, 1)) }}
                @endif
            </div>
            <div>
                <span class="text-xs font-bold text-[#FFBE2A] uppercase tracking-wider block">Panel Siswa Terdaftar</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold font-serif">{{ session('student_name') }}</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 flex flex-wrap items-center gap-3">
                    <span><i class="fa-solid fa-envelope mr-1 text-[#FFBE2A]"></i>{{ session('student_email') }}</span>
                    <span><i class="fa-solid fa-phone mr-1 text-[#FFBE2A]"></i>{{ session('student_phone', '-') }}</span>
                    <span><i class="fa-solid fa-school mr-1 text-[#FFBE2A]"></i>{{ session('student_school', '-') }}</span>
                </p>
            </div>
        </div>
        <div>
            <a href="{{ url('/student/catalog') }}" class="btn-cemerlang-yellow text-xs font-extrabold px-5 py-3.5 rounded-xl shadow inline-flex items-center">
                <i class="fa-solid fa-cart-plus mr-2"></i> Tambah Paket Try Out
            </a>
        </div>
    </div>

    <!-- 1. Enrolled & Active Packages -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-black font-serif text-[#1F366A] flex items-center">
                <i class="fa-solid fa-circle-check text-emerald-600 mr-2"></i> Paket Try Out Aktif Saya
            </h2>
            <span class="text-xs font-bold text-slate-500">{{ $enrolledPackages->count() }} Paket Siap Dikerjakan</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($enrolledPackages as $pkg)
                @php $qCount = $questionsCount->get($pkg['id'], 0); @endphp
                <div class="bg-white rounded-3xl p-6 shadow-md border-2 border-emerald-400 hover:shadow-xl transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="bg-[#1F366A] text-[#FFBE2A] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
                                {{ $pkg['category'] }}
                            </span>
                            <span class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                <i class="fa-solid fa-shield mr-1"></i> Akses Aktif
                            </span>
                        </div>
                        <h3 class="text-lg font-black text-[#1F366A] font-serif mb-2 line-clamp-2">{{ $pkg['title'] }}</h3>
                        <p class="text-xs text-slate-600 line-clamp-2 mb-4">{{ $pkg['description'] }}</p>

                        <div class="bg-[#FFF7E9] p-3 rounded-2xl border border-amber-200/60 grid grid-cols-2 gap-2 text-xs mb-4">
                            <div>
                                <span class="text-[10px] text-slate-500 block">Durasi Ujian:</span>
                                <span class="font-bold text-[#1F366A]">{{ $pkg['duration_minutes'] }} Menit</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 block">Total Soal:</span>
                                <span class="font-bold text-[#1F366A]">{{ $qCount }} Butir</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 block">Passing Grade:</span>
                                <span class="font-bold text-emerald-700">{{ $pkg['passing_grade'] }}%</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 block">Pembahasan:</span>
                                <span class="font-bold text-[#1F366A]">Lengkap</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ url('/exam/' . $pkg['id']) }}" class="btn-cemerlang-yellow w-full py-3 rounded-xl font-extrabold text-xs text-center block shadow">
                        <i class="fa-solid fa-play mr-1.5"></i> Mulai Ujian Sekarang
                    </a>
                </div>
            @empty
                <div class="col-span-3 bg-white rounded-3xl p-8 text-center border-2 border-dashed border-slate-300">
                    <i class="fa-solid fa-graduation-cap text-4xl text-slate-300 mb-2"></i>
                    <h3 class="font-bold text-slate-700 text-sm">Belum ada paket try out yang aktif</h3>
                    <p class="text-xs text-slate-500 mb-4">Ambil paket simulasi gratis atau lakukan checkout untuk memulai try out.</p>
                    <a href="{{ url('/student/catalog') }}" class="btn-cemerlang-navy text-xs font-bold px-4 py-2 rounded-xl">
                        Buka Katalog Paket
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2. Exam History & Evaluation Results -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-black font-serif text-[#1F366A] flex items-center">
                <i class="fa-solid fa-chart-line text-[#FFBE2A] mr-2"></i> Riwayat & Hasil Evaluasi Ujian
            </h2>
            <span class="text-xs font-bold text-slate-500">{{ $attempts->count() }} Sesi Tercatat</span>
        </div>

        <div class="bg-white rounded-3xl shadow-md border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-[#1F366A] text-white text-xs uppercase font-extrabold tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Paket Try Out</th>
                            <th class="py-3.5 px-4">Waktu Selesai</th>
                            <th class="py-3.5 px-4 text-center">Skor Akhir</th>
                            <th class="py-3.5 px-4 text-center">Passing Grade</th>
                            <th class="py-3.5 px-4 text-center">Status Kelulusan</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($attempts as $att)
                            @php
                                $pkg = $packages->get($att['package_id']);
                                $isPassed = !empty($att['is_passed']);
                                $isSubmitted = ($att['status'] ?? '') === 'submitted';
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-4 px-4 font-bold text-[#1F366A]">
                                    {{ $att['package_title'] ?? ($pkg['title'] ?? 'Try Out') }}
                                    <div class="text-[11px] text-slate-500 font-normal">
                                        Benar: <span class="text-emerald-600 font-bold">{{ $att['correct_count'] ?? 0 }}</span> | Salah: <span class="text-rose-600 font-bold">{{ $att['wrong_count'] ?? 0 }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-600">
                                    {{ $att['finished_at'] ?? $att['started_at'] }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($isSubmitted)
                                        <span class="text-base font-extrabold text-[#1F366A]">{{ $att['score'] }}</span>
                                        <span class="text-[10px] text-slate-400 block">/ 100</span>
                                    @else
                                        <span class="text-xs text-amber-600 font-bold bg-amber-50 px-2 py-1 rounded">Sedang Berlangsung</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-slate-700">
                                    {{ $att['passing_grade'] ?? ($pkg['passing_grade'] ?? 70) }}%
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($isSubmitted)
                                        @if($isPassed)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                <i class="fa-solid fa-trophy mr-1.5 text-amber-500"></i> LULUS
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-300">
                                                <i class="fa-solid fa-triangle-exclamation mr-1.5"></i> BELUM LULUS
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    @if($isSubmitted)
                                        <a href="{{ url('/exam/' . $att['id'] . '/result') }}" class="btn-cemerlang-navy text-xs font-bold px-3 py-1.5 rounded-lg shadow inline-flex items-center">
                                            <i class="fa-solid fa-book-open-reader mr-1.5 text-[#FFBE2A]"></i> Pembahasan
                                        </a>
                                    @else
                                        <a href="{{ url('/exam/' . $att['package_id']) }}" class="btn-cemerlang-yellow text-xs font-bold px-3 py-1.5 rounded-lg shadow inline-flex items-center">
                                            Lanjutkan Ujian
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-xs text-slate-400">
                                    Belum ada riwayat pengerjaan ujian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Transactions / Order History -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-black font-serif text-[#1F366A] flex items-center">
                <i class="fa-solid fa-receipt text-[#1F366A] mr-2"></i> Status Transaksi & Pembayaran
            </h2>
        </div>

        <div class="bg-white rounded-3xl shadow-md border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-100 text-slate-700 text-xs uppercase font-extrabold">
                        <tr>
                            <th class="py-3 px-4">No. Transaksi</th>
                            <th class="py-3 px-4">Paket Try Out</th>
                            <th class="py-3 px-4">Metode & Nominal</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4 text-center">Status Akses</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($orders as $ord)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3.5 px-4 font-mono font-bold text-xs text-slate-600">
                                    {{ $ord['id'] }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-[#1F366A]">
                                    {{ $ord['package_title'] }}
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <span class="block text-slate-600">{{ $ord['payment_method'] ?? 'Transfer' }}</span>
                                    <span class="font-bold text-[#1F366A]">
                                        {{ $ord['amount'] == 0 ? 'GRATIS' : 'Rp ' . number_format($ord['amount'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-500">
                                    {{ $ord['created_at'] }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($ord['status'] === 'approved')
                                        <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                                            <i class="fa-solid fa-check mr-1"></i> Disetujui (Aktif)
                                        </span>
                                    @elseif($ord['status'] === 'pending')
                                        <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800">
                                            <i class="fa-solid fa-hourglass-half mr-1"></i> Menunggu Verifikasi Admin
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800">
                                            <i class="fa-solid fa-xmark mr-1"></i> Ditolak
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-xs text-slate-400">
                                    Belum ada transaksi pembelian paket.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
