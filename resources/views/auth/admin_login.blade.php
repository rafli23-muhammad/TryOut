@extends('layouts.cemerlang')

@section('title', 'Login Administrator - Bimbel Cemerlang')

@section('content')
<div class="max-w-md mx-auto my-14 px-4">
    <div class="bg-white rounded-3xl shadow-2xl border-4 border-[#1F366A] p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 bg-[#1F366A] text-[#FFBE2A] text-[10px] font-extrabold px-3 py-1 rounded-bl-xl uppercase tracking-wider">
            Portal Admin
        </div>

        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-[#1F366A] text-[#FFBE2A] mx-auto flex items-center justify-center text-3xl mb-3 shadow">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h2 class="text-2xl font-black font-serif text-[#1F366A]">Administrator Panel</h2>
            <p class="text-xs text-slate-500 font-semibold mt-1">Akses khusus pengelola Bimbel Cemerlang</p>
        </div>

        <form method="POST" action="{{ url('/admin/login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Username atau Email (Gmail) Admin</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i class="fa-solid fa-user-gear"></i>
                    </span>
                    <input type="text" name="username" id="admin-user" required value="{{ old('username') }}" placeholder="admin atau admin@gmail.com" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1F366A] text-sm font-medium">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i class="fa-solid fa-key"></i>
                    </span>
                    <input type="password" name="password" id="admin-pass" required placeholder="••••••••" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1F366A] text-sm font-medium">
                </div>
            </div>

            <button type="submit" class="btn-cemerlang-navy w-full py-3.5 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition">
                Masuk ke Dashboard Admin <i class="fa-solid fa-arrow-right ml-2 text-[#FFBE2A]"></i>
            </button>
        </form>

        <div class="text-center mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
            Bukan Administrator?
            <a href="{{ url('/login') }}" class="font-bold text-[#1F366A] hover:underline ml-1">Masuk sebagai Siswa</a>
        </div>
    </div>
</div>
@endsection
