@extends('layouts.admin')

@section('title', 'Kelola Soal: ' . $package['title'] . ' - Bimbel Cemerlang')
@section('header_title', 'Bank & Manajemen Soal Try Out')

@section('content')
<div class="space-y-6">

    <!-- Package Context Bar & Package Switcher -->
    <div class="bg-gradient-to-r from-[#1F366A] to-[#132347] text-white p-6 rounded-3xl shadow-md border-4 border-[#FFBE2A] flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <span class="bg-[#FFBE2A] text-[#1F366A] text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider mb-2 inline-block">
                {{ $package['category'] }}
            </span>
            <h2 class="text-xl font-black font-serif">{{ $package['title'] }}</h2>
            <p class="text-xs text-slate-200 mt-1">
                Total: <b>{{ $questions->count() }} Butir Soal</b> | Passing Grade: <b>{{ $package['passing_grade'] }}%</b> | Durasi: <b>{{ $package['duration_minutes'] }} Menit</b>
            </p>
        </div>

        <!-- Package Selector Dropdown -->
        <div class="flex items-center space-x-2">
            <label class="text-xs text-slate-300 font-bold whitespace-nowrap">Pilih Paket:</label>
            <select onchange="window.location.href='/admin/packages/' + this.value + '/questions'" class="bg-[#132347] text-white border border-amber-300/40 text-xs rounded-xl px-3 py-2 font-semibold focus:outline-none">
                @foreach($allPackages as $p)
                    <option value="{{ $p['id'] }}" {{ $p['id'] === $package['id'] ? 'selected' : '' }}>
                        {{ $p['title'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Action Toolbar: Unified AI Generator & Import Soal + Manual Add -->
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <span class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-700 flex items-center justify-center font-bold text-lg shadow-sm">
                <i class="fa-solid fa-layer-group"></i>
            </span>
            <div>
                <h4 class="text-sm font-black font-serif text-[#1F366A]">Bank & Manajemen Soal</h4>
                <p class="text-[11px] text-slate-500">Impor ribuan soal atau generate otomatis dengan pembersih rumus AI</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Unified Smart AI Generator & Import Button -->
            <button onclick="openSmartImportModal()" class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-black text-xs shadow-md hover:shadow-lg transition flex items-center group">
                <i class="fa-solid fa-wand-magic-sparkles text-[#FFBE2A] mr-2 text-sm group-hover:rotate-12 transition transform"></i>
                <span>✨ Generate & Import Soal (AI)</span>
            </button>

            <!-- Manual Add Button -->
            <button onclick="openManualQuestionModal()" class="btn-cemerlang-yellow px-4 py-2.5 rounded-2xl font-black text-xs flex items-center shadow">
                <i class="fa-solid fa-plus mr-2"></i> Tambah Soal Manual
            </button>
        </div>
    </div>

    <!-- Questions Table / Cards -->
    <div class="space-y-4">
        @forelse($questions as $index => $q)
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 hover:border-[#FFBE2A] transition">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                    <div class="flex items-center space-x-3">
                        <span class="w-8 h-8 rounded-full bg-[#1F366A] text-[#FFBE2A] font-black text-xs flex items-center justify-center">
                            {{ $q['number'] ?? ($index + 1) }}
                        </span>
                        <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-md
                            {{ $q['type'] === 'pg' ? 'bg-blue-100 text-blue-800' : ($q['type'] === 'mcma' ? 'bg-purple-100 text-purple-800' : 'bg-amber-100 text-amber-800') }}">
                            {{ $q['type'] === 'pg' ? 'Pilihan Ganda' : ($q['type'] === 'mcma' ? 'MCMA (Multi-Select)' : 'Benar / Salah') }}
                        </span>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                            Kunci: <b>{{ is_array($q['correct_answer']) ? implode(', ', $q['correct_answer']) : $q['correct_answer'] }}</b>
                        </span>
                    </div>

                    <form method="POST" action="{{ url('/admin/packages/' . $package['id'] . '/questions/' . $q['id'] . '/delete') }}" onsubmit="return confirm('Hapus soal ini?')">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-rose-600 text-xs p-1" title="Hapus Soal">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </div>

                <!-- Image if present -->
                @if(!empty($q['image']))
                    <div class="mb-3 max-w-xs p-2 bg-slate-50 border border-slate-200 rounded-xl">
                        <img src="{{ asset($q['image']) }}" alt="Gambar Soal {{ $q['number'] }}" class="w-full max-h-48 object-contain rounded-lg bg-white">
                    </div>
                @endif

                <!-- Text -->
                <div class="text-sm font-semibold text-slate-800 leading-relaxed mb-4">
                    {!! nl2br(e($q['text'])) !!}
                </div>

                <!-- Options -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs mb-4">
                    @foreach($q['options'] ?? [] as $k => $val)
                        @php
                            $isKey = false;
                            if (is_array($q['correct_answer'])) {
                                $isKey = in_array($k, $q['correct_answer']);
                            } else {
                                $isKey = (strtoupper(trim((string)$q['correct_answer'])) === strtoupper(trim((string)$k)));
                            }
                        @endphp
                        <div class="p-2.5 rounded-xl border flex items-center space-x-2 {{ $isKey ? 'bg-emerald-50 border-emerald-300 font-bold text-emerald-950' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                            <span class="w-5 h-5 rounded flex items-center justify-center font-bold text-[10px] {{ $isKey ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $k }}</span>
                            <span class="truncate flex-1">{{ $val }}</span>
                            @if($isKey)
                                <i class="fa-solid fa-check text-emerald-600 text-xs"></i>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Pembahasan Lengkap & Rumus -->
                <div class="bg-[#FFF7E9] p-4 rounded-2xl border-2 border-amber-300/80 text-xs text-slate-800 leading-relaxed space-y-1.5">
                    <b class="text-[#1F366A] flex items-center mb-1">
                        <i class="fa-solid fa-lightbulb text-[#FFBE2A] mr-1.5 text-sm"></i> Pembahasan Lengkap & Rumus:
                    </b>
                    <div class="font-normal text-slate-700 space-y-1.5 math-content">
                        {!! nl2br(preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', e($q['explanation'] ?? 'Tidak ada pembahasan.'))) !!}
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center border-2 border-dashed border-slate-300">
                <i class="fa-solid fa-file-circle-question text-4xl text-slate-300 mb-3"></i>
                <h3 class="font-bold text-slate-700 text-sm">Belum Ada Soal di Paket Ini</h3>
                <p class="text-xs text-slate-500 mb-4 max-w-sm mx-auto">
                    Gunakan fitur Generate & Import Soal (AI) untuk mengunggah berkas PDF, Excel, atau Word secara instan.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button onclick="openSmartImportModal()" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-black text-xs rounded-2xl shadow flex items-center">
                        <i class="fa-solid fa-wand-magic-sparkles text-[#FFBE2A] mr-2"></i> Generate & Import Soal (AI)
                    </button>
                    <button onclick="openManualQuestionModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl shadow-sm">
                        <i class="fa-solid fa-plus mr-1.5"></i> Tambah Manual
                    </button>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Unified Modal: Smart Question Generator & Import (AI) -->
<div id="modal-smart-import" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border-4 border-indigo-500/80 relative">
        <button onclick="closeSmartImportModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-lg">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="flex items-center space-x-3 mb-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-indigo-200">
                <i class="fa-solid fa-wand-magic-sparkles text-[#FFBE2A]"></i>
            </div>
            <div>
                <h3 class="text-lg font-black font-serif text-[#1F366A]">Generate & Import Soal (AI)</h3>
                <span class="text-xs text-slate-500">Pilih format berkas untuk ekstraksi otomatis & perapian rumus AI</span>
            </div>
        </div>

        <!-- Format Tabs -->
        <div class="flex rounded-2xl bg-slate-100 p-1.5 mb-5 border border-slate-200/80">
            <button type="button" onclick="switchImportTab('pdf')" id="tab-btn-pdf" class="import-tab-btn flex-1 py-2 px-3 rounded-xl text-xs font-black transition flex items-center justify-center space-x-1.5 bg-white text-[#1F366A] shadow-sm">
                <i class="fa-solid fa-file-pdf text-rose-500 text-sm"></i>
                <span>PDF (.pdf)</span>
            </button>
            <button type="button" onclick="switchImportTab('excel')" id="tab-btn-excel" class="import-tab-btn flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5 text-slate-600 hover:text-slate-900">
                <i class="fa-solid fa-file-excel text-emerald-600 text-sm"></i>
                <span>Excel / CSV</span>
            </button>
            <button type="button" onclick="switchImportTab('word')" id="tab-btn-word" class="import-tab-btn flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5 text-slate-600 hover:text-slate-900">
                <i class="fa-solid fa-file-word text-blue-600 text-sm"></i>
                <span>Word (.docx)</span>
            </button>
        </div>

        <!-- Pane 1: PDF -->
        <div id="tab-pane-pdf" class="import-tab-pane space-y-4">
            <form method="POST" action="{{ url('/admin/packages/' . $package['id'] . '/pdf/parse') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="bg-rose-50/70 p-4 rounded-2xl border border-rose-200/80 text-xs text-rose-900 leading-relaxed">
                    <b class="flex items-center text-rose-950 mb-1">
                        <i class="fa-solid fa-circle-info mr-1.5 text-rose-600"></i> Ekstraksi Cerdas Dokumen PDF:
                    </b>
                    <ul class="list-disc list-inside space-y-0.5 text-rose-800 text-[11px]">
                        <li>Mendeteksi nomor urut soal (1., 2., dst) dan opsi pilihan (A, B, C, D, E).</li>
                        <li>Mengekstrak gambar ilustrasi / diagram geometri langsung dari PDF.</li>
                        <li>Menyelaraskan font miring rumus matematika menjadi teks standar.</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Pilih Berkas PDF Soal</label>
                    <input type="file" name="pdf_file" required accept=".pdf" class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-rose-600 file:text-white hover:file:bg-rose-700 border border-slate-300 rounded-xl p-1.5">
                </div>

                <!-- AI Refine Checkbox -->
                <label class="flex items-start space-x-3 p-3.5 rounded-2xl bg-purple-50/90 border border-purple-200 cursor-pointer hover:bg-purple-100/70 transition">
                    <input type="checkbox" name="ai_refine" value="1" checked class="mt-0.5 w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                    <div class="text-xs">
                        <span class="font-black text-purple-900 flex items-center">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-500 mr-1.5"></i> Aktifkan AI Pembersih Rumus & Pembahasan
                        </span>
                        <span class="text-purple-700 text-[11px] block mt-0.5">Memperbaiki simbol matematika, koordinat, dan menata langkah pembahasan sebelum masuk bank soal.</span>
                    </div>
                </label>

                <div class="pt-2 flex items-center justify-end space-x-3 border-t border-slate-100">
                    <button type="button" onclick="closeSmartImportModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 text-white text-xs font-black shadow flex items-center">
                        <i class="fa-solid fa-file-pdf mr-1.5"></i> Parse & Generate dari PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- Pane 2: Excel / CSV -->
        <div id="tab-pane-excel" class="import-tab-pane hidden space-y-4">
            <form method="POST" action="{{ url('/admin/packages/' . $package['id'] . '/import-excel') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="bg-emerald-50/70 p-4 rounded-2xl border border-emerald-200/80 text-xs text-emerald-900 leading-relaxed">
                    <div class="flex items-center justify-between mb-2">
                        <b class="flex items-center text-emerald-950">
                            <i class="fa-solid fa-file-excel mr-1.5 text-emerald-600"></i> Import Spreadsheet Masal
                        </b>
                        <div class="flex items-center space-x-1.5">
                            <a href="{{ url('/admin/questions/template/excel') }}" class="px-2.5 py-1 rounded-lg bg-emerald-200/80 hover:bg-emerald-300 text-emerald-900 font-bold text-[10px] flex items-center transition" title="Download Excel Template">
                                <i class="fa-solid fa-download mr-1"></i> .xlsx
                            </a>
                            <a href="{{ url('/admin/questions/template/csv') }}" class="px-2.5 py-1 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-[10px] flex items-center transition" title="Download CSV Template">
                                <i class="fa-solid fa-download mr-1"></i> .csv
                            </a>
                        </div>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-emerald-800 text-[11px]">
                        <li>Mendukung file format <b>.xlsx</b>, <b>.xls</b>, dan <b>.csv</b>.</li>
                        <li>Format kolom: No, Tipe (PG/MCMA/TF), Soal, Opsi A-E, Kunci, Pembahasan.</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Pilih Berkas Excel / CSV</label>
                    <input type="file" name="excel_file" required accept=".xlsx,.xls,.csv" class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 border border-slate-300 rounded-xl p-1.5">
                </div>

                <!-- AI Refine Checkbox -->
                <label class="flex items-start space-x-3 p-3.5 rounded-2xl bg-purple-50/90 border border-purple-200 cursor-pointer hover:bg-purple-100/70 transition">
                    <input type="checkbox" name="ai_refine" value="1" checked class="mt-0.5 w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                    <div class="text-xs">
                        <span class="font-black text-purple-900 flex items-center">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-500 mr-1.5"></i> Aktifkan AI Pembersih Rumus & Pembahasan
                        </span>
                        <span class="text-purple-700 text-[11px] block mt-0.5">Merapikan karakter simbol matematika dan memformat pembahasan otomatis saat diimpor.</span>
                    </div>
                </label>

                <div class="pt-2 flex items-center justify-end space-x-3 border-t border-slate-100">
                    <button type="button" onclick="closeSmartImportModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow flex items-center">
                        <i class="fa-solid fa-file-import mr-1.5"></i> Unggah & Import Excel
                    </button>
                </div>
            </form>
        </div>

        <!-- Pane 3: Word (.docx) -->
        <div id="tab-pane-word" class="import-tab-pane hidden space-y-4">
            <form method="POST" action="{{ url('/admin/packages/' . $package['id'] . '/word/parse') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="bg-blue-50/70 p-4 rounded-2xl border border-blue-200/80 text-xs text-blue-900 leading-relaxed">
                    <b class="flex items-center text-blue-950 mb-1">
                        <i class="fa-solid fa-circle-info mr-1.5 text-blue-600"></i> Ekstraksi Cerdas Dokumen Word (.docx):
                    </b>
                    <ul class="list-disc list-inside space-y-0.5 text-blue-800 text-[11px]">
                        <li>Mengekstrak naskah soal, pilihan A-E, dan pembahasan dari file Word.</li>
                        <li>Mengekstrak gambar ilustrasi / grafik yang disisipkan di dalam naskah .docx.</li>
                        <li>Merapikan simbol matematika dan notasi pecahan/eksponen.</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Pilih Berkas Word (.docx)</label>
                    <input type="file" name="word_file" required accept=".docx,.doc" class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-700 border border-slate-300 rounded-xl p-1.5">
                </div>

                <!-- AI Refine Checkbox -->
                <label class="flex items-start space-x-3 p-3.5 rounded-2xl bg-purple-50/90 border border-purple-200 cursor-pointer hover:bg-purple-100/70 transition">
                    <input type="checkbox" name="ai_refine" value="1" checked class="mt-0.5 w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                    <div class="text-xs">
                        <span class="font-black text-purple-900 flex items-center">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-500 mr-1.5"></i> Aktifkan AI Pembersih Rumus & Pembahasan
                        </span>
                        <span class="text-purple-700 text-[11px] block mt-0.5">Memperbaiki simbol matematika, koordinat, dan menata langkah pembahasan sebelum masuk bank soal.</span>
                    </div>
                </label>

                <div class="pt-2 flex items-center justify-end space-x-3 border-t border-slate-100">
                    <button type="button" onclick="closeSmartImportModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-black shadow flex items-center">
                        <i class="fa-solid fa-file-word mr-1.5"></i> Parse & Generate dari Word
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Manual Question Add -->
<div id="modal-manual-question" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border-4 border-[#FFBE2A] relative overflow-y-auto max-h-[90vh]">
        <button onclick="closeManualQuestionModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-lg">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <h3 class="text-xl font-black font-serif text-[#1F366A] mb-1">Tambah Soal Manual</h3>
        <p class="text-xs text-slate-500 mb-4">Pilih tipe soal dan masukkan pertanyaan, opsi, serta pembahasan.</p>

        <form method="POST" action="{{ url('/admin/packages/' . $package['id'] . '/questions') }}" class="space-y-4">
            @csrf

            <!-- Type Selector -->
            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Tipe Soal</label>
                <select name="type" id="manual-type-select" onchange="toggleManualTypeFields(this.value)" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                    <option value="pg">Pilihan Ganda (Single Choice: A, B, C, D, E)</option>
                    <option value="mcma">Pilihan Ganda Kompleks / MCMA (Checkbox >1 Jawaban)</option>
                    <option value="tf">Benar / Salah (True / False)</option>
                </select>
            </div>

            <!-- Question Text -->
            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Teks Pertanyaan</label>
                <textarea name="text" rows="3" required placeholder="Tuliskan pertanyaan atau deskripsi soal..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]"></textarea>
            </div>

            <!-- Field 1: PG -->
            <div id="section-pg" class="space-y-3">
                <span class="block text-xs font-bold text-slate-600 uppercase">Pilihan Jawaban (A-E):</span>
                <div class="space-y-2">
                    <input type="text" name="option_a" placeholder="Opsi A" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    <input type="text" name="option_b" placeholder="Opsi B" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    <input type="text" name="option_c" placeholder="Opsi C" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    <input type="text" name="option_d" placeholder="Opsi D" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    <input type="text" name="option_e" placeholder="Opsi E" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-emerald-800 uppercase tracking-wider mb-1">Kunci Jawaban Benar (PG)</label>
                    <select name="correct_pg" class="w-full px-3 py-2 rounded-xl border border-emerald-400 bg-emerald-50 text-xs font-bold">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
            </div>

            <!-- Field 2: MCMA -->
            <div id="section-mcma" class="hidden space-y-3">
                <span class="block text-xs font-bold text-purple-800 uppercase">Pilihan Jawaban MCMA (Centang yang Benar):</span>
                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="correct_mcma[]" value="A" class="w-4 h-4 rounded text-purple-600">
                        <input type="text" name="option_mcma_a" placeholder="Opsi A" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="correct_mcma[]" value="B" class="w-4 h-4 rounded text-purple-600">
                        <input type="text" name="option_mcma_b" placeholder="Opsi B" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="correct_mcma[]" value="C" class="w-4 h-4 rounded text-purple-600">
                        <input type="text" name="option_mcma_c" placeholder="Opsi C" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="correct_mcma[]" value="D" class="w-4 h-4 rounded text-purple-600">
                        <input type="text" name="option_mcma_d" placeholder="Opsi D" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="correct_mcma[]" value="E" class="w-4 h-4 rounded text-purple-600">
                        <input type="text" name="option_mcma_e" placeholder="Opsi E" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>
            </div>

            <!-- Field 3: TF -->
            <div id="section-tf" class="hidden space-y-3">
                <label class="block text-xs font-bold text-amber-800 uppercase tracking-wider mb-1">Kunci Jawaban Benar / Salah</label>
                <select name="correct_tf" class="w-full px-3 py-2 rounded-xl border border-amber-400 bg-amber-50 text-xs font-bold">
                    <option value="Benar">Benar (True)</option>
                    <option value="Salah">Salah (False)</option>
                </select>
            </div>

            <!-- Explanation -->
            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Pembahasan Singkat</label>
                <textarea name="explanation" rows="2" placeholder="Tuliskan konsep atau langkah penyelesaian untuk siswa..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeManualQuestionModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="btn-cemerlang-yellow px-6 py-2.5 rounded-xl text-xs font-black shadow">
                    Simpan Soal
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openSmartImportModal(tab = 'pdf') {
        document.getElementById('modal-smart-import').classList.remove('hidden');
        if (tab) switchImportTab(tab);
    }
    function closeSmartImportModal() {
        document.getElementById('modal-smart-import').classList.add('hidden');
    }

    // Keep backwards compatibility
    function openImportExcelModal() {
        openSmartImportModal('excel');
    }
    function closeImportExcelModal() {
        closeSmartImportModal();
    }

    function switchImportTab(tabName) {
        const tabs = ['pdf', 'excel', 'word'];
        tabs.forEach(t => {
            const btn = document.getElementById('tab-btn-' + t);
            const pane = document.getElementById('tab-pane-' + t);
            if (t === tabName) {
                if (pane) pane.classList.remove('hidden');
                if (btn) btn.className = 'import-tab-btn flex-1 py-2 px-3 rounded-xl text-xs font-black transition flex items-center justify-center space-x-1.5 bg-white text-[#1F366A] shadow-sm';
            } else {
                if (pane) pane.classList.add('hidden');
                if (btn) btn.className = 'import-tab-btn flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5 text-slate-600 hover:text-slate-900';
            }
        });
    }

    function openManualQuestionModal() {
        document.getElementById('modal-manual-question').classList.remove('hidden');
    }
    function closeManualQuestionModal() {
        document.getElementById('modal-manual-question').classList.add('hidden');
    }

    function toggleManualTypeFields(type) {
        document.getElementById('section-pg').classList.toggle('hidden', type !== 'pg');
        document.getElementById('section-mcma').classList.toggle('hidden', type !== 'mcma');
        document.getElementById('section-tf').classList.toggle('hidden', type !== 'tf');
    }
</script>
@endpush
@endsection
