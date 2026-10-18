@extends('layouts.admin')

@section('title', 'Profil & Keamanan Akun Admin - Bimbel Cemerlang')
@section('header_title', 'Profil & Akun Administrator')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-[#1F366A] to-[#152449] rounded-3xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 opacity-10 text-9xl">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-2xl bg-[#FFBE2A] text-[#1F366A] flex items-center justify-center font-black text-2xl shadow-lg shrink-0">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <div>
                    <span class="bg-white/10 text-[#FFBE2A] border border-[#FFBE2A]/30 text-[10px] font-extrabold px-3 py-0.5 rounded-full uppercase tracking-wider">
                        Hak Akses Administrator
                    </span>
                    <h2 class="text-xl sm:text-2xl font-black font-serif mt-1">{{ $admin['name'] ?? 'Administrator' }}</h2>
                    <p class="text-xs text-blue-200 mt-0.5">Kelola informasi profil, alamat Gmail login, dan kata sandi admin di sini.</p>
                </div>
            </div>
            <a href="{{ url('/admin/users') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition border border-white/20 flex items-center shrink-0">
                <i class="fa-solid fa-users-gear mr-2 text-[#FFBE2A]"></i> Kelola Semua Akun
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form Edit Profil & Password (2 Columns) -->
        <div class="lg:col-span-2 space-y-6">
            <form method="POST" action="{{ url('/admin/profile') }}" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                @csrf

                <!-- Section 1: Profil & Gmail -->
                <div class="p-6 sm:p-7 border-b border-slate-100">
                    <div class="flex items-center space-x-3 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#1F366A] flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-address-card"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-[#1F366A]">Data Akun & Kontak</h3>
                            <p class="text-xs text-slate-500">Ubah nama, username, dan akun Gmail yang digunakan untuk masuk admin.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Nama Lengkap Administrator <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                                <input type="text" name="name" required value="{{ old('name', $admin['name'] ?? '') }}" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Username Login <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <i class="fa-solid fa-at"></i>
                                    </span>
                                    <input type="text" name="username" required value="{{ old('username', $admin['username'] ?? '') }}" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">Bisa digunakan saat login</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">
                                    Akun Email / Gmail <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-red-500">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <input type="email" name="email" required value="{{ old('email', $admin['email'] ?? '') }}" placeholder="admin@gmail.com" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                                </div>
                                <span class="text-[10px] text-emerald-600 font-semibold mt-1 block">
                                    <i class="fa-solid fa-circle-check mr-0.5"></i> Bisa dipakai login langsung
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">No. WhatsApp / HP</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-emerald-500">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </span>
                                <input type="text" name="phone" value="{{ old('phone', ($admin['phone'] ?? '-') !== '-' ? ($admin['phone'] ?? '') : '') }}" placeholder="08123456789" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Kata Sandi / Password -->
                <div class="p-6 sm:p-7 bg-slate-50/50">
                    <div class="flex items-center space-x-3 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-[#1F366A]">Ubah Kata Sandi (Password)</h3>
                            <p class="text-xs text-slate-500">Biarkan kolom password di bawah ini kosong jika tidak ingin mengubahnya.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Kata Sandi Saat Ini</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <i class="fa-solid fa-key"></i>
                                </span>
                                <input type="password" name="current_password" placeholder="Masukkan password saat ini (untuk verifikasi ubah pw)" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Kata Sandi Baru</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </span>
                                    <input type="password" name="new_password" minlength="4" placeholder="Minimal 4 karakter" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Ulangi Kata Sandi Baru</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <i class="fa-solid fa-check-double"></i>
                                    </span>
                                    <input type="password" name="new_password_confirmation" minlength="4" placeholder="Ketik ulang password baru" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="p-5 sm:p-6 bg-white border-t border-slate-200 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        <i class="fa-solid fa-circle-info mr-1 text-[#FFBE2A]"></i> Data session login akan otomatis diperbarui.
                    </span>
                    <button type="submit" class="btn-cemerlang-yellow text-xs font-black px-6 py-3 rounded-xl shadow-md hover:shadow-lg transition flex items-center">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar Info & Credentials Card (1 Column) -->
        <div class="space-y-6">
            <!-- Active Credentials Card -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-4">
                <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-base">
                        <i class="fa-solid fa-id-badge"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-[#1F366A] text-sm">Kredensial Saat Ini</h4>
                        <span class="text-[11px] text-slate-400">Informasi aktif akun Anda</span>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block mb-1">Email / Gmail Aktif</span>
                        <div class="flex items-center space-x-2 text-slate-800 font-mono font-bold">
                            <i class="fa-solid fa-envelope text-red-500"></i>
                            <span class="break-all">{{ $admin['email'] ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block mb-1">Username Login</span>
                        <div class="flex items-center space-x-2 text-slate-800 font-mono font-bold">
                            <i class="fa-solid fa-user-shield text-[#1F366A]"></i>
                            <span>{{ $admin['username'] ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block mb-1">Status Akun</span>
                        <span class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-800 font-black text-[10px] px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> Aktif & Terlindungi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Login Tips Card -->
            <div class="bg-amber-50/70 border border-amber-200/80 rounded-3xl p-5 sm:p-6 space-y-3.5 shadow-sm">
                <div class="flex items-center space-x-2.5 pb-2.5 border-b border-amber-200/60">
                    <div class="w-8 h-8 rounded-xl bg-amber-400/20 text-amber-600 flex items-center justify-center text-sm shrink-0">
                        <i class="fa-solid fa-lightbulb"></i>
                    </div>
                    <span class="font-extrabold text-xs text-amber-950 uppercase tracking-wider">Tips Akses & Keamanan</span>
                </div>

                <div class="space-y-3 text-xs text-amber-900/90 leading-relaxed">
                    <div class="flex items-start space-x-2.5">
                        <i class="fa-solid fa-circle-check text-amber-600 text-[11px] mt-0.5 shrink-0"></i>
                        <span>Anda dapat masuk ke portal admin menggunakan <strong class="font-bold text-amber-950">Username</strong> maupun alamat <strong class="font-bold text-amber-950">Gmail / Email</strong>.</span>
                    </div>
                    <div class="flex items-start space-x-2.5">
                        <i class="fa-solid fa-circle-check text-amber-600 text-[11px] mt-0.5 shrink-0"></i>
                        <span>Setiap perubahan pada akun Gmail atau password yang disimpan akan <strong class="font-bold text-amber-950">langsung aktif</strong> saat itu juga.</span>
                    </div>
                    <div class="flex items-start space-x-2.5">
                        <i class="fa-solid fa-circle-check text-amber-600 text-[11px] mt-0.5 shrink-0"></i>
                        <span>Pastikan mencatat atau mengingat kata sandi baru dengan aman demi kelancaran operasional bimbingan belajar.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
