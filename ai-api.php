<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-guard.php';
if (PHP_SAPI !== 'cli') {
    require_teacher();
}


header('Content-Type: application/json; charset=utf-8');

function ai_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function get_gemini_api_key(): string
{
    $key = getenv('GEMINI_API_KEY');
    if ($key !== false && $key !== '') {
        return (string) $key;
    }
    $secretsPaths = [
        dirname(__DIR__, 2) . '/readpilot-secrets.php',
        'C:/xampp/readpilot-secrets.php',
        __DIR__ . '/readpilot.secrets.php'
    ];
    foreach ($secretsPaths as $path) {
        if (is_file($path)) {
            $secrets = (array) (require $path);
            if (!empty($secrets['GEMINI_API_KEY'])) {
                return (string) $secrets['GEMINI_API_KEY'];
            }
        }
    }
    return '';
}

/**
 * Deterministic heuristic evaluator: computes high quality, realistic pedagogical diagnostics
 * when Gemini API key is absent or when offline / rate-limited.
 */
function evaluate_session_heuristically(array $session, ?array $quiz, array $student, array $history): array
{
    $wpm = (int) ($session['wpm'] ?? 0);
    $accuracy = (int) ($session['accuracy'] ?? 0);
    $book = (string) ($session['book'] ?? 'Story');
    $trickyWordsRaw = (string) ($session['tricky_words'] ?? '');
    $trickyWords = array_values(array_filter(array_map('trim', explode(',', $trickyWordsRaw))));
    
    // Quiz metrics
    $quizScore = $quiz ? (int) $quiz['score'] : null;
    $quizTotal = $quiz ? (int) $quiz['total_questions'] : null;
    $quizPct = ($quizScore !== null && $quizTotal > 0) ? round(($quizScore / $quizTotal) * 100) : null;

    // Fluency Rating
    if ($wpm >= 280 && $accuracy >= 95) {
        $fluencyRating = 'advanced';
    } elseif ($wpm >= 210 && $accuracy >= 88) {
        $fluencyRating = 'proficient';
    } elseif ($wpm >= 160 && $accuracy >= 80) {
        $fluencyRating = 'approaching';
    } else {
        $fluencyRating = 'emerging';
    }

    // Comprehension Rating
    if ($quizPct === null) {
        $compRating = ($accuracy >= 92) ? 'good' : 'partial';
    } elseif ($quizPct >= 90) {
        $compRating = 'excellent';
    } elseif ($quizPct >= 70) {
        $compRating = 'good';
    } elseif ($quizPct >= 50) {
        $compRating = 'partial';
    } else {
        $compRating = 'needs_support';
    }

    // Baseline growth
    $prevAvgWpm = (float) ($history['avg_wpm'] ?? $wpm);
    $wpmDiff = $wpm - $prevAvgWpm;

    // Overall Progress Status
    if ($fluencyRating === 'advanced' && in_array($compRating, ['excellent', 'good'], true)) {
        $overallStatus = ($wpmDiff >= 15) ? 'rapid_growth' : 'on_track';
    } elseif (in_array($fluencyRating, ['proficient'], true) && in_array($compRating, ['excellent', 'good'], true)) {
        $overallStatus = 'on_track';
    } elseif ($fluencyRating === 'emerging' || $compRating === 'needs_support') {
        $overallStatus = 'needs_intervention';
    } else {
        $overallStatus = 'steady';
    }

    // Generate narrative dictation
    $name = htmlspecialchars((string) ($student['name'] ?? 'The student'), ENT_QUOTES, 'UTF-8');
    $narrativeParts = [];
    if ($fluencyRating === 'advanced') {
        $narrativeParts[] = "{$name} demonstrated expressive, fluent oral reading on \"{$book}\" with {$accuracy}% accuracy at {$wpm} WPM.";
    } elseif ($fluencyRating === 'proficient') {
        $narrativeParts[] = "{$name} read \"{$book}\" with solid pacing and word identification ({$accuracy}% accuracy, {$wpm} WPM).";
    } elseif ($fluencyRating === 'approaching') {
        $narrativeParts[] = "{$name} showed developing fluency on \"{$book}\" at {$wpm} WPM ({$accuracy}% accuracy), demonstrating good effort with periodic pausing.";
    } else {
        $narrativeParts[] = "{$name} experienced notable decoding difficulty while reading \"{$book}\" ({$accuracy}% accuracy at {$wpm} WPM), requiring focused phonemic scaffolding.";
    }

    if ($quizPct !== null) {
        $narrativeParts[] = "In comprehension testing, {$name} achieved {$quizScore}/{$quizTotal} ({$quizPct}%), showing " . 
            ($quizPct >= 80 ? 'strong synthesis of the main ideas.' : ($quizPct >= 60 ? 'adequate recall with opportunities to deepen inference.' : 'difficulty with key recall and story details.'));
    }

    $narrative = implode(' ', $narrativeParts);

    // Phonics insight
    if (!empty($trickyWords)) {
        $sampleWords = implode(', ', array_slice($trickyWords, 0, 3));
        $phonicsInsight = "Hesitation noted on targeted vocabulary: {$sampleWords}. Recommend reinforcing syllable division and silent letter identification.";
    } else {
        $phonicsInsight = "Smooth decoding across high-frequency and multi-syllabic vocabulary with self-monitoring and immediate self-correction.";
    }

    // Comprehension insight
    if ($quizPct !== null) {
        $compInsight = $quizPct >= 80 
            ? "Solid comprehension grasp; successfully identified plot sequence and character motivation."
            : "Literal recall was present, but inference and contextual deduction require reinforcement.";
    } else {
        $compInsight = "Oral accuracy suggests receptive understanding; follow up with checking comprehension questions.";
    }

    // Strengths
    $strengths = [];
    if ($accuracy >= 90) $strengths[] = "Strong sight word recognition and decoding automaticity ({$accuracy}%)";
    if ($wpm >= 220) $strengths[] = "Confident reading tempo and natural phrasing ({$wpm} WPM)";
    if ($quizPct !== null && $quizPct >= 75) $strengths[] = "High comprehension accuracy on post-reading test ({$quizPct}%)";
    if (empty($strengths)) $strengths[] = "Persistent engagement and willingness to read through unfamiliar text";

    // Struggles
    $struggles = [];
    if (!empty($trickyWords)) {
        $struggles[] = "Decoding specific vocabulary: " . implode(', ', array_slice($trickyWords, 0, 4));
    }
    if ($accuracy < 85) $struggles[] = "Word-level pronunciation accuracy dropped below 85%";
    if ($quizPct !== null && $quizPct < 70) $struggles[] = "Comprehension test score fell below target threshold ({$quizPct}%)";
    if (empty($struggles)) $struggles[] = "Minor pacing fluctuations on complex sentences";

    // Next step
    if ($overallStatus === 'needs_intervention') {
        $nextStep = "Schedule a 1-on-1 phonics session focusing on tricky words (" . implode(', ', array_slice($trickyWords, 0, 3)) . ") and paired echo-reading.";
    } elseif ($overallStatus === 'rapid_growth') {
        $nextStep = "Introduce higher Lexile challenges with rich expository text to nurture analytical comprehension.";
    } else {
        $nextStep = "Continue current guided reading routine with a 2-minute pre-reading vocabulary preview before the next chapter.";
    }

    return [
        'fluency_rating'          => $fluencyRating,
        'comprehension_rating'    => $compRating,
        'overall_progress_status' => $overallStatus,
        'progress_narrative'      => $narrative,
        'phonics_insight'         => $phonicsInsight,
        'comprehension_insight'   => $compInsight,
        'strengths_json'          => $strengths,
        'struggles_json'          => $struggles,
        'actionable_next_step'    => $nextStep,
        'model_name'              => 'readpilot-heuristic-engine',
    ];
}

function call_gemini_api(string $apiKey, array $session, ?array $quiz, array $student, array $history): ?array
{
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key=' . urlencode($apiKey);

    $wpm = (int) ($session['wpm'] ?? 0);
    $accuracy = (int) ($session['accuracy'] ?? 0);
    $book = (string) ($session['book'] ?? 'Unknown Book');
    $duration = (int) ($session['duration_seconds'] ?? 0);
    $trickyWords = (string) ($session['tricky_words'] ?? 'None recorded');
    $studentName = (string) ($student['name'] ?? 'Student');
    $grade = (string) ($student['grade_level'] ?? 'Grade 3');
    
    $quizScore = $quiz ? (int) $quiz['score'] : 'N/A';
    $quizTotal = $quiz ? (int) $quiz['total_questions'] : 'N/A';
    $prevWpm = round((float) ($history['avg_wpm'] ?? $wpm));
    $prevAcc = round((float) ($history['avg_acc'] ?? $accuracy));

    $prompt = <<<EOT
You are an expert elementary literacy specialist and reading diagnostic evaluator for ReadPilot AI.
Analyze this primary student's reading session and comprehension test performance to dictate their progress report.

STUDENT PROFILE:
- Name: {$studentName}
- Grade Level: {$grade}
- Book/Story Read: "{$book}"

PERFORMANCE DATA:
- Oral Reading Fluency: {$wpm} Words Per Minute (WPM)
- Oral Reading Accuracy: {$accuracy}%
- Session Duration: {$duration} seconds
- Tricky / Paused Words: {$trickyWords}
- Comprehension Quiz Score: {$quizScore} out of {$quizTotal}
- Historical Baseline: {$prevWpm} avg WPM, {$prevAcc}% avg accuracy

Evaluate the combined oral reading + comprehension performance and return a JSON object with EXACTLY this structure:
{
  "fluency_rating": "advanced" | "proficient" | "approaching" | "emerging",
  "comprehension_rating": "excellent" | "good" | "partial" | "needs_support",
  "overall_progress_status": "rapid_growth" | "on_track" | "steady" | "needs_intervention",
  "progress_narrative": "2 to 3 sentences dictating the student's progress and performance this session.",
  "phonics_insight": "Specific observation on decoding, pronunciation, and phonics patterns.",
  "comprehension_insight": "Specific observation on understanding, recall, and quiz result.",
  "strengths": ["Strength 1", "Strength 2"],
  "struggles": ["Struggle or tricky word pattern 1", "Struggle 2"],
  "actionable_next_step": "1 or 2 concrete actionable recommendations for the teacher to guide the student's next reading."
}
EOT;

    $payload = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.2,
            'responseMimeType' => 'application/json'
        ]
    ];

    $ch = curl_init($url);
    if ($ch === false) {
        return null;
    }

    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !is_string($response)) {
        return null;
    }

    $json = json_decode($response, true);
    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    if ($text === '') {
        return null;
    }

    $parsed = json_decode($text, true);
    if (!is_array($parsed) || empty($parsed['progress_narrative'])) {
        return null;
    }

    return [
        'fluency_rating'          => in_array($parsed['fluency_rating'] ?? '', ['advanced', 'proficient', 'approaching', 'emerging'], true) ? $parsed['fluency_rating'] : 'proficient',
        'comprehension_rating'    => in_array($parsed['comprehension_rating'] ?? '', ['excellent', 'good', 'partial', 'needs_support'], true) ? $parsed['comprehension_rating'] : 'good',
        'overall_progress_status' => in_array($parsed['overall_progress_status'] ?? '', ['rapid_growth', 'on_track', 'steady', 'needs_intervention'], true) ? $parsed['overall_progress_status'] : 'on_track',
        'progress_narrative'      => (string) ($parsed['progress_narrative'] ?? ''),
        'phonics_insight'         => (string) ($parsed['phonics_insight'] ?? ''),
        'comprehension_insight'   => (string) ($parsed['comprehension_insight'] ?? ''),
        'strengths_json'          => is_array($parsed['strengths'] ?? null) ? $parsed['strengths'] : [],
        'struggles_json'          => is_array($parsed['struggles'] ?? null) ? $parsed['struggles'] : [],
        'actionable_next_step'    => (string) ($parsed['actionable_next_step'] ?? ''),
        'model_name'              => 'gemini-3.5-flash-lite',
    ];
}

