@extends('layouts.admin')

@section('title', 'Manajemen Paket Try Out - Bimbel Cemerlang')
@section('header_title', 'Manajemen Paket Try Out')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
        <div>
            <h3 class="text-lg font-black font-serif text-[#1F366A]">Daftar Paket Simulasi Try Out</h3>
            <p class="text-xs text-slate-500">Atur judul paket, durasi timer ujian, standar passing grade, dan biaya pendaftaran.</p>
        </div>
        <button onclick="openCreatePackageModal()" class="btn-cemerlang-yellow text-xs font-black px-5 py-3 rounded-xl shadow flex items-center">
            <i class="fa-solid fa-plus mr-2"></i> Buat Paket Baru
        </button>
    </div>

    <!-- Packages Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-[#1F366A] text-white text-xs uppercase font-extrabold tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5">Judul & Kategori</th>
                        <th class="py-3.5 px-4 text-center">Durasi Timer</th>
                        <th class="py-3.5 px-4 text-center">Passing Grade</th>
                        <th class="py-3.5 px-4 text-center">Harga</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Soal</th>
                        <th class="py-3.5 px-4 text-center">Toleransi</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($packages as $pkg)
                        @php $qCount = $questionsCount->get($pkg['id'], 0); @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-5">
                                <span class="bg-[#FFF7E9] text-[#1F366A] border border-amber-300 text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider mb-1 inline-block">
                                    {{ $pkg['category'] }}
                                </span>
                                <h4 class="font-extrabold text-[#1F366A] text-sm">{{ $pkg['title'] }}</h4>
                                <p class="text-[11px] text-slate-500 line-clamp-1 max-w-md">{{ $pkg['description'] }}</p>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="font-bold text-[#1F366A]">{{ $pkg['duration_minutes'] }}</span>
                                <span class="text-[10px] text-slate-400 block">Menit</span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                    {{ $pkg['passing_grade'] }}%
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center font-bold">
                                @if($pkg['price'] == 0)
                                    <span class="text-emerald-600 font-black">GRATIS</span>
                                @else
                                    <span class="text-slate-800">Rp {{ number_format($pkg['price'], 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center font-bold text-[#1F366A]">
                                <span class="text-sm font-black">{{ $qCount }}</span>
                                <span class="text-[10px] text-slate-400 block">Soal</span>
                            </td>
                            <td class="py-4 px-4 text-center text-rose-700 font-bold">
                                {{ $pkg['max_violations'] ?? 3 }}x
                            </td>
                            <td class="py-4 px-5 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ url('/admin/packages/' . $pkg['id'] . '/questions') }}" class="btn-cemerlang-navy px-3 py-1.5 rounded-lg text-xs font-bold inline-flex items-center shadow-sm">
                                    <i class="fa-solid fa-list-check mr-1.5 text-[#FFBE2A]"></i> Kelola Soal ({{ $qCount }})
                                </a>
                                <button onclick="openEditPackageModal({{ json_encode($pkg) }})" class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 text-xs" title="Edit Paket">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form method="POST" action="{{ url('/admin/packages/' . $pkg['id'] . '/delete') }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini beserta semua soalnya?')">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:text-rose-800 hover:bg-rose-50 text-xs" title="Hapus Paket">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-slate-400">
                                Belum ada paket try out. Silakan buat paket baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create / Edit Package -->
<div id="package-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border-4 border-[#FFBE2A] relative overflow-y-auto max-h-[90vh]">
        <button onclick="closePackageModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-lg">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <h3 id="modal-pkg-heading" class="text-xl font-black font-serif text-[#1F366A] mb-1">Buat Paket Try Out Baru</h3>
        <p class="text-xs text-slate-500 mb-6">Isi konfigurasi waktu timer, ambang passing grade, dan harga try out.</p>

        <form id="pkg-form" method="POST" action="{{ url('/admin/packages') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Judul Paket Try Out</label>
                <input type="text" name="title" id="form-pkg-title" required placeholder="Contoh: Try Out Akbar UTBK-SNBT 2026 Gelombang 1" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Kategori Ujian</label>
                    <select name="category" id="form-pkg-category" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                        <option value="SNBT">SNBT (Seleksi Nasional Berdasarkan Tes)</option>
                        <option value="TKA">TKA (Tes Kemampuan Akademik)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Harga (Rp - 0 = Gratis)</label>
                    <input type="number" name="price" id="form-pkg-price" required min="0" step="5000" value="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Timer (Menit)</label>
                    <input type="number" name="duration_minutes" id="form-pkg-duration" required min="5" max="360" value="60" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Passing Grade IRT</label>
                    <input type="number" name="passing_grade" id="form-pkg-passing" required min="1" max="1000" value="650" placeholder="Skala IRT 200 - 1000" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Max Pelanggaran</label>
                    <input type="number" name="max_violations" id="form-pkg-violations" required min="1" max="10" value="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1">Deskripsi Singkat Paket</label>
                <textarea name="description" id="form-pkg-desc" rows="3" required placeholder="Jelaskan cakupan materi, kisi-kisi, atau petunjuk pengerjaan..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFBE2A]"></textarea>
            </div>

            <div class="flex items-center space-x-2 pt-1">
                <input type="checkbox" name="is_active" id="form-pkg-active" value="1" checked class="w-4 h-4 rounded text-[#1F366A]">
                <label for="form-pkg-active" class="text-xs font-bold text-slate-700">Publikasikan Paket Ini ke Siswa (Status Aktif)</label>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closePackageModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="btn-cemerlang-yellow px-6 py-2.5 rounded-xl text-xs font-black shadow">
                    Simpan Konfigurasi Paket
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreatePackageModal() {
        document.getElementById('modal-pkg-heading').innerText = 'Buat Paket Try Out Baru (Fokus SNBT & TKA)';
        const form = document.getElementById('pkg-form');
        form.action = '/admin/packages';

        document.getElementById('form-pkg-title').value = '';
        document.getElementById('form-pkg-category').value = 'SNBT';
        document.getElementById('form-pkg-price').value = 0;
        document.getElementById('form-pkg-duration').value = 60;
        document.getElementById('form-pkg-passing').value = 650;
        document.getElementById('form-pkg-violations').value = 3;
        document.getElementById('form-pkg-desc').value = '';
        document.getElementById('form-pkg-active').checked = true;

        document.getElementById('package-modal').classList.remove('hidden');
    }

    function openEditPackageModal(pkg) {
        document.getElementById('modal-pkg-heading').innerText = 'Edit Konfigurasi Paket Try Out';
        const form = document.getElementById('pkg-form');
        form.action = '/admin/packages/' + pkg.id + '/update';

        document.getElementById('form-pkg-title').value = pkg.title;
        let cat = pkg.category;
        if (cat === 'UTBK SNBT' || cat === 'SNBT') {
            cat = 'SNBT';
        } else if (cat === 'TKA' || cat === 'CPNS' || cat === 'Kedinasan') {
            cat = 'TKA';
        }
        document.getElementById('form-pkg-category').value = cat;
        document.getElementById('form-pkg-price').value = pkg.price;
        document.getElementById('form-pkg-duration').value = pkg.duration_minutes;
        document.getElementById('form-pkg-passing').value = pkg.passing_grade;
        document.getElementById('form-pkg-violations').value = pkg.max_violations || 3;
        document.getElementById('form-pkg-desc').value = pkg.description;
        document.getElementById('form-pkg-active').checked = !!pkg.is_active;

        document.getElementById('package-modal').classList.remove('hidden');
    }

    function closePackageModal() {
        document.getElementById('package-modal').classList.add('hidden');
    }
</script>
@endpush
@endsection
