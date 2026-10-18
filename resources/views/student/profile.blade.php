@extends('layouts.cemerlang')

@section('title', 'Edit Profil Siswa - Bimbel Cemerlang')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center space-x-2 text-xs font-bold text-slate-500 mb-1">
                <a href="{{ url('/student/dashboard') }}" class="hover:text-[#1F366A]"><i class="fa-solid fa-graduation-cap mr-1"></i> Dashboard</a>
                <span>/</span>
                <span class="text-[#1F366A]">Edit Profil</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-serif text-[#1F366A]">Profil Siswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Perbarui foto profil, informasi akun, data sekolah, dan kata sandi Anda</p>
        </div>
        <div>
            <a href="{{ url('/student/dashboard') }}" class="inline-flex items-center text-xs font-bold text-[#1F366A] bg-white hover:bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm transition">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Profile Card with Avatar Change -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-3xl p-6 shadow-md border-2 border-slate-200 text-center relative overflow-hidden">
                <!-- Avatar Display & Change Button -->
                <div class="relative w-28 h-28 mx-auto mb-4 group">
                    <div id="avatar-preview-container" class="w-28 h-28 rounded-3xl overflow-hidden shadow-md border-4 border-white flex items-center justify-center bg-gradient-to-br from-[#FFBE2A] to-[#E5A817] text-[#1F366A]">
                        @if(!empty($student['avatar']))
                            <img id="avatar-img-preview" src="{{ asset($student['avatar']) }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            <img id="avatar-img-preview" src="" alt="Avatar" class="w-full h-full object-cover hidden">
                            <span id="avatar-initials-preview" class="font-black text-4xl">
                                {{ strtoupper(substr($student['name'] ?? 'S', 0, 1)) }}
                            </span>
                        @endif
                    </div>

                    <!-- Overlay trigger button -->
                    <button type="button" onclick="openPhotoModal()" class="absolute inset-0 bg-black/50 text-white rounded-3xl opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center text-xs font-bold space-y-1 backdrop-blur-xs">
                        <i class="fa-solid fa-camera text-xl"></i>
                        <span>Ganti Foto</span>
                    </button>

                    <!-- Small edit badge -->
                    <button type="button" onclick="openPhotoModal()" class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-[#1F366A] text-[#FFBE2A] flex items-center justify-center shadow-lg border-2 border-white hover:scale-110 transition" title="Ganti Foto Profil">
                        <i class="fa-solid fa-camera text-xs"></i>
                    </button>
                </div>

                <h3 class="text-lg font-extrabold text-[#1F366A] font-serif leading-tight mt-1">{{ $student['name'] ?? 'Siswa' }}</h3>
                <span class="inline-block bg-[#FFF7E9] text-[#1F366A] border border-amber-300 text-[10px] font-bold px-3 py-0.5 rounded-full uppercase tracking-wider mt-1.5">
                    Peserta Try Out Aktif
                </span>

                <div class="mt-6 pt-5 border-t border-slate-100 text-left space-y-3 text-xs">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">ID Pengguna</span>
                        <span class="font-mono font-bold text-slate-700">{{ $student['id'] ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">Terdaftar Sejak</span>
                        <span class="font-bold text-slate-700">{{ isset($student['created_at']) ? date('d M Y', strtotime($student['created_at'])) : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">Status Akun</span>
                        <span class="inline-flex items-center text-emerald-700 font-bold text-[11px]">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span> Aktif & Terverifikasi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-[#FFF7E9] border border-amber-200/90 rounded-2xl p-4 text-xs text-[#1F366A] space-y-1.5">
                <div class="flex items-center space-x-2 font-bold text-amber-900">
                    <i class="fa-solid fa-circle-info text-amber-600"></i>
                    <span>Informasi Sertifikat & Nilai</span>
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Nama lengkap dan foto profil yang Anda cantumkan di sini akan otomatis tertera pada hasil evaluasi ujian dan riwayat skor try out.
                </p>
            </div>
        </div>

        <!-- Main Form Column -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-md border-2 border-slate-200">
                <form id="form-edit-profile" method="POST" action="{{ url('/student/profile') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Hidden Inputs for Avatar -->
                    <input type="file" id="input-avatar-file" name="avatar_file" accept="image/*" class="hidden">
                    <input type="hidden" id="input-avatar-snapshot" name="avatar_snapshot" value="">

                    <div>
                        <h4 class="text-base font-extrabold text-[#1F366A] font-serif border-b pb-2 mb-4 flex items-center">
                            <i class="fa-solid fa-user-pen mr-2 text-[#FFBE2A]"></i> Data Diri Siswa
                        </h4>

                        <div class="space-y-4">
                            <!-- Nama Lengkap -->
                            <div>
                                <label for="name" class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">
                                    Nama Lengkap <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <i class="fa-solid fa-user"></i>
                                    </span>
                                    <input type="text" name="name" id="name" required value="{{ old('name', $student['name'] ?? '') }}" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] focus:border-transparent text-sm font-medium">
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">
                                    Alamat Email <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <input type="email" name="email" id="email" required value="{{ old('email', $student['email'] ?? '') }}" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] focus:border-transparent text-sm font-medium">
                                </div>
                            </div>

                            <!-- No WhatsApp / HP -->
                            <div>
                                <label for="phone" class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">
                                    Nomor WhatsApp / HP
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <i class="fa-solid fa-phone"></i>
                                    </span>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone', $student['phone'] ?? '') }}" placeholder="Contoh: 081234567890" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] focus:border-transparent text-sm font-medium">
                                </div>
                            </div>

                            <!-- Asal Sekolah / Instansi -->
                            <div>
                                <label for="school" class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">
                                    Asal Sekolah / Instansi Target
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <i class="fa-solid fa-school"></i>
                                    </span>
                                    <input type="text" name="school" id="school" value="{{ old('school', $student['school'] ?? '') }}" placeholder="Contoh: SMA Negeri 1 Jakarta / Alumni" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] focus:border-transparent text-sm font-medium">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security / Password Section -->
                    <div class="pt-4 border-t border-slate-100">
                        <h4 class="text-base font-extrabold text-[#1F366A] font-serif border-b pb-2 mb-4 flex items-center">
                            <i class="fa-solid fa-lock mr-2 text-[#FFBE2A]"></i> Ganti Kata Sandi (Opsional)
                        </h4>

                        <div>
                            <label for="password" class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">
                                Kata Sandi Baru
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <i class="fa-solid fa-key"></i>
                                </span>
                                <input type="password" name="password" id="password" placeholder="Kosongkan jika tidak ingin mengubah password" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] focus:border-transparent text-sm font-medium">
                            </div>
                            <span class="text-[11px] text-slate-400 mt-1 block">Minimal 4 karakter jika ingin mengganti password akun Anda.</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 flex items-center justify-end space-x-3">
                        <a href="{{ url('/student/dashboard') }}" class="px-5 py-3 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </a>
                        <button type="submit" class="btn-cemerlang-yellow px-7 py-3 rounded-xl text-xs font-extrabold shadow-md hover:shadow-lg inline-flex items-center">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilihan Ganti Foto Profil -->
<div id="modal-photo-picker" class="fixed inset-0 z-[100] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 text-center border-4 border-[#FFBE2A] shadow-2xl animate-scaleIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-5">
            <h3 class="text-lg font-black font-serif text-[#1F366A] flex items-center">
                <i class="fa-solid fa-camera mr-2 text-[#FFBE2A]"></i> Ganti Foto Profil
            </h3>
            <button type="button" onclick="closePhotoModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <p class="text-xs text-slate-500 mb-6">
            Pilih cara Anda ingin memperbarui foto profil siswa:
        </p>

        <!-- Pilihan Mode: Galeri atau Kamera -->
        <div id="photo-options-view" class="grid grid-cols-2 gap-4 mb-4">
            <!-- Option 1: Ambil dari Galeri / File -->
            <button type="button" onclick="triggerFilePicker()" class="bg-[#FFF7E9] hover:bg-amber-100/70 p-5 rounded-2xl border-2 border-amber-300 text-center transition group">
                <div class="w-14 h-14 rounded-2xl bg-white text-[#1F366A] flex items-center justify-center text-2xl mx-auto mb-3 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-images text-amber-500"></i>
                </div>
                <h4 class="font-extrabold text-sm text-[#1F366A]">Pilih dari Galeri</h4>
                <p class="text-[11px] text-slate-500 mt-1">Upload file JPG, PNG, atau WEBP</p>
            </button>

            <!-- Option 2: Ambil Foto Langsung via Webcam -->
            <button type="button" onclick="startCameraCapture()" class="bg-[#FFF7E9] hover:bg-amber-100/70 p-5 rounded-2xl border-2 border-amber-300 text-center transition group">
                <div class="w-14 h-14 rounded-2xl bg-white text-[#1F366A] flex items-center justify-center text-2xl mx-auto mb-3 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-camera text-[#1F366A]"></i>
                </div>
                <h4 class="font-extrabold text-sm text-[#1F366A]">Ambil Foto</h4>
                <p class="text-[11px] text-slate-500 mt-1">Gunakan kamera perangkat langsung</p>
            </button>
        </div>

        <!-- Camera Live Capture View (Initially Hidden) -->
        <div id="camera-capture-view" class="hidden space-y-4">
            <div class="relative w-full aspect-square max-w-[280px] mx-auto bg-slate-900 rounded-2xl overflow-hidden border-2 border-[#1F366A] shadow-inner">
                <video id="webcam-stream" autoplay playsinline muted class="w-full h-full object-cover"></video>
                <canvas id="snapshot-canvas" class="hidden"></canvas>
                <div id="camera-loading" class="absolute inset-0 flex items-center justify-center text-white text-xs font-semibold bg-slate-900/80">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Mengaktifkan kamera...
                </div>
            </div>

            <div class="flex items-center justify-center space-x-3">
                <button type="button" onclick="cancelCameraCapture()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">
                    Batal
                </button>
                <button type="button" onclick="takeSnapshot()" class="btn-cemerlang-yellow px-6 py-2.5 rounded-xl text-xs font-extrabold shadow flex items-center">
                    <i class="fa-solid fa-circle-dot mr-1.5 text-rose-600 animate-pulse"></i> Jepret Foto
                </button>
            </div>
        </div>

        <button type="button" onclick="closePhotoModal()" class="w-full py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 border border-slate-200 mt-2">
            Tutup
        </button>
    </div>
</div>

@push('scripts')
<script>
    let localStream = null;

    function openPhotoModal() {
        document.getElementById('modal-photo-picker').classList.remove('hidden');
        document.getElementById('photo-options-view').classList.remove('hidden');
        document.getElementById('camera-capture-view').classList.add('hidden');
    }

    function closePhotoModal() {
        stopCamera();
        document.getElementById('modal-photo-picker').classList.add('hidden');
    }

    // 1. Ambil dari Galeri
    function triggerFilePicker() {
        const fileInput = document.getElementById('input-avatar-file');
        fileInput.onchange = function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    applyPreview(evt.target.result);
                    // Clear snapshot field because file was picked
                    document.getElementById('input-avatar-snapshot').value = '';
                    closePhotoModal();
                };
                reader.readAsDataURL(file);
            }
        };
        fileInput.click();
    }

    // 2. Ambil Foto via Kamera
    async function startCameraCapture() {
        document.getElementById('photo-options-view').classList.add('hidden');
        document.getElementById('camera-capture-view').classList.remove('hidden');
        document.getElementById('camera-loading').classList.remove('hidden');

        try {
            localStream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 640 }, facingMode: 'user' },
                audio: false
            });
            const videoEl = document.getElementById('webcam-stream');
            videoEl.srcObject = localStream;
            videoEl.onloadedmetadata = () => {
                document.getElementById('camera-loading').classList.add('hidden');
            };
        } catch (err) {
            alert('Tidak dapat mengakses kamera perangkat. Pastikan izin kamera aktif pada browser Anda.');
            cancelCameraCapture();
        }
    }

    function stopCamera() {
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }
    }

    function cancelCameraCapture() {
        stopCamera();
        document.getElementById('camera-capture-view').classList.add('hidden');
        document.getElementById('photo-options-view').classList.remove('hidden');
    }

    function takeSnapshot() {
        const video = document.getElementById('webcam-stream');
        const canvas = document.getElementById('snapshot-canvas');
        if (!video || !localStream) return;

        const size = Math.min(video.videoWidth || 480, video.videoHeight || 480);
        canvas.width = size;
        canvas.height = size;

        const ctx = canvas.getContext('2d');
        // Center crop square
        const startX = ((video.videoWidth || size) - size) / 2;
        const startY = ((video.videoHeight || size) - size) / 2;
        ctx.drawImage(video, startX, startY, size, size, 0, 0, size, size);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        applyPreview(dataUrl);

        // Set snapshot into form and clear file input
        document.getElementById('input-avatar-snapshot').value = dataUrl;
        document.getElementById('input-avatar-file').value = '';

        stopCamera();
        closePhotoModal();
    }

    function applyPreview(url) {
        const imgEl = document.getElementById('avatar-img-preview');
        const initialsEl = document.getElementById('avatar-initials-preview');
        if (imgEl) {
            imgEl.src = url;
            imgEl.classList.remove('hidden');
        }
        if (initialsEl) {
            initialsEl.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection
