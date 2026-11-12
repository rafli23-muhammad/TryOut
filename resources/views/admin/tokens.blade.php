@extends('layouts.admin')

@section('title', 'Kelola Token Siswa Bimbel - Bimbel Cemerlang')
@section('header_title', 'Kelola Token Siswa Bimbel')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Token</p>
                <h3 class="text-2xl font-black text-[#1F366A] mt-1">{{ $totalTokens }}</h3>
                <span class="text-[11px] text-slate-400">Kode token dibuat</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-key"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Token Aktif</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $activeTokens }}</h3>
                <span class="text-[11px] text-emerald-600 font-semibold">Siap digunakan siswa</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Klaim Siswa</p>
                <h3 class="text-2xl font-black text-[#1F366A] mt-1">{{ $totalClaims }}</h3>
                <span class="text-[11px] text-slate-400">Kali token digunakan</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#1F366A] flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Siswa Bimbel Aktif</p>
                <h3 class="text-2xl font-black text-[#1F366A] mt-1">{{ $bimbelStudentsCount }}</h3>
                <span class="text-[11px] text-[#FFBE2A] font-extrabold">Akses Try Out 100% Gratis</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-extrabold text-[#1F366A]">Daftar Token Bimbel Resmi</h3>
            <p class="text-xs text-slate-500 mt-0.5">Siswa yang mendaftar atau mengklaim token ini otomatis mendapatkan seluruh paket Try Out gratis.</p>
        </div>
        <button type="button" onclick="openCreateTokenModal()" class="btn-cemerlang-yellow px-5 py-2.5 rounded-xl text-xs font-black shadow flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Buat Token Baru</span>
        </button>
    </div>

    <!-- Table Tokens -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-[#FFF7E9] text-[#1F366A] text-[11px] uppercase font-extrabold border-b border-amber-200">
                    <tr>
                        <th class="py-4 px-5">Kode Token</th>
                        <th class="py-4 px-4">Nama Program / Label</th>
                        <th class="py-4 px-4 text-center">Penggunaan / Kuota</th>
                        <th class="py-4 px-4 text-center">Kedaluwarsa</th>
                        <th class="py-4 px-4 text-center">Status</th>
                        <th class="py-4 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($tokens as $tok)
                        @php
                            $max = (int) ($tok['max_uses'] ?? 0);
                            $used = (int) ($tok['used_count'] ?? 0);
                            $isExpired = false;
                            if (!empty($tok['expires_at'])) {
                                $expTime = strtotime($tok['expires_at'] . ' 23:59:59');
                                $isExpired = ($expTime && time() > $expTime);
                            }
                            $isActive = ($tok['is_active'] ?? true) && !$isExpired;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Kode Token -->
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-black text-sm bg-slate-100 text-[#1F366A] px-3 py-1 rounded-lg border border-slate-200 select-all tracking-wider">
                                        {{ $tok['code'] }}
                                    </span>
                                    <button type="button" onclick="copyToken('{{ $tok['code'] }}')" class="text-slate-400 hover:text-[#1F366A] text-xs p-1" title="Salin Kode Token">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                </div>
                            </td>

                            <!-- Label & Catatan -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-[#1F366A] text-xs">{{ $tok['label'] }}</div>
                                @if(!empty($tok['notes']))
                                    <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">{{ $tok['notes'] }}</div>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-0.5">Dibuat: {{ date('d M Y', strtotime($tok['created_at'])) }}</div>
                            </td>

                            <!-- Kuota / Penggunaan -->
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 bg-slate-100 px-3 py-1 rounded-full text-xs font-bold text-slate-700">
                                    <span>{{ $used }}</span>
                                    <span class="text-slate-400">/</span>
                                    <span>{{ $max > 0 ? $max : '∞' }} Siswa</span>
                                </div>
                                @if(!empty($tok['claimed_users']))
                                    <button type="button" onclick='showClaimedUsers(@json($tok))' class="block mx-auto text-[10px] text-[#1E75FF] hover:underline font-bold mt-1">
                                        Lihat {{ count($tok['claimed_users']) }} Siswa
                                    </button>
                                @endif
                            </td>

                            <!-- Masa Berlaku -->
                            <td class="py-4 px-4 text-center text-xs">
                                @if(!empty($tok['expires_at']))
                                    <span class="{{ $isExpired ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                                        {{ date('d M Y', strtotime($tok['expires_at'])) }}
                                    </span>
                                    @if($isExpired)
                                        <span class="block text-[10px] text-rose-500 font-extrabold uppercase">Kedaluwarsa</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Tanpa Batas Waktu</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4 text-center">
                                @if($isActive)
                                    <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2.5 py-1 rounded-full border border-emerald-200">
                                        <i class="fa-solid fa-circle-check"></i> Aktif
                                    </span>
                                @elseif($isExpired)
                                    <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-1 rounded-full border border-amber-200">
                                        <i class="fa-solid fa-clock"></i> Kedaluwarsa
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 text-[10px] font-extrabold px-2.5 py-1 rounded-full border border-slate-200">
                                        <i class="fa-solid fa-pause"></i> Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1.5">
                                    <form method="POST" action="{{ url('/admin/tokens/' . $tok['id'] . '/toggle') }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg {{ ($tok['is_active'] ?? true) ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }} transition text-xs" title="{{ ($tok['is_active'] ?? true) ? 'Nonaktifkan Token' : 'Aktifkan Token' }}">
                                            <i class="fa-solid {{ ($tok['is_active'] ?? true) ? 'fa-pause' : 'fa-play' }}"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ url('/admin/tokens/' . $tok['id'] . '/delete') }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus token {{ $tok['code'] }}?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition text-xs" title="Hapus Token">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-key text-3xl mb-2 text-slate-300"></i>
                                <p class="text-sm font-bold text-slate-600">Belum ada token siswa bimbel yang dibuat</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "Buat Token Baru" di atas untuk membuat kode akses gratis.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Buat Token Baru -->
<div id="modal-create-token" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border-2 border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-[#1F366A]">Buat Token Siswa Bimbel</h3>
                    <p class="text-xs text-slate-500">Siswa yang memiliki token ini bebas akses semua paket</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateTokenModal()" class="text-slate-400 hover:text-slate-600 text-lg p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ url('/admin/tokens') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Kode Token (Opsional)</label>
                <div class="flex items-center space-x-2">
                    <input type="text" name="code" id="input-token-code" placeholder="Contoh: BIMBEL-INTENSIF2026" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] text-sm font-mono uppercase font-bold">
                    <button type="button" onclick="generateRandomToken()" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-dice"></i>
                        <span>Acak</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika ingin sistem men-generate otomatis.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Nama Program / Label Kelas *</label>
                <input type="text" name="label" required placeholder="Contoh: Siswa Reguler Kelas 12 IPA 2026" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] text-sm font-medium">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Batas Kuota Siswa</label>
                    <input type="number" name="max_uses" min="0" placeholder="0 = Tanpa Batas" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] text-sm font-medium">
                    <p class="text-[10px] text-slate-400 mt-0.5">Isi 0 untuk kuota tak terbatas.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Berlaku Sampai (Opsional)</label>
                    <input type="date" name="expires_at" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] text-sm font-medium">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Catatan / Keterangan</label>
                <textarea name="notes" rows="2" placeholder="Catatan internal pengurus bimbel (opsional)..." class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] text-xs font-medium"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeCreateTokenModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit" class="btn-cemerlang-yellow px-5 py-2.5 rounded-xl text-xs font-black shadow transition">
                    Simpan & Terbitkan Token
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Riwayat Klaim Siswa -->
<div id="modal-claimed-users" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border-2 border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <div>
                <h3 class="text-base font-black text-[#1F366A]">Daftar Siswa Pengklaim Token</h3>
                <p id="claimed-token-title" class="text-xs text-slate-500 font-mono font-bold"></p>
            </div>
            <button type="button" onclick="closeClaimedUsersModal()" class="text-slate-400 hover:text-slate-600 text-lg p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="claimed-users-list" class="max-h-72 overflow-y-auto space-y-2 text-xs">
            <!-- Dynamic items -->
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100 text-right">
            <button type="button" onclick="closeClaimedUsersModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openCreateTokenModal() {
        document.getElementById('modal-create-token').classList.remove('hidden');
    }
    function closeCreateTokenModal() {
        document.getElementById('modal-create-token').classList.add('hidden');
    }

    function generateRandomToken() {
        const rand = Math.random().toString(36).substring(2, 8).toUpperCase();
        document.getElementById('input-token-code').value = 'BIMBEL-' + rand;
    }

    function copyToken(code) {
        navigator.clipboard.writeText(code).then(() => {
            alert('Kode token [' + code + '] berhasil disalin!');
        });
    }

    function showClaimedUsers(tok) {
        document.getElementById('claimed-token-title').innerText = tok.code + ' (' + tok.label + ')';
        const container = document.getElementById('claimed-users-list');
        container.innerHTML = '';

        const users = tok.claimed_users || [];
        if (users.length === 0) {
            container.innerHTML = '<div class="text-center py-6 text-slate-400">Belum ada siswa yang mengklaim token ini.</div>';
        } else {
            users.forEach((u, i) => {
                const el = document.createElement('div');
                el.className = 'flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200';
                el.innerHTML = `
                    <div class="flex items-center space-x-2.5">
                        <span class="w-6 h-6 rounded-full bg-[#1F366A] text-[#FFBE2A] flex items-center justify-center font-bold text-[10px]">${i + 1}</span>
                        <div>
                            <span class="font-bold text-[#1F366A] block">${u.name || 'Siswa'}</span>
                            <span class="text-[10px] text-slate-400">ID: ${u.user_id}</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-slate-500">${u.claimed_at || '-'}</span>
                `;
                container.appendChild(el);
            });
        }

        document.getElementById('modal-claimed-users').classList.remove('hidden');
    }

    function closeClaimedUsersModal() {
        document.getElementById('modal-claimed-users').classList.add('hidden');
    }
</script>
@endpush
@endsection
