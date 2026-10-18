@extends('layouts.cemerlang')

@section('title', 'Masuk Siswa - Bimbel Cemerlang')

@section('content')
<div class="max-w-md mx-auto my-6 sm:my-12 px-3 sm:px-4">
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-xl border-2 sm:border-4 border-[#FFBE2A] p-5 sm:p-8">
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Bimbel Cemerlang" class="h-20 sm:h-28 mx-auto mb-2 object-contain drop-shadow-md animate-float">
            <h2 class="text-xl sm:text-2xl font-black font-serif text-[#1F366A]">Masuk Peserta Try Out</h2>
            <p class="text-xs text-slate-500 font-semibold mt-1">Gunakan akun Bimbel Cemerlang Anda untuk memulai ujian</p>
        </div>

        <!-- Social Sign-In (Google & Facebook) -->
        <div class="mb-6 space-y-3">
            @if(!empty($googleClientId))
                <div id="g_id_onload"
                     data-client_id="{{ $googleClientId }}"
                     data-context="signin"
                     data-ux_mode="popup"
                     data-callback="handleGoogleCredentialResponse"
                     data-auto_prompt="true"
                     data-cancel_on_tap_outside="true">
                </div>
                <div class="flex justify-center">
                    <div id="google-signin-btn" class="w-full flex justify-center"></div>
                </div>
            @else
                <button type="button" onclick="triggerGoogleDeviceAuth()" class="w-full max-w-[320px] mx-auto flex items-center justify-center gap-2.5 py-2.5 px-4 rounded-full border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold shadow-sm hover:shadow transition group">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span>Masuk dengan Google</span>
                </button>
            @endif

            <div class="flex justify-center">
                <button type="button" onclick="loginWithFacebook()" id="fb-signin-btn" class="w-full max-w-[320px] h-[40px] flex items-center justify-center gap-2 rounded-full bg-[#1877F2] hover:bg-[#166fe5] text-white text-[13px] sm:text-[14px] font-medium transition shadow-xs">
                    <i class="fa-brands fa-facebook text-[18px]"></i>
                    <span>Masuk dengan Facebook</span>
                </button>
            </div>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-[10px] uppercase font-extrabold tracking-widest text-slate-400">
                    <span class="bg-white px-3">Atau Masuk dengan Email</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ url('/login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Alamat Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" id="input-email" required value="{{ old('email') }}" placeholder="nama@email.com" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] focus:border-transparent text-sm font-medium">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#1F366A] uppercase tracking-wider mb-1.5">Kata Sandi (Password)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" id="input-password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFBE2A] focus:border-transparent text-sm font-medium">
                </div>
            </div>

            <button type="submit" class="btn-cemerlang-navy w-full py-3.5 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition">
                Masuk ke Akun Siswa <i class="fa-solid fa-right-to-bracket ml-2 text-[#FFBE2A]"></i>
            </button>
        </form>

        <div class="text-center mt-6 pt-4 border-t border-slate-100 text-xs text-slate-600">
            Belum memiliki akun try out?
            <a href="{{ url('/register') }}" class="font-bold text-[#1F366A] hover:text-[#FFBE2A] ml-1 underline">Daftar sekarang</a>
        </div>
    </div>
</div>

<!-- Google Loading Overlay -->
<div id="google-loading-overlay" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex flex-col items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-6 shadow-2xl flex flex-col items-center max-w-sm text-center">
        <div class="w-12 h-12 border-4 border-[#1F366A] border-t-[#FFBE2A] rounded-full animate-spin mb-4"></div>
        <h4 class="font-extrabold text-[#1F366A] text-sm mb-1">Menghubungkan Akun Google...</h4>
        <p class="text-xs text-slate-500">Mohon tunggu sebentar, sistem sedang memverifikasi identitas Anda.</p>
    </div>
</div>

<!-- Google Identity Services SDK -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
    function showGoogleLoading(show) {
        const overlay = document.getElementById('google-loading-overlay');
        if (overlay) {
            overlay.classList.toggle('hidden', !show);
        }
    }

    function handleGoogleCredentialResponse(response) {
        if (!response.credential) {
            alert('Tidak ada kredensial yang diterima dari Google.');
            return;
        }
        showGoogleLoading(true);

        fetch("{{ url('/auth/google/callback') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ credential: response.credential })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                showGoogleLoading(false);
                alert(data.message || 'Gagal memproses autentikasi Google.');
            }
        })
        .catch(err => {
            console.error(err);
            showGoogleLoading(false);
            alert('Terjadi kendala koneksi ke server saat memverifikasi akun Google.');
        });
    }

    function triggerGoogleDeviceAuth() {
        showGoogleLoading(true);
        fetch("{{ url('/auth/google/callback') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ credential: 'demo_google_one_tap' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                showGoogleLoading(false);
                alert(data.message || 'Gagal masuk dengan akun Google.');
            }
        })
        .catch(err => {
            console.error(err);
            showGoogleLoading(false);
            alert('Terjadi kesalahan koneksi.');
        });
    }

    window.addEventListener('load', function() {
        const clientId = "{{ $googleClientId }}";
        if (clientId && window.google && google.accounts && google.accounts.id) {
            google.accounts.id.initialize({
                client_id: clientId,
                callback: handleGoogleCredentialResponse,
                auto_select: false,
                cancel_on_tap_outside: true
            });

            const btnEl = document.getElementById("google-signin-btn");
            if (btnEl) {
                google.accounts.id.renderButton(
                    btnEl,
                    {
                        theme: "outline",
                        size: "large",
                        shape: "pill",
                        text: "signin_with",
                        logo_alignment: "left",
                        width: 320
                    }
                );
            }

            // Prompt One Tap automatically
            google.accounts.id.prompt();

            // Pixel-perfect match Facebook button size to Google button
            const syncTimer = setInterval(() => {
                const gIframe = document.querySelector('#google-signin-btn iframe');
                const fbBtn = document.getElementById('fb-signin-btn');
                if (gIframe && fbBtn && gIframe.offsetWidth > 0) {
                    fbBtn.style.width = gIframe.offsetWidth + 'px';
                    fbBtn.style.height = (gIframe.offsetHeight || 40) + 'px';
                    clearInterval(syncTimer);
                }
            }, 50);
            setTimeout(() => clearInterval(syncTimer), 3000);
        }
    });

    window.fbAsyncInit = function() {
        if (typeof FB !== 'undefined') {
            FB.init({
                appId      : '{{ $facebookAppId }}',
                cookie     : true,
                xfbml      : false,
                version    : 'v19.0'
            });
        }
    };

    function loginWithFacebook() {
        if (typeof FB !== 'undefined' && '{{ $facebookAppId }}') {
            FB.login(function(response) {
                if (response.authResponse && response.authResponse.accessToken) {
                    showGoogleLoading(true);
                    fetch("{{ url('/auth/facebook/callback') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ access_token: response.authResponse.accessToken })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            showGoogleLoading(false);
                            alert(data.message || 'Gagal masuk dengan Facebook.');
                        }
                    })
                    .catch(err => {
                        showGoogleLoading(false);
                        console.error(err);
                        alert('Terjadi kesalahan koneksi saat login Facebook.');
                    });
                }
            }, { scope: 'public_profile,email' });
        } else {
            window.location.href = "{{ url('/auth/facebook/redirect') }}";
        }
    }
</script>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/id_ID/sdk.js"></script>
@endsection
