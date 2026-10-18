<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\TryoutDataStore;

class CemerlangTryoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset store for idempotent testing
        TryoutDataStore::write(TryoutDataStore::defaultData());
    }

    public function test_landing_and_auth_pages_accessible()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('BIMBEL CEMERLANG');

        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_student_registration_and_login()
    {
        $testEmail = 'peserta_' . time() . '@test.com';
        $response = $this->post('/register', [
            'name' => 'Peserta Baru Cemerlang',
            'email' => $testEmail,
            'phone' => '081234567890',
            'school' => 'SMA Cemerlang 1',
            'password' => 'secret123',
        ]);
        $response->assertRedirect('/student/catalog');

        $response = $this->get('/logout');
        $response->assertRedirect('/');

        $response = $this->post('/login', [
            'email' => $testEmail,
            'password' => 'secret123',
        ]);
        $response->assertRedirect('/student/dashboard');

        // Test Edit Profile
        $response = $this->get('/student/profile');
        $response->assertStatus(200);
        $response->assertSee('Profil Siswa');

        $response = $this->post('/student/profile', [
            'name' => 'Peserta Update Cemerlang',
            'email' => $testEmail,
            'phone' => '089876543210',
            'school' => 'SMA Prestasi Jakarta',
            'password' => 'newpassword123',
        ]);
        $response->assertRedirect('/student/profile');
        $this->assertEquals('Peserta Update Cemerlang', session('student_name'));
        $this->assertEquals('SMA Prestasi Jakarta', session('student_school'));
    }

    public function test_admin_login_and_dashboard()
    {
        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);
        $response->assertRedirect('/admin/dashboard');

        $response = $this->withSession(['admin_id' => 'usr_admin', 'admin_username' => 'admin'])
            ->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Ringkasan & Analitik Try Out');
    }

    public function test_packages_management_crud()
    {
        $adminSession = ['admin_id' => 'usr_admin', 'admin_username' => 'admin'];

        // Create
        $response = $this->withSession($adminSession)->post('/admin/packages', [
            'title' => 'Simulasi UTBK Mandiri 2026',
            'category' => 'UTBK SNBT',
            'description' => 'Paket pengujian mandiri untuk siswa berprestasi',
            'duration_minutes' => 45,
            'passing_grade' => 65,
            'price' => 20000,
            'max_violations' => 3,
            'is_active' => '1',
        ]);
        $response->assertRedirect('/admin/packages');

        $data = TryoutDataStore::read();
        $created = collect($data['packages'])->firstWhere('title', 'Simulasi UTBK Mandiri 2026');
        $this->assertNotNull($created);

        // Update
        $response = $this->withSession($adminSession)->post('/admin/packages/' . $created['id'] . '/update', [
            'title' => 'Simulasi UTBK Mandiri 2026 (Updated)',
            'category' => 'UTBK SNBT',
            'description' => 'Updated deskripsi',
            'duration_minutes' => 50,
            'passing_grade' => 70,
            'price' => 25000,
            'max_violations' => 3,
            'is_active' => '1',
        ]);
        $response->assertRedirect('/admin/packages');

        // Delete
        $response = $this->withSession($adminSession)->post('/admin/packages/' . $created['id'] . '/delete');
        $response->assertRedirect('/admin/packages');
    }

    public function test_questions_crud_and_3_types()
    {
        $adminSession = ['admin_id' => 'usr_admin', 'admin_username' => 'admin'];

        // 1. PG
        $response = $this->withSession($adminSession)->post('/admin/packages/pkg_utbk_2026/questions', [
            'type' => 'pg',
            'text' => 'Berapa hasil dari 5 + 5?',
            'option_a' => '8',
            'option_b' => '10',
            'option_c' => '12',
            'correct_pg' => 'B',
            'explanation' => '5 + 5 = 10 (Jawaban B).',
        ]);
        $response->assertRedirect('/admin/packages/pkg_utbk_2026/questions');

        // 2. MCMA
        $response = $this->withSession($adminSession)->post('/admin/packages/pkg_utbk_2026/questions', [
            'type' => 'mcma',
            'text' => 'Pilih bilangan genap:',
            'option_mcma_a' => '2',
            'option_mcma_b' => '4',
            'option_mcma_c' => '5',
            'correct_mcma' => ['A', 'B'],
            'explanation' => '2 dan 4 adalah bilangan genap.',
        ]);
        $response->assertRedirect('/admin/packages/pkg_utbk_2026/questions');

        // 3. TF
        $response = $this->withSession($adminSession)->post('/admin/packages/pkg_utbk_2026/questions', [
            'type' => 'tf',
            'text' => 'Bumi mengelilingi matahari.',
            'correct_tf' => 'Benar',
            'explanation' => 'Revolusi bumi mengelilingi matahari membutuhkan 365,25 hari.',
        ]);
        $response->assertRedirect('/admin/packages/pkg_utbk_2026/questions');
    }

    public function test_templates_download()
    {
        $response = $this->get('/admin/questions/template/csv');
        $response->assertStatus(200);

        $response = $this->get('/admin/questions/template/excel');
        $response->assertStatus(200);
    }

    public function test_checkout_and_payment_approval()
    {
        $studentSession = [
            'student_id' => 'usr_student_01',
            'student_name' => 'Budi Pratama Santoso',
            'student_email' => 'siswa@cemerlang.com',
        ];

        // Free package checkout -> auto approved
        $response = $this->withSession($studentSession)->post('/student/checkout/pkg_utbk_2026');
        $response->assertRedirect('/student/dashboard');

        // Paid package checkout -> pending
        $response = $this->withSession($studentSession)->post('/student/checkout/pkg_cpns_2026', [
            'payment_method' => 'Transfer Bank BCA',
        ]);
        $response->assertRedirect('/student/dashboard');

        $data = TryoutDataStore::read();
        $pendingOrder = collect($data['orders'])->first(fn($o) => $o['package_id'] === 'pkg_cpns_2026' && $o['status'] === 'pending');
        $this->assertNotNull($pendingOrder);

        // Admin approves payment
        $adminSession = ['admin_id' => 'usr_admin', 'admin_username' => 'admin'];
        $response = $this->withSession($adminSession)->post('/admin/orders/' . $pendingOrder['id'] . '/approve');
        $response->assertRedirect();

        $data = TryoutDataStore::read();
        $approvedOrder = collect($data['orders'])->firstWhere('id', $pendingOrder['id']);
        $this->assertEquals('approved', $approvedOrder['status']);
    }

    public function test_exam_flow_snapshot_violation_and_evaluation()
    {
        $studentSession = [
            'student_id' => 'usr_student_01',
            'student_name' => 'Budi Pratama Santoso',
            'student_email' => 'siswa@cemerlang.com',
        ];

        // Camera check redirects directly to exam
        $response = $this->withSession($studentSession)->get('/student/camera-check/pkg_utbk_2026');
        $response->assertRedirect('/exam/pkg_utbk_2026');

        // Start exam
        $response = $this->withSession($studentSession)->get('/exam/pkg_utbk_2026');
        $response->assertStatus(200);
        $response->assertSee('Sisa Waktu');
        $safeQuestions = $response->viewData('safeQuestions');
        $this->assertNotEmpty($safeQuestions);
        $this->assertArrayHasKey('image', $safeQuestions[0]);

        $data = TryoutDataStore::read();
        $sess = end($data['exam_sessions']);
        $sessionId = $sess['id'];

        // Snapshot API
        $response = $this->withSession($studentSession)->post('/api/exam/' . $sessionId . '/snapshot', [
            'image' => 'data:image/jpeg;base64,mockImageFrame',
        ]);
        $response->assertJson(['success' => true]);

        // Violation API
        $response = $this->withSession($studentSession)->post('/api/exam/' . $sessionId . '/violation', [
            'violation_type' => 'tab_switch',
            'violation_message' => 'Pindah tab terdeteksi',
            'snapshot_image' => 'data:image/jpeg;base64,mockViolationFrame',
        ]);
        $response->assertJson(['success' => true]);

        // Save answer API
        $response = $this->withSession($studentSession)->post('/api/exam/' . $sessionId . '/save-answer', [
            'question_id' => 'q_utbk_01',
            'answer' => 'C',
            'is_doubt' => false,
        ]);
        $response->assertJson(['success' => true]);

        // Submit exam
        $response = $this->withSession($studentSession)->post('/exam/' . $sessionId . '/submit', [
            'answers' => [
                'q_utbk_01' => 'C',
                'q_utbk_03' => 'Benar',
            ]
        ]);
        $response->assertRedirect('/exam/' . $sessionId . '/result');

        // View result page with explanations
        $response = $this->withSession($studentSession)->get('/exam/' . $sessionId . '/result');
        $response->assertStatus(200);
        $response->assertSee('Hasil Evaluasi Instan');
        $response->assertSee('Pembahasan Singkat');
    }

    public function test_reports_and_exports()
    {
        $adminSession = ['admin_id' => 'usr_admin', 'admin_username' => 'admin'];

        $response = $this->withSession($adminSession)->get('/admin/reports');
        $response->assertStatus(200);

        $response = $this->withSession($adminSession)->get('/admin/reports/export/csv');
        $response->assertStatus(200);

        $response = $this->withSession($adminSession)->get('/admin/reports/export/excel');
        $response->assertStatus(200);
    }

    public function test_payment_settings_management()
    {
        $adminSession = ['admin_id' => 'usr_admin', 'admin_username' => 'admin'];

        // Access payment settings page
        $response = $this->withSession($adminSession)->get('/admin/payment-settings');
        $response->assertStatus(200);
        $response->assertSee('Kelola Rekening, E-Wallet & QRIS');

        // Update payment settings
        $response = $this->withSession($adminSession)->post('/admin/payment-settings', [
            'bank_name' => ['BCA Syariah', 'Bank Jago'],
            'account_number' => ['9900-1122', '1029-3847'],
            'account_holder' => ['Bimbel Cemerlang', 'Bendahara Bimbel'],
            'bank_active' => ['0' => '1', '1' => '1'],
            'ewallet_provider' => ['GoPay', 'ShopeePay'],
            'ewallet_phone' => ['0811-2233-4455', '0899-8877-6655'],
            'ewallet_holder' => ['Cemerlang Pay', 'Cemerlang Shopee'],
            'ewallet_active' => ['0' => '1', '1' => '1'],
            'qris_enabled' => '1',
            'qris_merchant_name' => 'BIMBEL CEMERLANG TEST QRIS',
            'qris_notes' => 'Scan QRIS untuk pembayaran langsung.',
            'instructions' => 'Instruksi pembayaran khusus.',
        ]);
        $response->assertRedirect('/admin/payment-settings');

        $data = TryoutDataStore::read();
        $this->assertEquals('BIMBEL CEMERLANG TEST QRIS', $data['payment_settings']['qris']['merchant_name']);
        $this->assertEquals('BCA Syariah', $data['payment_settings']['bank_accounts'][0]['bank_name']);
        $this->assertEquals('GoPay', $data['payment_settings']['ewallets'][0]['provider']);
    }

    public function test_pdf_question_generator_and_ai_refiner()
    {
        $adminSession = ['admin_id' => 'usr_admin', 'admin_username' => 'admin'];

        // Access PDF generator page
        $response = $this->withSession($adminSession)->get('/admin/packages/pkg_utbk_2026/pdf');
        $response->assertStatus(200);
        $response->assertSee('Generate Soal');

        // Test AI Refine API
        $rawQuestion = [
            'number' => 1,
            'type' => 'pg',
            'text' => 'Garis direfleksikan terhadap y=-x, koordinat ujung-ujung garis adalah....',
            'options' => ['A' => '(-1, 6)', 'B' => '(2, -4)'],
            'correct_answer' => 'B',
            'explanation' => 'Rumus refleksi terhadap garis y=-x: (x, y) -> (-y, -x) => Bayangan: (2, -4).',
        ];

        $response = $this->withSession($adminSession)->postJson('/admin/packages/pkg_utbk_2026/pdf/ai-refine', [
            'questions' => [$rawQuestion],
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $json = $response->json();
        $this->assertNotEmpty($json['questions']);
        $this->assertStringContainsString('→', $json['questions'][0]['explanation']);

        // Test Saving PDF Questions with image
        $response = $this->withSession($adminSession)->post('/admin/packages/pkg_utbk_2026/pdf/save', [
            'questions' => [
                [
                    'text' => 'Soal dari ekstraksi PDF dengan gambar',
                    'type' => 'pg',
                    'option_a' => 'Opsi 1',
                    'option_b' => 'Opsi 2',
                    'correct_answer' => 'A',
                    'image' => '/uploads/questions/sample_diagram.png',
                    'explanation' => 'Pembahasan lengkap dan bersih.',
                ]
            ]
        ]);
        $response->assertRedirect('/admin/packages/pkg_utbk_2026/questions');

        $data = TryoutDataStore::read();
        $savedQ = collect($data['questions'])->last();
        $this->assertEquals('Soal dari ekstraksi PDF dengan gambar', $savedQ['text']);
        $this->assertEquals('/uploads/questions/sample_diagram.png', $savedQ['image']);
    }

    public function test_unified_smart_generator_and_word_extractor()
    {
        $adminSession = ['admin_id' => 'usr_admin', 'admin_username' => 'admin'];

        // 1. Verify questions page has the unified Generate & Import Soal (AI) button and modal
        $response = $this->withSession($adminSession)->get('/admin/packages/pkg_utbk_2026/questions');
        $response->assertStatus(200);
        $response->assertSee('Generate & Import Soal (AI)', false);
        $response->assertSee('modal-smart-import');
        $response->assertSee('tab-btn-pdf');
        $response->assertSee('tab-btn-excel');
        $response->assertSee('tab-btn-word');

        // 2. Test Word (.docx) upload and extraction with AI refinement
        $docxPath = tempnam(sys_get_temp_dir(), 'test_docx_') . '.docx';
        $zip = new \ZipArchive();
        $zip->open($docxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $xmlContent = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    <w:p><w:r><w:t>1. Nilai dari f(x) = 2x + 1 jika x = 3 adalah...</w:t></w:r></w:p>
    <w:p><w:r><w:t>A. 5</w:t></w:r></w:p>
    <w:p><w:r><w:t>B. 6</w:t></w:r></w:p>
    <w:p><w:r><w:t>C. 7</w:t></w:r></w:p>
    <w:p><w:r><w:t>D. 8</w:t></w:r></w:p>
    <w:p><w:r><w:t>Kunci: C</w:t></w:r></w:p>
    <w:p><w:r><w:t>Pembahasan: Substitusi nilai x: f(3) = 2(3) + 1 = 7.</w:t></w:r></w:p>
  </w:body>
</w:document>';
        $zip->addFromString('word/document.xml', $xmlContent);
        $zip->close();

        $uploadedDocx = new \Illuminate\Http\UploadedFile(
            $docxPath,
            'soal_matematika.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $response = $this->withSession($adminSession)->post('/admin/packages/pkg_utbk_2026/word/parse', [
            'word_file' => $uploadedDocx,
            'ai_refine' => '1',
        ]);
        $response->assertStatus(200);
        $response->assertSee('Tinjau & Verifikasi Soal (Word (.docx))', false);
        $response->assertSee('Nilai dari f(x) = 2x + 1');

        if (file_exists($docxPath)) {
            @unlink($docxPath);
        }
    }

    public function test_admin_user_management()
    {
        $adminSession = ['admin_id' => 'usr_admin', 'admin_username' => 'admin'];

        // 1. View /admin/users list page
        $response = $this->withSession($adminSession)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Kelola Akun & Pengguna');
        $response->assertSee('Daftar Akun Terdaftar');
        $response->assertSee('Tambah Akun Baru');

        // 2. Create a new student user
        $newUserData = [
            'name' => 'Budi Tester Gemastik',
            'email' => 'budi.tester@gemastik.id',
            'phone' => '081299988877',
            'role' => 'student',
            'school' => 'SMA Gemastik Indonesia',
            'password' => 'secret123',
        ];

        $response = $this->withSession($adminSession)->post('/admin/users', $newUserData);
        $response->assertRedirect('/admin/users');

        $data = TryoutDataStore::read();
        $createdUser = collect($data['users'])->firstWhere('email', 'budi.tester@gemastik.id');
        $this->assertNotNull($createdUser);
        $this->assertEquals('Budi Tester Gemastik', $createdUser['name']);
        $this->assertEquals('active', $createdUser['status']);
        $this->assertEquals('student', $createdUser['role']);
        $userId = $createdUser['id'];

        // Duplicate email validation check
        $duplicateResponse = $this->withSession($adminSession)->post('/admin/users', $newUserData);
        $duplicateResponse->assertSessionHas('error');

        // 3. Edit user profile
        $response = $this->withSession($adminSession)->post('/admin/users/' . $userId . '/update', [
            'name' => 'Budi Tester Gemastik Ed',
            'email' => 'budi.tester@gemastik.id',
            'phone' => '081299988877',
            'role' => 'student',
            'status' => 'active',
            'school' => 'SMA Unggulan Cemerlang',
        ]);
        $response->assertRedirect('/admin/users');

        $data = TryoutDataStore::read();
        $updatedUser = collect($data['users'])->firstWhere('id', $userId);
        $this->assertEquals('Budi Tester Gemastik Ed', $updatedUser['name']);
        $this->assertEquals('SMA Unggulan Cemerlang', $updatedUser['school']);

        // 4. Reset password
        $response = $this->withSession($adminSession)->post('/admin/users/' . $userId . '/reset-password', [
            'password' => 'newpassword999',
        ]);
        $response->assertRedirect();
        $data = TryoutDataStore::read();
        $pwdUser = collect($data['users'])->firstWhere('id', $userId);
        $this->assertEquals('newpassword999', $pwdUser['password']);

        // 5. Toggle status to suspended
        $response = $this->withSession($adminSession)->post('/admin/users/' . $userId . '/toggle-status');
        $response->assertRedirect();
        $data = TryoutDataStore::read();
        $suspendedUser = collect($data['users'])->firstWhere('id', $userId);
        $this->assertEquals('suspended', $suspendedUser['status']);

        // Suspended student cannot login
        $loginResponse = $this->post('/login', [
            'email' => 'budi.tester@gemastik.id',
            'password' => 'newpassword999',
        ]);
        $loginResponse->assertSessionHasErrors(['email']);

        // Toggle status back to active
        $response = $this->withSession($adminSession)->post('/admin/users/' . $userId . '/toggle-status');
        $response->assertRedirect();
        $data = TryoutDataStore::read();
        $activeUser = collect($data['users'])->firstWhere('id', $userId);
        $this->assertEquals('active', $activeUser['status']);

        // 6. Admin self-protection
        $selfToggle = $this->withSession($adminSession)->post('/admin/users/usr_admin/toggle-status');
        $selfToggle->assertSessionHas('error');

        $selfDelete = $this->withSession($adminSession)->post('/admin/users/usr_admin/delete');
        $selfDelete->assertSessionHas('error');

        // 7. Delete user
        $response = $this->withSession($adminSession)->post('/admin/users/' . $userId . '/delete');
        $response->assertRedirect('/admin/users');

        $data = TryoutDataStore::read();
        $deletedCheck = collect($data['users'])->firstWhere('id', $userId);
        $this->assertNull($deletedCheck);
    }

    public function test_admin_can_update_profile_gmail_and_password()
    {
        $adminSession = ['admin_id' => 'usr_admin', 'admin_username' => 'admin'];

        // 1. Visit admin profile page
        $response = $this->withSession($adminSession)->get('/admin/profile');
        $response->assertStatus(200);
        $response->assertSee('Profil & Akun Administrator');
        $response->assertSee('Akun Email / Gmail');
        $response->assertSee('Kata Sandi Baru');

        // 2. Update Gmail and Password
        $response = $this->withSession($adminSession)->post('/admin/profile', [
            'name' => 'Super Admin Cemerlang',
            'username' => 'admincemerlang',
            'email' => 'admin.cemerlang@gmail.com',
            'phone' => '081299887766',
            'current_password' => 'admin',
            'new_password' => 'passwordbaru2026',
            'new_password_confirmation' => 'passwordbaru2026',
        ]);
        $response->assertRedirect('/admin/profile');
        $response->assertSessionHas('success');

        // Verify data store has been updated
        $data = TryoutDataStore::read();
        $admin = collect($data['users'])->firstWhere('id', 'usr_admin');
        $this->assertEquals('admin.cemerlang@gmail.com', $admin['email']);
        $this->assertEquals('admincemerlang', $admin['username']);
        $this->assertEquals('passwordbaru2026', $admin['password']);

        // 3. Test logging in using the new Gmail address and new password
        $loginResponse = $this->post('/admin/login', [
            'username' => 'admin.cemerlang@gmail.com',
            'password' => 'passwordbaru2026',
        ]);
        $loginResponse->assertRedirect('/admin/dashboard');

        // 4. Restore original admin credentials for clean test state
        $this->withSession(['admin_id' => 'usr_admin', 'admin_username' => 'admincemerlang'])->post('/admin/profile', [
            'name' => 'Administrator Bimbel Cemerlang',
            'username' => 'admin',
            'email' => 'admin@cemerlang.com',
            'phone' => '081299998888',
            'current_password' => 'passwordbaru2026',
            'new_password' => 'admin',
            'new_password_confirmation' => 'admin',
        ]);
    }

    public function test_google_auth_registration_and_login()
    {
        // 1. Test registration via simulated Google device one tap
        $response = $this->postJson('/auth/google/callback', [
            'credential' => 'demo_google_one_tap',
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'redirect_url']);
        $response->assertSessionHas('student_id');
        $response->assertSessionHas('student_email', 'siswa.google@gmail.com');

        // 2. Test registration via simulated Google JWT ID token
        $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
        $payload = rtrim(strtr(base64_encode(json_encode([
            'sub' => 'google_sub_testing_12345',
            'email' => 'siswa.baru.google@cemerlang.test',
            'name' => 'Siswa Baru Google',
            'picture' => 'https://lh3.googleusercontent.com/test_avatar.png',
        ])), '+/', '-_'), '=');
        $jwtToken = $header . '.' . $payload . '.dummy_signature_for_test';

        $jwtResponse = $this->postJson('/auth/google/callback', [
            'credential' => $jwtToken,
        ]);
        $jwtResponse->assertStatus(200);
        $jwtResponse->assertJson(['success' => true]);
        $jwtResponse->assertSessionHas('student_email', 'siswa.baru.google@cemerlang.test');

        // 3. Verify student created in data store with Google ID and avatar
        $data = TryoutDataStore::read();
        $student = collect($data['users'])->firstWhere('email', 'siswa.baru.google@cemerlang.test');
        $this->assertNotNull($student);
        $this->assertEquals('google_sub_testing_12345', $student['google_id']);
        $this->assertEquals('https://lh3.googleusercontent.com/test_avatar.png', $student['avatar']);

        // 4. Test re-login for the same user via Google
        $loginResponse = $this->postJson('/auth/google/callback', [
            'credential' => $jwtToken,
        ]);
        $loginResponse->assertStatus(200);
        $loginResponse->assertJson(['success' => true]);
        $this->assertStringContainsString('dashboard', $loginResponse->json('redirect_url'));
    }

    public function test_facebook_auth_registration_and_login()
    {
        // 1. Test redirect to Facebook OAuth
        $response = $this->get('/auth/facebook/redirect');
        $response->assertStatus(302);
        $this->assertStringContainsString('facebook.com', $response->headers->get('Location'));

        // 2. Test registration via simulated Facebook callback
        $callbackResponse = $this->postJson('/auth/facebook/callback', [
            'credential' => 'demo_facebook',
        ]);
        $callbackResponse->assertStatus(200);
        $callbackResponse->assertJsonStructure(['success', 'message', 'redirect_url']);
        $callbackResponse->assertSessionHas('student_id');
        $callbackResponse->assertSessionHas('student_email', 'siswa.facebook@gmail.com');

        // 3. Verify user in data store has facebook_id
        $data = TryoutDataStore::read();
        $student = collect($data['users'])->firstWhere('email', 'siswa.facebook@gmail.com');
        $this->assertNotNull($student);
        $this->assertNotEmpty($student['facebook_id']);
        $this->assertStringContainsString('fb_demo_', $student['facebook_id']);

        // 4. Test re-login for the same student
        $loginResponse = $this->postJson('/auth/facebook/callback', [
            'credential' => 'demo_facebook',
        ]);
        $loginResponse->assertStatus(200);
        $loginResponse->assertJson(['success' => true]);
        $this->assertStringContainsString('dashboard', $loginResponse->json('redirect_url'));
    }
}


