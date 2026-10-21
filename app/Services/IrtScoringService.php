<?php

namespace App\Services;

class IrtScoringService
{
    /**
     * Bobot IRT berdasarkan tingkat kesulitan butir soal:
     * - Mudah: Soal dengan daya serap tinggi / tingkat kesulitan rendah (Bobot 1.0)
     * - Sedang: Soal dengan daya serap menengah / standar nasional (Bobot 1.6)
     * - Sukar: Soal tingkat analitis tinggi / pembeda utama kemampuan (Bobot 2.4)
     */
    public const DIFFICULTY_WEIGHTS = [
        'mudah' => 1.0,
        'sedang' => 1.6,
        'sukar' => 2.4,
    ];

    /**
     * Parameter tingkat kesulitan (b) untuk estimasi kemampuan logistik (Theta)
     */
    public const DIFFICULTY_B_PARAMS = [
        'mudah' => -1.2,
        'sedang' => 0.0,
        'sukar' => 1.4,
    ];

    /**
     * Evaluasi lembar jawaban ujian menggunakan metode Item Response Theory (IRT).
     *
     * @param array $package
     * @param array $questions
     * @param array $answers [question_id => submitted_answer]
     * @param array $allSessions (koleksi sesi ujian untuk kalibrasi empiris)
     * @return array
     */
    public static function evaluateExam(array $package, array $questions, array $answers, array $allSessions = []): array
    {
        $empiricalDifficulty = self::calculateEmpiricalDifficulty($questions, $allSessions);

        $totalQuestions = count($questions);
        $correctCount = 0;
        $wrongCount = 0;
        $emptyCount = 0;

        $totalMaxWeight = 0.0;
        $earnedWeight = 0.0;

        $evaluations = [];
        $breakdown = [
            'mudah' => ['total' => 0, 'correct' => 0, 'earned_weight' => 0.0, 'max_weight' => 0.0],
            'sedang' => ['total' => 0, 'correct' => 0, 'earned_weight' => 0.0, 'max_weight' => 0.0],
            'sukar' => ['total' => 0, 'correct' => 0, 'earned_weight' => 0.0, 'max_weight' => 0.0],
        ];

        foreach ($questions as $index => $q) {
            $qId = $q['id'];
            $qType = $q['type'] ?? 'pg';
            $correctKey = $q['correct_answer'] ?? null;
            $studentAns = $answers[$qId] ?? null;

            // Tentukan tingkat kesulitan butir
            $difficulty = strtolower($q['difficulty'] ?? $empiricalDifficulty[$qId] ?? self::defaultDifficultyByIndex($index, $totalQuestions));
            if (!in_array($difficulty, ['mudah', 'sedang', 'sukar'])) {
                $difficulty = 'sedang';
            }

            $weight = self::DIFFICULTY_WEIGHTS[$difficulty] ?? 1.6;
            $totalMaxWeight += $weight;

            $breakdown[$difficulty]['total']++;
            $breakdown[$difficulty]['max_weight'] += $weight;

            $isCorrect = false;
            $isEmpty = ($studentAns === null || $studentAns === '' || $studentAns === []);

            if ($isEmpty) {
                $emptyCount++;
            } else {
                if ($qType === 'pg') {
                    $isCorrect = (strtoupper(trim((string)$studentAns)) === strtoupper(trim((string)$correctKey)));
                } elseif ($qType === 'tf') {
                    $isCorrect = (strtolower(trim((string)$studentAns)) === strtolower(trim((string)$correctKey)));
                } elseif ($qType === 'mcma') {
                    $stArr = is_array($studentAns) ? $studentAns : [$studentAns];
                    $crArr = is_array($correctKey) ? $correctKey : [$correctKey];
                    sort($stArr);
                    sort($crArr);
                    $isCorrect = ($stArr === $crArr);
                }

                if ($isCorrect) {
                    $correctCount++;
                    $earnedWeight += $weight;
                    $breakdown[$difficulty]['correct']++;
                    $breakdown[$difficulty]['earned_weight'] += $weight;
                } else {
                    $wrongCount++;
                }
            }

            $evaluations[$qId] = [
                'type' => $qType,
                'student_answer' => $studentAns,
                'correct_answer' => $correctKey,
                'is_correct' => $isCorrect,
                'is_empty' => $isEmpty,
                'difficulty' => ucfirst($difficulty),
                'irt_weight' => $weight,
                'earned_weight' => $isCorrect ? $weight : 0.0,
            ];
        }

        // Rasio bobot IRT terbobot (0.0 sampai 1.0)
        $weightRatio = $totalMaxWeight > 0 ? ($earnedWeight / $totalMaxWeight) : 0.0;

        // Transformasi ke Skala Resmi Standar UTBK SNBT / TKA (200 - 1000)
        $irtScore = round(200 + ($weightRatio * 800), 1);

        // Akurasi mentah (% jawaban benar)
        $accuracy = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 1) : 0.0;

