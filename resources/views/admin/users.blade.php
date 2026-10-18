@extends('layouts.admin')

@section('title', 'Kelola Akun & Pengguna - Bimbel Cemerlang')
@section('header_title', 'Kelola Akun & Pengguna')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Akun</p>
                <h3 class="text-2xl font-black text-[#1F366A] mt-1">{{ $totalUsers }}</h3>
                <span class="text-[11px] text-slate-400">Semua akun terdaftar</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#1F366A] flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Siswa</p>
                <h3 class="text-2xl font-black text-amber-600 mt-1">{{ $totalStudents }}</h3>
                <span class="text-[11px] text-emerald-600 font-semibold">{{ $activeStudents }} Siswa Aktif</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Siswa Aktif</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $activeStudents }}</h3>
                <span class="text-[11px] text-slate-400">Siap ikuti try out</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Administrator</p>
                <h3 class="text-2xl font-black text-[#1F366A] mt-1">{{ $totalAdmins }}</h3>
                <span class="text-[11px] text-slate-400">Pengelola sistem</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-[#1F366A] flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-user-shield"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Action Bar -->
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            
            <!-- Search & Filters Form -->
            <form method="GET" action="{{ url('/admin/users') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <!-- Search Input -->
                <div class="sm:col-span-6 relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, email, username, telepon, sekolah..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] transition">
                </div>

                <!-- Role Filter -->
                <div class="sm:col-span-3">
                    <select name="role" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                        <option value="all" {{ $roleFilter === 'all' ? 'selected' : '' }}>Semua Peran</option>
                        <option value="student" {{ $roleFilter === 'student' ? 'selected' : '' }}>Hanya Siswa</option>
                        <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Hanya Admin</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="sm:col-span-3 flex items-center gap-2">
                    <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Status Aktif</option>
                        <option value="suspended" {{ $statusFilter === 'suspended' ? 'selected' : '' }}>Status Ditangguhkan</option>
                    </select>

                    <button type="submit" class="bg-[#1F366A] text-white px-3.5 py-2.5 rounded-xl text-xs font-bold hover:bg-[#152449] transition" title="Terapkan Filter">
                        <i class="fa-solid fa-filter"></i>
                    </button>

                    @if($q !== '' || $roleFilter !== 'all' || $statusFilter !== 'all')
                        <a href="{{ url('/admin/users') }}" class="bg-slate-100 text-slate-600 hover:bg-slate-200 px-3 py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center" title="Reset Filter">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- Add User Button -->
            <div class="flex items-center justify-end shrink-0">
                <button type="button" onclick="openCreateUserModal()" class="btn-cemerlang-yellow text-xs font-black px-5 py-3 rounded-xl shadow flex items-center whitespace-nowrap">
                    <i class="fa-solid fa-user-plus mr-2"></i> Tambah Akun Baru
                </button>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-[#1F366A] text-sm">Daftar Akun Terdaftar</h3>
                <p class="text-xs text-slate-400">Total ditemukan: {{ count($users) }} akun</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-[#1F366A] text-white text-[11px] uppercase font-extrabold tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5">Pengguna</th>
                        <th class="py-3.5 px-4">Kontak & Sekolah</th>
                        <th class="py-3.5 px-4 text-center">Peran</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aktivitas Try Out</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($users as $u)
                        @php
                            $isCurrentAdmin = session('admin_id') === $u['id'];
                            $isSuspended = ($u['status'] ?? 'active') === 'suspended';
                            $isAdmin = ($u['role'] ?? '') === 'admin';
                            $initials = strtoupper(substr($u['name'] ?? 'U', 0, 2));
                        @endphp
                        <tr class="hover:bg-slate-50 transition {{ $isSuspended ? 'bg-rose-50/30' : '' }}">
                            <!-- Pengguna / Avatar / Nama -->
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3">
                                    @if(!empty($u['avatar']))
                                        <img src="{{ $u['avatar'] }}" alt="{{ $u['name'] }}" class="w-10 h-10 rounded-full object-cover border-2 border-slate-200">
                                    @else
                                        <div class="w-10 h-10 rounded-full {{ $isAdmin ? 'bg-[#1F366A] text-[#FFBE2A]' : 'bg-[#FFBE2A] text-[#1F366A]' }} flex items-center justify-center font-black text-xs shadow-inner shrink-0">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="flex items-center space-x-1.5">
                                            <span class="font-extrabold text-[#1F366A] text-sm">{{ $u['name'] }}</span>
                                            @if($isCurrentAdmin)
                                                <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-1.5 py-0.5 rounded-full">Anda</span>
                                            @endif
                                            @if(!empty($u['google_id']))
                                                <span class="bg-red-50 text-red-600 border border-red-200 text-[10px] font-bold px-1.5 py-0.5 rounded-full inline-flex items-center gap-1" title="Terhubung Akun Google SSO">
                                                    <i class="fa-brands fa-google text-[9px]"></i> Google
                                                </span>
                                            @endif
                                            @if(!empty($u['facebook_id']))
                                                <span class="bg-blue-50 text-blue-600 border border-blue-200 text-[10px] font-bold px-1.5 py-0.5 rounded-full inline-flex items-center gap-1" title="Terhubung Akun Facebook SSO">
                                                    <i class="fa-brands fa-facebook text-[9px]"></i> Facebook
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[11px] text-slate-500 font-mono">@<span>{{ $u['username'] ?? explode('@', $u['email'])[0] }}</span></span>
                                            <span class="text-[10px] text-slate-400">• Terdaftar: {{ !empty($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Kontak & Sekolah -->
                            <td class="py-4 px-4 text-xs">
                                <div class="space-y-1">
                                    <div class="flex items-center text-slate-600">
                                        <i class="fa-regular fa-envelope w-4 text-slate-400 mr-1.5"></i>
                                        <span class="font-mono text-[11px]">{{ $u['email'] }}</span>
                                    </div>
                                    <div class="flex items-center text-slate-600">
                                        <i class="fa-brands fa-whatsapp w-4 text-emerald-500 mr-1.5"></i>
                                        @if(!empty($u['phone']) && $u['phone'] !== '-')
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $u['phone']) }}" target="_blank" class="hover:text-emerald-600 underline font-mono text-[11px]">
                                                {{ $u['phone'] }}
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-[11px]">-</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center text-slate-600">
                                        <i class="fa-solid fa-school w-4 text-slate-400 mr-1.5"></i>
                                        <span class="truncate max-w-[180px]" title="{{ $u['school'] ?? '-' }}">{{ $u['school'] ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Peran / Role -->
                            <td class="py-4 px-4 text-center">
                                @if($isAdmin)
                                    <span class="inline-flex items-center gap-1.5 bg-[#1F366A] text-[#FFBE2A] text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                        <i class="fa-solid fa-shield-halved"></i> Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-[#1F366A] border border-blue-200 text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <i class="fa-solid fa-user-graduate"></i> Siswa
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4 text-center">
                                @if($isSuspended)
                                    <span class="inline-flex items-center gap-1 bg-rose-100 text-rose-700 text-[10px] font-extrabold px-2.5 py-1 rounded-full border border-rose-200">
                                        <i class="fa-solid fa-ban"></i> Ditangguhkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-2.5 py-1 rounded-full border border-emerald-200">
                                        <i class="fa-solid fa-circle-check"></i> Aktif
                                    </span>
                                @endif
                            </td>

                            <!-- Aktivitas Try Out -->
                            <td class="py-4 px-4 text-center">
                                @if(!$isAdmin)
                                    <div class="inline-flex items-center gap-3 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl text-xs">
                                        <div title="Paket yang diikuti / disetujui">
                                            <span class="font-extrabold text-[#1F366A]">{{ $u['enrolled_count'] ?? 0 }}</span>
                                            <span class="text-[10px] text-slate-400 block">Paket</span>
                                        </div>
                                        <div class="h-6 w-px bg-slate-200"></div>
                                        <div title="Ujian yang telah diselesaikan">
                                            <span class="font-extrabold text-emerald-700">{{ $u['exams_count'] ?? 0 }}</span>
                                            <span class="text-[10px] text-slate-400 block">Ujian</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Hak Akses Admin</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1.5">
                                    <!-- Toggle Status Button -->
                                    @if(!$isCurrentAdmin)
                                        <form method="POST" action="{{ url('/admin/users/' . $u['id'] . '/toggle-status') }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $isSuspended ? 'mengaktifkan kembali' : 'menonaktifkan / menangguhkan' }} akun {{ addslashes($u['name']) }}?')">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg {{ $isSuspended ? 'text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50' : 'text-amber-600 hover:text-amber-800 hover:bg-amber-50' }} transition text-xs" title="{{ $isSuspended ? 'Aktifkan Akun' : 'Tangguhkan / Blokir Sementara' }}">
                                                <i class="fa-solid {{ $isSuspended ? 'fa-user-check' : 'fa-user-slash' }}"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Edit User Button -->
                                    <button type="button" onclick="openEditUserModal({{ json_encode($u) }})" class="p-1.5 rounded-lg text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition text-xs" title="Edit Data Akun">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- Reset Password Button -->
                                    <button type="button" onclick="openResetPasswordModal('{{ $u['id'] }}', '{{ addslashes($u['name']) }}')" class="p-1.5 rounded-lg text-purple-600 hover:text-purple-800 hover:bg-purple-50 transition text-xs" title="Reset Password">
                                        <i class="fa-solid fa-key"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    @if(!$isCurrentAdmin)
                                        <form method="POST" action="{{ url('/admin/users/' . $u['id'] . '/delete') }}" class="inline" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus akun {{ addslashes($u['name']) }}? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:text-rose-800 hover:bg-rose-50 transition text-xs" title="Hapus Akun">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <i class="fa-solid fa-user-xmark text-4xl text-slate-300"></i>
                                    <p class="font-bold text-slate-600 text-sm">Tidak ada pengguna yang cocok</p>
                                    <p class="text-xs text-slate-400">Coba ubah kata kunci pencarian atau sesuaikan filter peran dan status di atas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Tambah Pengguna Baru -->
<div id="modal-create-user" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100">
        <div class="bg-[#1F366A] p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-[#FFBE2A] text-[#1F366A] flex items-center justify-center font-black">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                </div>
                <div>
                    <h3 class="font-bold font-serif text-base">Tambah Akun Pengguna Baru</h3>
                    <p class="text-[11px] text-blue-200">Daftarkan akun siswa atau administrator baru ke sistem</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateUserModal()" class="text-white/70 hover:text-white text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ url('/admin/users') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required placeholder="budi@example.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">No. WhatsApp / HP <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" required placeholder="08123456789" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Peran Akun <span class="text-rose-500">*</span></label>
                    <select name="role" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                        <option value="student" selected>Siswa (Peserta Ujian)</option>
                        <option value="admin">Administrator (Pengelola)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Asal Sekolah</label>
                    <input type="text" name="school" placeholder="Contoh: SMAN 1 Jakarta" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Password Masuk <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required minlength="4" placeholder="Minimal 4 karakter" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateUserModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="btn-cemerlang-yellow px-6 py-2.5 rounded-xl text-xs font-black shadow">
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Data Pengguna -->
<div id="modal-edit-user" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100">
        <div class="bg-[#1F366A] p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-400 text-[#1F366A] flex items-center justify-center font-black">
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                </div>
                <div>
                    <h3 class="font-bold font-serif text-base">Edit Data Akun Pengguna</h3>
                    <p class="text-[11px] text-blue-200">Perbarui profil, peranan, atau status akun pengguna</p>
                </div>
            </div>
            <button type="button" onclick="closeEditUserModal()" class="text-white/70 hover:text-white text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form-edit-user" method="POST" action="" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="edit-user-name" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="edit-user-email" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">No. WhatsApp / HP <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" id="edit-user-phone" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Peran Akun <span class="text-rose-500">*</span></label>
                    <select name="role" id="edit-user-role" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                        <option value="student">Siswa (Peserta Ujian)</option>
                        <option value="admin">Administrator (Pengelola)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Status Akun <span class="text-rose-500">*</span></label>
                    <select name="status" id="edit-user-status" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                        <option value="active">Aktif (Bisa Login & Ujian)</option>
                        <option value="suspended">Ditangguhkan (Diblokir Sementara)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Asal Sekolah</label>
                <input type="text" name="school" id="edit-user-school" placeholder="Contoh: SMAN 1 Jakarta" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Password Baru (Opsional)</label>
                <input type="password" name="password" minlength="4" placeholder="Kosongkan jika tidak ingin mengubah password" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                <p class="text-[11px] text-slate-400 mt-1">Hanya isi kolom ini jika ingin sekaligus mengganti kata sandi pengguna.</p>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="btn-cemerlang-yellow px-6 py-2.5 rounded-xl text-xs font-black shadow">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reset Password -->
<div id="modal-reset-password" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100">
        <div class="bg-[#1F366A] p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-purple-400 text-[#1F366A] flex items-center justify-center font-black">
                    <i class="fa-solid fa-key text-xs"></i>
                </div>
                <div>
                    <h3 class="font-bold font-serif text-base">Reset Password Akun</h3>
                    <p class="text-[11px] text-blue-200" id="reset-modal-subheading">Atur kata sandi baru untuk pengguna</p>
                </div>
            </div>
            <button type="button" onclick="closeResetPasswordModal()" class="text-white/70 hover:text-white text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form-reset-password" method="POST" action="" class="p-6 space-y-4">
            @csrf

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800">
                <p>Anda akan mengatur ulang password untuk: <strong id="reset-target-name" class="font-bold text-[#1F366A]"></strong></p>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Password Baru <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required minlength="4" placeholder="Ketik kata sandi baru (min. 4 karakter)" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="bg-[#1F366A] hover:bg-[#152449] text-white px-6 py-2.5 rounded-xl text-xs font-black shadow transition">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreateUserModal() {
        document.getElementById('modal-create-user').classList.remove('hidden');
    }

    function closeCreateUserModal() {
        document.getElementById('modal-create-user').classList.add('hidden');
    }

    function openEditUserModal(u) {
        const form = document.getElementById('form-edit-user');
        form.action = '/admin/users/' + u.id + '/update';

        document.getElementById('edit-user-name').value = u.name || '';
        document.getElementById('edit-user-email').value = u.email || '';
        document.getElementById('edit-user-phone').value = u.phone || '';
        document.getElementById('edit-user-role').value = u.role || 'student';
        document.getElementById('edit-user-status').value = u.status || 'active';
        document.getElementById('edit-user-school').value = (u.school && u.school !== '-') ? u.school : '';

        document.getElementById('modal-edit-user').classList.remove('hidden');
    }

    function closeEditUserModal() {
        document.getElementById('modal-edit-user').classList.add('hidden');
    }

    function openResetPasswordModal(id, name) {
        const form = document.getElementById('form-reset-password');
        form.action = '/admin/users/' + id + '/reset-password';
        document.getElementById('reset-target-name').innerText = name;
        document.getElementById('reset-modal-subheading').innerText = 'Atur kata sandi baru untuk ' + name;

        document.getElementById('modal-reset-password').classList.remove('hidden');
    }

    function closeResetPasswordModal() {
        document.getElementById('modal-reset-password').classList.add('hidden');
    }

    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateUserModal();
            closeEditUserModal();
            closeResetPasswordModal();
        }
    });
</script>
@endpush
@endsection
