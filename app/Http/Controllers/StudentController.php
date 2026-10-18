<?php

namespace App\Http\Controllers;

use App\Services\TryoutDataStore;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected function checkStudentAuth()
    {
        if (!session('student_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu untuk mengakses fitur siswa.');
        }

        // Keep session synced with persistent user avatar
        if (!session('student_avatar')) {
            $data = TryoutDataStore::read();
            $student = collect($data['users'] ?? [])->firstWhere('id', session('student_id'));
            if ($student && !empty($student['avatar'])) {
                session(['student_avatar' => $student['avatar']]);
            }
        }

        return null;
    }

    public function dashboard()
    {
        if ($redirect = $this->checkStudentAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $studentId = session('student_id');

        $packages = collect($data['packages'] ?? [])->keyBy('id');
        $orders = collect($data['orders'] ?? [])->where('student_id', $studentId)->sortByDesc('created_at')->values();
        $attempts = collect($data['exam_sessions'] ?? [])->where('student_id', $studentId)->sortByDesc('started_at')->values();

        // Enrolled packages
        $approvedPackageIds = $orders->where('status', 'approved')->pluck('package_id')->unique()->all();
        $enrolledPackages = $packages->filter(fn($p) => in_array($p['id'], $approvedPackageIds))->values();

        // Count questions per package
        $questionsCount = collect($data['questions'] ?? [])->groupBy('package_id')->map->count();

        return view('student.dashboard', compact('enrolledPackages', 'orders', 'attempts', 'questionsCount', 'packages'));
    }

    public function catalog()
    {
        if ($redirect = $this->checkStudentAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $studentId = session('student_id');

        $packages = collect($data['packages'] ?? [])->where('is_active', true)->values();
        $questionsCount = collect($data['questions'] ?? [])->groupBy('package_id')->map->count();

        $studentOrders = collect($data['orders'] ?? [])
            ->where('student_id', $studentId)
            ->keyBy('package_id');

        $paymentSettings = $data['payment_settings'] ?? TryoutDataStore::defaultPaymentSettings();

        return view('student.catalog', compact('packages', 'questionsCount', 'studentOrders', 'paymentSettings'));
    }

    public function checkout(Request $request, $packageId)
    {
        if ($redirect = $this->checkStudentAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $studentId = session('student_id');
        $package = collect($data['packages'] ?? [])->firstWhere('id', $packageId);

        if (!$package) {
            return redirect('/student/catalog')->with('error', 'Paket try out tidak ditemukan.');
        }

        // Check if already enrolled
        $existingOrder = collect($data['orders'] ?? [])->first(function ($o) use ($studentId, $packageId) {
            return $o['student_id'] === $studentId && $o['package_id'] === $packageId && $o['status'] === 'approved';
        });

        if ($existingOrder) {
            return redirect('/student/dashboard')->with('info', 'Anda sudah memiliki akses ke paket ini.');
        }

        $price = (int) ($package['price'] ?? 0);
        $orderId = 'TRX-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $paymentProof = null;
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $filename = 'proof_' . time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/proofs'), $filename);
            $paymentProof = '/uploads/proofs/' . $filename;
        }

        $isFree = ($price === 0);
        $status = $isFree ? 'approved' : 'pending';

        $newOrder = [
            'id' => $orderId,
            'student_id' => $studentId,
            'student_name' => session('student_name'),
            'student_email' => session('student_email'),
            'package_id' => $package['id'],
            'package_title' => $package['title'],
            'amount' => $price,
            'payment_method' => $isFree ? 'Gratis' : ($request->input('payment_method') ?: 'Transfer Bank'),
            'payment_proof' => $paymentProof,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'approved_at' => $isFree ? date('Y-m-d H:i:s') : null,
        ];

        $data['orders'][] = $newOrder;
        TryoutDataStore::write($data);

        if ($isFree) {
            return redirect('/student/dashboard')->with('success', 'Paket Gratis berhasil diaktifkan! Anda siap mengerjakan try out.');
        }

        return redirect('/student/dashboard')->with('success', 'Checkout berhasil! Pembayaran sedang diverifikasi oleh admin.');
    }

    public function cameraCheck($packageId)
    {
        return redirect('/exam/' . $packageId);
    }

    public function showProfile()
    {
        if ($redirect = $this->checkStudentAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $studentId = session('student_id');
        $student = collect($data['users'] ?? [])->firstWhere('id', $studentId);

        if (!$student) {
            return redirect('/student/dashboard')->with('error', 'Data profil siswa tidak ditemukan.');
        }

        return view('student.profile', compact('student'));
    }

    public function updateProfile(Request $request)
    {
        if ($redirect = $this->checkStudentAuth()) return $redirect;

        $studentId = session('student_id');

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'school' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:4',
            'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'avatar_snapshot' => 'nullable|string',
        ]);

        $data = TryoutDataStore::read();
        $newEmail = strtolower(trim($request->input('email')));

        // Check if email already used by another user
        $emailExists = collect($data['users'] ?? [])->first(function ($u) use ($newEmail, $studentId) {
            return ($u['id'] !== $studentId) && strtolower($u['email'] ?? '') === $newEmail;
        });

        if ($emailExists) {
            return back()->withErrors(['email' => 'Alamat email ini sudah digunakan oleh akun lain.'])->withInput();
        }

        $userIndex = null;
        foreach ($data['users'] as $idx => $u) {
            if ($u['id'] === $studentId) {
                $userIndex = $idx;
                break;
            }
        }

        if ($userIndex === null) {
            return redirect('/student/dashboard')->with('error', 'Data pengguna tidak ditemukan.');
        }

        // Process avatar: from file upload or camera snapshot (base64)
        $avatarPath = $data['users'][$userIndex]['avatar'] ?? null;

        if ($request->hasFile('avatar_file')) {
            $file = $request->file('avatar_file');
            $filename = 'avatar_' . $studentId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/avatars'), $filename);
            $avatarPath = '/uploads/avatars/' . $filename;
        } elseif ($request->filled('avatar_snapshot')) {
            $snapshotData = $request->input('avatar_snapshot');
            if (preg_match('/^data:image\/(\w+);base64,/', $snapshotData, $type)) {
                $dataImg = substr($snapshotData, strpos($snapshotData, ',') + 1);
                $dataImg = base64_decode($dataImg);
                if ($dataImg !== false) {
                    $ext = strtolower($type[1]);
                    $filename = 'avatar_' . $studentId . '_' . time() . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                    $destPath = public_path('uploads/avatars');
                    if (!file_exists($destPath)) {
                        mkdir($destPath, 0777, true);
                    }
                    file_put_contents($destPath . '/' . $filename, $dataImg);
                    $avatarPath = '/uploads/avatars/' . $filename;
                }
            }
        }

        $data['users'][$userIndex]['name'] = trim($request->input('name'));
        $data['users'][$userIndex]['email'] = $newEmail;
        $data['users'][$userIndex]['phone'] = trim($request->input('phone') ?? '');
        $data['users'][$userIndex]['school'] = trim($request->input('school') ?? '');
        $data['users'][$userIndex]['avatar'] = $avatarPath;

        if ($request->filled('password')) {
            $data['users'][$userIndex]['password'] = trim($request->input('password'));
        }

        // Update name/email in orders/sessions for consistency
        foreach ($data['orders'] as &$ord) {
            if (($ord['student_id'] ?? '') === $studentId) {
                $ord['student_name'] = $data['users'][$userIndex]['name'];
                $ord['student_email'] = $newEmail;
            }
        }
        foreach ($data['exam_sessions'] as &$sess) {
            if (($sess['student_id'] ?? '') === $studentId) {
                $sess['student_name'] = $data['users'][$userIndex]['name'];
                $sess['student_email'] = $newEmail;
            }
        }

        TryoutDataStore::write($data);

        // Update active session
        session([
            'student_name' => $data['users'][$userIndex]['name'],
            'student_email' => $data['users'][$userIndex]['email'],
            'student_phone' => $data['users'][$userIndex]['phone'],
            'student_school' => $data['users'][$userIndex]['school'],
            'student_avatar' => $avatarPath,
        ]);

        return redirect('/student/profile')->with('success', 'Profil dan foto profil Anda berhasil diperbarui!');
    }
}