        // Estimasi Kemampuan Theta (-3.00 s/d +3.00)
        $theta = round(($weightRatio - 0.5) * 6, 2);

        // Ambang batas Passing Grade
        $pkgPassingGrade = (float)($package['passing_grade'] ?? 650);
        $effectivePassingGrade = $pkgPassingGrade <= 100 
            ? round(200 + ($pkgPassingGrade / 100) * 800) 
            : $pkgPassingGrade;

        $isPassed = ($irtScore >= $effectivePassingGrade);

        return [
            'score' => $irtScore,
            'irt_score' => $irtScore,
            'raw_accuracy' => $accuracy,
            'theta' => $theta,
            'passing_grade' => $effectivePassingGrade,
            'is_passed' => $isPassed,
            'total_questions' => $totalQuestions,
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
            'empty_count' => $emptyCount,
            'total_weight' => round($totalMaxWeight, 2),
            'earned_weight' => round($earnedWeight, 2),
            'scoring_system' => 'IRT (Item Response Theory)',
            'breakdown' => $breakdown,
            'evaluations' => $evaluations,
        ];
    }

    /**
     * Kalibrasi empiris tingkat kesulitan butir soal berdasarkan rasio jawaban benar seluruh peserta.
     */
    public static function calculateEmpiricalDifficulty(array $questions, array $allSessions): array
    {
        $difficulties = [];
        if (empty($allSessions)) {
            return $difficulties;
        }

        $attemptsPerQ = [];
        $correctsPerQ = [];

        foreach ($allSessions as $session) {
            $evals = $session['evaluations'] ?? [];
            foreach ($evals as $qId => $ev) {
                if (!isset($attemptsPerQ[$qId])) {
                    $attemptsPerQ[$qId] = 0;
                    $correctsPerQ[$qId] = 0;
                }
                $attemptsPerQ[$qId]++;
                if (!empty($ev['is_correct'])) {
                    $correctsPerQ[$qId]++;
                }
            }
        }

        foreach ($questions as $q) {
            $qId = $q['id'];
            if (!empty($attemptsPerQ[$qId]) && $attemptsPerQ[$qId] >= 3) {
                $facility = $correctsPerQ[$qId] / $attemptsPerQ[$qId];
                if ($facility >= 0.70) {
                    $difficulties[$qId] = 'mudah';
                } elseif ($facility >= 0.35) {
                    $difficulties[$qId] = 'sedang';
                } else {
                    $difficulties[$qId] = 'sukar';
                }
            }
        }

        return $difficulties;
    }

    /**
     * Distribusi tingkat kesulitan default jika belum ada riwayat pengerjaan empiris.
     */
    public static function defaultDifficultyByIndex(int $index, int $total): string
    {
        $mod = $index % 3;
        if ($mod === 0) return 'mudah';
        if ($mod === 1) return 'sedang';
        return 'sukar';
    }
}
