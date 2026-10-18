@extends('layouts.admin')

@section('title', 'Dashboard Administrator - Bimbel Cemerlang')
@section('header_title', 'Ringkasan & Analitik Try Out')

@section('content')
<div class="space-y-8">

    <!-- KPI Metrics Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Total Paket -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
            <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Paket Try Out</span>
            <div class="text-2xl font-black text-[#1F366A]">{{ $totalPackages }}</div>
            <a href="{{ url('/admin/packages') }}" class="text-[10px] text-[#FFBE2A] hover:underline font-bold mt-1 block">Kelola Paket &rarr;</a>
        </div>

        <!-- Total Soal -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
            <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Bank Soal</span>
            <div class="text-2xl font-black text-[#1F366A]">{{ $totalQuestions }}</div>
            <span class="text-[10px] text-slate-400 font-semibold mt-1 block">3 Tipe Soal Aktif</span>
        </div>

        <!-- Siswa Terdaftar -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
            <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Peserta / Siswa</span>
            <div class="text-2xl font-black text-[#1F366A]">{{ $totalStudents }}</div>
            <a href="{{ url('/admin/users') }}" class="text-[10px] text-emerald-600 hover:underline font-bold mt-1 block">Kelola Akun &rarr;</a>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border-2 {{ $pendingOrders > 0 ? 'border-amber-400 bg-amber-50/40' : 'border-slate-200' }}">
            <span class="text-[10px] font-bold text-amber-800 uppercase block mb-1">Verifikasi Bayar</span>
            <div class="text-2xl font-black {{ $pendingOrders > 0 ? 'text-amber-600' : 'text-slate-700' }}">{{ $pendingOrders }}</div>
            <a href="{{ url('/admin/orders') }}" class="text-[10px] text-amber-700 hover:underline font-bold mt-1 block">Cek Bukti &rarr;</a>
        </div>

        <!-- Ujian Berlangsung -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
            <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Ujian Berjalan</span>
            <div class="text-2xl font-black text-emerald-600 flex items-center">
                <span>{{ $activeSessions }}</span>
                @if($activeSessions > 0)
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ml-2 animate-ping"></span>
                @endif
            </div>
            <a href="{{ url('/admin/proctoring') }}" class="text-[10px] text-emerald-700 hover:underline font-bold mt-1 block">Pantau Sesi &rarr;</a>
        </div>

        <!-- Peringatan -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
            <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Catatan Peringatan</span>
            <div class="text-2xl font-black text-amber-600">{{ $totalViolations }}</div>
            <span class="text-[10px] text-slate-400 font-semibold mt-1 block">Tercatat Sistem</span>
        </div>
    </div>

    <!-- Quick Action Banners -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-[#FFF7E9] rounded-2xl p-6 border-2 border-amber-300 shadow-sm flex items-center justify-between">
            <div>
                <h4 class="font-black text-[#1F366A] text-base mb-1 font-serif">Paket & Bank Soal</h4>
                <p class="text-xs text-slate-600">Import soal via Excel (.xlsx) atau generate otomatis dari file PDF.</p>
            </div>
            <a href="{{ url('/admin/packages') }}" class="btn-cemerlang-navy text-xs font-bold px-4 py-2.5 rounded-xl flex-shrink-0 ml-4 shadow">
                Kelola Soal
            </a>
        </div>

        <div class="bg-emerald-50 rounded-2xl p-6 border-2 border-emerald-300 shadow-sm flex items-center justify-between">
            <div>
                <h4 class="font-black text-emerald-950 text-base mb-1 font-serif">Verifikasi Pembayaran</h4>
                <p class="text-xs text-slate-600">Aktivasi akses try out siswa yang telah mentransfer pembayaran.</p>
            </div>
            <a href="{{ url('/admin/orders') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl flex-shrink-0 ml-4 shadow">
                Lihat Bukti Bayar
            </a>
        </div>

        <div class="bg-blue-50 rounded-2xl p-6 border-2 border-blue-300 shadow-sm flex items-center justify-between">
            <div>
                <h4 class="font-black text-blue-950 text-base mb-1 font-serif">Ekspor Rekapitulasi</h4>
                <p class="text-xs text-slate-600">Unduh laporan nilai siswa dalam format Excel (.xlsx) atau CSV.</p>
            </div>
            <a href="{{ url('/admin/reports') }}" class="bg-blue-800 hover:bg-blue-900 text-white text-xs font-bold px-4 py-2.5 rounded-xl flex-shrink-0 ml-4 shadow">
                Ekspor Laporan
            </a>
        </div>
    </div>

    <!-- Tables: Recent Transactions & Recent Sessions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Recent Orders -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-black text-sm text-[#1F366A] uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-receipt mr-2 text-[#FFBE2A]"></i> Transaksi Terbaru
                </h3>
                <a href="{{ url('/admin/orders') }}" class="text-xs text-[#1F366A] font-bold hover:underline">Semua &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="py-2.5 px-3 text-left">Siswa</th>
                            <th class="py-2.5 px-3 text-left">Paket</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentOrders as $ro)
                            <tr>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-[#1F366A] block">{{ $ro['student_name'] }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $ro['student_email'] }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="truncate block max-w-[140px] text-slate-700 font-semibold">{{ $ro['package_title'] }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $ro['amount'] == 0 ? 'Gratis' : 'Rp ' . number_format($ro['amount'], 0, ',', '.') }}</span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($ro['status'] === 'approved')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                    @elseif($ro['status'] === 'pending')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Ditolak</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if($ro['status'] === 'pending')
                                        <a href="{{ url('/admin/orders') }}" class="btn-cemerlang-yellow px-2.5 py-1 rounded text-[10px] font-bold shadow-sm">
                                            Verifikasi
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Belum ada transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Attempts -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-black text-sm text-[#1F366A] uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-graduation-cap mr-2 text-[#FFBE2A]"></i> Ujian Siswa Terbaru
                </h3>
                <a href="{{ url('/admin/reports') }}" class="text-xs text-[#1F366A] font-bold hover:underline">Semua Rekap &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="py-2.5 px-3 text-left">Nama Siswa</th>
                            <th class="py-2.5 px-3 text-center">Skor</th>
                            <th class="py-2.5 px-3 text-center">Hasil</th>
                            <th class="py-2.5 px-3 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentSessions as $rs)
                            <tr>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-[#1F366A] block">{{ $rs['student_name'] }}</span>
                                    <span class="text-[10px] text-slate-400 truncate block max-w-[130px]">{{ $rs['package_title'] ?? 'Try Out' }}</span>
                                </td>
                                <td class="py-3 px-3 text-center font-extrabold text-[#1F366A]">
                                    {{ $rs['score'] ?? '-' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if(($rs['status'] ?? '') === 'submitted')
                                        @if(!empty($rs['is_passed']))
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Lulus</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Belum Lulus</span>
                                        @endif
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Aktif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right text-slate-400 text-[10px]">
                                    {{ substr($rs['started_at'], 0, 16) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Belum ada ujian yang dikerjakan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
