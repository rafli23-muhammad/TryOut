<?php

namespace App\Services;

class TryoutDataStore
{
    public static function dataPath(): string
    {
        $filename = app()->environment('testing') ? 'tryout_data_testing.json' : 'tryout_data.json';
        $path = storage_path('app/' . $filename);
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        return $path;
    }

    public static function defaultPaymentSettings(): array
    {
        return [
            'bank_accounts' => [
                [
                    'id' => 'bank_bca',
                    'bank_name' => 'BCA',
                    'account_number' => '8820-192-888',
                    'account_holder' => 'BIMBEL CEMERLANG UTBK',
                    'is_active' => true,
                ],
                [
                    'id' => 'bank_mandiri',
                    'bank_name' => 'Mandiri',
                    'account_number' => '137-00-1928374-1',
                    'account_holder' => 'BIMBEL CEMERLANG INDONESIA',
                    'is_active' => true,
                ],
                [
                    'id' => 'bank_bri',
                    'bank_name' => 'BRI',
                    'account_number' => '0341-01-002938-53-1',
                    'account_holder' => 'BIMBEL CEMERLANG',
                    'is_active' => true,
                ],
            ],
            'ewallets' => [
                [
                    'id' => 'ewallet_gopay',
                    'provider' => 'GoPay',
                    'phone_number' => '0812-3456-7890',
                    'account_holder' => 'Bimbel Cemerlang Official',
                    'is_active' => true,
                ],
                [
                    'id' => 'ewallet_shopeepay',
                    'provider' => 'ShopeePay',
                    'phone_number' => '0812-3456-7890',
                    'account_holder' => 'Bimbel Cemerlang Official',
                    'is_active' => true,
                ],
                [
                    'id' => 'ewallet_dana',
                    'provider' => 'DANA',
                    'phone_number' => '0812-3456-7890',
                    'account_holder' => 'Bimbel Cemerlang',
                    'is_active' => true,
                ],
                [
                    'id' => 'ewallet_ovo',
                    'provider' => 'OVO',
                    'phone_number' => '0812-3456-7890',
                    'account_holder' => 'Bimbel Cemerlang',
                    'is_active' => true,
                ],
            ],
            'qris' => [
                'enabled' => true,
                'image_url' => '/images/sample_qris.png',
                'merchant_name' => 'QRIS BIMBEL CEMERLANG (NMID: ID1020039281)',
                'notes' => 'Mendukung semua bank & aplikasi e-wallet (BCA Mobile, Livin, GoPay, ShopeePay, DANA, OVO, LinkAja)',
            ],
            'instructions' => 'Pastikan nominal transfer sesuai dengan total biaya paket. Simpan screenshot/foto bukti transfer lalu upload pada form checkout untuk verifikasi otomatis oleh tim admin.',
        ];
    }

    public static function read(): array
    {
        $path = self::dataPath();
        if (!file_exists($path)) {
            $initial = self::defaultData();
            self::write($initial);
            return $initial;
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);
        if (!is_array($data) || empty($data['packages'])) {
            $initial = self::defaultData();
            self::write($initial);
            return $initial;
        }

        // Ensure payment settings exists
        if (!isset($data['payment_settings']) || !is_array($data['payment_settings'])) {
            $data['payment_settings'] = self::defaultPaymentSettings();
        }

        return $data;
    }

