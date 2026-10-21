<?php

namespace App\Http\Controllers;

use App\Services\TryoutDataStore;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use App\Services\PdfQuestionExtractor;
use App\Services\DocxQuestionExtractor;

class AdminController extends Controller
{
    protected function checkAdminAuth()
    {
        if (!session('admin_id')) {
            return redirect('/admin/login')->with('error', 'Silakan login sebagai administrator.');
        }
        return null;
    }

    public function dashboard()
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();

        $totalPackages = count($data['packages'] ?? []);
        $totalQuestions = count($data['questions'] ?? []);
        $totalStudents = count(array_filter($data['users'] ?? [], fn($u) => ($u['role'] ?? '') === 'student'));
        $totalOrders = count($data['orders'] ?? []);
        $pendingOrders = count(array_filter($data['orders'] ?? [], fn($o) => ($o['status'] ?? '') === 'pending'));
        $activeSessions = count(array_filter($data['exam_sessions'] ?? [], fn($s) => ($s['status'] ?? '') === 'in_progress'));
        $totalViolations = count($data['violation_logs'] ?? []);

        $recentOrders = collect($data['orders'] ?? [])->sortByDesc('created_at')->take(5)->values();
        $recentSessions = collect($data['exam_sessions'] ?? [])->sortByDesc('started_at')->take(5)->values();