$action = (string) ($_POST['action'] ?? ($_GET['action'] ?? ''));
if ($action === '' && PHP_SAPI === 'cli') {
    return;
}

$pdo = db();
$user = current_user();
$teacherId = (int) ($user['id'] ?? 0);
if ($teacherId <= 0) {
    ai_response(['error' => 'Unauthorized'], 401);
}


try {
    // -------------------------------------------------------------
    // ACTION: evaluate_session
    // -------------------------------------------------------------
    if ($action === 'evaluate_session') {
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        if ($sessionId <= 0) {
            ai_response(['error' => 'Valid session_id is required.'], 422);
        }

        // Fetch reading session
        $sessStmt = $pdo->prepare('SELECT rs.*, s.name AS student_name FROM reading_sessions rs INNER JOIN students s ON s.id = rs.student_id WHERE rs.id = ? AND rs.teacher_id = ? LIMIT 1');
        $sessStmt->execute([$sessionId, $teacherId]);
        $session = $sessStmt->fetch();
        if (!$session) {
            ai_response(['error' => 'Reading session not found.'], 404);
        }

        $studentId = (int) $session['student_id'];

        // Fetch linked or closest quiz attempt
        $quizStmt = $pdo->prepare(
            'SELECT * FROM quiz_attempts WHERE teacher_id = ? AND student_id = ? AND (session_id = ? OR (session_id IS NULL AND title = ? AND created_at >= DATE_SUB(?, INTERVAL 2 HOUR))) ORDER BY created_at DESC LIMIT 1'
        );
        $quizStmt->execute([$teacherId, $studentId, $sessionId, $session['book'], $session['created_at']]);
        $quiz = $quizStmt->fetch() ?: null;

        // Fetch student info
        $stuStmt = $pdo->prepare('SELECT s.*, u.grade_level FROM students s LEFT JOIN users u ON u.id = s.teacher_id WHERE s.id = ? LIMIT 1');
        $stuStmt->execute([$studentId]);
        $student = $stuStmt->fetch() ?: [];

        // Historical baseline
        $histStmt = $pdo->prepare('SELECT AVG(wpm) AS avg_wpm, AVG(accuracy) AS avg_acc FROM reading_sessions WHERE student_id = ? AND teacher_id = ? AND id <> ?');
        $histStmt->execute([$studentId, $teacherId, $sessionId]);
        $history = $histStmt->fetch() ?: [];

        // Call Gemini or fallback heuristic
        $apiKey = get_gemini_api_key();
        $evaluation = null;
        if ($apiKey !== '') {
            $evaluation = call_gemini_api($apiKey, $session, $quiz, $student, $history);
        }
        if ($evaluation === null) {
            $evaluation = evaluate_session_heuristically($session, $quiz, $student, $history);
        }

        // Persist to session_ai_evaluations
        $upsertStmt = $pdo->prepare(
            'INSERT INTO session_ai_evaluations 
             (teacher_id, student_id, session_id, quiz_attempt_id, fluency_rating, comprehension_rating, overall_progress_status, progress_narrative, phonics_insight, comprehension_insight, strengths_json, struggles_json, actionable_next_step, model_name)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE 
                quiz_attempt_id = VALUES(quiz_attempt_id),
                fluency_rating = VALUES(fluency_rating),
                comprehension_rating = VALUES(comprehension_rating),
                overall_progress_status = VALUES(overall_progress_status),
                progress_narrative = VALUES(progress_narrative),
                phonics_insight = VALUES(phonics_insight),
                comprehension_insight = VALUES(comprehension_insight),
                strengths_json = VALUES(strengths_json),
                struggles_json = VALUES(struggles_json),
                actionable_next_step = VALUES(actionable_next_step),
                model_name = VALUES(model_name)'
        );

        $quizAttemptId = $quiz ? (int) $quiz['id'] : null;
        $upsertStmt->execute([
            $teacherId,
            $studentId,
            $sessionId,
            $quizAttemptId,
            $evaluation['fluency_rating'],
            $evaluation['comprehension_rating'],
            $evaluation['overall_progress_status'],
            $evaluation['progress_narrative'],
            $evaluation['phonics_insight'],
            $evaluation['comprehension_insight'],
            json_encode($evaluation['strengths_json'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            json_encode($evaluation['struggles_json'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $evaluation['actionable_next_step'],
            $evaluation['model_name']
        ]);

        // If quiz was found without session_id, link it now
        if ($quiz && empty($quiz['session_id'])) {
            $pdo->prepare('UPDATE quiz_attempts SET session_id = ? WHERE id = ?')->execute([$sessionId, $quiz['id']]);
        }

        // Bridge to recommendations if intervention is flagged
        if (in_array($evaluation['overall_progress_status'], ['needs_intervention'], true) || !empty($session['tricky_words'])) {
            $existingRec = $pdo->prepare('SELECT id FROM recommendations WHERE teacher_id = ? AND student_id = ? AND related_book = ? LIMIT 1');
            $existingRec->execute([$teacherId, $studentId, $session['book']]);
            if (!$existingRec->fetchColumn()) {
                $recStmt = $pdo->prepare('INSERT INTO recommendations (teacher_id, student_id, source, status, related_book, skill_focus, practice_notes) VALUES (?, ?, \'ai\', \'pending\', ?, ?, ?)');
                $recStmt->execute([
                    $teacherId,
                    $studentId,
                    $session['book'],
                    $evaluation['phonics_insight'],
                    $evaluation['actionable_next_step']
                ]);
            }
        }

        ai_response([
            'ok'         => true,
            'evaluation' => $evaluation,
            'quiz'       => $quiz ? [
                'score'           => (int) $quiz['score'],
                'total_questions' => (int) $quiz['total_questions']
            ] : null
        ]);
    }

    // -------------------------------------------------------------
    // ACTION: get_evaluation
    // -------------------------------------------------------------
    if ($action === 'get_evaluation') {
        $sessionId = (int) ($_GET['session_id'] ?? 0);
        $statement = $pdo->prepare('SELECT * FROM session_ai_evaluations WHERE session_id = ? AND teacher_id = ? LIMIT 1');
        $statement->execute([$sessionId, $teacherId]);
        $row = $statement->fetch();
        if (!$row) {
            ai_response(['evaluation' => null]);
        }
        $row['strengths'] = json_decode((string) $row['strengths_json'], true) ?: [];
        $row['struggles'] = json_decode((string) $row['struggles_json'], true) ?: [];
        unset($row['strengths_json'], $row['struggles_json']);
        ai_response(['evaluation' => $row]);
    }

    // -------------------------------------------------------------
    // ACTION: get_student_evaluations
    // -------------------------------------------------------------
    if ($action === 'get_student_evaluations') {
        $studentId = (int) ($_GET['student_id'] ?? 0);
        $statement = $pdo->prepare(
            'SELECT e.*, rs.book, rs.wpm, rs.accuracy, rs.duration_seconds, qa.score AS quiz_score, qa.total_questions AS quiz_total, UNIX_TIMESTAMP(e.created_at) * 1000 AS ts
             FROM session_ai_evaluations e
             INNER JOIN reading_sessions rs ON rs.id = e.session_id
             LEFT JOIN quiz_attempts qa ON qa.id = e.quiz_attempt_id
             WHERE e.student_id = ? AND e.teacher_id = ?
             ORDER BY e.created_at DESC'
        );
        $statement->execute([$studentId, $teacherId]);
        $rows = $statement->fetchAll();
        foreach ($rows as &$r) {
            $r['strengths'] = json_decode((string) $r['strengths_json'], true) ?: [];
            $r['struggles'] = json_decode((string) $r['struggles_json'], true) ?: [];
            unset($r['strengths_json'], $r['struggles_json']);
        }
        ai_response(['evaluations' => $rows]);
    }

    ai_response(['error' => 'Unknown AI action.'], 400);
} catch (Throwable $e) {
    error_log('AI API failure: ' . $e->getMessage());
    ai_response(['error' => 'AI processing failed: ' . $e->getMessage()], 500);
}
