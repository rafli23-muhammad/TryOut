@extends('layouts.cemerlang')

@section('title', 'Bimbel Cemerlang - Platform Try Out Online & Bimbingan Belajar')

@section('content')
<!-- Hero Section matching reference image -->
<section class="relative bg-gradient-to-r from-[#173F8A] via-[#1A4FA8] to-[#1462D6] text-white py-16 md:py-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
    <!-- Abstract background decorative shapes -->
    <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
    <div class="absolute right-1/3 bottom-0 w-80 h-80 rounded-full bg-blue-400/10 blur-2xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center relative z-10">
        <!-- Hero Left Column -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Badge pill -->
            <div class="inline-flex items-center space-x-2.5 bg-white/15 backdrop-blur-md px-4 py-2 rounded-full border border-white/20 text-xs font-semibold text-white shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-[#FFBE2A]"></span>
                <span>Standar Ujian Resmi UTBK-SNBT & CPNS 2026</span>
            </div>

            <!-- Hero Heading -->
            <h1 class="text-3xl sm:text-5xl lg:text-[3.25rem] font-black tracking-tight leading-[1.18] text-white">
                Raih Masa Depan <span class="text-[#FFBE2A]">Cemerlang</span><br>
                dengan Try Out Online Presisi
            </h1>

            <!-- Subtitle -->
            <p class="text-sm sm:text-base text-blue-100/90 leading-relaxed max-w-xl font-normal">
                Simulasi ujian berstandar nasional dengan timer real-time auto-submit, evaluasi Passing Grade instan, dan pembahasan lengkap per butir soal untuk 3 tipe soal (Pilihan Ganda, MCMA, dan Benar/Salah).
            </p>

            <!-- Buttons matching reference -->
            <div class="flex flex-wrap items-center gap-4 pt-2">
                <!-- Yellow primary button with rocket -->
                <a href="{{ url('/student/catalog') }}" class="bg-[#FFBE2A] hover:bg-[#E5A817] text-[#1F366A] font-extrabold px-6 py-3.5 rounded-2xl text-xs sm:text-sm flex items-center shadow-lg transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-rocket mr-2 text-sm text-[#1F366A]"></i>
                    <span>Jelajahi Paket Try Out</span>
                    <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                </a>

                <!-- Navy outline button with user icon -->
                <a href="{{ url('/register') }}" class="bg-[#122A5C]/70 hover:bg-[#122A5C] text-white border border-white/20 px-6 py-3.5 rounded-2xl font-bold text-xs sm:text-sm transition flex items-center">
                    <i class="fa-regular fa-user mr-2 text-xs"></i>
                    <span>Daftar Akun Siswa</span>
                </a>
            </div>

            <!-- Trust Badges with thin white icons -->
            <div class="flex flex-wrap items-center gap-6 sm:gap-10 pt-4 text-xs font-medium text-white/90">
                <div class="flex items-center space-x-2">
                    <i class="fa-regular fa-clock text-lg text-white"></i>
                    <span>Timer & Auto-Submit</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-list-ul text-lg text-white"></i>
                    <span>3 Tipe Soal Lengkap</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fa-regular fa-circle-check text-lg text-white"></i>
                    <span>Passing Grade Akurat</span>
                </div>
            </div>
        </div>

        <!-- Hero Right Column: Exam UI Simulation Mockup (Matching Photo 2) -->
        <div class="lg:col-span-5 flex justify-center relative">
            <!-- Background glow behind card -->
            <div class="absolute inset-0 bg-blue-400/20 rounded-3xl blur-2xl transform scale-95 pointer-events-none"></div>

            <!-- Main Mockup Container with Dark Blue Border / Outer Frame -->
            <div class="w-full max-w-[430px] relative z-10 p-2 sm:p-2.5 rounded-[2.2rem] bg-gradient-to-b from-[#13306B]/80 to-[#0A1D44]/90 border border-blue-400/30 shadow-2xl backdrop-blur-sm">
                
                <!-- Floating Badge Top Right: Auto-Submit aktif -->
                <div class="absolute -top-3.5 right-4 z-20 bg-[#FFB81C] text-[#1A2E5A] px-3.5 py-1 rounded-xl text-[11px] font-black flex items-center space-x-1.5 shadow-lg border border-amber-300">
                    <i class="fa-solid fa-check text-[10px]"></i>
                    <span>Auto-Submit aktif</span>
                </div>

                <!-- White Exam Card -->
                <div class="bg-white rounded-[1.75rem] p-5 sm:p-6 text-slate-800 shadow-xl relative overflow-hidden">
                    
                    <!-- Header Info Inside Card: Title & Timer Pill -->
                    <div class="flex items-center justify-between pb-3">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-9 h-9 rounded-xl bg-[#FFF6E5] text-[#C07E00] flex items-center justify-center text-sm shadow-sm border border-amber-200">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-[13px] font-black text-[#132A5A] leading-tight">Try Out UTBK-SNBT 2026</h4>
                                <span class="text-[10px] text-slate-400 font-semibold">Penalaran Umum</span>
                            </div>
                        </div>

                        <!-- Timer Pill (Dark Navy Pill with gold clock) -->
                        <div class="bg-[#122A5C] text-white px-3 py-1.5 rounded-full flex items-center space-x-1.5 shadow-sm">
                            <i class="fa-regular fa-clock text-[#FFBE2A] text-xs"></i>
                            <span class="font-mono text-xs font-black tracking-wider text-[#FFBE2A]">01:24:30</span>
                        </div>
                    </div>

                    <!-- Progress Bar & Count -->
                    <div class="pt-2 pb-3.5">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 mb-1.5">
                            <span class="text-slate-600">Soal 12 dari 30</span>
                            <span class="text-slate-500">40%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#FFB81C] rounded-full" style="width: 40%"></div>
                        </div>
                    </div>

                    <!-- Question Text -->
                    <p class="text-xs sm:text-[13px] font-extrabold text-[#132A5A] leading-snug mb-3.5">
                        Manakah simpulan yang paling tepat dari bacaan tersebut?
                    </p>

                    <!-- Multiple Choice Options (A, B selected, C, D) -->
                    <div class="space-y-2 mb-4">
                        <!-- Option A -->
                        <div class="flex items-center space-x-3 p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition cursor-pointer">
                            <span class="w-6 h-6 rounded-full bg-[#EDF3FC] text-[#1E56B3] flex items-center justify-center text-[10px] font-black flex-shrink-0">
                                A
                            </span>
                            <div class="h-2 bg-slate-200 rounded-full w-4/5"></div>
                        </div>

                        <!-- Option B (Selected / Active State) -->
                        <div class="flex items-center space-x-3 p-2 rounded-xl border-2 border-[#1B4B9C] bg-[#F3F7FD] shadow-sm cursor-pointer">
                            <span class="w-6 h-6 rounded-full bg-[#122A5C] text-white flex items-center justify-center text-[10px] font-black flex-shrink-0">
                                B
                            </span>
                            <div class="h-2 bg-slate-300 rounded-full w-3/4"></div>
                        </div>

                        <!-- Option C -->
                        <div class="flex items-center space-x-3 p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition cursor-pointer">
                            <span class="w-6 h-6 rounded-full bg-[#EDF3FC] text-[#1E56B3] flex items-center justify-center text-[10px] font-black flex-shrink-0">
                                C
                            </span>
                            <div class="h-2 bg-slate-200 rounded-full w-1/2"></div>
                        </div>

                        <!-- Option D -->
                        <div class="flex items-center space-x-3 p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition cursor-pointer">
                            <span class="w-6 h-6 rounded-full bg-[#EDF3FC] text-[#1E56B3] flex items-center justify-center text-[10px] font-black flex-shrink-0">
                                D
                            </span>
                            <div class="h-2 bg-slate-200 rounded-full w-2/3"></div>
                        </div>
                    </div>

                    <!-- Number Grid (1 to 20) with Status Colors -->
                    <div class="space-y-2 pt-1 pb-3">
                        <!-- Row 1: 1 to 10 -->
                        <div class="grid grid-cols-10 gap-1 text-[10px] font-black text-center">
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">1</span>
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">2</span>
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">3</span>
                            <span class="py-1 rounded-md bg-white border border-[#FFB81C] text-amber-800">4</span>
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">5</span>
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">6</span>
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">7</span>
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">8</span>
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">9</span>
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">10</span>
                        </div>
                        <!-- Row 2: 11 to 20 -->
                        <div class="grid grid-cols-10 gap-1 text-[10px] font-black text-center">
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">11</span>
                            <span class="py-1 rounded-md bg-[#FFB81C] text-[#132A5A]">12</span>
                            <span class="py-1 rounded-md bg-[#EDF3FC] text-slate-500">13</span>
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">14</span>
                            <span class="py-1 rounded-md bg-white border border-[#FFB81C] text-amber-800">15</span>
                            <span class="py-1 rounded-md bg-[#132A5A] text-white">16</span>
                            <span class="py-1 rounded-md bg-[#EDF3FC] text-slate-500">17</span>
                            <span class="py-1 rounded-md bg-[#EDF3FC] text-slate-500">18</span>
                            <span class="py-1 rounded-md bg-[#EDF3FC] text-slate-500">19</span>
                            <span class="py-1 rounded-md bg-[#EDF3FC] text-slate-500">20</span>
                        </div>

                        <!-- Legend under grid -->
                        <div class="flex items-center space-x-3 text-[9px] font-bold text-slate-500 pt-1">
                            <div class="flex items-center space-x-1">
                                <span class="w-2.5 h-2.5 rounded-sm bg-[#132A5A]"></span>
                                <span>Terjawab</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span class="w-2.5 h-2.5 rounded-sm bg-white border border-[#FFB81C]"></span>
                                <span>Ragu-ragu</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span class="w-2.5 h-2.5 rounded-sm bg-[#EDF3FC]"></span>
                                <span>Belum dijawab</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Nav Buttons: Raguragu (yellow outline) & Selanjutnya (navy solid) -->
                    <div class="flex items-center justify-between gap-3 pt-2">
                        <button type="button" class="w-1/2 py-2.5 rounded-xl border border-amber-300 bg-[#FFFBF0] text-amber-900 font-bold text-xs flex items-center justify-center">
                            <i class="fa-regular fa-bookmark mr-1.5 text-xs text-amber-600"></i>
                            <span>Ragu-ragu</span>
                        </button>
                        <a href="{{ url('/student/catalog') }}" class="w-1/2 py-2.5 rounded-xl bg-[#122A5C] hover:bg-[#0A1A3C] text-white font-extrabold text-xs shadow-md transition flex items-center justify-center space-x-1.5">
                            <span>Selanjutnya</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <!-- Floating Score Card at Bottom Left: Lulus Passing Grade 86 -->
                <div class="absolute -bottom-6 -left-4 z-20 bg-white rounded-2xl p-3.5 shadow-2xl border border-slate-100 flex items-center space-x-3 animate-float">
                    <!-- Circular percentage gauge -->
                    <div class="relative w-12 h-12 flex items-center justify-center">
                        <svg class="w-12 h-12 transform -rotate-90">
                            <circle cx="24" cy="24" r="18" stroke="#E2E8F0" stroke-width="4" fill="transparent" />
                            <circle cx="24" cy="24" r="18" stroke="#00C48C" stroke-width="4" stroke-dasharray="113" stroke-dashoffset="15" stroke-linecap="round" fill="transparent" />
                        </svg>
                        <span class="absolute font-black text-xs text-slate-800">86</span>
                    </div>
                    <div>
                        <span class="block text-xs font-black text-[#00A86B]">Lulus Passing Grade</span>
                        <span class="block text-[10px] text-slate-400 font-medium">Pembahasan siap dibuka</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Features Grid matching reference image -->
<section class="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <!-- Section Title & Pill -->
    <div class="text-center max-w-3xl mx-auto mb-16 reveal-on-scroll">
        <div class="inline-block bg-[#1B67D3] text-white text-[11px] font-extrabold uppercase tracking-wider px-4 py-1.5 rounded-full mb-4 shadow-sm">
            FITUR UNGGULAN
        </div>
        <h2 class="text-2xl sm:text-4xl font-black text-[#132A5A] tracking-tight">
            Dirancang Khusus untuk Pengalaman<br>Ujian Nyata
        </h2>
        <p class="text-slate-500 mt-3 text-xs sm:text-sm font-normal">
            Mempersiapkan siswa menghadapi seleksi nasional dengan teknologi simulasi termodern.
        </p>
    </div>

    <!-- 3 Feature Cards with soft pastel gradients & circular arrows -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Feature 1: Simulasi Ujian Real-Time (Soft Blue) -->
        <div class="reveal-pop reveal-delay-1 bg-white rounded-3xl p-7 shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-300 relative overflow-hidden flex flex-col justify-between group">
            <!-- Background top-right subtle blob -->
            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-[#EBF3FF] pointer-events-none"></div>

            <div class="relative z-10 space-y-4">
                <!-- Circular Icon with dark blue -->
                <div class="w-14 h-14 rounded-full bg-[#EBF3FF] flex items-center justify-center">
                    <div class="w-10 h-10 rounded-full bg-[#0E54C7] text-white flex items-center justify-center text-base shadow-sm">
                        <i class="fa-solid fa-stopwatch"></i>
                    </div>
                </div>

                <h3 class="text-lg font-black text-[#1F366A]">Simulasi Ujian Real-Time</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Pengalaman pengerjaan try out dengan timer otomatis, navigasi grid interaktif, dan penanda ragu-ragu untuk latihan yang optimal.
                </p>
            </div>

            <!-- Card Bottom: Pelajari lebih lanjut + circular arrow -->
            <div class="relative z-10 pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ url('/student/catalog') }}" class="text-xs font-bold text-[#1B67D3] hover:underline flex items-center">
                    Pelajari lebih lanjut <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
                <a href="{{ url('/student/catalog') }}" class="w-8 h-8 rounded-full bg-[#EBF3FF] group-hover:bg-[#1B67D3] text-[#1B67D3] group-hover:text-white flex items-center justify-center text-xs transition">
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </a>
            </div>
        </div>

        <!-- Feature 2: 3 Tipe Soal Komprehensif (Soft Purple) -->
        <div class="reveal-pop reveal-delay-2 bg-white rounded-3xl p-7 shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-300 relative overflow-hidden flex flex-col justify-between group">
            <!-- Background top-right subtle blob -->
            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-[#F5F0FF] pointer-events-none"></div>

            <div class="relative z-10 space-y-4">
                <!-- Circular Icon with purple -->
                <div class="w-14 h-14 rounded-full bg-[#F5F0FF] flex items-center justify-center">
                    <div class="w-10 h-10 rounded-full bg-[#5E35B1] text-white flex items-center justify-center text-base shadow-sm">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>

                <h3 class="text-lg font-black text-[#1F366A]">3 Tipe Soal Komprehensif</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Mendukung Pilihan Ganda (Single Choice), Pilihan Ganda Kompleks / MCMA (multi-select), dan Benar/Salah (True/False) dengan grid interaktif.
                </p>
            </div>

            <!-- Card Bottom -->
            <div class="relative z-10 pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ url('/student/catalog') }}" class="text-xs font-bold text-[#1B67D3] hover:underline flex items-center">
                    Pelajari lebih lanjut <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
                <a href="{{ url('/student/catalog') }}" class="w-8 h-8 rounded-full bg-[#F5F0FF] group-hover:bg-[#5E35B1] text-[#5E35B1] group-hover:text-white flex items-center justify-center text-xs transition">
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </a>
            </div>
        </div>

        <!-- Feature 3: Evaluasi & Pembahasan Instan (Soft Green) -->
        <div class="reveal-pop reveal-delay-3 bg-white rounded-3xl p-7 shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-300 relative overflow-hidden flex flex-col justify-between group">
            <!-- Background top-right subtle blob -->
            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-[#E8F8F2] pointer-events-none"></div>

            <div class="relative z-10 space-y-4">
                <!-- Circular Icon with green -->
                <div class="w-14 h-14 rounded-full bg-[#E8F8F2] flex items-center justify-center">
                    <div class="w-10 h-10 rounded-full bg-[#00897B] text-white flex items-center justify-center text-base shadow-sm">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                </div>

                <h3 class="text-lg font-black text-[#1F366A]">Evaluasi & Pembahasan Instan</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Hasil keluar detik itu juga lengkap dengan status Lulus/Belum Lulus berdasarkan Passing Grade, rincian akurasi, dan pembahasan per nomor.
                </p>
            </div>

            <!-- Card Bottom -->
            <div class="relative z-10 pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ url('/student/catalog') }}" class="text-xs font-bold text-[#1B67D3] hover:underline flex items-center">
                    Pelajari lebih lanjut <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
                <a href="{{ url('/student/catalog') }}" class="w-8 h-8 rounded-full bg-[#E8F8F2] group-hover:bg-[#00897B] text-[#00897B] group-hover:text-white flex items-center justify-center text-xs transition">
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Section Tipe Soal matching user's uploaded image -->
<section class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <!-- Header: Pill Badge + Heading + Subtitle -->
    <div class="text-center max-w-3xl mx-auto mb-14 reveal-on-scroll">
        <div class="inline-block bg-[#FFF4DE] text-[#C07E00] text-[11px] font-extrabold uppercase tracking-wider px-4 py-1.5 rounded-full mb-4 border border-[#FFE19E]">
            TIPE SOAL
        </div>
        <h2 class="text-2xl sm:text-4xl font-black text-[#132A5A] tracking-tight leading-tight">
            Tiga Format Soal, Satu<br>Pengalaman Ujian
        </h2>
        <p class="text-slate-500 mt-3 text-xs sm:text-sm font-normal">
            Tampilan pengerjaan yang bersih dan konsisten untuk setiap format soal.
        </p>
    </div>

    <!-- 3 Cards: Pilihan Ganda, Pilihan Ganda Kompleks (MCMA), Benar / Salah (True/False) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
        
        <!-- CARD 1: Pilihan Ganda (Single Choice) -->
        <div class="reveal-pop reveal-delay-1 bg-[#F8FAFD] rounded-[2rem] p-5 sm:p-6 border border-slate-200/80 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
            <div>
                <!-- Top Header: Title + Pill Badge -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-[#132A5A]">Pilihan Ganda</h3>
                    <span class="bg-[#EEF5FF] text-[#1E75FF] text-[10px] font-extrabold px-3 py-1 rounded-full border border-blue-200/60">
                        Single Choice
                    </span>
                </div>

                <!-- White Question Workspace Mockup -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm space-y-3.5 mb-4">
                    <!-- Question lines placeholder -->
                    <div class="space-y-1.5 pb-1">
                        <div class="h-2 bg-slate-200 rounded-full w-2/5"></div>
                        <div class="h-2 bg-slate-200 rounded-full w-4/5"></div>
                    </div>

                    <!-- Radio Option 1 -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-xl border border-slate-200 bg-white">
                        <span class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center flex-shrink-0"></span>
                        <div class="h-2 bg-slate-200 rounded-full w-3/4"></div>
                    </div>

                    <!-- Radio Option 2 (Selected State with Navy Border & Dot) -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-xl border-2 border-[#1F366A] bg-[#F3F7FD] shadow-xs">
                        <span class="w-4 h-4 rounded-full border-2 border-[#1F366A] flex items-center justify-center flex-shrink-0">
                            <span class="w-2 h-2 rounded-full bg-[#1F366A]"></span>
                        </span>
                        <div class="h-2 bg-slate-300 rounded-full w-2/3"></div>
                    </div>

                    <!-- Radio Option 3 -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-xl border border-slate-200 bg-white">
                        <span class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center flex-shrink-0"></span>
                        <div class="h-2 bg-slate-200 rounded-full w-4/5"></div>
                    </div>

                    <!-- Radio Option 4 -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-xl border border-slate-200 bg-white">
                        <span class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center flex-shrink-0"></span>
                        <div class="h-2 bg-slate-200 rounded-full w-3/5"></div>
                    </div>
                </div>
            </div>

            <!-- Bottom Caption -->
            <p class="text-[11px] text-slate-500 font-medium pt-1">
                Satu jawaban benar dari beberapa pilihan.
            </p>
        </div>

        <!-- CARD 2: Pilihan Ganda Kompleks (MCMA) -->
        <div class="reveal-pop reveal-delay-2 bg-[#F8FAFD] rounded-[2rem] p-5 sm:p-6 border border-slate-200/80 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
            <div>
                <!-- Top Header: Title + Pill Badge -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-[#132A5A]">Pilihan Ganda Kompleks</h3>
                    <span class="bg-[#FFF4DE] text-[#C07E00] text-[10px] font-extrabold px-3 py-1 rounded-full border border-amber-200">
                        MCMA
                    </span>
                </div>

                <!-- White Question Workspace Mockup -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm space-y-3.5 mb-4">
                    <!-- Question lines placeholder -->
                    <div class="space-y-1.5 pb-1">
                        <div class="h-2 bg-slate-200 rounded-full w-2/5"></div>
                        <div class="h-2 bg-slate-200 rounded-full w-4/5"></div>
                    </div>

                    <!-- Checkbox Option 1 (Selected with Gold Border & Checked Box) -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-xl border-2 border-[#D98200] bg-[#FFF8EC] shadow-xs">
                        <span class="w-4 h-4 rounded-md bg-[#BF6A02] text-white flex items-center justify-center text-[10px] flex-shrink-0">
                            <i class="fa-solid fa-check text-[9px]"></i>
                        </span>
                        <div class="h-2 bg-slate-300 rounded-full w-3/4"></div>
                    </div>

                    <!-- Checkbox Option 2 -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-xl border border-slate-200 bg-white">
                        <span class="w-4 h-4 rounded-md border-2 border-slate-300 flex items-center justify-center flex-shrink-0"></span>
                        <div class="h-2 bg-slate-200 rounded-full w-2/3"></div>
                    </div>

                    <!-- Checkbox Option 3 (Selected with Gold Border & Checked Box) -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-xl border-2 border-[#D98200] bg-[#FFF8EC] shadow-xs">
                        <span class="w-4 h-4 rounded-md bg-[#BF6A02] text-white flex items-center justify-center text-[10px] flex-shrink-0">
                            <i class="fa-solid fa-check text-[9px]"></i>
                        </span>
                        <div class="h-2 bg-slate-300 rounded-full w-4/5"></div>
                    </div>

                    <!-- Checkbox Option 4 -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-xl border border-slate-200 bg-white">
                        <span class="w-4 h-4 rounded-md border-2 border-slate-300 flex items-center justify-center flex-shrink-0"></span>
                        <div class="h-2 bg-slate-200 rounded-full w-1/2"></div>
                    </div>
                </div>
            </div>

            <!-- Bottom Caption -->
            <p class="text-[11px] text-slate-500 font-medium pt-1">
                Pilih lebih dari satu jawaban yang tepat (multi-select).
            </p>
        </div>

        <!-- CARD 3: Benar / Salah (True/False Matrix) -->
        <div class="reveal-pop reveal-delay-3 bg-[#F8FAFD] rounded-[2rem] p-5 sm:p-6 border border-slate-200/80 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
            <div>
                <!-- Top Header: Title + Pill Badge -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-[#132A5A]">Benar / Salah</h3>
                    <span class="bg-[#EAF9F1] text-[#00A86B] text-[10px] font-extrabold px-3 py-1 rounded-full border border-emerald-200">
                        True/False
                    </span>
                </div>

                <!-- White Question Workspace Mockup -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm space-y-3.5 mb-4">
                    <!-- Question lines placeholder -->
                    <div class="space-y-1.5 pb-1">
                        <div class="h-2 bg-slate-200 rounded-full w-2/5"></div>
                        <div class="h-2 bg-slate-200 rounded-full w-4/5"></div>
                    </div>

                    <!-- Statement Row 1: Statement bar + Benar (Active Navy) / Salah -->
                    <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-white">
                        <div class="h-2 bg-slate-200 rounded-full w-2/5"></div>
                        <div class="flex items-center space-x-1.5 text-[10px] font-bold">
                            <span class="px-2.5 py-1 rounded-md bg-[#132A5A] text-white">Benar</span>
                            <span class="px-2.5 py-1 rounded-md bg-white border border-slate-200 text-slate-500">Salah</span>
                        </div>
                    </div>

                    <!-- Statement Row 2: Statement bar + Benar / Salah (Active Brown/Gold) -->
                    <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-white">
                        <div class="h-2 bg-slate-200 rounded-full w-1/3"></div>
                        <div class="flex items-center space-x-1.5 text-[10px] font-bold">
                            <span class="px-2.5 py-1 rounded-md bg-white border border-slate-200 text-slate-500">Benar</span>
                            <span class="px-2.5 py-1 rounded-md bg-[#BF6A02] text-white">Salah</span>
                        </div>
                    </div>

                    <!-- Statement Row 3: Statement bar + Benar (Active Navy) / Salah -->
                    <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-white">
                        <div class="h-2 bg-slate-200 rounded-full w-2/5"></div>
                        <div class="flex items-center space-x-1.5 text-[10px] font-bold">
                            <span class="px-2.5 py-1 rounded-md bg-[#132A5A] text-white">Benar</span>
                            <span class="px-2.5 py-1 rounded-md bg-white border border-slate-200 text-slate-500">Salah</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Caption -->
            <p class="text-[11px] text-slate-500 font-medium pt-1">
                Tentukan setiap pernyataan benar atau salah.
            </p>
        </div>

    </div>
</section>

<!-- Call to Action Banner matching reference image -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20 reveal-on-scroll">
    <div class="bg-gradient-to-r from-[#0E4BB5] via-[#1459D6] to-[#0A3D9E] rounded-3xl p-8 sm:p-10 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
        <!-- Abstract diagonal shapes on right side -->
        <div class="absolute right-48 top-1/2 -translate-y-1/2 flex space-x-2 opacity-80 pointer-events-none">
            <span class="w-2.5 h-7 rounded-full bg-[#FFBE2A] transform -rotate-45"></span>
            <span class="w-2.5 h-7 rounded-full bg-blue-300 transform -rotate-45"></span>
        </div>

        <!-- Left: Target icon + Heading -->
        <div class="flex items-center space-x-5 relative z-10">
            <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-2xl text-white shadow-inner flex-shrink-0">
                <i class="fa-regular fa-circle-dot"></i>
            </div>
            <div>
                <h3 class="text-xl sm:text-2xl font-black text-white">Siap Uji Kemampuanmu Sekarang?</h3>
                <p class="text-xs sm:text-sm text-blue-100/90 mt-1">
                    Daftar akun gratis dan ikuti Try Out UTBK-SNBT Akbar bersama ribuan peserta lainnya!
                </p>
            </div>
        </div>

        <!-- Right: Yellow Button -->
        <div class="relative z-10 flex-shrink-0">
            <a href="{{ url('/student/catalog') }}" class="bg-[#FFBE2A] hover:bg-[#E5A817] text-[#1F366A] font-extrabold px-7 py-3.5 rounded-2xl text-xs sm:text-sm shadow-md transition flex items-center space-x-2">
                <span>Mulai Try Out</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</section>
@endsection
