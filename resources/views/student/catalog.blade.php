@extends('layouts.cemerlang')

@section('title', 'Katalog Paket Try Out - Bimbel Cemerlang')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#1F366A] to-[#132347] text-white rounded-3xl p-8 mb-10 shadow-xl border-4 border-[#FFBE2A] flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <div class="inline-block bg-[#FFBE2A] text-[#1F366A] text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider mb-2">
                Katalog Resmi
            </div>
            <h1 class="text-3xl font-black font-serif">Katalog Paket Try Out Online</h1>
            <p class="text-slate-200 text-sm mt-1 max-w-xl">
                Pilih paket simulasi try out sesuai jenjang dan target impianmu. Dilengkapi timer pengerjaan real-time dan evaluasi skor instan.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ url('/student/dashboard') }}" class="btn-cemerlang-yellow text-xs font-bold px-5 py-3 rounded-xl shadow">
                <i class="fa-solid fa-graduation-cap mr-2"></i> Dashboard Saya
            </a>
        </div>
    </div>

    <!-- Package Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @forelse($packages as $pkg)
            @php
                $order = $studentOrders->get($pkg['id']);
                $isApproved = $order && $order['status'] === 'approved';
                $isPending = $order && $order['status'] === 'pending';
                $qCount = $questionsCount->get($pkg['id'], 0);
            @endphp
            <div class="bg-white rounded-3xl shadow-md border-2 border-slate-200/80 hover:border-[#FFBE2A] transition duration-300 flex flex-col justify-between overflow-hidden group hover:shadow-2xl">
                <div>
                    <!-- Header Card -->
                    <div class="bg-[#FFF7E9] p-6 border-b border-amber-200/60 relative">
                        <span class="inline-block bg-[#1F366A] text-[#FFBE2A] text-[11px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider mb-2">
                            {{ $pkg['category'] }}
                        </span>
                        <h3 class="text-xl font-black font-serif text-[#1F366A] group-hover:text-[#2A4B8D] transition line-clamp-2">
                            {{ $pkg['title'] }}
                        </h3>
                    </div>

                    <!-- Body Details -->
                    <div class="p-6 space-y-4">
                        <p class="text-slate-600 text-xs sm:text-sm leading-relaxed line-clamp-3">
                            {{ $pkg['description'] }}
                        </p>

                        <!-- Stats Pills -->
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60 flex items-center space-x-2">
                                <i class="fa-solid fa-clock text-[#FFBE2A] text-sm"></i>
                                <div>
                                    <span class="block text-[10px] text-slate-500 font-bold uppercase">Durasi</span>
                                    <span class="font-extrabold text-[#1F366A]">{{ $pkg['duration_minutes'] }} Menit</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60 flex items-center space-x-2">
                                <i class="fa-solid fa-list-ol text-[#FFBE2A] text-sm"></i>
                                <div>
                                    <span class="block text-[10px] text-slate-500 font-bold uppercase">Total Soal</span>
                                    <span class="font-extrabold text-[#1F366A]">{{ $qCount }} Butir</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60 flex items-center space-x-2">
                                <i class="fa-solid fa-bullseye text-[#FFBE2A] text-sm"></i>
                                <div>
                                    <span class="block text-[10px] text-slate-500 font-bold uppercase">Passing Grade</span>
                                    <span class="font-extrabold text-emerald-700">{{ $pkg['passing_grade'] }}%</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60 flex items-center space-x-2">
                                <i class="fa-solid fa-book-open text-[#FFBE2A] text-sm"></i>
                                <div>
                                    <span class="block text-[10px] text-slate-500 font-bold uppercase">Pembahasan</span>
                                    <span class="font-extrabold text-[#1F366A]">Tersedia</span>
                                </div>
                            </div>
                        </div>

                        <!-- Price Tag -->
                        <div class="pt-2 flex items-baseline justify-between border-t border-slate-100">
                            <span class="text-xs text-slate-500 font-bold">Biaya Investasi:</span>
                            @if($pkg['price'] == 0)
                                <span class="text-lg font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full">GRATIS</span>
                            @else
                                <span class="text-xl font-black text-[#1F366A]">Rp {{ number_format($pkg['price'], 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="p-6 pt-0">
                    @if($isApproved)
                        <a href="{{ url('/exam/' . $pkg['id']) }}" class="btn-cemerlang-yellow w-full py-3.5 rounded-xl font-bold text-sm text-center block shadow">
                            <i class="fa-solid fa-play mr-2"></i> Mulai Ujian Sekarang
                        </a>
                    @elseif($isPending)
                        <div class="bg-amber-100 border border-amber-300 text-amber-900 p-3 rounded-xl text-center text-xs font-bold">
                            <i class="fa-solid fa-clock-rotate-left mr-1"></i> Menunggu Verifikasi Admin
                        </div>
                    @else
                        <button onclick="openCheckoutModal('{{ $pkg['id'] }}', '{{ addslashes($pkg['title']) }}', {{ $pkg['price'] }})" class="btn-cemerlang-navy w-full py-3.5 rounded-xl font-bold text-sm text-center block shadow hover:shadow-lg transition">
                            <i class="fa-solid fa-cart-shopping mr-2 text-[#FFBE2A]"></i> Ambil Paket Try Out
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-16 bg-white rounded-3xl border-2 border-slate-200">
                <i class="fa-solid fa-box-open text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-lg font-bold text-slate-700">Belum Ada Paket Tersedia</h3>
                <p class="text-xs text-slate-500">Administrator belum menambahkan paket try out aktif.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkout-modal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border-4 border-[#FFBE2A] relative my-6 animate-scaleIn max-h-[92vh] flex flex-col justify-between">
        <button onclick="closeCheckoutModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-lg p-1">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="overflow-y-auto pr-1">
            <div class="flex items-center space-x-2 text-xs font-black uppercase text-amber-700 mb-1">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Konfirmasi Checkout Paket</span>
            </div>
            <h3 class="text-xl font-black font-serif text-[#1F366A]">Pilih Metode Pembayaran</h3>
            <p class="text-xs text-slate-500 mb-4">Transfer sesuai nominal dan unggah bukti transaksi Anda.</p>

            <form id="checkout-form" method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <!-- Summary Card -->
                <div class="bg-[#FFF7E9] p-4 rounded-2xl border border-amber-200">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Paket yang Dipilih:</span>
                    <span id="modal-package-title" class="text-sm font-extrabold text-[#1F366A] block mt-0.5">Judul Paket</span>
                    <div class="mt-2.5 pt-2 border-t border-amber-200/80 flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-600">Total Biaya:</span>
                        <span id="modal-package-price" class="font-black text-lg text-[#1F366A]">Rp 0</span>
                    </div>
                </div>

                <!-- Free vs Paid Fields -->
                <div id="paid-payment-fields" class="space-y-4">
                    <!-- Tab Selector: Bank vs E-Wallet vs QRIS -->
                    <div>
                        <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-2">Tujuan Pembayaran:</label>
                        <div class="grid grid-cols-3 gap-2 p-1 bg-slate-100 rounded-2xl text-xs font-bold text-center">
                            <button type="button" onclick="switchPayTab('bank')" id="tab-btn-bank" class="py-2 rounded-xl transition bg-[#1F366A] text-white shadow">
                                <i class="fa-solid fa-building-columns mr-1"></i> Transfer Bank
                            </button>
                            <button type="button" onclick="switchPayTab('ewallet')" id="tab-btn-ewallet" class="py-2 rounded-xl transition text-slate-600 hover:text-slate-900">
                                <i class="fa-solid fa-mobile-screen mr-1"></i> E-Wallet
                            </button>
                            <button type="button" onclick="switchPayTab('qris')" id="tab-btn-qris" class="py-2 rounded-xl transition text-slate-600 hover:text-slate-900">
                                <i class="fa-solid fa-qrcode mr-1"></i> QRIS
                            </button>
                        </div>
                    </div>

                    <!-- TAB CONTENT: BANK -->
                    <div id="pay-content-bank" class="space-y-2">
                        @php
                            $activeBanks = collect($paymentSettings['bank_accounts'] ?? [])->where('is_active', true)->values();
                        @endphp
                        @forelse($activeBanks as $b)
                            <div class="bg-blue-50/70 border border-blue-200 p-3 rounded-2xl flex items-center justify-between text-xs">
                                <div>
                                    <span class="inline-block bg-[#1F366A] text-white text-[10px] font-black px-2 py-0.5 rounded-md uppercase mb-1">
                                        {{ $b['bank_name'] }}
                                    </span>
                                    <div class="font-mono font-black text-sm text-[#1F366A] tracking-wider">{{ $b['account_number'] }}</div>
                                    <span class="text-[11px] text-slate-500 font-semibold">a.n. {{ $b['account_holder'] }}</span>
                                </div>
                                <button type="button" onclick="copyText('{{ $b['account_number'] }}')" title="Salin Rekening" class="px-2.5 py-1.5 bg-white hover:bg-slate-50 text-[#1F366A] border border-blue-300 rounded-xl text-[11px] font-bold shadow-sm transition">
                                    <i class="fa-regular fa-copy mr-1"></i> Salin
                                </button>
                            </div>
                        @empty
                            <div class="p-3 bg-slate-50 rounded-xl text-center text-xs text-slate-400">Tidak ada rekening bank yang aktif saat ini.</div>
                        @endforelse
                    </div>

                    <!-- TAB CONTENT: EWALLET -->
                    <div id="pay-content-ewallet" class="space-y-2 hidden">
                        @php
                            $activeEwallets = collect($paymentSettings['ewallets'] ?? [])->where('is_active', true)->values();
                        @endphp
                        @forelse($activeEwallets as $ew)
                            <div class="bg-amber-50/70 border border-amber-200 p-3 rounded-2xl flex items-center justify-between text-xs">
                                <div>
                                    <span class="inline-block bg-[#FFBE2A] text-[#1F366A] text-[10px] font-black px-2 py-0.5 rounded-md uppercase mb-1">
                                        {{ $ew['provider'] }}
                                    </span>
                                    <div class="font-mono font-black text-sm text-[#1F366A] tracking-wider">{{ $ew['phone_number'] }}</div>
                                    <span class="text-[11px] text-slate-500 font-semibold">a.n. {{ $ew['account_holder'] }}</span>
                                </div>
                                <button type="button" onclick="copyText('{{ $ew['phone_number'] }}')" title="Salin Nomor" class="px-2.5 py-1.5 bg-white hover:bg-slate-50 text-[#1F366A] border border-amber-300 rounded-xl text-[11px] font-bold shadow-sm transition">
                                    <i class="fa-regular fa-copy mr-1"></i> Salin
                                </button>
                            </div>
                        @empty
                            <div class="p-3 bg-slate-50 rounded-xl text-center text-xs text-slate-400">Tidak ada akun e-wallet yang aktif saat ini.</div>
                        @endforelse
                    </div>

                    <!-- TAB CONTENT: QRIS -->
                    <div id="pay-content-qris" class="hidden">
                        @if(!empty($paymentSettings['qris']['enabled']))
                            <div class="bg-[#FFF7E9] border-2 border-amber-300 p-4 rounded-2xl flex flex-col items-center text-center">
                                @if(!empty($paymentSettings['qris']['image_url']))
                                    <div class="w-44 h-44 bg-white p-2 rounded-xl border border-slate-300 shadow-md mb-2 flex items-center justify-center">
                                        <img src="{{ asset($paymentSettings['qris']['image_url']) }}" alt="QRIS Bimbel Cemerlang" class="w-full h-full object-contain">
                                    </div>
                                    <a href="{{ asset($paymentSettings['qris']['image_url']) }}" download="qris_bimbel_cemerlang.png" class="text-[11px] text-[#1F366A] hover:underline font-bold mb-2 inline-flex items-center">
                                        <i class="fa-solid fa-download mr-1"></i> Unduh Barcode QRIS
                                    </a>
                                @else
                                    <div class="w-36 h-36 bg-white p-2 rounded-xl border border-slate-300 shadow-md mb-2 flex flex-col items-center justify-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-qrcode text-3xl mb-1 text-slate-300"></i>
                                        <span>QRIS belum diunggah</span>
                                    </div>
                                @endif
                                <b class="text-xs text-[#1F366A]">{{ $paymentSettings['qris']['merchant_name'] ?? 'BIMBEL CEMERLANG QRIS' }}</b>
                                <p class="text-[10px] text-slate-500 mt-1 max-w-xs leading-relaxed">
                                    {{ $paymentSettings['qris']['notes'] ?? 'Mendukung semua bank & aplikasi dompet digital (GoPay, ShopeePay, DANA, OVO, dll).' }}
                                </p>
                            </div>
                        @else
                            <div class="p-3 bg-slate-50 rounded-xl text-center text-xs text-slate-400">Metode QRIS sedang tidak aktif.</div>
                        @endif
                    </div>

                    <!-- Selected Method for Record -->
                    <div>
                        <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Pilihan Saluran yang Anda Gunakan</label>
                        <select name="payment_method" id="select-payment-method" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] bg-white">
                            <optgroup label="Transfer Bank">
                                @foreach($activeBanks as $b)
                                    <option value="Transfer Bank {{ $b['bank_name'] }} ({{ $b['account_number'] }})">Transfer Bank {{ $b['bank_name'] }} - {{ $b['account_number'] }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="E-Wallet">
                                @foreach($activeEwallets as $ew)
                                    <option value="E-Wallet {{ $ew['provider'] }} ({{ $ew['phone_number'] }})">{{ $ew['provider'] }} - {{ $ew['phone_number'] }}</option>
                                @endforeach
                            </optgroup>
                            @if(!empty($paymentSettings['qris']['enabled']))
                                <optgroup label="QRIS">
                                    <option value="QRIS Nasional All Payment">Scan QRIS Bimbel Cemerlang</option>
                                </optgroup>
                            @endif
                        </select>
                    </div>

                    <!-- Upload Proof -->
                    <div>
                        <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Upload Bukti Transfer / Resi Pembayaran</label>
                        <input type="file" name="payment_proof" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#1F366A] file:text-white hover:file:bg-[#132347] border border-slate-300 rounded-xl p-1 bg-white">
                        <span class="text-[10px] text-slate-400 mt-1 block">Format: JPG, PNG, WEBP (Maks 3MB)</span>
                    </div>

                    @if(!empty($paymentSettings['instructions']))
                        <div class="bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-[11px] text-slate-500 leading-relaxed">
                            <i class="fa-solid fa-circle-info text-amber-500 mr-1"></i>
                            {{ $paymentSettings['instructions'] }}
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <button type="button" onclick="closeCheckoutModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-checkout" class="btn-cemerlang-yellow px-6 py-2.5 rounded-xl text-xs font-extrabold shadow">
                        Konfirmasi & Aktifkan Paket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openCheckoutModal(pkgId, title, price) {
        const modal = document.getElementById('checkout-modal');
        const form = document.getElementById('checkout-form');
        const titleEl = document.getElementById('modal-package-title');
        const priceEl = document.getElementById('modal-package-price');
        const paidFields = document.getElementById('paid-payment-fields');
        const submitBtn = document.getElementById('btn-submit-checkout');

        form.action = '/student/checkout/' + pkgId;
        titleEl.innerText = title;

        if (price === 0) {
            priceEl.innerHTML = '<span class="text-emerald-600 font-extrabold">GRATIS (Rp 0)</span>';
            paidFields.classList.add('hidden');
            submitBtn.innerText = 'Aktifkan Paket Gratis Sekarang';
        } else {
            priceEl.innerText = 'Rp ' + Number(price).toLocaleString('id-ID');
            paidFields.classList.remove('hidden');
            submitBtn.innerText = 'Kirim Bukti & Verifikasi Pembayaran';
        }

        modal.classList.remove('hidden');
    }

    function closeCheckoutModal() {
        document.getElementById('checkout-modal').classList.add('hidden');
    }

    // Tab switcher between Bank, E-Wallet, and QRIS
    function switchPayTab(tab) {
        const bankContent = document.getElementById('pay-content-bank');
        const ewalletContent = document.getElementById('pay-content-ewallet');
        const qrisContent = document.getElementById('pay-content-qris');

        const btnBank = document.getElementById('tab-btn-bank');
        const btnEwallet = document.getElementById('tab-btn-ewallet');
        const btnQris = document.getElementById('tab-btn-qris');

        const activeClass = 'py-2 rounded-xl transition bg-[#1F366A] text-white shadow font-bold';
        const inactiveClass = 'py-2 rounded-xl transition text-slate-600 hover:text-slate-900 font-bold';

        bankContent?.classList.add('hidden');
        ewalletContent?.classList.add('hidden');
        qrisContent?.classList.add('hidden');

        btnBank.className = inactiveClass;
        btnEwallet.className = inactiveClass;
        btnQris.className = inactiveClass;

        if (tab === 'bank') {
            bankContent?.classList.remove('hidden');
            btnBank.className = activeClass;
        } else if (tab === 'ewallet') {
            ewalletContent?.classList.remove('hidden');
            btnEwallet.className = activeClass;
        } else if (tab === 'qris') {
            qrisContent?.classList.remove('hidden');
            btnQris.className = activeClass;
        }
    }

    function copyText(str) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(str).then(() => {
                alert('Tersalin ke clipboard: ' + str);
            }).catch(() => {
                prompt('Salin nomor berikut:', str);
            });
        } else {
            prompt('Salin nomor berikut:', str);
        }
    }
</script>
@endpush
@endsection
