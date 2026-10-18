<?php

namespace App\Services;

use ZipArchive;

class DocxQuestionExtractor
{
    /**
     * Extract questions, options, answers, explanations, and diagrams from Word (.docx) file.
     */
    public static function extract(string $docxPath, string $packageId): array
    {
        $destDir = public_path('uploads/questions');
        if (!file_exists($destDir)) {
            @mkdir($destDir, 0777, true);
        }

        $diagrams = [];
        $textLines = [];

        $zip = new ZipArchive();
        if ($zip->open($docxPath) === true) {
            // 1. Extract embedded media/images from word/media/
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (str_starts_with($entryName, 'word/media/')) {
                    $ext = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
                    if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
                        $imgData = $zip->getFromIndex($i);
                        if (strlen($imgData) > 500) {
                            $origName = basename($entryName);
                            $newFilename = 'docx_' . time() . '_' . $origName;
                            @file_put_contents($destDir . '/' . $newFilename, $imgData);

                            $diagrams[] = [
                                'url' => '/uploads/questions/' . $newFilename,
                                'filename' => $newFilename,
                                'orig_name' => $origName,
                                'title' => 'Gambar Word: ' . $origName,
                                'page' => 1,
                                'size' => strlen($imgData),
                            ];
                        }
                    }
                }
            }

            // 2. Extract paragraphs from word/document.xml
            $xml = $zip->getFromName('word/document.xml');
            if ($xml) {
                // Strip w: namespace prefix so SimpleXML can parse standard tags
                $cleanXml = preg_replace('/<(\/?)w:/', '<$1', $xml);
                $cleanXml = preg_replace('/\sxmlns:[^=]+="[^"]+"/', '', $cleanXml);
                $cleanXml = preg_replace('/\sxmlns="[^"]+"/', '', $cleanXml);
                libxml_use_internal_errors(true);
                $doc = @simplexml_load_string($cleanXml);
                libxml_clear_errors();

                if ($doc) {
                    foreach ($doc->xpath('//p') as $p) {
                        $pTexts = [];
                        foreach ($p->xpath('.//t') as $t) {
                            $pTexts[] = (string) $t;
                        }
                        $line = trim(implode('', $pTexts));
                        if ($line !== '') {
                            $textLines[] = $line;
                        }
                    }
                }
            }

            $zip->close();
        }

        if (empty($textLines)) {
            return [
                'questions' => [],
                'images' => $diagrams,
            ];
        }

        $fullText = implode("\n", $textLines);
        $fullText = PdfQuestionExtractor::normalizeMathUnicode($fullText);

        // Parse questions using the same robust stream parser
        $questions = self::parseQuestionsStream($fullText, $packageId);

        // Assign diagrams to questions
        $assigned = [];
        foreach ($questions as &$q) {
            $context = strtolower($q['text'] . ' ' . $q['explanation']);
            if (preg_match('/(?:gambar|grafik|diagram|ilustrasi|tabel|denah|desain)/i', $context)) {
                foreach ($diagrams as $d) {
                    if (!in_array($d['url'], $assigned)) {
                        $q['image'] = $d['url'];
                        $assigned[] = $d['url'];
                        break;
                    }
                }
            }
        }
        unset($q);

        return [
            'questions' => $questions,
            'images' => $diagrams,
        ];
    }

    /**
     * Parse stream of text with strict sequential question numbers.
     */
    protected static function parseQuestionsStream(string $text, string $packageId): array
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
                $num = (int) $m[1];
                $expectedNext = $currentQ ? ($currentQ['number'] + 1) : 1;

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

            // Options: A. / B. / C. / D. / E.
            if (preg_match('/^([A-Ea-e])[\.\)]\s*(.*)$/', $trimmed, $optMatch)) {
                $letter = strtoupper($optMatch[1]);
                $currentQ['options'][$letter] = trim($optMatch[2]);
                $currentSection = 'option_' . $letter;
                continue;
            }

            // True / False
            if (preg_match('/^(?:Pernyataan|Klaim|Benar\/Salah):/i', $trimmed)) {
                $currentQ['type'] = 'tf';
            }

            // Kunci Jawaban
            if (preg_match('/^(?:Kunci|Jawaban|Kunci Jawaban)[\s\:\=]+(.*)$/i', $trimmed, $keyMatch)) {
                $rawK = trim($keyMatch[1]);
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

            // Pembahasan
            if (preg_match('/^(?:Pembahasan|Penjelasan)[\s\:\=]+(.*)$/i', $trimmed, $expMatch)) {
                $currentQ['explanation'] = trim($expMatch[1]);
                $currentSection = 'explanation';
                continue;
            }

            // Append multiline content
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
            $exp = 'Pembahasan hasil ekstraksi berkas Word.';
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
}