    public static function write(array $data): void
    {
        $path = self::dataPath();
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function defaultData(): array
    {
        $packages = [
            [
                'id' => 'pkg_utbk_2026',
                'title' => 'Try Out Akbar UTBK-SNBT 2026 (TPS & Literasi)',
                'category' => 'SNBT',
                'description' => 'Simulasi resmi UTBK-SNBT dengan sistem pembobotan IRT (Item Response Theory), batas waktu real-time, dan pembahasan mendalam Bimbel Cemerlang.',
                'duration_minutes' => 60,
                'passing_grade' => 650,
                'price' => 0,
                'max_violations' => 3,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'pkg_cpns_2026',
                'title' => 'Simulasi Komprehensif TKA Soshum 2026 (Sejarah & Sosiologi)',
                'category' => 'TKA',
                'description' => 'Paket Try Out Tes Kemampuan Akademik (TKA) kelompok Soshum dengan sistem penilaian IRT berstandar nasional dan tipe soal variatif.',
                'duration_minutes' => 90,
                'passing_grade' => 650,
                'price' => 35000,
                'max_violations' => 3,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'pkg_kedinasan_2026',
                'title' => 'Mastery Try Out TKA Saintek 2026 (Matematika & Sains)',
                'category' => 'TKA',
                'description' => 'Uji penguasaan konsep saintek mendalam untuk Tes Kemampuan Akademik (TKA) dengan sistem pembobotan butir IRT.',
                'duration_minutes' => 75,
                'passing_grade' => 680,
                'price' => 50000,
                'max_violations' => 3,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $questions = [
            // Soal Paket UTBK (Campuran 3 tipe: PG, MCMA, Benar/Salah)
            [
                'id' => 'q_utbk_01',
                'package_id' => 'pkg_utbk_2026',
                'type' => 'pg',
                'number' => 1,
                'text' => 'Jika x + 2y = 10 dan 3x - y = 9, berapakah nilai dari 2x + y?',
                'options' => [
                    'A' => '7',
                    'B' => '9',
                    'C' => '11',
                    'D' => '13',
                    'E' => '15',
                ],
                'correct_answer' => 'C',
                'explanation' => 'Dari persamaan 3x - y = 9 => y = 3x - 9. Substitusikan ke persamaan pertama: x + 2(3x - 9) = 10 => x + 6x - 18 = 10 => 7x = 28 => x = 4. Maka y = 3(4) - 9 = 3. Nilai 2x + y = 2(4) + 3 = 11.',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'q_utbk_02',
                'package_id' => 'pkg_utbk_2026',
                'type' => 'mcma',
                'number' => 2,
                'text' => 'Manakah di antara pernyataan berikut yang BENAR mengenai sistem organ ekskresi pada manusia? (Pilih semua yang benar)',
                'options' => [
                    'A' => 'Ginjal menyaring limbah metabolisme berupa urea dari darah.',
                    'B' => 'Paru-paru mengekskresikan karbon dioksida (CO2) dan uap air.',
                    'C' => 'Hati menghasilkan empedu yang berperan merombak hemoglobin sel darah merah yang telah tua.',
                    'D' => 'Kulit mengeluarkan sisa metabolisme hanya berupa hormon kalsitonin.',
                    'E' => 'Nefron adalah unit fungsional terkecil dari ginjal.',
                ],
                'correct_answer' => ['A', 'B', 'C', 'E'],
                'explanation' => 'Organ ekskresi manusia meliputi: Ginjal (urea dan urin), Paru-paru (CO2 dan H2O), Hati (empedu & perombakan eritrosit), dan Kulit (keringat). Nefron merupakan unit fungsional penyaring pada ginjal. Pilihan D salah karena kulit mengekskresikan keringat (air, garam, sedikit urea), bukan kalsitonin.',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'q_utbk_03',
                'package_id' => 'pkg_utbk_2026',
                'type' => 'tf',
                'number' => 3,
                'text' => 'Pancasila sila ke-4 mengandung prinsip musyawarah untuk mufakat serta menjunjung tinggi demokrasi perwakilan yang dipimpin oleh hikmat kebijaksanaan.',
                'options' => [
                    'Benar' => 'Benar (True)',
                    'Salah' => 'Salah (False)',
                ],
                'correct_answer' => 'Benar',
                'explanation' => 'Sila ke-4 "Kerakyatan yang dipimpin oleh hikmat kebijaksanaan dalam permusyawaratan/perwakilan" menegaskan budaya musyawarah mufakat, menghargai pendapat, dan kepemimpinan yang berlandaskan kebijaksanaan serta akal sehat.',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'q_utbk_04',
                'package_id' => 'pkg_utbk_2026',
                'type' => 'pg',
                'number' => 4,
                'text' => 'Sebuah mobil menempuh jarak 180 km dalam waktu 3 jam. Jika kecepatan mobil ditingkatkan sebesar 20 km/jam, berapa waktu yang dibutuhkan untuk menempuh jarak yang sama?',
                'options' => [
                    'A' => '2 jam',
                    'B' => '2 jam 15 menit',
                    'C' => '2 jam 30 menit',
                    'D' => '2 jam 45 menit',
                    'E' => '1 jam 45 menit',
                ],
                'correct_answer' => 'B',
                'explanation' => 'Kecepatan awal = 180 km / 3 jam = 60 km/jam. Kecepatan baru = 60 + 20 = 80 km/jam. Waktu baru = 180 / 80 = 2,25 jam = 2 jam 15 menit.',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'q_utbk_05',
                'package_id' => 'pkg_utbk_2026',
                'type' => 'mcma',
                'number' => 5,
                'text' => 'Manakah di bawah ini kata-kata yang merupakan kata baku sesuai KBBI? (Pilih jawaban yang benar)',
                'options' => [
                    'A' => 'Analisis',
                    'B' => 'Apotek',
                    'C' => 'Praktik',
                    'D' => 'Kwalitas',
                    'E' => 'Objektif',
                ],
                'correct_answer' => ['A', 'B', 'C', 'E'],
                'explanation' => 'Baku: Analisis (bukan analisa), Apotek (bukan apotik), Praktik (bukan praktek), Objektif (bukan obyektif). Bentuk baku untuk kwalitas adalah Kualitas.',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'q_utbk_06',
                'package_id' => 'pkg_utbk_2026',
                'type' => 'tf',
                'number' => 6,
                'text' => 'Gaya gravitasi antara dua benda berbanding lurus dengan kuadrat jarak antara kedua pusat massa benda tersebut.',
                'options' => [
                    'Benar' => 'Benar (True)',
                    'Salah' => 'Salah (False)',
                ],
                'correct_answer' => 'Salah',
                'explanation' => 'Pernyataan SALAH. Menurut Hukum Gravitasi Universal Newton (F = G * m1 * m2 / r^2), gaya gravitasi berbanding TERBALIK dengan kuadrat jarak (r^2), bukan berbanding lurus.',
                'created_at' => date('Y-m-d H:i:s'),
            ],

            // Soal Paket CPNS SKD
            [
                'id' => 'q_cpns_01',
                'package_id' => 'pkg_cpns_2026',
                'type' => 'pg',
                'number' => 1,
                'text' => 'Pasal berapakah dalam UUD 1945 yang mengatur bahwa setiap warga negara berhak dan wajib ikut serta dalam upaya pembelaan negara?',
                'options' => [
                    'A' => 'Pasal 27 ayat 1',
                    'B' => 'Pasal 27 ayat 3',
                    'C' => 'Pasal 30 ayat 1',
                    'D' => 'Pasal 31 ayat 1',
                    'E' => 'Pasal 33 ayat 1',
                ],
                'correct_answer' => 'B',
                'explanation' => 'Pasal 27 ayat 3 UUD 1945 berbunyi: "Setiap warga negara berhak dan wajib ikut serta dalam upaya pembelaan negara". Sedangkan Pasal 30 ayat 1 mengatur tentang usaha pertahanan dan keamanan negara.',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'q_cpns_02',
                'package_id' => 'pkg_cpns_2026',
                'type' => 'mcma',
                'number' => 2,
                'text' => 'Pilih prinsip-prinsip Good Governance dalam tata kelola pemerintahan yang baik: (Pilih beberapa)',
                'options' => [
                    'A' => 'Transparansi dan Keterbukaan Informasi',
                    'B' => 'Akuntabilitas publik',
                    'C' => 'Partisipasi masyarakat',
                    'D' => 'Kerahasiaan anggaran internal tanpa audit',
                    'E' => 'Supremasi hukum (Rule of Law)',
                ],
                'correct_answer' => ['A', 'B', 'C', 'E'],
                'explanation' => 'Good governance mencakup Transparansi, Akuntabilitas, Partisipasi, dan Supremasi Hukum. Opsi D bertentangan dengan asas akuntabilitas dan transparansi.',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'q_cpns_03',
                'package_id' => 'pkg_cpns_2026',
                'type' => 'tf',
                'number' => 3,
                'text' => 'Bhinneka Tunggal Ika pertama kali tertuang dalam Kitab Sutasoma karangan Mpu Tantular pada masa kejayaan Kerajaan Majapahit.',
                'options' => [
                    'Benar' => 'Benar (True)',
                    'Salah' => 'Salah (False)',
                ],
                'correct_answer' => 'Benar',
                'explanation' => 'Semboyan Bhinneka Tunggal Ika berasal dari Kakawin Sutasoma karangan Mpu Tantular pada masa Kerajaan Majapahit abad ke-14.',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $users = [
            [
                'id' => 'usr_admin',
                'username' => 'admin',
                'name' => 'Administrator Bimbel Cemerlang',
                'email' => 'admin@cemerlang.com',
                'phone' => '081299998888',
                'role' => 'admin',
                'password' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'usr_student_01',
                'username' => 'budi',
                'name' => 'Budi Pratama Santoso',
                'email' => 'siswa@cemerlang.com',
                'phone' => '081234567890',
                'school' => 'SMA Negeri 1 Jakarta',
                'role' => 'student',
                'password' => 'siswa123',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $orders = [
            [
                'id' => 'TRX-CEMERLANG-001',
                'student_id' => 'usr_student_01',
                'student_name' => 'Budi Pratama Santoso',
                'student_email' => 'siswa@cemerlang.com',
                'package_id' => 'pkg_utbk_2026',
                'package_title' => 'Try Out Akbar UTBK-SNBT 2026 (TPS & Literasi)',
                'amount' => 0,
                'payment_method' => 'Gratis / Beasiswa',
                'payment_proof' => null,
                'status' => 'approved',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'approved_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'id' => 'TRX-CEMERLANG-002',
                'student_id' => 'usr_student_01',
                'student_name' => 'Budi Pratama Santoso',
                'student_email' => 'siswa@cemerlang.com',
                'package_id' => 'pkg_cpns_2026',
                'package_title' => 'Simulasi Komprehensif SKD CPNS 2026 (TWK, TIU, TKP)',
                'amount' => 35000,
                'payment_method' => 'Transfer Bank BCA',
                'payment_proof' => '/images/sample_payment_proof.png',
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'approved_at' => null,
            ],
        ];

        return [
            'packages' => $packages,
            'questions' => $questions,
            'users' => $users,
            'orders' => $orders,
            'payment_settings' => self::defaultPaymentSettings(),
            'exam_sessions' => [],
            'violation_logs' => [],
            'active_proctorings' => [],
        ];
    }
}
