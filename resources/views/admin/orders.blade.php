@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran - Bimbel Cemerlang')
@section('header_title', 'Verifikasi & Aktivasi Akses Siswa')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-black font-serif text-[#1F366A]">Persetujuan Pembayaran Try Out</h3>
            <p class="text-xs text-slate-500">Verifikasi bukti transfer siswa untuk mengaktifkan akses pengerjaan paket ujian.</p>
        </div>

        <div class="flex items-center space-x-2">
            <span class="text-xs font-bold text-slate-600">Filter:</span>
            <a href="{{ url('/admin/orders?status=all') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $statusFilter === 'all' ? 'bg-[#1F366A] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Semua
            </a>
            <a href="{{ url('/admin/orders?status=pending') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $statusFilter === 'pending' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                Menunggu Verifikasi
            </a>
            <a href="{{ url('/admin/orders?status=approved') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                Disetujui
            </a>
            <a href="{{ url('/admin/orders?status=rejected') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $statusFilter === 'rejected' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                Ditolak
            </a>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-[#1F366A] text-white text-xs uppercase font-extrabold tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">No. Transaksi</th>
                        <th class="py-3.5 px-4">Siswa</th>
                        <th class="py-3.5 px-4">Paket Try Out</th>
                        <th class="py-3.5 px-4">Nominal & Metode</th>
                        <th class="py-3.5 px-4 text-center">Bukti Bayar</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($orders as $ord)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-4 font-mono font-bold text-xs text-slate-700">
                                {{ $ord['id'] }}
                                <span class="block text-[10px] text-slate-400 font-normal font-sans">{{ substr($ord['created_at'], 0, 16) }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-extrabold text-[#1F366A] block">{{ $ord['student_name'] }}</span>
                                <span class="text-xs text-slate-500">{{ $ord['student_email'] }}</span>
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-800">
                                {{ $ord['package_title'] }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-black text-sm text-[#1F366A] block">
                                    {{ $ord['amount'] == 0 ? 'GRATIS' : 'Rp ' . number_format($ord['amount'], 0, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-slate-500">{{ $ord['payment_method'] ?? 'Transfer' }}</span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if(!empty($ord['payment_proof']))
                                    <button onclick="viewPaymentProof('{{ asset($ord['payment_proof']) }}')" class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 hover:bg-blue-100 text-xs font-bold border border-blue-200">
                                        <i class="fa-solid fa-image"></i>
                                        <span>Lihat Bukti</span>
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 italic">Tanpa File Bukti</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($ord['status'] === 'approved')
                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check mr-1"></i> Aktif
                                    </span>
                                @elseif($ord['status'] === 'pending')
                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-clock mr-1"></i> Pending
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-xmark mr-1"></i> Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right whitespace-nowrap space-x-1.5">
                                @if($ord['status'] === 'pending')
                                    <form method="POST" action="{{ url('/admin/orders/' . $ord['id'] . '/approve') }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black shadow-sm transition">
                                            <i class="fa-solid fa-check mr-1"></i> Setujui
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ url('/admin/orders/' . $ord['id'] . '/reject') }}" class="inline" onsubmit="return confirm('Tolak pembayaran ini?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-black shadow-sm transition">
                                            <i class="fa-solid fa-xmark mr-1"></i> Tolak
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">Terverifikasi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-slate-400">
                                Tidak ada data transaksi pembayaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Zoom Bukti Transfer -->
<div id="modal-proof" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 text-center relative shadow-2xl">
        <button onclick="closePaymentProof()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <h4 class="text-base font-black text-[#1F366A] mb-4 font-serif">Foto Bukti Transfer Pembayaran</h4>
        <div class="rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 max-h-[70vh] flex items-center justify-center">
            <img id="img-proof-preview" src="" alt="Bukti Transfer" class="max-w-full max-h-[65vh] object-contain">
        </div>
        <div class="mt-4">
            <button onclick="closePaymentProof()" class="btn-cemerlang-navy px-6 py-2 rounded-xl text-xs font-bold">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function viewPaymentProof(url) {
        document.getElementById('img-proof-preview').src = url;
        document.getElementById('modal-proof').classList.remove('hidden');
    }
    function closePaymentProof() {
        document.getElementById('modal-proof').classList.add('hidden');
    }
</script>
@endpush
@endsection
