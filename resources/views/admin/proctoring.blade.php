@extends('layouts.admin')

@section('title', 'Monitoring Sesi Ujian - Bimbel Cemerlang')
@section('header_title', 'Pusat Monitoring Sesi Ujian')

@section('content')
<div class="space-y-8">

    <!-- Header Stats Bar -->
    <div class="bg-gradient-to-r from-[#132347] to-[#1F366A] text-white p-6 rounded-3xl shadow-md border-4 border-[#FFBE2A] flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-black uppercase text-emerald-400 tracking-wider">Live System Active</span>
            </div>
            <h2 class="text-xl font-black font-serif">Monitoring Sesi Ujian Real-Time</h2>
            <p class="text-xs text-slate-300 mt-1">
                Pantau siswa yang sedang mengerjakan ujian secara langsung dan rekap riwayat sesi pengerjaan.
            </p>
        </div>

        <div class="flex items-center space-x-3 bg-white/10 px-4 py-2.5 rounded-2xl border border-white/20 text-xs">
            <span>Siswa Sedang Ujian: <b id="live-active-count" class="text-[#FFBE2A] text-base">{{ count($activeSessions) }}</b></span>
            <span class="text-slate-400">|</span>
            <span class="text-emerald-300"><i class="fa-solid fa-arrows-rotate fa-spin mr-1"></i> Auto-Sync 10s</span>
        </div>
    </div>

    <!-- Live Active Sessions Grid -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-black font-serif text-[#1F366A] flex items-center">
                <i class="fa-solid fa-desktop mr-2 text-[#FFBE2A]"></i> Peserta Ujian Sedang Aktif
            </h3>
            <span class="text-xs font-bold text-slate-500">{{ count($activeSessions) }} Sesi Berjalan</span>
        </div>

        <div id="proctoring-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($activeSessions as $sess)
                <div class="bg-white rounded-3xl p-5 shadow-sm border-2 border-slate-200 hover:border-[#FFBE2A] transition">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[10px] font-black uppercase text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                                Sedang Mengerjakan
                            </span>
                        </div>
                        @if(($sess['violation_count'] ?? 0) > 0)
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">
                                {{ $sess['violation_count'] }}x Peringatan
                            </span>
                        @else
                            <span class="text-[10px] font-bold text-slate-400">0 Peringatan</span>
                        @endif
                    </div>

                    <div class="space-y-1 mb-4">
                        <h4 class="font-extrabold text-[#1F366A] text-sm truncate">{{ $sess['student_name'] }}</h4>
                        <p class="text-xs text-slate-500 truncate">{{ $sess['student_email'] }}</p>
                        <div class="text-[11px] font-semibold text-slate-700 pt-1">
                            Paket: <span class="font-bold text-[#1F366A]">{{ $sess['package_title'] ?? 'Try Out' }}</span>
                        </div>
                    </div>

                    <div class="bg-[#FFF7E9] p-3 rounded-2xl border border-amber-200/60 text-[11px] flex items-center justify-between">
                        <span class="text-slate-600">Waktu Mulai:</span>
                        <span class="font-mono font-bold text-[#1F366A]">{{ substr($sess['started_at'], 11, 8) }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white rounded-3xl p-8 text-center border-2 border-dashed border-slate-300">
                    <i class="fa-solid fa-graduation-cap text-4xl text-slate-300 mb-2"></i>
                    <h4 class="font-bold text-slate-700 text-sm">Tidak Ada Siswa yang Sedang Ujian</h4>
                    <p class="text-xs text-slate-400">Ketika peserta memulai pengerjaan try out, sesi aktif akan langsung muncul di sini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Security & Violation Logs Table -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-black font-serif text-[#1F366A] flex items-center">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 mr-2"></i> Riwayat Log Pelanggaran & Pindah Tab
            </h3>
            <span class="text-xs font-bold text-slate-500">{{ $violations->count() }} Kejadian Tercatat</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 text-left">Waktu Kejadian</th>
                        <th class="py-3 px-4 text-left">Nama Siswa</th>
                        <th class="py-3 px-4 text-left">Paket Try Out</th>
                        <th class="py-3 px-4 text-left">Jenis Indikasi</th>
                        <th class="py-3 px-4 text-center">Peringatan Ke</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="violations-tbody" class="divide-y divide-slate-100 font-medium">
                    @forelse($violations as $v)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono text-slate-500">{{ substr($v['timestamp'], 11, 8) }}</td>
                            <td class="py-3 px-4 font-bold text-[#1F366A]">{{ $v['student_name'] }}</td>
                            <td class="py-3 px-4 text-slate-700 truncate max-w-[150px]">{{ $v['package_title'] ?? 'Try Out' }}</td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                    {{ $v['violation_message'] ?? $v['violation_type'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">
                                {{ $v['violation_count'] }}x
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if(!empty($v['is_blocked']))
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-600 text-white">Diblokir</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Tercatat</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada catatan peringatan yang terdeteksi. Sesi pengerjaan berjalan lancar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Polling live session count every 10 seconds
    setInterval(async () => {
        try {
            const res = await axios.get('/admin/proctoring/poll');
            const data = res.data;
            const proctorings = data.proctorings || [];
            document.getElementById('live-active-count').innerText = proctorings.length;
        } catch (e) {}
    }, 10000);
</script>
@endpush
@endsection
