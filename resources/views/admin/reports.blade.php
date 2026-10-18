@extends('layouts.admin')

@section('title', 'Laporan & Rekapitulasi Nilai - Bimbel Cemerlang')
@section('header_title', 'Laporan & Rekapitulasi Nilai Ujian')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-black font-serif text-[#1F366A]">Rekapitulasi Nilai Seluruh Peserta</h3>
            <p class="text-xs text-slate-500">Pantau performa kelulusan siswa dan unduh rekap nilai lengkap dalam format Excel (.xlsx) atau CSV.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter Package Dropdown -->
            <form method="GET" action="{{ url('/admin/reports') }}" class="flex items-center space-x-2">
                <label class="text-xs font-bold text-slate-600">Paket:</label>
                <select name="package_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                    <option value="all">Semua Paket Try Out</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg['id'] }}" {{ $packageFilter === $pkg['id'] ? 'selected' : '' }}>
                            {{ $pkg['title'] }}
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Export Buttons -->
            <a href="{{ url('/admin/reports/export/excel?package_id=' . $packageFilter) }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow flex items-center transition">
                <i class="fa-solid fa-file-excel mr-2"></i> Ekspor Excel (.xlsx)
            </a>

            <a href="{{ url('/admin/reports/export/csv?package_id=' . $packageFilter) }}" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-black shadow-sm flex items-center transition">
                <i class="fa-solid fa-file-csv mr-2 text-slate-600"></i> Ekspor CSV
            </a>
        </div>
    </div>

    <!-- Scores Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-[#1F366A] text-white text-xs uppercase font-extrabold tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Siswa</th>
                        <th class="py-3.5 px-4">Paket Try Out</th>
                        <th class="py-3.5 px-4 text-center">Statistik (B/S/K)</th>
                        <th class="py-3.5 px-4 text-center">Skor Akhir</th>
                        <th class="py-3.5 px-4 text-center">Passing Grade</th>
                        <th class="py-3.5 px-4 text-center">Status Kelulusan</th>
                        <th class="py-3.5 px-4 text-center">Pelanggaran</th>
                        <th class="py-3.5 px-4 text-right">Waktu Selesai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($sessions as $index => $s)
                        @php $isPassed = !empty($s['is_passed']); @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 text-center font-bold text-slate-500">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-[#1F366A] block">{{ $s['student_name'] }}</span>
                                <span class="text-[10px] text-slate-400">{{ $s['student_email'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700 truncate max-w-[180px]">
                                {{ $s['package_title'] ?? 'Try Out' }}
                            </td>
                            <td class="py-3.5 px-4 text-center text-xs">
                                <span class="text-emerald-700 font-bold">{{ $s['correct_count'] ?? 0 }}</span> /
                                <span class="text-rose-700 font-bold">{{ $s['wrong_count'] ?? 0 }}</span> /
                                <span class="text-slate-400 font-bold">{{ $s['empty_count'] ?? 0 }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-black text-sm text-[#1F366A]">
                                {{ $s['score'] ?? 0 }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-600">
                                {{ $s['passing_grade'] ?? 70 }}%
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($isPassed)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-solid fa-trophy mr-1 text-amber-500"></i> LULUS
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-black bg-rose-100 text-rose-800 border border-rose-300">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> BELUM LULUS
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                {{ $s['violation_count'] ?? 0 }}x
                            </td>
                            <td class="py-3.5 px-4 text-right text-xs text-slate-500">
                                {{ substr($s['finished_at'] ?? $s['started_at'], 0, 16) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-xs text-slate-400">
                                Belum ada data rekapitulasi nilai untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
