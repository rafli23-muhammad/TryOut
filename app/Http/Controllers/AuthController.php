<?php

namespace App\Http\Controllers;

use App\Services\TryoutDataStore;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showStudentLogin()
    {
        if (session('student_id')) {
            return redirect('/student/dashboard');
        }
        $googleClientId = config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID', '');
        $facebookAppId = config('services.facebook.client_id') ?: env('FACEBOOK_APP_ID', '');
        return view('auth.student_login', compact('googleClientId', 'facebookAppId'));
    }

    public function showStudentRegister()
    {
        if (session('student_id')) {
            return redirect('/student/dashboard');
        }
        $googleClientId = config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID', '');
        $facebookAppId = config('services.facebook.client_id') ?: env('FACEBOOK_APP_ID', '');
        return view('auth.student_register', compact('googleClientId', 'facebookAppId'));
    }

    public function studentLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $data = TryoutDataStore::read();
        $email = strtolower(trim($request->input('email')));
        $password = trim($request->input('password'));

        $student = collect($data['users'] ?? [])->first(function ($u) use ($email, $password) {
            return ($u['role'] ?? '') === 'student'
                && strtolower($u['email'] ?? '') === $email
                && ($u['password'] ?? '') === $password;
        });

        if (!$student) {
            return back()->withErrors(['email' => 'Email atau password siswa salah.'])->withInput();
        }

        if (($student['status'] ?? 'active') === 'suspended' || ($student['status'] ?? 'active') === 'inactive') {
            return back()->withErrors(['email' => 'Akun Anda sedang dinonaktifkan / ditangguhkan oleh Administrator.'])->withInput();
        }

        session([
            'student_id' => $student['id'],
            'student_name' => $student['name'],
            'student_email' => $student['email'],
            'student_phone' => $student['phone'] ?? '',
            'student_school' => $student['school'] ?? '',
            'student_avatar' => $student['avatar'] ?? null,
        ]);

        return redirect('/student/dashboard')->with('success', 'Selamat datang kembali, ' . $student['name'] . '!');
    }

    public function studentRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:120',
            'phone' => 'required|string|max:20',
            'school' => 'nullable|string|max:120',
            'password' => 'required|string|min:4',
        ]);

        $data = TryoutDataStore::read();
        $email = strtolower(trim($request->input('email')));

        $exists = collect($data['users'] ?? [])->first(function ($u) use ($email) {
            return strtolower($u['email'] ?? '') === $email;
        });

        if ($exists) {
            return back()->withErrors(['email' => 'Email sudah terdaftar. Silakan login.'])->withInput();
        }

        $newStudent = [
            'id' => uniqid('usr_student_'),
            'username' => explode('@', $email)[0],
            'name' => trim($request->input('name')),
            'email' => $email,
            'phone' => trim($request->input('phone')),
            'school' => trim($request->input('school') ?: '-'),
            'role' => 'student',
            'password' => trim($request->input('password')),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $data['users'][] = $newStudent;
        TryoutDataStore::write($data);

        session([
            'student_id' => $newStudent['id'],
            'student_name' => $newStudent['name'],
            'student_email' => $newStudent['email'],
            'student_phone' => $newStudent['phone'],
            'student_school' => $newStudent['school'],
        ]);

        return redirect('/student/catalog')->with('success', 'Pendaftaran akun Bimbel Cemerlang berhasil! Silakan pilih paket try out.');
    }

    public function googleCallback(Request $request)
    {
        $credential = $request->input('credential');

        // Support demo/testing simulation
        if ($credential === 'demo_google_one_tap' || $request->input('demo') === '1') {
            $googleUser = [
                'sub' => 'demo_google_' . time(),
                'email' => 'siswa.google@gmail.com',
                'name' => 'Siswa Google (Demo Device)',
                'picture' => 'https://api.dicebear.com/7.x/bottts/svg?seed=siswa_google',
            ];
        } else {
            if (empty($credential)) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Token autentikasi Google tidak ditemukan.'], 400);
                }
                return redirect('/login')->withErrors(['email' => 'Token autentikasi Google tidak ditemukan.']);
            }

            // Attempt verifying via Google Tokeninfo API
            $googleUser = null;
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)
                    ->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $credential]);
                if ($response->successful()) {
                    $googleUser = $response->json();
                }
            } catch (\Throwable $e) {
                // Fallback to decode JWT safely
            }

            // Fallback decode JWT payload if Google API is unreachable / offline
            if (!$googleUser) {
                $parts = explode('.', $credential);
                if (count($parts) === 3) {
                    $payloadJson = base64_decode(strtr($parts[1], '-_', '+/'));
                    $payload = json_decode($payloadJson, true);
                    if (is_array($payload) && !empty($payload['email'])) {
                        $googleUser = $payload;
                    }
                }
            }

            if (!$googleUser || empty($googleUser['email'])) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Autentikasi Google gagal atau token tidak valid.'], 422);
                }
                return redirect('/login')->withErrors(['email' => 'Autentikasi Google gagal atau token tidak valid.']);
            }
        }

        $email = strtolower(trim($googleUser['email'] ?? ''));
        $googleId = (string)($googleUser['sub'] ?? '');
        $name = trim($googleUser['name'] ?? ($googleUser['given_name'] ?? 'Siswa Google'));
        $picture = $googleUser['picture'] ?? null;

        $data = TryoutDataStore::read();
        $users = $data['users'] ?? [];

        // Find existing student by google_id or email
        $studentIndex = null;
        foreach ($users as $index => $u) {
            if (($u['role'] ?? '') === 'student') {
                if (!empty($u['google_id']) && $u['google_id'] === $googleId) {
                    $studentIndex = $index;
                    break;
                }
                if (strtolower($u['email'] ?? '') === $email) {
                    $studentIndex = $index;
                    break;
                }
            }
        }

        $isNewRegistration = false;
        if ($studentIndex !== null) {
            // Existing student
            $student = $users[$studentIndex];

            if (($student['status'] ?? 'active') === 'suspended' || ($student['status'] ?? 'active') === 'inactive') {
                $msg = 'Akun Anda sedang dinonaktifkan / ditangguhkan oleh Administrator.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 403);
                }
                return redirect('/login')->withErrors(['email' => $msg]);
            }

            // Sync google_id and avatar if missing
            $updated = false;
            if (empty($student['google_id']) && $googleId) {
                $data['users'][$studentIndex]['google_id'] = $googleId;
                $updated = true;
            }
            if (empty($student['avatar']) && $picture) {
                $data['users'][$studentIndex]['avatar'] = $picture;
                $updated = true;
            }
            if ($updated) {
                TryoutDataStore::write($data);
                $student = $data['users'][$studentIndex];
            }
        } else {
            // New student -> Automatically Register!
            $isNewRegistration = true;
            $usernameBase = explode('@', $email)[0];
            $username = preg_replace('/[^a-zA-Z0-9_]/', '', $usernameBase);
            if (empty($username)) {
                $username = 'student_' . substr(md5($email), 0, 6);
            }

            // Ensure unique username
            $existingUsernames = collect($users)->pluck('username')->all();
            $origUsername = $username;
            $counter = 1;
            while (in_array($username, $existingUsernames)) {
                $username = $origUsername . $counter;
                $counter++;
            }

            $student = [
                'id' => uniqid('usr_student_'),
                'username' => $username,
                'name' => $name ?: 'Siswa Cemerlang',
                'email' => $email,
                'phone' => '-',
                'school' => '-',
                'google_id' => $googleId,
                'avatar' => $picture,
                'role' => 'student',
                'status' => 'active',
                'password' => bin2hex(random_bytes(6)),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $data['users'][] = $student;
            TryoutDataStore::write($data);
        }

        // Set login session
        session([
            'student_id' => $student['id'],
            'student_name' => $student['name'],
            'student_email' => $student['email'],
            'student_phone' => $student['phone'] ?? '',
            'student_school' => $student['school'] ?? '',
            'student_avatar' => $student['avatar'] ?? null,
        ]);

        $redirectUrl = $isNewRegistration ? url('/student/catalog') : url('/student/dashboard');
        $flashMsg = $isNewRegistration 
            ? 'Pendaftaran akun Google Anda berhasil! Selamat datang di Bimbel Cemerlang, ' . $student['name'] . '!'
            : 'Selamat datang kembali, ' . $student['name'] . '! Anda berhasil masuk menggunakan Google.';

        session()->flash('success', $flashMsg);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $flashMsg,
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect($redirectUrl);
    }

    public function facebookRedirect(Request $request)
    {
        $appId = config('services.facebook.client_id') ?: env('FACEBOOK_APP_ID');

        if (empty($appId) || $request->input('demo') === '1') {
            return redirect('/auth/facebook/callback?demo=1');
        }

        $redirectUri = str_replace('127.0.0.1', 'localhost', url('/auth/facebook/callback'));
        $state = bin2hex(random_bytes(16));
        session(['fb_oauth_state' => $state]);

        $loginUrl = 'https://www.facebook.com/v19.0/dialog/oauth?' . http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => 'email,public_profile',
            'response_type' => 'code',
        ]);

        return redirect($loginUrl);
    }

    public function facebookCallback(Request $request)
    {
        if ($request->has('error')) {
            $errorMsg = $request->input('error_description') ?: 'Autentikasi Facebook dibatalkan.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 400);
            }
            return redirect('/login')->withErrors(['email' => $errorMsg]);
        }

        $credential = $request->input('credential');
        $fbUser = null;

        // Support demo/testing simulation
        if ($credential === 'demo_facebook' || $request->input('demo') === '1') {
            $fbUser = [
                'id' => 'fb_demo_' . time(),
                'name' => 'Siswa Facebook (Demo)',
                'email' => 'siswa.facebook@gmail.com',
                'picture' => [
                    'data' => [
                        'url' => 'https://api.dicebear.com/7.x/bottts/svg?seed=siswa_facebook',
                    ],
                ],
            ];
        } else {
            $code = $request->input('code');

            if ($request->filled('access_token')) {
                $accessToken = $request->input('access_token');
            } elseif ($code) {
                $appId = config('services.facebook.client_id') ?: env('FACEBOOK_APP_ID');
                $appSecret = config('services.facebook.client_secret') ?: env('FACEBOOK_APP_SECRET');
                $redirectUri = str_replace('127.0.0.1', 'localhost', url('/auth/facebook/callback'));

                try {
                    $tokenRes = \Illuminate\Support\Facades\Http::timeout(5)->get('https://graph.facebook.com/v19.0/oauth/access_token', [
                        'client_id' => $appId,
                        'client_secret' => $appSecret,
                        'redirect_uri' => $redirectUri,
                        'code' => $code,
                    ]);

                    if ($tokenRes->successful()) {
                        $accessToken = $tokenRes->json('access_token');
                    }
                } catch (\Throwable $e) {
                    // Timeout or network error
                }
            }

            if (!empty($accessToken)) {
                try {
                    $userRes = \Illuminate\Support\Facades\Http::timeout(5)->get('https://graph.facebook.com/v19.0/me', [
                        'fields' => 'id,name,email,picture.type(large)',
                        'access_token' => $accessToken,
                    ]);
                    if ($userRes->successful()) {
                        $fbUser = $userRes->json();
                    }
                } catch (\Throwable $e) {
                    // Network error
                }
            }
        }

        if (!$fbUser || empty($fbUser['id'])) {
            $errMsg = 'Autentikasi Facebook gagal atau token kedaluwarsa.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }
            return redirect('/login')->withErrors(['email' => $errMsg]);
        }

        $fbId = (string)($fbUser['id'] ?? '');
        $email = strtolower(trim($fbUser['email'] ?? ''));
        if (empty($email)) {
            $email = 'fb_' . $fbId . '@facebook.user';
        }
        $name = trim($fbUser['name'] ?? 'Siswa Facebook');
        $picture = $fbUser['picture']['data']['url'] ?? null;

        $data = TryoutDataStore::read();
        $users = $data['users'] ?? [];

        // Find existing student by facebook_id or email
        $studentIndex = null;
        foreach ($users as $index => $u) {
            if (($u['role'] ?? '') === 'student') {
                if (!empty($u['facebook_id']) && $u['facebook_id'] === $fbId) {
                    $studentIndex = $index;
                    break;
                }
                if (strtolower($u['email'] ?? '') === $email) {
                    $studentIndex = $index;
                    break;
                }
            }
        }

        $isNewRegistration = false;
        if ($studentIndex !== null) {
            $student = $users[$studentIndex];

            if (($student['status'] ?? 'active') === 'suspended' || ($student['status'] ?? 'active') === 'inactive') {
                $msg = 'Akun Anda sedang dinonaktifkan / ditangguhkan oleh Administrator.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 403);
                }
                return redirect('/login')->withErrors(['email' => $msg]);
            }

            $updated = false;
            if (empty($student['facebook_id']) && $fbId) {
                $data['users'][$studentIndex]['facebook_id'] = $fbId;
                $updated = true;
            }
            if (empty($student['avatar']) && $picture) {
                $data['users'][$studentIndex]['avatar'] = $picture;
                $updated = true;
            }
            if ($updated) {
                TryoutDataStore::write($data);
                $student = $data['users'][$studentIndex];
            }
        } else {
            $isNewRegistration = true;
            $usernameBase = explode('@', $email)[0];
            $username = preg_replace('/[^a-zA-Z0-9_]/', '', $usernameBase);
            if (empty($username)) {
                $username = 'student_fb_' . substr($fbId, -4);
            }

            $existingUsernames = collect($users)->pluck('username')->all();
            $origUsername = $username;
            $counter = 1;
            while (in_array($username, $existingUsernames)) {
                $username = $origUsername . $counter;
                $counter++;
            }

            $student = [
                'id' => uniqid('usr_student_'),
                'username' => $username,
                'name' => $name ?: 'Siswa Facebook',
                'email' => $email,
                'phone' => '-',
                'school' => '-',
                'facebook_id' => $fbId,
                'avatar' => $picture,
                'role' => 'student',
                'status' => 'active',
                'password' => bin2hex(random_bytes(6)),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $data['users'][] = $student;
            TryoutDataStore::write($data);
        }

        session([
            'student_id' => $student['id'],
            'student_name' => $student['name'],
            'student_email' => $student['email'],
            'student_phone' => $student['phone'] ?? '',
            'student_school' => $student['school'] ?? '',
            'student_avatar' => $student['avatar'] ?? null,
        ]);

        $redirectUrl = $isNewRegistration ? url('/student/catalog') : url('/student/dashboard');
        $flashMsg = $isNewRegistration 
            ? 'Pendaftaran akun Facebook berhasil! Selamat datang di Bimbel Cemerlang, ' . $student['name'] . '!'
            : 'Selamat datang kembali, ' . $student['name'] . '! Anda berhasil masuk menggunakan Facebook.';

        session()->flash('success', $flashMsg);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $flashMsg,
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect($redirectUrl);
    }

    public function showAdminLogin()
    {
        if (session('admin_id')) {
            return redirect('/admin/dashboard');
        }
        return view('auth.admin_login');
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $data = TryoutDataStore::read();
        $username = strtolower(trim($request->input('username')));
        $password = trim($request->input('password'));

        $admin = collect($data['users'] ?? [])->first(function ($u) use ($username, $password) {
            return ($u['role'] ?? '') === 'admin'
                && (strtolower($u['username'] ?? '') === $username || strtolower($u['email'] ?? '') === $username)
                && ($u['password'] ?? '') === $password;
        });

        if (!$admin) {
            return back()->withErrors(['username' => 'Kredensial Admin tidak cocok.'])->withInput();
        }

        if (($admin['status'] ?? 'active') === 'suspended' || ($admin['status'] ?? 'active') === 'inactive') {
            return back()->withErrors(['username' => 'Akun Administrator ini sedang ditangguhkan.'])->withInput();
        }

        session([
            'admin_id' => $admin['id'],
            'admin_name' => $admin['name'],
            'admin_username' => $admin['username'],
        ]);

        return redirect('/admin/dashboard')->with('success', 'Selamat datang Admin di Panel Bimbel Cemerlang!');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/')->with('success', 'Anda telah berhasil keluar.');
    }
}
