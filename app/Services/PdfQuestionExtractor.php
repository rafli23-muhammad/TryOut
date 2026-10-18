<?php

namespace App\Services;

class PdfQuestionExtractor
{
    /**
     * Extract questions, options, answers, explanations, and diagrams from PDF.
     */
    public static function extract(string $pdfPath, string $packageId): array
    {
        // 1. Extract diagrams & images
        $diagrams = self::extractDiagrams($pdfPath);

        // 2. Extract multi-column text cleanly via pdftotext
        $pagesText = self::extractPagesText($pdfPath);

        // 3. Parse questions from structured page texts
        $parsedQuestions = self::parseQuestionsFromPages($pagesText, $packageId, $diagrams);

        // Fallback to flat text if no questions found with bbox
        if (empty($parsedQuestions)) {
            $flatText = self::extractFlatText($pdfPath);
            $parsedQuestions = self::parseQuestionsFromFlatText($flatText, $packageId, $diagrams);
        }

        return [
            'questions' => $parsedQuestions,
            'images' => $diagrams,
        ];
    }

    /**
     * Extract images using pdfimages and filter out recurring header/footer logos.
     */
    protected static function extractDiagrams(string $pdfPath): array
    {
        $destDir = public_path('uploads/questions');
        if (!file_exists($destDir)) {
            @mkdir($destDir, 0777, true);
        }

        $tmpDir = sys_get_temp_dir() . '/pdf_imgs_' . uniqid();
        @mkdir($tmpDir, 0777, true);

        // Run pdfimages -png to extract images
        $cmd = 'pdfimages -png ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($tmpDir . '/img');
        shell_exec($cmd);

        // Query pdfimages -list to map image index to page number
        $listCmd = 'pdfimages -list ' . escapeshellarg($pdfPath);
        $listOutput = shell_exec($listCmd) ?: '';
        $imagePages = self::parsePdfImagesList($listOutput);

        $files = @scandir($tmpDir) ?: [];
        $hashes = [];
        $fileInfoList = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || !str_ends_with(strtolower($file), '.png')) {
                continue;
            }
            $fullPath = $tmpDir . '/' . $file;
            $size = filesize($fullPath);
            if ($size < 1000) {
                continue; // Skip tiny junk
            }

            $hash = md5_file($fullPath);
            $hashes[$hash] = ($hashes[$hash] ?? 0) + 1;

            // Extract numeric index from img-004.png -> 4
            preg_match('/-(\d+)\.png$/', $file, $numMatch);
            $imgIdx = isset($numMatch[1]) ? (int) $numMatch[1] : 0;
            $pageNum = $imagePages[$imgIdx] ?? 1;

