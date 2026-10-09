<?php

declare(strict_types=1);

function ensure_quiz_ai_report(PDO $pdo, int $teacherId, int $attemptId): array
{
    $attemptStatement = $pdo->prepare(
        'SELECT qa.id, qa.teacher_id, qa.student_id, qa.title, qa.score, qa.total_questions, qa.created_at, s.name AS student_name
         FROM quiz_attempts qa
         INNER JOIN students s ON s.id = qa.student_id AND s.teacher_id = qa.teacher_id
         WHERE qa.id = ? AND qa.teacher_id = ? LIMIT 1'
    );
    $attemptStatement->execute([$attemptId, $teacherId]);
    $attempt = $attemptStatement->fetch();
    if (!$attempt) {
        throw new RuntimeException('Quiz attempt not found.');
    }

    $existingStatement = $pdo->prepare('SELECT * FROM quiz_ai_reports WHERE quiz_attempt_id = ? AND teacher_id = ? LIMIT 1');
    $existingStatement->execute([$attemptId, $teacherId]);
    $existing = $existingStatement->fetch();
    if ($existing) {
        return $existing;
    }

    $readingStatement = $pdo->prepare(
        'SELECT COUNT(*) AS session_count, ROUND(AVG(recent.wpm)) AS avg_wpm, ROUND(AVG(recent.accuracy)) AS avg_accuracy
         FROM (
             SELECT wpm, accuracy FROM reading_sessions
             WHERE teacher_id = ? AND student_id = ? AND created_at <= ?
             ORDER BY created_at DESC LIMIT 5
         ) recent'
    );
    $readingStatement->execute([$teacherId, $attempt['student_id'], $attempt['created_at']]);
    $reading = $readingStatement->fetch() ?: [];
    $readingCount = (int) ($reading['session_count'] ?? 0);
    $readingWpm = $reading['avg_wpm'] !== null ? (int) $reading['avg_wpm'] : null;
    $readingAccuracy = $reading['avg_accuracy'] !== null ? (int) $reading['avg_accuracy'] : null;

    $score = (int) $attempt['score'];
    $total = (int) $attempt['total_questions'];
    $scorePct = $total > 0 ? (int) round(($score / $total) * 100) : 0;
    $readsConfidently = $readingWpm !== null && $readingWpm >= 210 && $readingAccuracy >= 88;

    if ($scorePct < 70 && $readsConfidently) {
        $summary = $attempt['student_name'] . ' reads confidently, but this quiz suggests comprehension needs support.';
        $recommendation = 'After reading, practice retelling events in order, explaining the main idea, and supporting answers with clues from the text.';
    } elseif ($scorePct < 70) {
        $summary = 'This quiz suggests ' . $attempt['student_name'] . ' needs support understanding and recalling what they read.';
        $recommendation = 'Use a short passage, pause to clarify unfamiliar ideas, then ask the student to retell key details and explain answers using the text.';
    } elseif ($scorePct < 85 && $readsConfidently) {
        $summary = $attempt['student_name'] . ' reads confidently; quiz results show comprehension is developing.';
        $recommendation = 'Build inference and main-idea skills with prediction questions and evidence-based discussion after each passage.';
    } elseif ($scorePct < 85) {
        $summary = 'Quiz results show developing comprehension for ' . $attempt['student_name'] . ', alongside reading skills that can continue to strengthen.';
        $recommendation = 'Reread short sections together and use who, what, why, and how questions before gradually increasing text difficulty.';
    } elseif ($readsConfidently) {
        $summary = $attempt['student_name'] . ' reads confidently and demonstrates strong comprehension on this quiz.';
        $recommendation = 'Maintain progress with richer texts and questions that ask the student to compare ideas, explain evidence, and make inferences.';
    } else {
        $summary = 'Quiz results indicate strong comprehension for ' . $attempt['student_name'] . '; reading fluency may benefit from continued practice.';
        $recommendation = 'Continue repeated oral reading with feedback on pace and accuracy, while preserving the student’s strength in understanding the text.';
    }

    $saveStatement = $pdo->prepare(
        'INSERT INTO quiz_ai_reports
         (quiz_attempt_id, teacher_id, student_id, reading_session_count, reading_wpm, reading_accuracy, summary, recommendation)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $saveStatement->execute([
        $attemptId,
        $teacherId,
        (int) $attempt['student_id'],
        $readingCount,
        $readingWpm,
        $readingAccuracy,
        $summary,
        $recommendation
    ]);

    return [
        'quiz_attempt_id' => $attemptId,
        'teacher_id' => $teacherId,
        'student_id' => (int) $attempt['student_id'],
        'reading_session_count' => $readingCount,
        'reading_wpm' => $readingWpm,
        'reading_accuracy' => $readingAccuracy,
        'summary' => $summary,
        'recommendation' => $recommendation
    ];
}