        return view('admin.dashboard', compact(
            'totalPackages',
            'totalQuestions',
            'totalStudents',
            'totalOrders',
            'pendingOrders',
            'activeSessions',
            'totalViolations',
            'recentOrders',
            'recentSessions'
        ));
    }

    // --- MANAJEMEN PAKET TRY OUT ---
    public function packages()
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $packages = collect($data['packages'] ?? [])->sortByDesc('created_at')->values();
        $questionsCount = collect($data['questions'] ?? [])->groupBy('package_id')->map->count();

        return view('admin.packages', compact('packages', 'questionsCount'));
    }

    public function storePackage(Request $request)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|string|max:80',
            'description' => 'required|string',
            'duration_minutes' => 'required|integer|min:5|max:360',
            'passing_grade' => 'required|numeric|min:0|max:1000',
            'price' => 'required|integer|min:0',
            'max_violations' => 'required|integer|min:1|max:10',
        ]);

        $data = TryoutDataStore::read();
        $newPackage = [
            'id' => 'pkg_' . strtolower(substr(preg_replace('/[^a-zA-Z0-9]/', '', $request->input('title')), 0, 10)) . '_' . substr(uniqid(), -4),
            'title' => trim($request->input('title')),
            'category' => trim($request->input('category')),
            'description' => trim($request->input('description')),
            'duration_minutes' => (int) $request->input('duration_minutes'),
            'passing_grade' => (float) $request->input('passing_grade'),
            'price' => (int) $request->input('price'),
            'max_violations' => (int) $request->input('max_violations'),
            'is_active' => $request->boolean('is_active', true),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $data['packages'][] = $newPackage;
        TryoutDataStore::write($data);

        return redirect('/admin/packages')->with('success', 'Paket try out "' . $newPackage['title'] . '" berhasil dibuat!');
    }

    public function updatePackage(Request $request, $id)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|string|max:80',
            'description' => 'required|string',
            'duration_minutes' => 'required|integer|min:5|max:360',
            'passing_grade' => 'required|numeric|min:0|max:1000',
            'price' => 'required|integer|min:0',
            'max_violations' => 'required|integer|min:1|max:10',
        ]);

        $data = TryoutDataStore::read();
        $found = false;

        foreach ($data['packages'] as &$pkg) {
            if ($pkg['id'] === $id) {
                $pkg['title'] = trim($request->input('title'));
                $pkg['category'] = trim($request->input('category'));
                $pkg['description'] = trim($request->input('description'));
                $pkg['duration_minutes'] = (int) $request->input('duration_minutes');
                $pkg['passing_grade'] = (float) $request->input('passing_grade');
                $pkg['price'] = (int) $request->input('price');
                $pkg['max_violations'] = (int) $request->input('max_violations');
                $pkg['is_active'] = $request->boolean('is_active');
                $found = true;
                break;
            }
        }
        unset($pkg);

        if (!$found) {
            return redirect('/admin/packages')->with('error', 'Paket tidak ditemukan.');
        }

        TryoutDataStore::write($data);
        return redirect('/admin/packages')->with('success', 'Paket try out berhasil diperbarui!');
    }

    public function deletePackage($id)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $data['packages'] = array_values(array_filter($data['packages'] ?? [], fn($p) => $p['id'] !== $id));
        $data['questions'] = array_values(array_filter($data['questions'] ?? [], fn($q) => $q['package_id'] !== $id));

        TryoutDataStore::write($data);
        return redirect('/admin/packages')->with('success', 'Paket try out dan seluruh soalnya berhasil dihapus.');
    }

    // --- MANAJEMEN SOAL ---
    public function questions(Request $request, $packageId)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $package = collect($data['packages'] ?? [])->firstWhere('id', $packageId);

        if (!$package) {
            return redirect('/admin/packages')->with('error', 'Paket tidak ditemukan.');
        }

        $allPackages = collect($data['packages'] ?? [])->values();
        $questions = collect($data['questions'] ?? [])
            ->where('package_id', $packageId)
            ->sortBy('number')
            ->values();

        return view('admin.questions', compact('package', 'allPackages', 'questions'));
    }

    public function storeQuestion(Request $request, $packageId)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $request->validate([
            'type' => 'required|in:pg,mcma,tf',
            'text' => 'required|string',
            'explanation' => 'nullable|string',
        ]);

        $data = TryoutDataStore::read();
        $type = $request->input('type');

        $options = [];
        $correctAnswer = null;

        if ($type === 'pg') {
            $options = [
                'A' => trim($request->input('option_a', '')),
                'B' => trim($request->input('option_b', '')),
                'C' => trim($request->input('option_c', '')),
                'D' => trim($request->input('option_d', '')),
                'E' => trim($request->input('option_e', '')),
            ];
            $options = array_filter($options, fn($val) => $val !== '');
            $correctAnswer = strtoupper(trim($request->input('correct_pg', 'A')));
        } elseif ($type === 'mcma') {
            $options = [
                'A' => trim($request->input('option_mcma_a', '')),
                'B' => trim($request->input('option_mcma_b', '')),
                'C' => trim($request->input('option_mcma_c', '')),
                'D' => trim($request->input('option_mcma_d', '')),
                'E' => trim($request->input('option_mcma_e', '')),
            ];
            $options = array_filter($options, fn($val) => $val !== '');
            $correctAnswer = (array) $request->input('correct_mcma', []);
        } elseif ($type === 'tf') {
            $options = [
                'Benar' => 'Benar (True)',
                'Salah' => 'Salah (False)',
            ];
            $correctAnswer = $request->input('correct_tf', 'Benar');
        }

        $difficulty = strtolower($request->input('difficulty', 'sedang'));
        if (!in_array($difficulty, ['mudah', 'sedang', 'sukar'])) {
            $difficulty = 'sedang';
        }

        $existingCount = collect($data['questions'] ?? [])->where('package_id', $packageId)->count();

        $newQ = [
            'id' => uniqid('q_' . $type . '_'),
            'package_id' => $packageId,
            'type' => $type,
            'number' => $existingCount + 1,
            'difficulty' => $difficulty,
            'text' => trim($request->input('text')),
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'explanation' => trim($request->input('explanation') ?: 'Tidak ada pembahasan khusus.'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $data['questions'][] = $newQ;
        TryoutDataStore::write($data);

        return redirect('/admin/packages/' . $packageId . '/questions')->with('success', 'Soal nomor ' . $newQ['number'] . ' berhasil ditambahkan!');
    }

    public function deleteQuestion($packageId, $questionId)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $data['questions'] = array_values(array_filter($data['questions'] ?? [], fn($q) => $q['id'] !== $questionId));

        // Renumber remaining questions in package
        $num = 1;
        foreach ($data['questions'] as &$q) {
            if ($q['package_id'] === $packageId) {
                $q['number'] = $num++;
            }
        }
        unset($q);

        TryoutDataStore::write($data);
        return redirect('/admin/packages/' . $packageId . '/questions')->with('success', 'Soal berhasil dihapus.');
    }

    // --- TEMPLATE & IMPORT EXCEL ---
    public function downloadTemplateExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Soal');

        // Headers
        $headers = [
            'A1' => 'No',
            'B1' => 'Tipe Soal (PG/MCMA/TF)',
            'C1' => 'Pertanyaan',
            'D1' => 'Opsi A',
            'E1' => 'Opsi B',
            'F1' => 'Opsi C',
            'G1' => 'Opsi D',
            'H1' => 'Opsi E',
            'I1' => 'Kunci Jawaban',
            'J1' => 'Pembahasan',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Example Rows
        $samples = [
            [
                1,
                'PG',
                'Berapakah hasil dari 15 x 12 + 20?',
                '180',
                '190',
                '200',
                '210',
                '220',
                'C',
                '15 x 12 = 180. Lalu 180 + 20 = 200 (Jawaban C).',
            ],
            [
                2,
                'MCMA',
                'Pilih negara-negara yang merupakan anggota pendiri ASEAN: (Pilih jawaban benar)',
                'Indonesia',
                'Malaysia',
                'Singapura',
                'Australia',
                'Filipina',
                'A,B,C,E',
                'Lima negara pendiri ASEAN adalah Indonesia, Malaysia, Filipina, Singapura, dan Thailand.',
            ],
            [
                3,
                'TF',
                'Fotosintesis pada tumbuhan menghasilkan oksigen dan glukosa dari CO2 serta air dengan bantuan cahaya matahari.',
                'Benar',
                'Salah',
                '',
                '',
                '',
                'Benar',
                'Reaksi fotosintesis: 6CO2 + 6H2O + cahaya -> C6H12O6 + 6O2.',
            ],
        ];

        $rowIdx = 2;
        foreach ($samples as $sample) {
            $colLetter = 'A';
            foreach ($sample as $value) {
                $sheet->setCellValue($colLetter . $rowIdx, $value);
                $colLetter++;
            }
            $rowIdx++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_soal_cemerlang.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplateCsv()
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=template_soal_cemerlang.csv",
        ];

        $columns = ['No', 'Tipe Soal (PG/MCMA/TF)', 'Pertanyaan', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Opsi E', 'Kunci Jawaban', 'Pembahasan'];
        $samples = [
            [1, 'PG', 'Berapakah hasil dari 15 x 12 + 20?', '180', '190', '200', '210', '220', 'C', '15 x 12 = 180. 180 + 20 = 200.'],
            [2, 'MCMA', 'Pilih negara pendiri ASEAN:', 'Indonesia', 'Malaysia', 'Singapura', 'Australia', 'Filipina', 'A,B,C,E', 'Pendiri ASEAN: ID, MY, PH, SG, TH.'],
            [3, 'TF', 'Fotosintesis menghasilkan oksigen.', 'Benar', 'Salah', '', '', '', 'Benar', 'Fotosintesis menghasilkan O2.'],
        ];

        $callback = function () use ($columns, $samples) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);
            foreach ($samples as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importExcel(Request $request, $packageId)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt',
        ]);

        $file = $request->file('excel_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $data = TryoutDataStore::read();

        $importedQuestions = [];
        $existingCount = collect($data['questions'] ?? [])->where('package_id', $packageId)->count();
        $shouldRefine = $request->boolean('ai_refine', true);

        try {
            if ($extension === 'csv' || $extension === 'txt') {
                $handle = fopen($file->getRealPath(), 'r');
                $isHeader = true;
                while (($row = fgetcsv($handle, 4096, ',')) !== false) {
                    if ($isHeader) {
                        $isHeader = false;
                        continue;
                    }
                    if (empty($row[2])) continue; // No question text

                    $parsed = $this->parseRowToQuestion($row, $packageId, $existingCount + count($importedQuestions) + 1);
                    if ($parsed) {
                        if ($shouldRefine) {
                            $parsed = PdfQuestionExtractor::aiRefineQuestion($parsed);
                        }
                        $importedQuestions[] = $parsed;
                    }
                }
                fclose($handle);
            } else {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, false);

                foreach (array_slice($rows, 1) as $row) {
                    if (empty($row[2])) continue;
                    $parsed = $this->parseRowToQuestion($row, $packageId, $existingCount + count($importedQuestions) + 1);
                    if ($parsed) {
                        if ($shouldRefine) {
                            $parsed = PdfQuestionExtractor::aiRefineQuestion($parsed);
                        }
                        $importedQuestions[] = $parsed;
                    }
                }
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }

        if (empty($importedQuestions)) {
            return back()->with('error', 'Tidak ada soal valid yang ditemukan dalam file Excel.');
        }

        $data['questions'] = array_merge($data['questions'] ?? [], $importedQuestions);
        TryoutDataStore::write($data);

        return redirect('/admin/packages/' . $packageId . '/questions')
            ->with('success', 'Berhasil mengimpor ' . count($importedQuestions) . ' soal ke dalam paket try out' . ($shouldRefine ? ' (dibersihkan dengan AI)!' : '!'));
    }

    protected function parseRowToQuestion(array $row, string $packageId, int $number): ?array
    {
        $type = strtolower(trim((string) ($row[1] ?? 'pg')));
        if (!in_array($type, ['pg', 'mcma', 'tf'])) {
            $type = 'pg';
        }

        $text = trim((string) ($row[2] ?? ''));
        if ($text === '') return null;

        $optA = trim((string) ($row[3] ?? ''));
        $optB = trim((string) ($row[4] ?? ''));
        $optC = trim((string) ($row[5] ?? ''));
        $optD = trim((string) ($row[6] ?? ''));
        $optE = trim((string) ($row[7] ?? ''));
        $rawKey = trim((string) ($row[8] ?? ''));
        $explanation = trim((string) ($row[9] ?? ''));

        $options = [];
        $correctAnswer = null;

        if ($type === 'pg') {
            $options = array_filter([
                'A' => $optA,
                'B' => $optB,
                'C' => $optC,
                'D' => $optD,
                'E' => $optE,
            ], fn($v) => $v !== '');
            $correctAnswer = strtoupper(substr($rawKey, 0, 1)) ?: 'A';
        } elseif ($type === 'mcma') {
            $options = array_filter([
                'A' => $optA,
                'B' => $optB,
                'C' => $optC,
                'D' => $optD,
                'E' => $optE,
            ], fn($v) => $v !== '');
            $parts = explode(',', $rawKey);
            $correctAnswer = array_values(array_filter(array_map('trim', array_map('strtoupper', $parts))));
            if (empty($correctAnswer)) $correctAnswer = ['A'];
        } elseif ($type === 'tf') {
            $options = [
                'Benar' => 'Benar (True)',
                'Salah' => 'Salah (False)',
            ];
            $correctAnswer = (stripos($rawKey, 'salah') !== false || stripos($rawKey, 'false') !== false) ? 'Salah' : 'Benar';
        }

        return [
            'id' => uniqid('q_' . $type . '_'),
            'package_id' => $packageId,
            'type' => $type,
            'number' => $number,
            'text' => $text,
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'explanation' => $explanation ?: 'Pembahasan belum tersedia.',
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    // --- GENERATE SOAL DARI PDF ---
    public function showPdfGenerator($packageId)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $package = collect($data['packages'] ?? [])->firstWhere('id', $packageId);
        if (!$package) return redirect('/admin/packages')->with('error', 'Paket tidak ditemukan.');

        return view('admin.pdf_generator', compact('package'));
    }

    public function parsePdf(Request $request, $packageId)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf',
        ]);

        $file = $request->file('pdf_file');
        $pdfPath = $file->getRealPath();

        $extraction = PdfQuestionExtractor::extract($pdfPath, $packageId);
        $parsedQuestions = $extraction['questions'] ?? [];
        $extractedImages = $extraction['images'] ?? [];

        if (empty($parsedQuestions)) {
            return back()->with('error', 'Sistem tidak menemukan pola nomor soal (1., 2., dst) di dalam PDF ini. Pastikan berkas PDF memuat teks soal dan penomoran standar.');
        }

        if ($request->boolean('ai_refine', true)) {
            foreach ($parsedQuestions as &$q) {
                $q = PdfQuestionExtractor::aiRefineQuestion($q);
            }
            unset($q);
        }

        $data = TryoutDataStore::read();
        $package = collect($data['packages'] ?? [])->firstWhere('id', $packageId);

        return view('admin.pdf_preview', [
            'package' => $package,
            'extractedQuestions' => $parsedQuestions,
            'extractedImages' => $extractedImages,
            'sourceType' => 'PDF (.pdf)',
        ]);
    }

    public function parseWord(Request $request, $packageId)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $request->validate([
            'word_file' => 'required|file|mimes:docx,doc,zip',
        ]);

        $file = $request->file('word_file');
        $wordPath = $file->getRealPath();

        $extraction = DocxQuestionExtractor::extract($wordPath, $packageId);
        $parsedQuestions = $extraction['questions'] ?? [];
        $extractedImages = $extraction['images'] ?? [];

        if (empty($parsedQuestions)) {
            return back()->with('error', 'Sistem tidak menemukan pola nomor soal (1., 2., dst) di dalam berkas Word ini. Pastikan berkas .docx memuat teks soal dan penomoran standar.');
        }

        if ($request->boolean('ai_refine', true)) {
            foreach ($parsedQuestions as &$q) {
                $q = PdfQuestionExtractor::aiRefineQuestion($q);
            }
            unset($q);
        }

        $data = TryoutDataStore::read();
        $package = collect($data['packages'] ?? [])->firstWhere('id', $packageId);

        return view('admin.pdf_preview', [
            'package' => $package,
            'extractedQuestions' => $parsedQuestions,
            'extractedImages' => $extractedImages,
            'sourceType' => 'Word (.docx)',
        ]);
    }

    public function aiRefinePdfQuestions(Request $request, $packageId)
    {
        if ($redirect = $this->checkAdminAuth()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $questions = $request->input('questions', []);
        $refined = [];
        foreach ($questions as $q) {
            $refined[] = PdfQuestionExtractor::aiRefineQuestion($q);
        }

        return response()->json([
            'success' => true,
            'questions' => $refined,
            'message' => 'Berhasil merapikan simbol matematika dan struktur pembahasan menggunakan AI!',
        ]);
    }

    public function savePdfQuestions(Request $request, $packageId)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $items = $request->input('questions', []);

        if (empty($items)) {
            return redirect('/admin/packages/' . $packageId . '/questions')->with('error', 'Tidak ada soal yang dipilih untuk disimpan.');
        }

        $existingCount = collect($data['questions'] ?? [])->where('package_id', $packageId)->count();
        $newQuestions = [];

        foreach ($items as $item) {
            $qText = trim((string) ($item['text'] ?? ''));
            if ($qText === '') continue;

            $type = $item['type'] ?? 'pg';
            $options = [];
            $correct = $item['correct_answer'] ?? 'A';

            if ($type === 'pg' || $type === 'mcma') {
                $options = array_filter([
                    'A' => trim((string) ($item['option_a'] ?? '')),
                    'B' => trim((string) ($item['option_b'] ?? '')),
                    'C' => trim((string) ($item['option_c'] ?? '')),
                    'D' => trim((string) ($item['option_d'] ?? '')),
                    'E' => trim((string) ($item['option_e'] ?? '')),
                ], fn($v) => $v !== '');

                if ($type === 'mcma' && is_string($correct)) {
                    $correct = array_values(array_filter(array_map('trim', explode(',', $correct))));
                }
            } elseif ($type === 'tf') {
                $options = [
                    'Benar' => 'Benar (True)',
                    'Salah' => 'Salah (False)',
                ];
            }

            $image = !empty($item['image']) ? trim($item['image']) : null;

            $newQuestions[] = [
                'id' => uniqid('q_pdf_'),
                'package_id' => $packageId,
                'type' => $type,
                'number' => $existingCount + count($newQuestions) + 1,
                'text' => $qText,
                'image' => $image,
                'options' => $options,
                'correct_answer' => $correct,
                'explanation' => trim((string) ($item['explanation'] ?? 'Pembahasan dari ekstraksi PDF.')),
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        $data['questions'] = array_merge($data['questions'] ?? [], $newQuestions);
        TryoutDataStore::write($data);

        return redirect('/admin/packages/' . $packageId . '/questions')
            ->with('success', 'Berhasil menyimpan ' . count($newQuestions) . ' butir soal hasil ekstraksi PDF beserta ilustrasi gambar!');
    }

    protected function extractQuestionsFromText(string $text, string $packageId): array
    {
        $lines = preg_split('/\r?\n/', $text);
        $questions = [];

        $currentQ = null;
        $currentSection = 'text';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;

            // Detect Question start: "1. ", "1) ", "Soal 1: "
            if (preg_match('/^(?:Soal\s*)?(\d+)[\.\)]\s*(.*)$/i', $trimmed, $m)) {
                if ($currentQ && !empty($currentQ['text'])) {
                    $questions[] = $this->finalizeExtractedQuestion($currentQ, $packageId);
                }
                $currentQ = [
                    'number' => (int) $m[1],
                    'text' => $m[2],
                    'options' => [],
                    'correct_answer' => 'A',
                    'explanation' => '',
                    'type' => 'pg',
                ];
                $currentSection = 'text';
                continue;
            }

            if (!$currentQ) continue;

            // Detect Options: A. / B. / C. / D. / E.
            if (preg_match('/^([A-Ea-e])[\.\)]\s*(.*)$/', $trimmed, $optMatch)) {
                $letter = strtoupper($optMatch[1]);
                $currentQ['options'][$letter] = $optMatch[2];
                $currentSection = 'option_' . $letter;
                continue;
            }

            // Detect True/False statement
            if (preg_match('/^(?:Pernyataan|Klaim|Benar\/Salah):/i', $trimmed)) {
                $currentQ['type'] = 'tf';
            }

            // Detect Kunci Jawaban
            if (preg_match('/^(?:Kunci|Jawaban|Kunci Jawaban)[\s\:\=]+([A-Ea-e\,]+|Benar|Salah)/i', $trimmed, $keyMatch)) {
                $rawK = trim($keyMatch[1]);
                if (stripos($rawK, ',') !== false) {
                    $currentQ['type'] = 'mcma';
                    $currentQ['correct_answer'] = array_map('trim', explode(',', strtoupper($rawK)));
                } else {
                    $currentQ['correct_answer'] = strtoupper($rawK);
                }
                continue;
            }

            // Detect Pembahasan
            if (preg_match('/^(?:Pembahasan|Penjelasan)[\s\:\=]+(.*)$/i', $trimmed, $expMatch)) {
                $currentQ['explanation'] = $expMatch[1];
                $currentSection = 'explanation';
                continue;
            }

            // Append multiline text
            if ($currentSection === 'text') {
                $currentQ['text'] .= ' ' . $trimmed;
            } elseif (str_starts_with($currentSection, 'option_')) {
                $let = substr($currentSection, 7);
                $currentQ['options'][$let] .= ' ' . $trimmed;
            } elseif ($currentSection === 'explanation') {
                $currentQ['explanation'] .= ' ' . $trimmed;
            }
        }

        if ($currentQ && !empty($currentQ['text'])) {
            $questions[] = $this->finalizeExtractedQuestion($currentQ, $packageId);
        }

        return $questions;
    }

    protected function finalizeExtractedQuestion(array $q, string $packageId): array
    {
        $hasOptions = !empty($q['options']);
        $type = $q['type'] ?? ($hasOptions ? 'pg' : 'tf');

        if ($type === 'tf') {
            $options = ['Benar' => 'Benar (True)', 'Salah' => 'Salah (False)'];
            $key = $q['correct_answer'] ?: 'Benar';
        } else {
            $options = $q['options'];
            $key = $q['correct_answer'] ?: 'A';
        }

        return [
            'number' => $q['number'],
            'type' => $type,
            'text' => trim($q['text']),
            'options' => $options,
            'correct_answer' => $key,
            'explanation' => trim($q['explanation']) ?: 'Pembahasan hasil ekstraksi berkas PDF.',
        ];
    }

    // --- VERIFIKASI PEMBAYARAN ---
    public function orders(Request $request)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $statusFilter = $request->query('status', 'all');

        $orders = collect($data['orders'] ?? []);
        if ($statusFilter !== 'all') {
            $orders = $orders->where('status', $statusFilter);
        }
        $orders = $orders->sortByDesc('created_at')->values();

        return view('admin.orders', compact('orders', 'statusFilter'));
    }

    public function approveOrder($orderId)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $found = false;

        foreach ($data['orders'] as &$ord) {
            if ($ord['id'] === $orderId) {
                $ord['status'] = 'approved';
                $ord['approved_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        unset($ord);

        if (!$found) return back()->with('error', 'Transaksi tidak ditemukan.');

        TryoutDataStore::write($data);
        return back()->with('success', 'Pembayaran disetujui! Akses paket try out untuk siswa telah diaktifkan.');
    }

    public function rejectOrder($orderId)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        foreach ($data['orders'] as &$ord) {
            if ($ord['id'] === $orderId) {
                $ord['status'] = 'rejected';
                break;
            }
        }
        unset($ord);

        TryoutDataStore::write($data);
        return back()->with('success', 'Transaksi telah ditolak.');
    }

    // --- KELOLA METODE & REKENING PEMBAYARAN ---
    public function paymentSettings()
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $settings = $data['payment_settings'] ?? TryoutDataStore::defaultPaymentSettings();

        return view('admin.payment_settings', compact('settings'));
    }

    public function updatePaymentSettings(Request $request)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $currentSettings = $data['payment_settings'] ?? TryoutDataStore::defaultPaymentSettings();

        // 1. Process Bank Accounts
        $bankNames = $request->input('bank_name', []);
        $accNumbers = $request->input('account_number', []);
        $accHolders = $request->input('account_holder', []);
        $bankActive = $request->input('bank_active', []);

        $banks = [];
        foreach ($bankNames as $i => $name) {
            $nameTrim = trim((string) $name);
            if ($nameTrim === '') continue;
            $banks[] = [
                'id' => 'bank_' . ($i + 1),
                'bank_name' => $nameTrim,
                'account_number' => trim((string) ($accNumbers[$i] ?? '')),
                'account_holder' => trim((string) ($accHolders[$i] ?? '')),
                'is_active' => isset($bankActive[$i]),
            ];
        }

        // 2. Process E-Wallets (GoPay, ShopeePay, DANA, OVO, dll)
        $ewalletProviders = $request->input('ewallet_provider', []);
        $ewalletPhones = $request->input('ewallet_phone', []);
        $ewalletHolders = $request->input('ewallet_holder', []);
        $ewalletActive = $request->input('ewallet_active', []);

        $ewallets = [];
        foreach ($ewalletProviders as $i => $provider) {
            $providerTrim = trim((string) $provider);
            if ($providerTrim === '') continue;
            $ewallets[] = [
                'id' => 'ewallet_' . ($i + 1),
                'provider' => $providerTrim,
                'phone_number' => trim((string) ($ewalletPhones[$i] ?? '')),
                'account_holder' => trim((string) ($ewalletHolders[$i] ?? '')),
                'is_active' => isset($ewalletActive[$i]),
            ];
        }

        // 3. Process QRIS (Foto QRIS, Merchant Name, Notes)
        $qrisImage = $currentSettings['qris']['image_url'] ?? null;
        if ($request->hasFile('qris_image')) {
            $file = $request->file('qris_image');
            $filename = 'qris_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/qris'), $filename);
            $qrisImage = '/uploads/qris/' . $filename;
        }

        $qris = [
            'enabled' => $request->has('qris_enabled'),
            'image_url' => $qrisImage,
            'merchant_name' => trim((string) $request->input('qris_merchant_name', 'BIMBEL CEMERLANG QRIS')),
            'notes' => trim((string) $request->input('qris_notes', '')),
        ];

        $instructions = trim((string) $request->input('instructions', ''));

        $data['payment_settings'] = [
            'bank_accounts' => $banks,
            'ewallets' => $ewallets,
            'qris' => $qris,
            'instructions' => $instructions,
        ];

        TryoutDataStore::write($data);

        return redirect('/admin/payment-settings')->with('success', 'Pengaturan rekening, e-wallet, dan QRIS pembayaran berhasil disimpan!');
    }

    // --- WEBCAM PROCTORING MONITORING ---
    public function proctoring()
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();

        $activeSessions = collect($data['exam_sessions'] ?? [])
            ->where('status', 'in_progress')
            ->values();

        $proctorings = collect($data['active_proctorings'] ?? [])->values();
        $violations = collect($data['violation_logs'] ?? [])->sortByDesc('timestamp')->take(60)->values();

        return view('admin.proctoring', compact('activeSessions', 'proctorings', 'violations'));
    }

    public function pollProctoring()
    {
        $data = TryoutDataStore::read();
        $proctorings = collect($data['active_proctorings'] ?? [])->values();
        $violations = collect($data['violation_logs'] ?? [])->sortByDesc('timestamp')->take(30)->values();

        return response()->json([
            'proctorings' => $proctorings,
            'violations' => $violations,
            'timestamp' => date('H:i:s'),
        ]);
    }

    // --- REKAPITULASI & LAPORAN NILAI ---
    public function reports(Request $request)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $packageFilter = $request->query('package_id', 'all');

        $packages = collect($data['packages'] ?? [])->values();
        $sessions = collect($data['exam_sessions'] ?? [])
            ->whereIn('status', ['submitted', 'blocked']);

        if ($packageFilter !== 'all') {
            $sessions = $sessions->where('package_id', $packageFilter);
        }
        $sessions = $sessions->sortByDesc('finished_at')->values();

        return view('admin.reports', compact('packages', 'sessions', 'packageFilter'));
    }

    public function exportReportsExcel(Request $request)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $packageFilter = $request->query('package_id', 'all');

        $sessions = collect($data['exam_sessions'] ?? [])
            ->whereIn('status', ['submitted', 'blocked']);

        if ($packageFilter !== 'all') {
            $sessions = $sessions->where('package_id', $packageFilter);
        }
        $sessions = $sessions->sortByDesc('finished_at')->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Nilai');

        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Siswa',
            'C1' => 'Email',
            'D1' => 'Paket Try Out',
            'E1' => 'Total Soal',
            'F1' => 'Benar',
            'G1' => 'Salah',
            'H1' => 'Kosong',
            'I1' => 'Skor Total',
            'J1' => 'Passing Grade',
            'K1' => 'Status Kelulusan',
            'L1' => 'Pelanggaran',
            'M1' => 'Waktu Selesai',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        $rowIdx = 2;
        foreach ($sessions as $i => $s) {
            $sheet->setCellValue('A' . $rowIdx, $i + 1);
            $sheet->setCellValue('B' . $rowIdx, $s['student_name'] ?? '-');
            $sheet->setCellValue('C' . $rowIdx, $s['student_email'] ?? '-');
            $sheet->setCellValue('D' . $rowIdx, $s['package_title'] ?? '-');
            $sheet->setCellValue('E' . $rowIdx, $s['total_questions'] ?? 0);
            $sheet->setCellValue('F' . $rowIdx, $s['correct_count'] ?? 0);
            $sheet->setCellValue('G' . $rowIdx, $s['wrong_count'] ?? 0);
            $sheet->setCellValue('H' . $rowIdx, $s['empty_count'] ?? 0);
            $sheet->setCellValue('I' . $rowIdx, $s['score'] ?? 0);
            $sheet->setCellValue('J' . $rowIdx, ($s['passing_grade'] ?? 70) . '%');
            $sheet->setCellValue('K' . $rowIdx, !empty($s['is_passed']) ? 'LULUS' : 'BELUM LULUS');
            $sheet->setCellValue('L' . $rowIdx, $s['violation_count'] ?? 0);
            $sheet->setCellValue('M' . $rowIdx, $s['finished_at'] ?? '-');
            $rowIdx++;
        }

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'rekap_nilai_cemerlang_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportReportsCsv(Request $request)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $packageFilter = $request->query('package_id', 'all');

        $sessions = collect($data['exam_sessions'] ?? [])
            ->whereIn('status', ['submitted', 'blocked']);

        if ($packageFilter !== 'all') {
            $sessions = $sessions->where('package_id', $packageFilter);
        }
        $sessions = $sessions->sortByDesc('finished_at')->values();

        $filename = 'rekap_nilai_cemerlang_' . date('Ymd_His') . '.csv';
        $columns = ['No', 'Nama Siswa', 'Email', 'Paket Try Out', 'Total Soal', 'Benar', 'Salah', 'Kosong', 'Skor', 'Passing Grade', 'Status Kelulusan', 'Pelanggaran', 'Waktu Selesai'];

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($columns, $sessions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            foreach ($sessions as $i => $s) {
                fputcsv($file, [
                    $i + 1,
                    $s['student_name'] ?? '-',
                    $s['student_email'] ?? '-',
                    $s['package_title'] ?? '-',
                    $s['total_questions'] ?? 0,
                    $s['correct_count'] ?? 0,
                    $s['wrong_count'] ?? 0,
                    $s['empty_count'] ?? 0,
                    $s['score'] ?? 0,
                    ($s['passing_grade'] ?? 70) . '%',
                    !empty($s['is_passed']) ? 'LULUS' : 'BELUM LULUS',
                    $s['violation_count'] ?? 0,
                    $s['finished_at'] ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // --- MANAJEMEN AKUN & PENGGUNA TERDAFTAR ---
    public function users(Request $request)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $q = strtolower(trim($request->query('q', '')));
        $roleFilter = $request->query('role', 'all');
        $statusFilter = $request->query('status', 'all');

        $allUsers = collect($data['users'] ?? []);
        $orders = collect($data['orders'] ?? []);
        $sessions = collect($data['exam_sessions'] ?? []);

        // Stats
        $totalUsers = $allUsers->count();
        $totalStudents = $allUsers->where('role', 'student')->count();
        $totalAdmins = $allUsers->where('role', 'admin')->count();
        $activeStudents = $allUsers->where('role', 'student')->filter(fn($u) => ($u['status'] ?? 'active') === 'active')->count();

        // Filter
        $filtered = $allUsers;
        if ($roleFilter !== 'all') {
            $filtered = $filtered->where('role', $roleFilter);
        }
        if ($statusFilter !== 'all') {
            $filtered = $filtered->filter(fn($u) => ($u['status'] ?? 'active') === $statusFilter);
        }
        if ($q !== '') {
            $filtered = $filtered->filter(function ($u) use ($q) {
                return str_contains(strtolower($u['name'] ?? ''), $q)
                    || str_contains(strtolower($u['email'] ?? ''), $q)
                    || str_contains(strtolower($u['username'] ?? ''), $q)
                    || str_contains(strtolower($u['phone'] ?? ''), $q)
                    || str_contains(strtolower($u['school'] ?? ''), $q);
            });
        }

        // Map activity counts
        $users = $filtered->map(function ($u) use ($orders, $sessions) {
            $uid = $u['id'];
            $u['enrolled_count'] = $orders->where('student_id', $uid)->where('status', 'approved')->count();
            $u['exams_count'] = $sessions->where('student_id', $uid)->where('status', 'submitted')->count();
            return $u;
        })->values();

        return view('admin.users', compact(
            'users',
            'totalUsers',
            'totalStudents',
            'totalAdmins',
            'activeStudents',
            'q',
            'roleFilter',
            'statusFilter'
        ));
    }

    public function storeUser(Request $request)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:120',
            'phone' => 'required|string|max:20',
            'role' => 'required|in:student,admin',
            'password' => 'required|string|min:4',
            'school' => 'nullable|string|max:120',
        ]);

        $data = TryoutDataStore::read();
        $email = strtolower(trim($request->input('email')));

        $exists = collect($data['users'] ?? [])->first(function ($u) use ($email) {
            return strtolower($u['email'] ?? '') === $email;
        });

        if ($exists) {
            return back()->with('error', 'Email ' . $email . ' sudah digunakan oleh akun lain.')->withInput();
        }

        $role = $request->input('role');
        $id = uniqid($role === 'admin' ? 'usr_admin_' : 'usr_student_');
        $username = explode('@', $email)[0];

        $newUser = [
            'id' => $id,
            'username' => $username,
            'name' => trim($request->input('name')),
            'email' => $email,
            'phone' => trim($request->input('phone')),
            'school' => trim($request->input('school') ?: '-'),
            'role' => $role,
            'password' => trim($request->input('password')),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $data['users'][] = $newUser;
        TryoutDataStore::write($data);

        return redirect('/admin/users')->with('success', 'Akun ' . $newUser['name'] . ' (' . ($role === 'admin' ? 'Administrator' : 'Siswa') . ') berhasil ditambahkan!');
    }

    public function updateUser(Request $request, $id)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:120',
            'phone' => 'required|string|max:20',
            'role' => 'required|in:student,admin',
            'status' => 'required|in:active,suspended',
            'school' => 'nullable|string|max:120',
            'password' => 'nullable|string|min:4',
        ]);

        $data = TryoutDataStore::read();
        $userIndex = null;
        foreach ($data['users'] as $idx => $u) {
            if ($u['id'] === $id) {
                $userIndex = $idx;
                break;
            }
        }

        if ($userIndex === null) {
            return redirect('/admin/users')->with('error', 'Akun pengguna tidak ditemukan.');
        }

        // Email uniqueness check
        $email = strtolower(trim($request->input('email')));
        $emailTaken = collect($data['users'])->contains(function ($u, $idx) use ($email, $userIndex) {
            return $idx !== $userIndex && strtolower($u['email'] ?? '') === $email;
        });

        if ($emailTaken) {
            return back()->with('error', 'Email ' . $email . ' sudah digunakan oleh pengguna lain.')->withInput();
        }

        $newName = trim($request->input('name'));
        $data['users'][$userIndex]['name'] = $newName;
        $data['users'][$userIndex]['email'] = $email;
        $data['users'][$userIndex]['phone'] = trim($request->input('phone'));
        $data['users'][$userIndex]['role'] = $request->input('role');
        $data['users'][$userIndex]['status'] = $request->input('status');
        $data['users'][$userIndex]['school'] = trim($request->input('school') ?: '-');

        if ($request->filled('password')) {
            $data['users'][$userIndex]['password'] = trim($request->input('password'));
        }

        // Update related orders and exam sessions
        foreach ($data['orders'] ?? [] as &$ord) {
            if (($ord['student_id'] ?? '') === $id) {
                $ord['student_name'] = $newName;
                $ord['student_email'] = $email;
            }
        }
        foreach ($data['exam_sessions'] ?? [] as &$sess) {
            if (($sess['student_id'] ?? '') === $id) {
                $sess['student_name'] = $newName;
                $sess['student_email'] = $email;
            }
        }

        TryoutDataStore::write($data);

        if ($id === session('admin_id')) {
            session([
                'admin_name' => $newName,
                'admin_username' => $data['users'][$userIndex]['username'] ?? explode('@', $email)[0],
                'admin_email' => $email,
            ]);
        }

        return redirect('/admin/users')->with('success', 'Data akun ' . $newName . ' berhasil diperbarui.');
    }

    public function toggleUserStatus($id)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        if (session('admin_id') === $id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Administrator Anda sendiri.');
        }

        $data = TryoutDataStore::read();
        $targetUser = null;

        foreach ($data['users'] as &$u) {
            if ($u['id'] === $id) {
                $currentStatus = $u['status'] ?? 'active';
                $u['status'] = ($currentStatus === 'active') ? 'suspended' : 'active';
                $targetUser = $u;
                break;
            }
        }
        unset($u);

        if (!$targetUser) {
            return back()->with('error', 'Akun tidak ditemukan.');
        }

        TryoutDataStore::write($data);

        $statusLabel = ($targetUser['status'] === 'active') ? 'diaktifkan kembali' : 'ditangguhkan / dinonaktifkan';
        return back()->with('success', 'Akun ' . $targetUser['name'] . ' berhasil ' . $statusLabel . '.');
    }

    public function resetUserPassword(Request $request, $id)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $request->validate([
            'password' => 'required|string|min:4',
        ]);

        $data = TryoutDataStore::read();
        $targetName = null;

        foreach ($data['users'] as &$u) {
            if ($u['id'] === $id) {
                $u['password'] = trim($request->input('password'));
                $targetName = $u['name'];
                break;
            }
        }
        unset($u);

        if (!$targetName) {
            return back()->with('error', 'Akun tidak ditemukan.');
        }

        TryoutDataStore::write($data);

        return back()->with('success', 'Password untuk akun ' . $targetName . ' berhasil direset.');
    }

    public function deleteUser($id)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        if (session('admin_id') === $id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Administrator Anda sendiri yang sedang aktif.');
        }

        $data = TryoutDataStore::read();
        $initialCount = count($data['users'] ?? []);
        $data['users'] = array_values(array_filter($data['users'] ?? [], fn($u) => $u['id'] !== $id));

        if (count($data['users']) === $initialCount) {
            return back()->with('error', 'Akun tidak ditemukan atau sudah terhapus.');
        }

        TryoutDataStore::write($data);

        return redirect('/admin/users')->with('success', 'Akun pengguna berhasil dihapus dari sistem.');
    }

    // --- PROFIL & PENGATURAN KREDENSIAL ADMIN (GMAIL & PASSWORD) ---
    public function profile()
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $adminId = session('admin_id');
        $admin = collect($data['users'] ?? [])->firstWhere('id', $adminId);

        if (!$admin) {
            $admin = collect($data['users'] ?? [])->firstWhere('role', 'admin') ?? [
                'id' => 'usr_admin',
                'name' => 'Administrator Bimbel Cemerlang',
                'username' => 'admin',
                'email' => 'admin@cemerlang.com',
                'phone' => '081299998888',
                'role' => 'admin',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        return view('admin.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        if ($redirect = $this->checkAdminAuth()) return $redirect;

        $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:120',
            'username' => 'required|string|max:60',
            'phone' => 'nullable|string|max:25',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:4|confirmed',
        ]);

        $adminId = session('admin_id');
        $data = TryoutDataStore::read();

        $adminIndex = null;
        foreach ($data['users'] as $idx => $u) {
            if ($u['id'] === $adminId || (($u['role'] ?? '') === 'admin' && ($u['username'] ?? '') === session('admin_username'))) {
                $adminIndex = $idx;
                $adminId = $u['id'];
                break;
            }
        }

        if ($adminIndex === null) {
            return back()->with('error', 'Akun Administrator tidak ditemukan.');
        }

        $newEmail = strtolower(trim($request->input('email')));
        $newUsername = strtolower(trim($request->input('username')));

        // Check email uniqueness across all other accounts
        $emailTaken = collect($data['users'])->contains(function ($u, $idx) use ($newEmail, $adminIndex) {
            return $idx !== $adminIndex && strtolower($u['email'] ?? '') === $newEmail;
        });

        if ($emailTaken) {
            return back()->with('error', 'Alamat email / Gmail ' . $newEmail . ' sudah dipakai oleh akun lain.')->withInput();
        }

        // If new password requested
        if ($request->filled('new_password')) {
            $currentSavedPassword = $data['users'][$adminIndex]['password'] ?? '';
            if (!empty($currentSavedPassword) && $request->filled('current_password')) {
                if ($request->input('current_password') !== $currentSavedPassword) {
                    return back()->with('error', 'Kata sandi saat ini tidak cocok. Silakan coba lagi.')->withInput();
                }
            }
            $data['users'][$adminIndex]['password'] = trim($request->input('new_password'));
        }

        $data['users'][$adminIndex]['name'] = trim($request->input('name'));
        $data['users'][$adminIndex]['email'] = $newEmail;
        $data['users'][$adminIndex]['username'] = $newUsername;
        $data['users'][$adminIndex]['phone'] = trim($request->input('phone') ?: '-');

        TryoutDataStore::write($data);

        // Update session credentials immediately
        session([
            'admin_id' => $data['users'][$adminIndex]['id'],
            'admin_name' => $data['users'][$adminIndex]['name'],
            'admin_username' => $data['users'][$adminIndex]['username'],
            'admin_email' => $data['users'][$adminIndex]['email'],
        ]);

        return redirect('/admin/profile')->with('success', 'Profil dan kredensial akun Gmail & kata sandi Administrator berhasil diperbarui!');
    }
}
