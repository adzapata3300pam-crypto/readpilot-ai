<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-guard.php';
require_teacher();
header('Content-Type: application/json');

$pdo = db();
$teacherId = (int) current_user()['id'];

function recommendation_response(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS recommendations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT UNSIGNED NOT NULL,
        student_id INT UNSIGNED NOT NULL,
        source ENUM('ai', 'specialist', 'manual') NOT NULL DEFAULT 'manual',
        status ENUM('pending', 'approved', 'applied', 'dismissed') NOT NULL DEFAULT 'pending',
        related_book VARCHAR(190) NOT NULL DEFAULT '',
        skill_focus VARCHAR(190) NOT NULL DEFAULT '',
        practice_notes TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        approved_at DATETIME NULL,
        applied_at DATETIME NULL,
        INDEX idx_recommendations_teacher_status (teacher_id, status, created_at),
        CONSTRAINT fk_recommendation_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_recommendation_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $statement = $pdo->prepare(
            'SELECT r.id, r.student_id AS studentId, s.name AS studentName, s.color, r.source, r.status,
                    r.related_book AS relatedBook, r.skill_focus AS skillFocus, r.practice_notes AS practiceNotes,
                    r.created_at AS createdAt, r.approved_at AS approvedAt, r.applied_at AS appliedAt
             FROM recommendations r INNER JOIN students s ON s.id = r.student_id
             WHERE r.teacher_id = ? AND r.status <> \'dismissed\' ORDER BY r.created_at DESC'
        );
        $statement->execute([$teacherId]);
        $recommendations = $statement->fetchAll();
        $statsStatement = $pdo->prepare(
            "SELECT
                SUM(source = 'ai' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS aiThisWeek,
                SUM(status = 'pending') AS pending,
                SUM(status = 'approved') AS approved,
                SUM(status = 'applied') AS applied
             FROM recommendations WHERE teacher_id = ? AND status <> 'dismissed'"
        );
        $statsStatement->execute([$teacherId]);
        $stats = $statsStatement->fetch() ?: [];
        foreach (['aiThisWeek', 'pending', 'approved', 'applied'] as $key) $stats[$key] = (int) ($stats[$key] ?? 0);
        recommendation_response(['recommendations' => $recommendations, 'stats' => $stats]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') recommendation_response(['error' => 'Method not allowed'], 405);
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $studentCheck = $pdo->prepare('SELECT id FROM students WHERE id = ? AND teacher_id = ?');
        $studentCheck->execute([$studentId, $teacherId]);
        if (!$studentCheck->fetchColumn()) recommendation_response(['error' => 'Student not found'], 404);
        $skill = trim((string) ($_POST['skill_focus'] ?? ''));
        $notes = trim((string) ($_POST['practice_notes'] ?? ''));
        if ($skill === '' || $notes === '') recommendation_response(['error' => 'Skill focus and practice notes are required'], 422);
        $statement = $pdo->prepare('INSERT INTO recommendations (teacher_id, student_id, source, status, related_book, skill_focus, practice_notes) VALUES (?, ?, \'manual\', \'pending\', ?, ?, ?)');
        $statement->execute([$teacherId, $studentId, trim((string) ($_POST['related_book'] ?? '')), $skill, $notes]);
        recommendation_response(['ok' => true, 'id' => (int) $pdo->lastInsertId()]);
    }

    if (in_array($action, ['approve', 'apply', 'dismiss'], true)) {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $action === 'approve' ? 'approved' : ($action === 'apply' ? 'applied' : 'dismissed');
        $statement = $pdo->prepare("UPDATE recommendations SET status = ?, approved_at = CASE WHEN ? IN ('approved', 'applied') THEN COALESCE(approved_at, NOW()) ELSE approved_at END, applied_at = CASE WHEN ? = 'applied' THEN NOW() ELSE applied_at END WHERE id = ? AND teacher_id = ?");
        $statement->execute([$status, $status, $status, $id, $teacherId]);
        if ($statement->rowCount() !== 1) recommendation_response(['error' => 'Recommendation not found'], 404);
        recommendation_response(['ok' => true]);
    }

    recommendation_response(['error' => 'Unknown action'], 400);
} catch (Throwable $exception) {
    recommendation_response(['error' => 'Unable to load recommendations'], 500);
}