@extends('layouts.admin')

@section('title', 'Kelola Rekening, E-Wallet & QRIS - Bimbel Cemerlang')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center space-x-2 bg-amber-50 text-amber-900 border border-amber-300 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider mb-2">
                <i class="fa-solid fa-wallet text-amber-600"></i>
                <span>Metode Pembayaran Siswa</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black font-serif text-[#1F366A]">Kelola Rekening, E-Wallet & QRIS</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Atur tujuan transfer bank, nomor e-wallet (GoPay, ShopeePay, DANA, OVO), dan upload barcode QRIS yang ditampilkan kepada siswa saat checkout paket try out.
            </p>
        </div>
        <a href="{{ url('/student/catalog') }}" target="_blank" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center">
            <i class="fa-solid fa-eye mr-2"></i> Pratinjau di Form Siswa
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border-2 border-emerald-300 text-emerald-900 px-5 py-4 rounded-2xl text-xs font-bold flex items-center space-x-3 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ url('/admin/payment-settings') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- 1. BANK ACCOUNTS SECTION -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#1F366A] flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-[#1F366A] font-serif">Rekening Transfer Bank</h2>
                        <p class="text-xs text-slate-500">Daftar bank tujuan transfer manual (BCA, Mandiri, BRI, BNI, dll)</p>
                    </div>
                </div>
                <button type="button" onclick="addBankRow()" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-[#1F366A] border border-blue-200 rounded-xl text-xs font-bold transition flex items-center">
                    <i class="fa-solid fa-plus mr-1.5"></i> Tambah Bank
                </button>
            </div>

            <div id="bank-list-container" class="space-y-3">
                @forelse($settings['bank_accounts'] ?? [] as $idx => $bank)
                    <div class="bank-row bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col md:flex-row items-center gap-3">
                        <div class="w-full md:w-32">
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Nama Bank</label>
                            <input type="text" name="bank_name[]" value="{{ $bank['bank_name'] ?? '' }}" placeholder="Contoh: BCA" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-[#1F366A] focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Nomor Rekening</label>
                            <input type="text" name="account_number[]" value="{{ $bank['account_number'] ?? '' }}" placeholder="Contoh: 8820-192-888" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
                        </div>
                        <div class="w-full md:flex-1">
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Atas Nama (A.N.)</label>
                            <input type="text" name="account_holder[]" value="{{ $bank['account_holder'] ?? '' }}" placeholder="Contoh: BIMBEL CEMERLANG UTBK" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
                        </div>
                        <div class="w-full md:w-28 flex items-center justify-between md:justify-center pt-2 md:pt-4 space-x-2">
                            <label class="inline-flex items-center space-x-1.5 cursor-pointer text-xs font-bold text-slate-600">
                                <input type="checkbox" name="bank_active[{{ $idx }}]" value="1" {{ !empty($bank['is_active']) ? 'checked' : '' }} class="w-4 h-4 text-[#1F366A] rounded border-slate-300 focus:ring-[#FFBE2A]">
                                <span>Aktif</span>
                            </label>
                            <button type="button" onclick="removeRow(this)" title="Hapus Bank" class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400 text-xs">Belum ada rekening bank yang didaftarkan.</div>
                @endforelse
            </div>
        </div>

        <!-- 2. EWALLET SECTION -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-[#1F366A] font-serif">Nomor Akun E-Wallet</h2>
                        <p class="text-xs text-slate-500">Pilihan pembayaran dompet digital (GoPay, ShopeePay, DANA, OVO, LinkAja)</p>
                    </div>
                </div>
                <button type="button" onclick="addEwalletRow()" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 rounded-xl text-xs font-bold transition flex items-center">
                    <i class="fa-solid fa-plus mr-1.5"></i> Tambah E-Wallet
                </button>
            </div>

            <div id="ewallet-list-container" class="space-y-3">
                @forelse($settings['ewallets'] ?? [] as $idx => $ew)
                    <div class="ewallet-row bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col md:flex-row items-center gap-3">
                        <div class="w-full md:w-36">
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Provider E-Wallet</label>
                            <input type="text" name="ewallet_provider[]" value="{{ $ew['provider'] ?? '' }}" placeholder="Contoh: GoPay / ShopeePay" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-[#1F366A] focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Nomor Handphone / Akun</label>
                            <input type="text" name="ewallet_phone[]" value="{{ $ew['phone_number'] ?? '' }}" placeholder="Contoh: 0812-3456-7890" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
                        </div>
                        <div class="w-full md:flex-1">
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Nama Pemilik Akun (A.N.)</label>
                            <input type="text" name="ewallet_holder[]" value="{{ $ew['account_holder'] ?? '' }}" placeholder="Contoh: Bimbel Cemerlang Official" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
                        </div>
                        <div class="w-full md:w-28 flex items-center justify-between md:justify-center pt-2 md:pt-4 space-x-2">
                            <label class="inline-flex items-center space-x-1.5 cursor-pointer text-xs font-bold text-slate-600">
                                <input type="checkbox" name="ewallet_active[{{ $idx }}]" value="1" {{ !empty($ew['is_active']) ? 'checked' : '' }} class="w-4 h-4 text-[#1F366A] rounded border-slate-300 focus:ring-[#FFBE2A]">
                                <span>Aktif</span>
                            </label>
                            <button type="button" onclick="removeRow(this)" title="Hapus E-Wallet" class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400 text-xs">Belum ada akun e-wallet yang didaftarkan.</div>
                @endforelse
            </div>
        </div>

        <!-- 3. QRIS SECTION (FOTO QRIS & MERCHANT) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-[#1F366A] font-serif">Barcode QRIS Nasional</h2>
                        <p class="text-xs text-slate-500">Scan QRIS otomatis yang mendukung seluruh m-banking dan e-wallet di Indonesia</p>
                    </div>
                </div>
                <label class="inline-flex items-center space-x-2 cursor-pointer bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 transition">
                    <input type="checkbox" name="qris_enabled" value="1" {{ !empty($settings['qris']['enabled']) ? 'checked' : '' }} class="w-4 h-4 text-[#1F366A] rounded border-slate-300 focus:ring-[#FFBE2A]">
                    <span>Aktifkan QRIS</span>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                <!-- QRIS Preview Card -->
                <div class="md:col-span-4 bg-[#FFF7E9] rounded-2xl p-5 border-2 border-amber-300 flex flex-col items-center text-center">
                    <span class="text-[10px] font-black uppercase text-amber-900 tracking-wider mb-2">Pratinjau Gambar QRIS</span>
                    <div class="w-48 h-48 bg-white rounded-xl border border-slate-300 p-2 shadow-inner flex items-center justify-center overflow-hidden mb-3">
                        @if(!empty($settings['qris']['image_url']))
                            <img id="qris-preview-img" src="{{ asset($settings['qris']['image_url']) }}" alt="QRIS Bimbel Cemerlang" class="w-full h-full object-contain">
                        @else
                            <div id="qris-preview-img" class="text-slate-400 text-xs flex flex-col items-center">
                                <i class="fa-solid fa-qrcode text-4xl mb-2 text-slate-300"></i>
                                <span>Belum ada foto QRIS</span>
                            </div>
                        @endif
                    </div>
                    <span class="text-xs font-black text-[#1F366A]">{{ $settings['qris']['merchant_name'] ?? 'BIMBEL CEMERLANG' }}</span>
                    <span class="text-[10px] text-slate-500 mt-1">Siswa dapat langsung mengunduh atau memindai barcode ini.</span>
                </div>

                <!-- QRIS Inputs -->
                <div class="md:col-span-8 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">
                            Upload Foto / Barcode QRIS Baru
                        </label>
                        <input type="file" name="qris_image" id="input-qris-file" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#1F366A] file:text-white hover:file:bg-[#132347] border border-slate-300 rounded-xl p-1 bg-white">
                        <span class="text-[10px] text-slate-400 mt-1 block">Format: JPG, PNG, WEBP (Disarankan rasio kotak / persegi 1:1, Maks 4MB)</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">
                            Nama Merchant / NMID
                        </label>
                        <input type="text" name="qris_merchant_name" value="{{ $settings['qris']['merchant_name'] ?? 'QRIS BIMBEL CEMERLANG' }}" placeholder="Contoh: QRIS BIMBEL CEMERLANG (NMID: ID1020039281)" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-[#1F366A] focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">
                            Keterangan / Catatan QRIS
                        </label>
                        <textarea name="qris_notes" rows="2" placeholder="Contoh: Mendukung semua bank & e-wallet (BCA Mobile, Livin, GoPay, ShopeePay, DANA, OVO)" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">{{ $settings['qris']['notes'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. INSTRUCTIONS -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div>
                    <h2 class="text-lg font-extrabold text-[#1F366A] font-serif">Instruksi Pembayaran Siswa</h2>
                    <p class="text-xs text-slate-500">Teks petunjuk yang dibaca siswa pada pop-up konfirmasi pembayaran</p>
                </div>
            </div>

            <textarea name="instructions" rows="3" class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none leading-relaxed">{{ $settings['instructions'] ?? '' }}</textarea>
        </div>

        <!-- Submit Button Bar -->
        <div class="sticky bottom-6 z-20 bg-white/90 backdrop-blur-md p-4 rounded-2xl border-2 border-slate-200 shadow-xl flex items-center justify-between">
            <span class="text-xs text-slate-500 font-semibold">Pastikan seluruh nomor rekening dan foto QRIS sudah dicek dengan benar.</span>
            <button type="submit" class="btn-cemerlang-yellow px-8 py-3 rounded-xl font-black text-xs sm:text-sm shadow-md flex items-center space-x-2">
                <i class="fa-solid fa-floppy-disk text-base"></i>
                <span>Simpan Pengaturan Pembayaran</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Preview QRIS when new image chosen
    document.getElementById('input-qris-file')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('qris-preview-img');
                if (preview) {
                    if (preview.tagName.toLowerCase() === 'img') {
                        preview.src = event.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.id = 'qris-preview-img';
                        img.src = event.target.result;
                        img.className = 'w-full h-full object-contain';
                        preview.parentNode.replaceChild(img, preview);
                    }
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // Dynamic row addition for Banks
    function addBankRow() {
        const container = document.getElementById('bank-list-container');
        const rows = container.querySelectorAll('.bank-row');
        const nextIdx = rows.length + 1;

        const row = document.createElement('div');
        row.className = 'bank-row bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col md:flex-row items-center gap-3 animate-scaleIn';
        row.innerHTML = `
            <div class="w-full md:w-32">
                <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Nama Bank</label>
                <input type="text" name="bank_name[]" placeholder="Contoh: BNI" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-[#1F366A] focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
            </div>
            <div class="w-full md:w-56">
                <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Nomor Rekening</label>
                <input type="text" name="account_number[]" placeholder="Contoh: 0123-456-789" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
            </div>
            <div class="w-full md:flex-1">
                <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Atas Nama (A.N.)</label>
                <input type="text" name="account_holder[]" placeholder="Contoh: BIMBEL CEMERLANG" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
            </div>
            <div class="w-full md:w-28 flex items-center justify-between md:justify-center pt-2 md:pt-4 space-x-2">
                <label class="inline-flex items-center space-x-1.5 cursor-pointer text-xs font-bold text-slate-600">
                    <input type="checkbox" name="bank_active[${nextIdx}]" value="1" checked class="w-4 h-4 text-[#1F366A] rounded border-slate-300 focus:ring-[#FFBE2A]">
                    <span>Aktif</span>
                </label>
                <button type="button" onclick="removeRow(this)" title="Hapus Bank" class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
    }

    // Dynamic row addition for E-Wallets
    function addEwalletRow() {
        const container = document.getElementById('ewallet-list-container');
        const rows = container.querySelectorAll('.ewallet-row');
        const nextIdx = rows.length + 1;

        const row = document.createElement('div');
        row.className = 'ewallet-row bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col md:flex-row items-center gap-3 animate-scaleIn';
        row.innerHTML = `
            <div class="w-full md:w-36">
                <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Provider E-Wallet</label>
                <input type="text" name="ewallet_provider[]" placeholder="Contoh: ShopeePay / GoPay" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-[#1F366A] focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
            </div>
            <div class="w-full md:w-56">
                <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Nomor Handphone / Akun</label>
                <input type="text" name="ewallet_phone[]" placeholder="Contoh: 0812-xxxx-xxxx" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
            </div>
            <div class="w-full md:flex-1">
                <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Nama Pemilik Akun (A.N.)</label>
                <input type="text" name="ewallet_holder[]" placeholder="Contoh: Bimbel Cemerlang" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-[#FFBE2A] focus:outline-none">
            </div>
            <div class="w-full md:w-28 flex items-center justify-between md:justify-center pt-2 md:pt-4 space-x-2">
                <label class="inline-flex items-center space-x-1.5 cursor-pointer text-xs font-bold text-slate-600">
                    <input type="checkbox" name="ewallet_active[${nextIdx}]" value="1" checked class="w-4 h-4 text-[#1F366A] rounded border-slate-300 focus:ring-[#FFBE2A]">
                    <span>Aktif</span>
                </label>
                <button type="button" onclick="removeRow(this)" title="Hapus E-Wallet" class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
    }

    function removeRow(btn) {
        const row = btn.closest('.bank-row, .ewallet-row');
        if (row) {
            row.remove();
        }
    }
</script>
@endpush
@endsection