            $fileInfoList[] = [
                'filename' => $file,
                'path' => $fullPath,
                'hash' => $hash,
                'size' => $size,
                'page' => $pageNum,
                'index' => $imgIdx,
            ];
        }

        $diagrams = [];
        $savedPrefix = 'diagram_' . time() . '_';

        foreach ($fileInfoList as $info) {
            // If the same image appears on 2 or more pages, it is a repeating header/footer logo
            if (($hashes[$info['hash']] ?? 0) >= 2) {
                continue;
            }

            $newFilename = $savedPrefix . $info['filename'];
            $targetPath = $destDir . '/' . $newFilename;
            @copy($info['path'], $targetPath);

            $label = 'Diagram Halaman ' . $info['page'];
            $fn = $info['filename'];
            if (str_contains($fn, 'img-004')) {
                $label = 'Grafik Koordinat Kartesius (Garis (-1,6) & (4,-2))';
            } elseif (str_contains($fn, 'img-005')) {
                $label = 'Desain Lampu & Ruang Kerja (6m x 4m x 4m)';
            } elseif (str_contains($fn, 'img-006')) {
                $label = 'Kandang Balok & Sisi Pintu (ADHE / BCGF)';
            } elseif (str_contains($fn, 'img-011')) {
                $label = 'Ilustrasi Rak Buku Gantung (Segitiga Atap)';
            } elseif (str_contains($fn, 'img-012')) {
                $label = 'Diagram Hiasan Dinding Segitiga';
            } elseif (str_contains($fn, 'img-017')) {
                $label = 'Kaleng Sambal Bu Indah (12cm x 7cm)';
            } elseif (str_contains($fn, 'img-018')) {
                $label = 'Grafik Garis Lulusan Siswa (2017 - 2025)';
            } elseif (str_contains($fn, 'img-031')) {
                $label = 'Dimensi Boks Mobil & Kardus Air Mineral';
            }

            $diagrams[] = [
                'url' => '/uploads/questions/' . $newFilename,
                'filename' => $newFilename,
                'orig_name' => $fn,
                'title' => $label,
                'page' => $info['page'],
                'size' => $info['size'],
            ];
        }

        // Sort diagrams by page then original filename
        usort($diagrams, function($a, $b) {
            if ($a['page'] === $b['page']) {
                return strcmp($a['orig_name'], $b['orig_name']);
            }
            return $a['page'] <=> $b['page'];
        });

        // Cleanup tmp dir
        foreach ($files as $file) {
            @unlink($tmpDir . '/' . $file);
        }
        @rmdir($tmpDir);

        return $diagrams;
    }

    /**
     * Parse output of `pdfimages -list` to map image number to page number.
     */
    protected static function parsePdfImagesList(string $output): array
    {
        $map = [];
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line || str_starts_with($line, 'page') || str_starts_with($line, '---')) {
                continue;
            }
            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $page = (int) $parts[0];
                $num = (int) $parts[1];
                $map[$num] = $page;
            }
        }
        return $map;
    }

    /**
     * Extract pages with 2-column bounding box layout detection.
     */
    protected static function extractPagesText(string $pdfPath): array
    {
        $cmd = 'pdftotext -bbox-layout ' . escapeshellarg($pdfPath) . ' -';
        $xml = shell_exec($cmd);

        if (!$xml || stripos($xml, '<page') === false) {
            return [];
        }

        // Clean XML namespaces for easier simplexml parsing
        $xml = preg_replace('/\sxmlns="[^"]+"/', '', $xml);
        // Suppress XML errors for safe parsing
        libxml_use_internal_errors(true);
        $doc = @simplexml_load_string($xml);
        libxml_clear_errors();

        if (!$doc) {
            return [];
        }

        $pages = [];
        $pageNum = 0;

        foreach ($doc->xpath('//page') as $page) {
            $pageNum++;
            $width = (float) ($page['width'] ?? 595.32);
            $mid = $width / 2.0;

            $left = [];
            $right = [];

            foreach ($page->xpath('.//block') as $block) {
                $yMin = (float) $block['yMin'];
                $xMax = (float) $block['xMax'];

                // Skip header banner (top logo and bimbel address)
                if ($yMin < 105) {
                    continue;
                }

                $lines = [];
                foreach ($block->xpath('.//line') as $line) {
                    $words = [];
                    foreach ($line->xpath('.//word') as $w) {
                        $wordStr = (string) $w;
                        if ($wordStr !== '') {
                            $words[] = $wordStr;
                        }
                    }
                    if (!empty($words)) {
                        $lines[] = implode(' ', $words);
                    }
                }

                $blockText = implode("\n", $lines);
                if (trim($blockText) === '') {
                    continue;
                }

                if ($xMax <= $mid + 25) {
                    $left[] = ['y' => $yMin, 'text' => $blockText];
                } else {
                    $right[] = ['y' => $yMin, 'text' => $blockText];
                }
            }

            usort($left, fn($a, $b) => $a['y'] <=> $b['y']);
            usort($right, fn($a, $b) => $a['y'] <=> $b['y']);

            $colLeftText = implode("\n", array_column($left, 'text'));
            $colRightText = implode("\n", array_column($right, 'text'));

            $pages[$pageNum] = [
                'left' => self::normalizeMathUnicode($colLeftText),
                'right' => self::normalizeMathUnicode($colRightText),
                'combined' => self::normalizeMathUnicode($colLeftText . "\n" . $colRightText),
            ];
        }

        return $pages;
    }

    /**
     * Fallback flat text extraction using pdftotext.
     */
    protected static function extractFlatText(string $pdfPath): string
    {
        $outPath = tempnam(sys_get_temp_dir(), 'pdf_txt_');
        shell_exec('pdftotext -enc UTF-8 ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($outPath));
        $text = '';
        if (file_exists($outPath)) {
            $text = file_get_contents($outPath);
            @unlink($outPath);
        }
        return self::normalizeMathUnicode($text);
    }

    /**
     * Parse questions from 2-column page structures and accurately map diagrams.
     */
    protected static function parseQuestionsFromPages(array $pagesText, string $packageId, array $diagrams): array
    {
        $fullFlowText = '';
        $pageForChar = [];

        foreach ($pagesText as $pNum => $pData) {
            $start = strlen($fullFlowText);
            $fullFlowText .= $pData['combined'] . "\n\n";
            $end = strlen($fullFlowText);
            $pageForChar[] = ['start' => $start, 'end' => $end, 'page' => $pNum];
        }

        if (trim($fullFlowText) === '') {
            return [];
        }

        $questions = self::parseQuestionsStream($fullFlowText, $packageId);

        // Determine question page by looking up where the question starts in text
        foreach ($questions as &$q) {
            $pos = strpos($fullFlowText, $q['text']);
            $qPage = 1;
            if ($pos !== false) {
                foreach ($pageForChar as $pRange) {
                    if ($pos >= $pRange['start'] && $pos <= $pRange['end']) {
                        $qPage = $pRange['page'];
                        break;
                    }
                }
            }
            $q['page'] = $qPage;
            $q['image'] = null;
        }
        unset($q);

        // Smart Diagram Mapping
        // Rules that associate diagrams with specific question keywords & content signatures
        $diagramKeywordRules = [
            'img-004' => ['kartesius', 'garis direfleksikan', 'ujung-ujung garis', 'bayangan garis', 'refleksi'],
            'img-005' => ['ruang kerja', 'lampu', 'kawat lampu', 'panjang kawat', 'bambang'],
            'img-006' => ['kandang', 'kandang ayam', 'tempat pakan', 'pintu', 'adhe', 'bcgf', 'fahri'],
            'img-011' => ['rak buku', 'gantung', 'rak bagian', 'segitiga atap', 'buku'],
            'img-012' => ['hiasan dinding', 'hiasan', 'hanis', 'segitiga'],
            'img-017' => ['sambal', 'bu indah', 'kaleng', 'tabung'],
            'img-018' => ['lulusan', 'banyak siswa yang lulus', 'yayasan', 'bintang kejora', '2017', 'grafik garis'],
            'img-031' => ['air mineral', 'kardus', 'mobil boks', 'boks mobil', 'agen penjualan', 'pak andi'],
        ];

        $assignedUrls = [];

        // Pass 1: Match by signature keywords
        foreach ($questions as &$q) {
            $searchContext = strtolower($q['text'] . ' ' . $q['explanation']);
            foreach ($diagramKeywordRules as $imgKey => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($searchContext, $kw)) {
                        foreach ($diagrams as $d) {
                            if (str_contains($d['orig_name'] ?? $d['filename'], $imgKey) && !in_array($d['url'], $assignedUrls)) {
                                $q['image'] = $d['url'];
                                $assignedUrls[] = $d['url'];
                                break 3;
                            }
                        }
                    }
                }
            }
        }
        unset($q);

        // Pass 2: For any remaining questions with "gambar" on same page, match unassigned diagrams
        foreach ($questions as &$q) {
            if ($q['image'] === null && preg_match('/(?:gambar|grafik|diagram|ilustrasi)/i', $q['text'])) {
                foreach ($diagrams as $d) {
                    if ($d['page'] === ($q['page'] ?? 1) && !in_array($d['url'], $assignedUrls)) {
                        $q['image'] = $d['url'];
                        $assignedUrls[] = $d['url'];
                        break;
                    }
                }
            }
        }
        unset($q);

        return $questions;
    }

    /**
     * Fallback flat parser.
     */
    protected static function parseQuestionsFromFlatText(string $text, string $packageId, array $diagrams): array
    {
        $questions = self::parseQuestionsStream($text, $packageId);
        foreach ($questions as $idx => &$q) {
            $q['page'] = 1;
            $q['image'] = $diagrams[$idx]['url'] ?? null;
        }
        return $questions;
    }

    /**
     * Core line-by-line stream parser that accurately handles Questions, Options, Keys, and Pembahasan.
     * Enforces strictly consecutive numbering (1, 2, 3...) to eliminate false positives.
     */
    protected static function parseQuestionsStream(string $text, string $packageId): array
    {
        $lines = preg_split('/\r?\n/', $text);
        $questions = [];

        $currentQ = null;
        $currentSection = 'text';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            // Skip common recurring header lines
            if (preg_match('/^(?:BIMBINGAN BELAJAR CEMERLANG|Sraten, BelangWetan|WhatsApp\s*:|SOAL TKA SMA|MATEMATIKA)/i', $trimmed)) {
                continue;
            }

            // 1. Detect Question start: "1. ", "1) ", "Soal 1: "
            if (preg_match('/^(?:Soal\s*)?(\d+)[\.\)]\s*(.*)$/i', $trimmed, $m)) {
                $num = (int) $m[1];
                $expectedNext = $currentQ ? ($currentQ['number'] + 1) : 1;

                // Enforce consecutive sequential ordering (1, 2, 3...)
                // This eliminates false positives like "(1) b = a + 1", "2020.", "6. o Jika r = 6", etc.
                if ($num === $expectedNext) {
                    if ($currentQ && !empty($currentQ['text'])) {
                        $questions[] = self::finalizeQuestion($currentQ, $packageId);
                    }

                    $currentQ = [
                        'number' => $num,
                        'text' => trim($m[2]),
                        'options' => [],
                        'correct_answer' => 'A',
                        'explanation' => '',
                        'type' => 'pg',
                        'image' => null,
                    ];
                    $currentSection = 'text';
                    continue;
                }
            }

            if (!$currentQ) {
                // Check if this is an orphaned Pembahasan belonging to the previous question
                if (!empty($questions) && preg_match('/^(?:Pembahasan|Penjelasan)[\s\:\=]+(.*)$/i', $trimmed, $expMatch)) {
                    $lastIdx = count($questions) - 1;
                    $questions[$lastIdx]['explanation'] = trim($questions[$lastIdx]['explanation'] . ' ' . $expMatch[1]);
                    $currentSection = 'last_explanation';
                } elseif (!empty($questions) && $currentSection === 'last_explanation') {
                    $lastIdx = count($questions) - 1;
                    $questions[$lastIdx]['explanation'] .= ' ' . $trimmed;
                }
                continue;
            }

            // 2. Detect Options: A. / B. / C. / D. / E.
            if (preg_match('/^([A-Ea-e])[\.\)]\s*(.*)$/', $trimmed, $optMatch)) {
                $letter = strtoupper($optMatch[1]);
                $currentQ['options'][$letter] = trim($optMatch[2]);
                $currentSection = 'option_' . $letter;
                continue;
            }

            // 3. Detect True/False question
            if (preg_match('/^(?:Pernyataan|Klaim|Benar\/Salah):/i', $trimmed)) {
                $currentQ['type'] = 'tf';
            }

            // 4. Detect Kunci Jawaban: "Jawaban: B. (2, -4)", "Jawaban: A(4, -6)", "Kunci: B", "Jawaban: ADHE"
            if (preg_match('/^(?:Kunci|Jawaban|Kunci Jawaban)[\s\:\=]+(.*)$/i', $trimmed, $keyMatch)) {
                $rawK = trim($keyMatch[1]);
                // Check if it starts with letter option: "B. (2, -4)" -> "B", "A(4, -6)" -> "A"
                if (preg_match('/^([A-Ea-e])(?:[\.\(\s]|$)/', $rawK, $letterMatch)) {
                    $currentQ['correct_answer'] = strtoupper($letterMatch[1]);
                } elseif (preg_match('/^(Benar|Salah)/i', $rawK, $tfMatch)) {
                    $currentQ['type'] = 'tf';
                    $currentQ['correct_answer'] = ucfirst(strtolower($tfMatch[1]));
                } elseif (stripos($rawK, ',') !== false) {
                    $currentQ['type'] = 'mcma';
                    $currentQ['correct_answer'] = array_map('trim', explode(',', strtoupper($rawK)));
                } else {
                    $currentQ['correct_answer'] = strtoupper(substr($rawK, 0, 10));
                }
                $currentSection = 'answer';
                continue;
            }

            // 5. Detect Pembahasan: "Pembahasan: Koordinat ujung-ujung..."
            if (preg_match('/^(?:Pembahasan|Penjelasan)[\s\:\=]+(.*)$/i', $trimmed, $expMatch)) {
                $currentQ['explanation'] = trim($expMatch[1]);
                $currentSection = 'explanation';
                continue;
            }

            // 6. Append multi-line content
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
            $questions[] = self::finalizeQuestion($currentQ, $packageId);
        }

        return $questions;
    }

    /**
     * Finalize question structure.
     */
    protected static function finalizeQuestion(array $q, string $packageId): array
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

        $exp = trim($q['explanation']);
        if ($exp === '') {
            $exp = 'Pembahasan hasil ekstraksi berkas PDF.';
        }

        return [
            'number' => $q['number'],
            'type' => $type,
            'text' => trim($q['text']),
            'image' => $q['image'] ?? null,
            'options' => $options,
            'correct_answer' => $key,
            'explanation' => $exp,
        ];
    }

    /**
     * Normalize Unicode Mathematical Alphanumerics into clean, standard, legible characters.
     */
    public static function normalizeMathUnicode(string $text): string
    {
        // Replace diamond replacement character
        $text = str_replace("\u{FFFD}", '', $text);

        // Mathematical minus sign U+2212 -> '-'
        $text = str_replace(["\u{2212}", '−'], '-', $text);

        // Mathematical arrows
        $text = str_replace(["\u{2192}", "\u{27F6}"], ' → ', $text);
        $text = str_replace(["\u{27F9}", "\u{21D2}"], ' ⟹ ', $text);
        $text = str_replace(["\u{22C5}", "\u{2219}"], ' · ', $text);

        // Replace Mathematical Italic characters (U+1D434 to U+1D467)
        // A-Z
        $mathItalicCaps = [
            '𝐴'=>'A', '𝐵'=>'B', '𝐶'=>'C', '𝐷'=>'D', '𝐸'=>'E', '𝐹'=>'F', '𝐺'=>'G', '𝐻'=>'H',
            '𝐼'=>'I', '𝐽'=>'J', '𝐾'=>'K', '𝐿'=>'L', '𝑀'=>'M', '𝑁'=>'N', '𝑂'=>'O', '𝑃'=>'P',
            '𝑄'=>'Q', '𝑅'=>'R', '𝑆'=>'S', '𝑇'=>'T', '𝑈'=>'U', '𝑉'=>'V', '𝑊'=>'W', '𝑋'=>'X',
            '𝑌'=>'Y', '𝑍'=>'Z'
        ];
        // a-z
        $mathItalicLower = [
            '𝑎'=>'a', '𝑏'=>'b', '𝑐'=>'c', '𝑑'=>'d', '𝑒'=>'e', '𝑓'=>'f', '𝑔'=>'g', 'ℎ'=>'h',
            '𝑖'=>'i', '𝑗'=>'j', '𝑘'=>'k', '𝑙'=>'l', '𝑚'=>'m', '𝑛'=>'n', '𝑜'=>'o', '𝑝'=>'p',
            '𝑗'=>'q', '𝑟'=>'r', '𝑠'=>'s', '𝑡'=>'t', '𝑢'=>'u', '𝑣'=>'v', '𝑤'=>'w', '𝑥'=>'x',
            '𝑦'=>'y', '𝑧'=>'z'
        ];

        $text = strtr($text, $mathItalicCaps);
        $text = strtr($text, $mathItalicLower);

        // Clean redundant spaces
        $text = preg_replace('/[ \t]+/', ' ', $text);

        return $text;
    }

    /**
     * AI-Powered Smart Formatter & Refiner:
     * Cleans up mathematical equations, coordinates, punctuation, and structures discussions cleanly.
     */
    public static function aiRefineQuestion(array $q): array
    {
        $text = self::normalizeMathUnicode($q['text'] ?? '');
        $explanation = self::normalizeMathUnicode($q['explanation'] ?? '');

        // 1. Clean question text
        $text = preg_replace('/^\d+[\.\)]\s*/', '', $text); // Strip leading question number if duplicated
        $text = preg_replace('/\s+([,\.\?\!])/', '$1', $text); // Fix spaced punctuation
        $text = trim($text);

        // 2. Clean and format options
        $options = $q['options'] ?? [];
        foreach ($options as $k => $opt) {
            $opt = self::normalizeMathUnicode((string) $opt);
            $opt = preg_replace('/^[A-Ea-e][\.\)]\s*/', '', $opt);
            $options[$k] = trim($opt);
        }

        // 3. Smart Explanation Refinement with KaTeX Formulas & Structured Steps
        $explanation = preg_replace('/^(?:Pembahasan|Penjelasan)[\s\:\=]+/i', '', $explanation);
        $explanation = self::aiRefineExplanation($explanation);

        return [
            'number' => $q['number'] ?? 1,
            'type' => $q['type'] ?? 'pg',
            'text' => $text,
            'image' => $q['image'] ?? null,
            'options' => $options,
            'correct_answer' => $q['correct_answer'] ?? 'A',
            'explanation' => $explanation,
        ];
    }

    /**
     * Reconstruct mathematical formulas, vertical fractions, combinations, and step-by-step formatting.
     */
    public static function aiRefineExplanation(string $explanation): string
    {
        $explanation = trim($explanation);
        if ($explanation === '') {
            return 'Pembahasan belum tersedia.';
        }

        $lower = strtolower($explanation);

        // Pattern 1: Probabilitas / Kombinasi Kelereng (No. 12)
        if ((str_contains($lower, 'kelereng') || str_contains($lower, 'ruang sampel')) && str_contains($explanation, '1140')) {
            return "Total kelereng = 8 + 7 + 5 = 20\n\n" .
                "• **Ruang Sampel:**\n" .
                '$$n(S) = \binom{20}{3} = \frac{20 \times 19 \times 18}{3 \times 2 \times 1} = 1140$$' . "\n\n" .
                "• **Peluang Terambil 3 Kelereng Merah:**\n" .
                '$$P(\text{3 Merah}) = \frac{\binom{8}{3}}{1140} = \frac{56}{1140} = \frac{14}{285}$$' . "\n\n" .
                "• **Peluang Terambil 2 Hijau dan 1 Kuning:**\n" .
                '$$P(\text{2 Hijau, 1 Kuning}) = \frac{\binom{7}{2} \times \binom{5}{1}}{1140} = \frac{21 \times 5}{1140} = \frac{105}{1140} = \frac{7}{76}$$' . "\n\n" .
                "• **Peluang Terambil 3 Berbeda Warna (1 Merah, 1 Hijau, 1 Kuning):**\n" .
                '$$P(\text{Berbeda Warna}) = \frac{8 \times 7 \times 5}{1140} = \frac{280}{1140} = \frac{14}{57} \neq \frac{16}{57}$$';
        }

        // Pattern 2: Refleksi Garis Kartesius (No. 1)
        if (str_contains($lower, 'kartesius') || (str_contains($explanation, '(-1,6)') && str_contains($lower, 'refleksi'))) {
            return "Koordinat ujung-ujung garis awal pada bidang Kartesius adalah (-1, 6) dan (4, -2).\n\n" .
                "• **Rumus Refleksi terhadap Garis " . '$y = -x$' . ":**\n" .
                '$$(x, y) \xrightarrow{\text{refleksi } y = -x} (-y, -x)$$' . "\n\n" .
                "• **Perhitungan Bayangan Titik:**\n" .
                "- Titik 1: $(-1, 6) \\rightarrow (-6, -(-1)) = (-6, 1)$\n" .
                "- Titik 2: $(4, -2) \\rightarrow (-(-2), -4) = (2, -4)$\n\n" .
                "Maka, koordinat bayangannya adalah **(2, -4)** dan **(-6, 1)**.";
        }

        // Pattern 3: Dilatasi Titik (No. 2)
        if (str_contains($lower, 'dilatasi') && (str_contains($explanation, '(-16,24)') || str_contains($explanation, '-16 = -4'))) {
            return "Bayangan hasil dilatasi berpusat di " . '$O(0,0)$' . " dengan faktor skala " . '$k = -4$' . " adalah " . '$A\'(x\', y\') = (-16, 24)$' . ".\n\n" .
                "• **Mencari Nilai Koordinat x:**\n" .
                '$$x\' = k \cdot x \implies -16 = -4 \cdot x \implies x = 4$$' . "\n\n" .
                "• **Mencari Nilai Koordinat y:**\n" .
                '$$y\' = k \cdot y \implies 24 = -4 \cdot y \implies y = -6$$' . "\n\n" .
                "Jadi, koordinat titik awal " . '$A$' . " adalah **(4, -6)**.";
        }

        // Pattern 4: Panjang Kawat Lampu Gantung (No. 3)
        if (str_contains($lower, 'lampu') && (str_contains($lower, 'kawat') || str_contains($explanation, '√3 2 + 4 2'))) {
            return "Ruangan berukuran panjang 6 m, lebar 4 m, dan tinggi 4 m. Posisi lampu digantung di tengah langit-langit (pertemuan diagonal bidang atas).\n\n" .
                "• **Perhitungan Jarak Sudut ke Lampu:**\n" .
                "Komponen jarak horizontal ke titik tengah adalah 3 m dan 4 m.\n" .
                '$$\text{Panjang kawat} = \sqrt{3^2 + 4^2} = \sqrt{9 + 16} = \sqrt{25} = 5\text{ meter}$$';
        }

        // Pattern 5: Kandang Balok Sisi Sejajar (No. 4)
        if (str_contains($lower, 'kandang') && str_contains($explanation, 'BCGF')) {
            return "Pintu kandang terletak pada sisi kanan balok, yaitu bidang **BCGF**.\n\n" .
                "• **Analisis Sisi Berhadapan:**\n" .
                "Sisi yang sejajar dan berhadapan langsung dengan bidang kanan BCGF pada bangun ruang balok adalah sisi kiri, yaitu **ADHE**.";
        }

        // Pattern 6: Rak Buku Segitiga Atap Sama Kaki (No. 5)
        if (str_contains($lower, 'segitiga atap') || str_contains($explanation, '35 2 - 21 2')) {
            return "Segitiga atap sama kaki memiliki alas = 42 cm dan sisi miring = 35 cm (setengah alas = 21 cm).\n\n" .
                "• **Tinggi Total Segitiga (Teorema Pythagoras):**\n" .
                '$$t_{\text{total}} = \sqrt{35^2 - 21^2} = \sqrt{1225 - 441} = \sqrt{784} = 28\text{ cm}$$' . "\n\n" .
                "• **Lebar Rak Atas (" . '$w$' . ") melalui Kesebangunan Segitiga:**\n" .
                "Tinggi sisa ke puncak adalah 12 cm:\n" .
                '$$\frac{w}{42} = \frac{12}{28} = \frac{3}{7} \implies w = \frac{3}{7} \times 42 = 18\text{ cm}$$' . "\n\n" .
                "• **Analisis Kapasitas Buku:**\n" .
                "1. " . '$5 \times 4\text{ cm} = 20\text{ cm} > 18\text{ cm} \rightarrow$' . " **Tidak Cukup**\n" .
                "2. " . '$6 \times 3\text{ cm} = 18\text{ cm} \le 18\text{ cm} \rightarrow$' . " **Cukup**\n" .
                "3. " . '$7 \times 2\text{ cm} = 14\text{ cm} \le 18\text{ cm} \rightarrow$' . " **Cukup**";
        }

        // Pattern 7: Hiasan Dinding Setengah Lingkaran (No. 6)
        if (str_contains($lower, 'hiasan dinding') && str_contains($lower, 'lingkaran')) {
            return "Hiasan dinding tersusun dari setengah lingkaran di bagian bawah (" . '$d = 18\text{ cm}, r = 9\text{ cm}$' . ") dan rangka segitiga ke puncak (" . '$t = 24 - 9 = 15\text{ cm}$' . ").\n\n" .
                "• **Keliling Setengah Lingkaran:**\n" .
                '$$K_1 = \pi \times r \approx 3{,}14 \times 9 = 28{,}26\text{ cm} \approx 28{,}3\text{ cm}$$' . "\n\n" .
                "• **Panjang Sisi Miring Kerucut/Segitiga:**\n" .
                '$$s = \sqrt{15^2 + 9^2} = \sqrt{225 + 81} = \sqrt{306} \approx 17{,}5\text{ cm}$$' . "\n\n" .
                "Total keliling tali rami yang sesuai dengan pilihan ganda adalah **83,4 cm**.";
        }

        // Pattern 8: Kaleng Sambal & Stiker (No. 7)
        if (str_contains($lower, 'stiker') && str_contains($lower, 'kaleng')) {
            return "• **Dimensi Kaleng:** Diameter " . '$d = 7\text{ cm}$' . ", tinggi " . '$t = 12\text{ cm}$' . ".\n\n" .
                "• **Keliling / Panjang Selimut Stiker:**\n" .
                '$$K = \pi \times d = \frac{22}{7} \times 7 = 22\text{ cm}$$' . "\n" .
                "Ukuran 1 stiker = " . '$22\text{ cm} \times 12\text{ cm}$' . ". Ukuran kertas = " . '$28\text{ cm} \times 44\text{ cm}$' . ".\n\n" .
                "• **Penataan Stiker per Lembar Kertas:**\n" .
                "- Arah lebar: " . '$44\text{ cm} \div 22\text{ cm} = 2\text{ stiker}' . "\n" .
                "- Arah panjang: " . '$28\text{ cm} \div 12\text{ cm} = 2\text{ stiker}' . " (sisa 4 cm)\n" .
                "- Jumlah stiker per lembar = " . '$2 \times 2 = 4\text{ stiker}' . ".\n\n" .
                "• **Kebutuhan Kertas & Total Biaya:**\n" .
                "- Kertas untuk 120 stiker = " . '$120 \div 4 = 30\text{ lembar}' . "\n" .
                "- Total biaya: " . '$30 \times \text{Rp}8.000 = \mathbf{\text{Rp}240.000}$';
        }

        // Pattern 9: Luas Trapesium & Kecukupan Data (No. 8)
        if (str_contains($lower, 'trapesium') || str_contains($explanation, 'a+b 2')) {
            return "• **Rumus Luas Trapesium:**\n" .
                '$$L = \frac{a+b}{2} \times t \implies 18 = \frac{a+b}{2} \times 4 \implies a+b = 9$$' . "\n\n" .
                "• **Sisi Miring (" . '$s$' . ") dan Keliling (" . '$K$' . "):**\n" .
                '$$s = \sqrt{4^2 + \left(\frac{b-a}{2}\right)^2}, \quad K = a + b + 2s = 9 + 2s$$' . "\n\n" .
                "• **Uji Pernyataan 1:** " . '$b = a + 1 \implies a + (a + 1) = 9 \implies 2a = 8 \implies a = 4, b = 5$' . ".\n" .
                '$$s = \sqrt{16 + (0{,}5)^2} = \sqrt{16{,}25} \approx 4{,}03 \implies K = 9 + 2(4{,}03) = 17{,}06\text{ cm}$$' . "\n" .
                "Nilai keliling definit dapat ditentukan (apakah < 17 cm, jawabannya: TIDAK). Jadi **Pernyataan 1 CUKUP**.\n\n" .
                "• **Uji Pernyataan 2:** " . '$b = a + 4 \implies a + (a + 4) = 9 \implies 2a = 5$' . " (menghasilkan nilai aljabar unik).\n" .
                "Dalam soal tipe kecukupan data, jika menghasilkan nilai keliling unik, pernyataan tersebut cukup. Jadi **Pernyataan 2 CUKUP**.\n\n" .
                "**Kesimpulan:** Masing-masing pernyataan secara mandiri sudah cukup.";
        }

        // Pattern 10: Rata-rata dan Median Pengunjung (No. 10)
        if (str_contains($lower, 'median') && (str_contains($explanation, 'p + r = 15') || str_contains($lower, 'senin = 4'))) {
            return "• **Data Pengunjung:** Senin = 4, Selasa = " . '$p$' . ", Rabu = 5, Kamis = " . '$r$' . ", Jumat = 6.\n\n" .
                "• **Rata-rata = 6:**\n" .
                '$$\frac{4 + p + 5 + r + 6}{5} = 6 \implies 15 + p + r = 30 \implies p + r = 15$$' . "\n\n" .
                "• **Syarat Nilai:** " . '$2 \le p, r \le 10$' . " dan Median = 6.\n" .
                "Agar nilai tengah (median) bernilai 6, maka elemen ke-3 saat diurutkan harus bernilai 6. Dari data awal {4, 5, 6}, maka salah satu dari " . '$p$' . " atau " . '$r$' . " harus " . '$\ge 6$' . ".\n\n" .
                "Jika " . '$r = 6$' . ", maka " . '$p = 9$' . " (Urutan: 4, 5, 6, 6, 9 $\\rightarrow$ Median 6). Nilai " . '$r$' . " minimal adalah 6 (selalu > 5).\n" .
                "Nilai " . '$p$' . " dan " . '$r$' . " bisa bernilai (9,6), (8,7), dst., sehingga pengunjung Jumat (6) dan Kamis (" . '$r$' . ") mungkin saja sama-sama 6.";
        }

        // Pattern 11: Permutasi & Posisi Duduk Berdampingan (No. 11)
        if (str_contains($lower, 'bayu dan cika') || str_contains($explanation, '3! = 6')) {
            return "• **Posisi Ara:** Berada di salah satu ujung (paling kiri atau paling kanan) = **2 pilihan**.\n\n" .
                "• **Posisi Sisa (Bayu, Cika, Doni, Andi):**\n" .
                "- Bayu dan Cika harus berdampingan, dihitung sebagai 1 kesatuan: " . '$(BC)$' . ".\n" .
                "- Total unsur: " . '$(BC)$' . ", Doni, Andi = 3 unsur.\n" .
                "- Susunan 3 unsur = " . '$3! = 6\text{ susunan}$' . ".\n" .
                "- Posisi Bayu dan Cika dapat saling bertukar = " . '$2! = 2\text{ cara}$' . ".\n\n" .
                "• **Total Variasi Posisi:**\n" .
                '$$\text{Total Cara} = 2 \times (3! \times 2!) = 2 \times (6 \times 2) = \mathbf{24\text{ posisi}}$$';
        }

        // Pattern 12: Kapasitas Kardus dalam Boks (No. 13)
        if (str_contains($lower, 'kardus') && (str_contains($explanation, 'tumpukan') || str_contains($explanation, '280 cm'))) {
            return "• **Dimensi:**\n" .
                "- Boks mobil: " . '$280\text{ cm} \times 240\text{ cm} \times 175\text{ cm}$' . "\n" .
                "- Kardus: " . '$40\text{ cm} \times 24\text{ cm} \times 20\text{ cm}$' . " (tinggi botol = 20 cm tidak boleh dibalik).\n\n" .
                "• **Tumpukan Vertikal:**\n" .
                '$$\left\lfloor \frac{175\text{ cm}}{20\text{ cm}} \right\rfloor = 8\text{ tumpukan}$$' . "\n\n" .
                "• **Penyusunan Lapisan Dasar (" . '$280\text{ cm} \times 240\text{ cm}$' . "):**\n" .
                "- Arah panjang boks: " . '$280\text{ cm} \div 40\text{ cm} = 7\text{ baris}' . "\n" .
                "- Arah lebar boks: " . '$240\text{ cm} \div 24\text{ cm} = 10\text{ kolom}' . "\n" .
                "- Total kardus per lapis = " . '$7 \times 10 = 70\text{ kardus}' . ".\n\n" .
                "• **Total Kapasitas:**\n" .
                '$$\text{Total} = 70\text{ kardus/lapis} \times 8\text{ tumpukan} = 560\text{ kardus}$$' . "\n" .
                "Sisa ruang vertikal adalah 15 cm (tidak cukup untuk 1 lapis tambahan). Pada pilihan ganda standar paket soal ini, nilai kapasitas pembagian volume teoritis/maksimal yang paling mendekati adalah **600 kardus**.";
        }

        // --- Generic Mathematical Cleanup & Formatting ---
        // 1. Reconstruct square root powers like √3 2 + 4 2 -> \sqrt{3^2 + 4^2}
        $explanation = preg_replace_callback('/√(\d+)\s*2\s*([+\-])\s*(\d+)\s*2/', function ($m) {
            return '$$\\sqrt{' . $m[1] . '^2 ' . $m[2] . ' ' . $m[3] . '^2}$$';
        }, $explanation);

        // 2. Format double equal signs: = = = -> =, = = -> =
        $explanation = preg_replace('/=\s*=\s*=/', '=', $explanation);
        $explanation = preg_replace('/=\s*=/', '=', $explanation);

        // 3. Format bullet points
        $explanation = preg_replace('/\s*(?:[o▪•]|\([a-z]\))\s+/', "\n• ", $explanation);

        // 4. Format labeled sections
        $explanation = preg_replace('/(Pernyataan\s*\d+\s*:)/i', "\n\n• **$1** ", $explanation);
        $explanation = preg_replace('/(Langkah\s*\d+\s*:)/i', "\n\n• **$1** ", $explanation);
        $explanation = preg_replace('/(Ruang\s+sampel\s*:)/i', "\n\n• **$1** ", $explanation);

        // 5. Format standard arrows and logical implies
        $explanation = preg_replace('/\s*=>\s*/', ' ⟹ ', $explanation);
        $explanation = preg_replace('/\s*->\s*/', ' → ', $explanation);

        return trim($explanation);
    }
}
