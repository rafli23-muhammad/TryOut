@extends('layouts.admin')

@section('title', 'Generate Soal dari PDF - Bimbel Cemerlang')
@section('header_title', 'Ekstraksi & Generate Soal dari PDF')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200">
        <div class="flex items-center space-x-3 mb-2">
            <span class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-file-pdf"></i>
            </span>
            <div>
                <h3 class="text-xl font-black font-serif text-[#1F366A]">Ekstrak & Generate Soal dari PDF</h3>
                <span class="text-xs text-slate-500">Target Paket: <b>{{ $package['title'] }}</b></span>
            </div>
        </div>

        <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
            Sistem otomatis membaca file PDF modul, bank soal, atau naskah ujian Anda, mendeteksi pola nomor (1., 2., dst), opsi jawaban A-E, kunci jawaban, dan pembahasan.
        </p>

        <form method="POST" action="{{ url('/admin/packages/' . $package['id'] . '/pdf/parse') }}" enctype="multipart/form-data" class="space-y-6 mt-6">
            @csrf

            <!-- PDF Upload Box -->
            <div class="border-2 border-dashed border-rose-300 bg-rose-50/40 rounded-3xl p-8 text-center hover:bg-rose-50 transition cursor-pointer">
                <i class="fa-solid fa-cloud-arrow-up text-4xl text-rose-500 mb-3 block"></i>
                <span class="font-extrabold text-sm text-[#1F366A] block mb-1">Pilih File PDF Bank Soal</span>
                <span class="text-xs text-slate-500 block mb-4">Format .pdf berbasis teks (Maks. 15MB)</span>
                <input type="file" name="pdf_file" required accept=".pdf" class="mx-auto text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-600 file:text-white hover:file:bg-rose-700">
            </div>

            <!-- Guidelines -->
            <div class="bg-[#FFF7E9] p-4 rounded-2xl border border-amber-200 text-xs text-slate-700 space-y-2">
                <b class="text-[#1F366A] block"><i class="fa-solid fa-circle-info mr-1 text-[#FFBE2A]"></i>Format yang Direkomendasikan dalam PDF:</b>
                <div class="font-mono text-[11px] bg-white p-3 rounded-xl border border-amber-200 text-slate-800 space-y-1">
                    <div>1. Ibu kota negara Indonesia saat ini adalah...</div>
                    <div class="pl-4">A. Jakarta</div>
                    <div class="pl-4">B. Bandung</div>
                    <div class="pl-4">C. Surabaya</div>
                    <div class="pl-4">D. Medan</div>
                    <div>Kunci: A</div>
                    <div>Pembahasan: Berdasarkan regulasi dan sejarah, Jakarta adalah ibu kota.</div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ url('/admin/packages/' . $package['id'] . '/questions') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Soal
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow flex items-center">
                    <i class="fa-solid fa-wand-magic-sparkles mr-2"></i> Ekstrak & Tinjau Soal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
