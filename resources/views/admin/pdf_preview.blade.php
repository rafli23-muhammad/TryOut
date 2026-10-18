@php
    $sourceLabel = $sourceType ?? 'PDF';
@endphp
@extends('layouts.admin')

@section('title', 'Tinjau Hasil Ekstraksi ' . $sourceLabel . ' - Bimbel Cemerlang')
@section('header_title', 'Tinjau Hasil Ekstraksi Soal ' . $sourceLabel)

@section('content')
<div class="space-y-6">

    <!-- Header Banner & Action Toolbar -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="bg-emerald-100 text-emerald-800 text-xs font-black px-3 py-0.5 rounded-full flex items-center">
                    <i class="fa-solid fa-check mr-1.5 text-emerald-600"></i> {{ count($extractedQuestions) }} Soal Ditemukan
                </span>
                <span class="bg-blue-100 text-[#1F366A] text-xs font-black px-3 py-0.5 rounded-full flex items-center">
                    <i class="fa-solid fa-images mr-1.5 text-blue-600"></i> {{ count($extractedImages) }} Gambar Diagram / Ilustrasi
                </span>
                <span class="text-xs text-slate-500 font-bold ml-1">Paket: {{ $package['title'] }}</span>
            </div>
            <h2 class="text-xl font-black font-serif text-[#1F366A]">Tinjau & Verifikasi Soal ({{ $sourceLabel }})</h2>
            <p class="text-xs text-slate-500">Karakter rumus matematika telah dinormalisasi secara otomatis. Anda dapat meninjau gambar ilustrasi dan merapikan pembahasan sebelum disimpan.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- AI Auto-Refine Button -->
            <button type="button" onclick="runAiRefinement()" id="btn-ai-refine" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-extrabold text-xs shadow-md hover:shadow-lg transition flex items-center">
                <i class="fa-solid fa-wand-magic-sparkles mr-2 text-[#FFBE2A] animate-pulse"></i>
                <span id="btn-ai-text">✨ Rapikan dengan AI</span>
            </button>

            <a href="{{ url('/admin/packages/' . $package['id'] . '/questions') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Bank Soal
            </a>

            <button type="button" onclick="document.getElementById('form-save-pdf-questions').submit()" class="btn-cemerlang-yellow px-5 py-2.5 rounded-xl text-xs font-black shadow flex items-center">
                <i class="fa-solid fa-cloud-arrow-down mr-2"></i> Simpan Semua Soal
            </button>
        </div>
    </div>

    <!-- AI Refine Status Alert Box (Hidden by default) -->
    <div id="ai-status-banner" class="hidden p-4 rounded-2xl bg-purple-50 border border-purple-200 text-xs text-purple-900 flex items-center justify-between shadow-sm">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold">
                <i class="fa-solid fa-wand-magic-sparkles text-sm animate-spin"></i>
            </div>
            <div>
                <b class="block font-bold">AI Smart Refiner Aktif</b>
                <span id="ai-status-desc">Sedang merapikan rumus matematika, simbol koordinat, dan struktur pembahasan...</span>
            </div>
        </div>
    </div>

    <!-- Edit Form for Extracted Questions -->
    <form id="form-save-pdf-questions" method="POST" action="{{ url('/admin/packages/' . $package['id'] . '/pdf/save') }}" class="space-y-6">
        @csrf

        @foreach($extractedQuestions as $i => $item)
            @php
                $assignedImage = $item['image'] ?? null;
            @endphp

            <div class="question-row bg-white rounded-3xl p-6 sm:p-7 shadow-sm border-2 border-slate-200/80 space-y-5 transition hover:border-[#1F366A]/40" data-index="{{ $i }}">
                
                <!-- Card Header -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <span class="w-9 h-9 rounded-2xl bg-[#1F366A] text-[#FFBE2A] font-black text-sm flex items-center justify-center shadow-sm">
                            {{ $i + 1 }}
                        </span>
                        <div class="flex items-center space-x-2">
                            <label class="text-xs font-bold text-slate-600">Tipe Soal:</label>
                            <select name="questions[{{ $i }}][type]" class="q-type px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-bold bg-white focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
                                <option value="pg" {{ ($item['type'] ?? 'pg') === 'pg' ? 'selected' : '' }}>Pilihan Ganda (PG)</option>
                                <option value="mcma" {{ ($item['type'] ?? '') === 'mcma' ? 'selected' : '' }}>MCMA (Multi-Choice)</option>
                                <option value="tf" {{ ($item['type'] ?? '') === 'tf' ? 'selected' : '' }}>Benar / Salah (TF)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <label class="text-xs font-bold text-emerald-800">Kunci Jawaban:</label>
                        <input type="text" name="questions[{{ $i }}][correct_answer]" value="{{ is_array($item['correct_answer']) ? implode(',', $item['correct_answer']) : $item['correct_answer'] }}" class="q-correct w-28 px-3 py-1.5 rounded-xl border-2 border-emerald-400 bg-emerald-50 text-xs font-black text-center text-emerald-900 focus:outline-none">
                    </div>
                </div>

                <!-- Text Question -->
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5 flex items-center">
                        <i class="fa-solid fa-pen-nib mr-1.5 text-amber-500"></i> Pertanyaan Soal:
                    </label>
                    <textarea name="questions[{{ $i }}][text]" rows="3" class="q-text w-full px-4 py-2.5 rounded-2xl border border-slate-300 text-xs sm:text-sm font-medium leading-relaxed focus:ring-2 focus:ring-[#FFBE2A] focus:border-transparent">{{ $item['text'] }}</textarea>
                </div>

                <!-- Question Image / Diagram Section -->
                <div class="bg-[#F8FAFD] rounded-2xl p-4 border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-[#1F366A] flex items-center">
                            <i class="fa-solid fa-image text-amber-500 mr-2"></i> Gambar / Ilustrasi Soal:
                        </span>
                        @if($assignedImage)
                            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full">
                                <i class="fa-solid fa-circle-check mr-1"></i> Gambar Terdeteksi dari PDF
                            </span>
                        @else
                            <span class="text-[11px] font-semibold text-slate-400">
                                Tanpa Gambar
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <!-- Preview Thumbnail Box -->
                        <div id="img-preview-box-{{ $i }}" class="w-32 h-28 rounded-xl border-2 border-dashed border-slate-300 bg-white flex items-center justify-center overflow-hidden shrink-0 shadow-inner">
                            @if($assignedImage)
                                <img id="img-preview-{{ $i }}" src="{{ asset($assignedImage) }}" alt="Diagram Soal {{ $i + 1 }}" class="w-full h-full object-contain p-1">
                            @else
                                <div id="img-empty-{{ $i }}" class="text-center text-slate-400 p-2">
                                    <i class="fa-regular fa-image text-2xl block mb-1 opacity-40"></i>
                                    <span class="text-[10px] font-semibold block">Tidak Ada</span>
                                </div>
                            @endif
                        </div>

                        <!-- Image Selector & Controls -->
                        <div class="flex-1 space-y-2 w-full">
                            <input type="hidden" name="questions[{{ $i }}][image]" id="input-img-{{ $i }}" value="{{ $assignedImage ?? '' }}">
                            
                            <div class="flex items-center gap-2">
                                <select onchange="updateQuestionImage({{ $i }}, this.value)" class="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
                                    <option value="">-- Tanpa Gambar (Hanya Teks) --</option>
                                    @foreach($extractedImages as $imgIdx => $img)
                                        <option value="{{ $img['url'] }}" {{ $assignedImage === $img['url'] ? 'selected' : '' }}>
                                            {{ $img['title'] ?? ('Gambar ' . ($imgIdx + 1)) }} (Hlm. {{ $img['page'] ?? 1 }} - {{ round(($img['size'] ?? 0) / 1024) }} KB)
                                        </option>
                                    @endforeach
                                </select>

                                @if($assignedImage)
                                    <button type="button" onclick="clearQuestionImage({{ $i }})" class="px-3 py-2 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition">
                                        <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                                    </button>
                                @endif
                            </div>

                            <p class="text-[11px] text-slate-500">
                                Gambar otomatis dicocokkan berdasarkan nomor dan halaman PDF. Anda dapat mengganti atau memilih diagram lain jika diperlukan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Pilihan Jawaban (Opsi A - E):</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">A</span>
                            <input type="text" name="questions[{{ $i }}][option_a]" value="{{ $item['options']['A'] ?? '' }}" placeholder="Opsi A" class="q-opt-a w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#FFBE2A]">
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">B</span>
                            <input type="text" name="questions[{{ $i }}][option_b]" value="{{ $item['options']['B'] ?? '' }}" placeholder="Opsi B" class="q-opt-b w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#FFBE2A]">
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">C</span>
                            <input type="text" name="questions[{{ $i }}][option_c]" value="{{ $item['options']['C'] ?? '' }}" placeholder="Opsi C" class="q-opt-c w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#FFBE2A]">
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">D</span>
                            <input type="text" name="questions[{{ $i }}][option_d]" value="{{ $item['options']['D'] ?? '' }}" placeholder="Opsi D" class="q-opt-d w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#FFBE2A]">
                        </div>
                        <div class="sm:col-span-2 flex items-center space-x-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">E</span>
                            <input type="text" name="questions[{{ $i }}][option_e]" value="{{ $item['options']['E'] ?? '' }}" placeholder="Opsi E" class="q-opt-e w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#FFBE2A]">
                        </div>
                    </div>
                </div>

                <!-- Explanation -->
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5 flex items-center">
                        <i class="fa-solid fa-lightbulb text-amber-500 mr-1.5"></i> Pembahasan Lengkap & Rumus:
                    </label>
                    <textarea name="questions[{{ $i }}][explanation]" rows="4" class="q-explanation w-full px-4 py-3 rounded-2xl border-2 border-amber-300 bg-[#FFFDF7] text-xs sm:text-sm font-medium leading-relaxed text-slate-800 focus:ring-2 focus:ring-amber-400 focus:outline-none shadow-sm">{{ $item['explanation'] }}</textarea>
                </div>
            </div>
        @endforeach

        <div class="flex justify-between items-center pt-4">
            <button type="button" onclick="runAiRefinement()" class="px-5 py-3 rounded-xl border-2 border-purple-300 bg-purple-50 text-purple-900 font-extrabold text-xs hover:bg-purple-100 transition flex items-center">
                <i class="fa-solid fa-wand-magic-sparkles mr-2 text-purple-600"></i> AI Format Ulang Semua Pembahasan
            </button>

            <button type="submit" class="btn-cemerlang-yellow px-8 py-3.5 rounded-2xl text-sm font-black shadow-xl hover:shadow-2xl transition transform hover:-translate-y-0.5">
                <i class="fa-solid fa-cloud-arrow-down mr-2"></i> Konfirmasi & Simpan {{ count($extractedQuestions) }} Soal ke Paket
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // 1. Update image preview dynamically
    function updateQuestionImage(index, url) {
        const input = document.getElementById('input-img-' + index);
        const box = document.getElementById('img-preview-box-' + index);
        input.value = url;

        if (url && url.trim() !== '') {
            box.innerHTML = `<img src="${url}" alt="Diagram Soal ${index + 1}" class="w-full h-full object-contain p-1">`;
        } else {
            box.innerHTML = `
                <div class="text-center text-slate-400 p-2">
                    <i class="fa-regular fa-image text-2xl block mb-1 opacity-40"></i>
                    <span class="text-[10px] font-semibold block">Tidak Ada</span>
                </div>
            `;
        }
    }

    function clearQuestionImage(index) {
        updateQuestionImage(index, '');
    }

    // 2. AI Smart Refiner
    async function runAiRefinement() {
        const btn = document.getElementById('btn-ai-refine');
        const btnText = document.getElementById('btn-ai-text');
        const banner = document.getElementById('ai-status-banner');

        btn.disabled = true;
        btnText.innerText = 'AI Sedang Bekerja...';
        banner.classList.remove('hidden');

        // Collect all questions currently in form
        const rows = document.querySelectorAll('.question-row');
        const questionsPayload = [];

        rows.forEach((row, i) => {
            const text = row.querySelector('.q-text').value;
            const explanation = row.querySelector('.q-explanation').value;
            const correct = row.querySelector('.q-correct').value;
            const type = row.querySelector('.q-type').value;

            questionsPayload.push({
                index: i,
                type: type,
                text: text,
                explanation: explanation,
                correct_answer: correct,
                options: {
                    A: row.querySelector('.q-opt-a').value,
                    B: row.querySelector('.q-opt-b').value,
                    C: row.querySelector('.q-opt-c').value,
                    D: row.querySelector('.q-opt-d').value,
                    E: row.querySelector('.q-opt-e').value,
                }
            });
        });

        try {
            const resp = await fetch("{{ url('/admin/packages/' . $package['id'] . '/pdf/ai-refine') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ questions: questionsPayload })
            });

            const data = await resp.json();

            if (data.success && data.questions) {
                data.questions.forEach((q, i) => {
                    const row = rows[i];
                    if (row) {
                        row.querySelector('.q-text').value = q.text;
                        row.querySelector('.q-explanation').value = q.explanation;
                        if (q.options) {
                            if (q.options.A !== undefined) row.querySelector('.q-opt-a').value = q.options.A;
                            if (q.options.B !== undefined) row.querySelector('.q-opt-b').value = q.options.B;
                            if (q.options.C !== undefined) row.querySelector('.q-opt-c').value = q.options.C;
                            if (q.options.D !== undefined) row.querySelector('.q-opt-d').value = q.options.D;
                            if (q.options.E !== undefined) row.querySelector('.q-opt-e').value = q.options.E;
                        }
                    }
                });

                document.getElementById('ai-status-desc').innerText = '✨ Berhasil! Seluruh simbol rumus, matematika, dan pembahasan telah dirapikan secara otomatis.';
                setTimeout(() => {
                    banner.classList.add('hidden');
                }, 4000);
            }
        } catch (err) {
            console.error('AI Refine error:', err);
            alert('Gagal menjalankan AI Refiner: ' + err.message);
        } finally {
            btn.disabled = false;
            btnText.innerText = '✨ Rapikan dengan AI';
        }
    }
</script>
@endpush
@endsection
