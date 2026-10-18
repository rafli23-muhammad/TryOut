<?php

namespace App\Http\Controllers;

use App\Services\TryoutDataStore;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    protected function checkStudentAuth()
    {
        if (!session('student_id')) {
            return redirect('/login')->with('error', 'Sesi siswa telah berakhir. Silakan login kembali.');
        }
        return null;
    }

    public function startExam(Request $request, $packageId)
    {
        if ($redirect = $this->checkStudentAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $studentId = session('student_id');
        $package = collect($data['packages'] ?? [])->firstWhere('id', $packageId);

        if (!$package) {
            return redirect('/student/dashboard')->with('error', 'Paket ujian tidak ditemukan.');
        }

        // Verify approved order
        $hasAccess = collect($data['orders'] ?? [])->contains(function ($o) use ($studentId, $packageId) {
            return $o['student_id'] === $studentId && $o['package_id'] === $packageId && $o['status'] === 'approved';
        });

        if (!$hasAccess) {
            return redirect('/student/catalog')->with('error', 'Anda belum memiliki akses ke paket ujian ini.');
        }

        // Get questions
        $questions = collect($data['questions'] ?? [])
            ->where('package_id', $packageId)
            ->sortBy('number')
            ->values();

        if ($questions->isEmpty()) {
            return redirect('/student/dashboard')->with('error', 'Belum ada soal yang tersedia pada paket ini.');
        }

        // Check for an ongoing active session
        $existingSession = collect($data['exam_sessions'] ?? [])->first(function ($s) use ($studentId, $packageId) {
            return $s['student_id'] === $studentId
                && $s['package_id'] === $packageId
                && ($s['status'] ?? '') === 'in_progress';
        });

        if ($existingSession) {
            $sessionId = $existingSession['id'];
            $session = $existingSession;
        } else {
            $durationSeconds = (int) ($package['duration_minutes'] ?? 60) * 60;
            $sessionId = 'SES-' . strtoupper(substr(md5(uniqid()), 0, 8));
            $now = date('Y-m-d H:i:s');
            $endTime = date('Y-m-d H:i:s', time() + $durationSeconds);

            $session = [
                'id' => $sessionId,
                'student_id' => $studentId,
                'student_name' => session('student_name'),
                'student_email' => session('student_email'),
                'package_id' => $packageId,
                'package_title' => $package['title'],
                'duration_seconds' => $durationSeconds,
                'started_at' => $now,
                'end_time' => $endTime,
                'finished_at' => null,
                'status' => 'in_progress',
                'answers' => [],
                'doubt_flags' => [],
                'violation_count' => 0,
                'last_snapshot' => null,
                'last_seen' => $now,
            ];

            $data['exam_sessions'][] = $session;
            TryoutDataStore::write($data);
        }

        // Prepare clean questions array for exam view (hide correct_answer and explanation during exam!)
        $safeQuestions = $questions->map(function ($q, $index) {
            return [
                'id' => $q['id'],
                'number' => $index + 1,
                'type' => $q['type'] ?? 'pg',
                'text' => $q['text'],
                'image' => $q['image'] ?? null,
                'options' => $q['options'] ?? [],
            ];
        })->values();

        $savedAnswers = $session['answers'] ?? [];
        $savedDoubts = $session['doubt_flags'] ?? [];
        $violationCount = $session['violation_count'] ?? 0;
        $maxViolations = (int) ($package['max_violations'] ?? 3);

        // Calculate remaining seconds
        $endTimeTs = strtotime($session['end_time']);
        $remainingSeconds = max(0, $endTimeTs - time());

        return view('exam.room', compact(
            'package',
            'session',
            'safeQuestions',
            'savedAnswers',
            'savedDoubts',
            'violationCount',
            'maxViolations',
            'remainingSeconds'
        ));
    }

    public function saveAnswer(Request $request, $sessionId)
    {
        $data = TryoutDataStore::read();
        $studentId = session('student_id');

        $sessionIndex = null;
        foreach ($data['exam_sessions'] ?? [] as $i => $s) {
            if ($s['id'] === $sessionId && $s['student_id'] === $studentId) {
                $sessionIndex = $i;
                break;
            }
        }

        if ($sessionIndex === null) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        $questionId = $request->input('question_id');
        $answer = $request->input('answer');
        $isDoubt = $request->boolean('is_doubt');

        if ($questionId) {
            $data['exam_sessions'][$sessionIndex]['answers'][$questionId] = $answer;

            $doubts = $data['exam_sessions'][$sessionIndex]['doubt_flags'] ?? [];
            if ($isDoubt) {
                if (!in_array($questionId, $doubts)) {
                    $doubts[] = $questionId;
                }
            } else {
                $doubts = array_values(array_filter($doubts, fn($q) => $q !== $questionId));
            }
            $data['exam_sessions'][$sessionIndex]['doubt_flags'] = $doubts;
            $data['exam_sessions'][$sessionIndex]['last_seen'] = date('Y-m-d H:i:s');

            TryoutDataStore::write($data);
        }

        return response()->json(['success' => true]);
    }

    public function uploadSnapshot(Request $request, $sessionId)
    {
        $data = TryoutDataStore::read();
        $studentId = session('student_id');
        $image = $request->input('image');

        if (!$image) {
            return response()->json(['error' => 'No image data'], 400);
        }

        foreach ($data['exam_sessions'] as &$s) {
            if ($s['id'] === $sessionId && $s['student_id'] === $studentId) {
                $s['last_snapshot'] = $image;
                $s['last_seen'] = date('Y-m-d H:i:s');
                break;
            }
        }
        unset($s);

        // Also update active proctorings map for real-time monitoring
        $packageId = null;
        foreach ($data['exam_sessions'] as $s) {
            if ($s['id'] === $sessionId) {
                $packageId = $s['package_id'];
                break;
            }
        }

        $data['active_proctorings'][$studentId] = [
            'session_id' => $sessionId,
            'student_id' => $studentId,
            'student_name' => session('student_name'),
            'student_email' => session('student_email'),
            'package_id' => $packageId,
            'last_snapshot' => $image,
            'last_seen' => date('Y-m-d H:i:s'),
            'violation_count' => session('violation_count', 0),
        ];

        TryoutDataStore::write($data);
        return response()->json(['success' => true]);
    }

    public function recordViolation(Request $request, $sessionId)
    {
        $data = TryoutDataStore::read();
        $studentId = session('student_id');
        $violationType = $request->input('violation_type', 'window_blur');
        $violationMessage = $request->input('violation_message', 'Pelanggaran pengawasan terdeteksi.');
        $snapshotImage = $request->input('snapshot_image');

        $sessionIndex = null;
        foreach ($data['exam_sessions'] ?? [] as $i => $s) {
            if ($s['id'] === $sessionId && $s['student_id'] === $studentId) {
                $sessionIndex = $i;
                break;
            }
        }

        if ($sessionIndex === null) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        $currentCount = ($data['exam_sessions'][$sessionIndex]['violation_count'] ?? 0) + 1;
        $data['exam_sessions'][$sessionIndex]['violation_count'] = $currentCount;
        $data['exam_sessions'][$sessionIndex]['last_seen'] = date('Y-m-d H:i:s');
        if ($snapshotImage) {
            $data['exam_sessions'][$sessionIndex]['last_snapshot'] = $snapshotImage;
        }

        $package = collect($data['packages'] ?? [])->firstWhere('id', $data['exam_sessions'][$sessionIndex]['package_id']);
        $maxViolations = (int) ($package['max_violations'] ?? 3);
        $isBlocked = ($currentCount >= $maxViolations);

        if ($isBlocked) {
            $data['exam_sessions'][$sessionIndex]['status'] = 'blocked';
        }

        $logEntry = [
            'id' => uniqid('viol_'),
            'session_id' => $sessionId,
            'student_id' => $studentId,
            'student_name' => session('student_name'),
            'package_id' => $data['exam_sessions'][$sessionIndex]['package_id'],
            'package_title' => $package['title'] ?? 'Try Out',
            'timestamp' => date('Y-m-d H:i:s'),
            'violation_type' => $violationType,
            'violation_message' => $violationMessage,
            'violation_count' => $currentCount,
            'is_blocked' => $isBlocked,
            'snapshot_image' => $snapshotImage,
        ];

        $data['violation_logs'][] = $logEntry;
        TryoutDataStore::write($data);

        return response()->json([
            'success' => true,
            'violation_count' => $currentCount,
            'max_violations' => $maxViolations,
            'is_blocked' => $isBlocked,
        ]);
    }

    public function submitExam(Request $request, $sessionId)
    {
        if ($redirect = $this->checkStudentAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $studentId = session('student_id');

        $sessionIndex = null;
        foreach ($data['exam_sessions'] ?? [] as $i => $s) {
            if ($s['id'] === $sessionId && $s['student_id'] === $studentId) {
                $sessionIndex = $i;
                break;
            }
        }

        if ($sessionIndex === null) {
            return redirect('/student/dashboard')->with('error', 'Sesi ujian tidak valid.');
        }

        $session = $data['exam_sessions'][$sessionIndex];
        $packageId = $session['package_id'];
        $package = collect($data['packages'] ?? [])->firstWhere('id', $packageId);
        $passingGrade = (float) ($package['passing_grade'] ?? 70);

        // Merge any final answers passed in form
        $answers = $request->input('answers', $session['answers'] ?? []);
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?: [];
        }

        $questions = collect($data['questions'] ?? [])
            ->where('package_id', $packageId)
            ->sortBy('number')
            ->values();

        $totalQuestions = $questions->count();
        $correctCount = 0;
        $wrongCount = 0;
        $emptyCount = 0;
        $evaluations = [];

        foreach ($questions as $q) {
            $qId = $q['id'];
            $qType = $q['type'] ?? 'pg';
            $correctKey = $q['correct_answer'];
            $studentAns = $answers[$qId] ?? null;

            $isCorrect = false;
            $isEmpty = ($studentAns === null || $studentAns === '' || $studentAns === []);

            if ($isEmpty) {
                $emptyCount++;
            } else {
                if ($qType === 'pg') {
                    // Pilihan Ganda (Single Choice)
                    $isCorrect = (strtoupper(trim((string) $studentAns)) === strtoupper(trim((string) $correctKey)));
                } elseif ($qType === 'mcma') {
                    // Multiple Choice Multiple Answer (Checkbox)
                    $studentArray = is_array($studentAns) ? $studentAns : explode(',', (string) $studentAns);
                    $correctArray = is_array($correctKey) ? $correctKey : explode(',', (string) $correctKey);

                    $studentArray = array_values(array_filter(array_map(fn($v) => strtoupper(trim((string) $v)), $studentArray)));
                    $correctArray = array_values(array_filter(array_map(fn($v) => strtoupper(trim((string) $v)), $correctArray)));
                    sort($studentArray);
                    sort($correctArray);

                    $isCorrect = ($studentArray === $correctArray);
                } elseif ($qType === 'tf') {
                    // True / False (Benar / Salah)
                    $isCorrect = (strcasecmp(trim((string) $studentAns), trim((string) $correctKey)) === 0);
                }

                if ($isCorrect) {
                    $correctCount++;
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
            ];
        }

        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 1) : 0;
        $isPassed = ($score >= $passingGrade);

        $now = date('Y-m-d H:i:s');
        $session['status'] = 'submitted';
        $session['finished_at'] = $now;
        $session['answers'] = $answers;
        $session['total_questions'] = $totalQuestions;
        $session['correct_count'] = $correctCount;
        $session['wrong_count'] = $wrongCount;
        $session['empty_count'] = $emptyCount;
        $session['score'] = $score;
        $session['passing_grade'] = $passingGrade;
        $session['is_passed'] = $isPassed;
        $session['evaluations'] = $evaluations;

        $data['exam_sessions'][$sessionIndex] = $session;
        unset($data['active_proctorings'][$studentId]);
        TryoutDataStore::write($data);

        return redirect('/exam/' . $sessionId . '/result');
    }

    public function showResult($sessionId)
    {
        if ($redirect = $this->checkStudentAuth()) return $redirect;

        $data = TryoutDataStore::read();
        $studentId = session('student_id');

        $session = collect($data['exam_sessions'] ?? [])->firstWhere('id', $sessionId);
        if (!$session) {
            return redirect('/student/dashboard')->with('error', 'Data hasil ujian tidak ditemukan.');
        }

        // Must be authorized student or admin
        if ($session['student_id'] !== $studentId && !session('admin_id')) {
            return redirect('/student/dashboard')->with('error', 'Akses ditolak.');
        }

        $package = collect($data['packages'] ?? [])->firstWhere('id', $session['package_id']);
        $questions = collect($data['questions'] ?? [])
            ->where('package_id', $session['package_id'])
            ->sortBy('number')
            ->values();

        $evaluations = $session['evaluations'] ?? [];

        return view('exam.result', compact('session', 'package', 'questions', 'evaluations'));
    }
}
