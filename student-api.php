<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-guard.php';
require_teacher();
header('Content-Type: application/json');

$user = current_user();
$pdo = db();

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function owned_student(PDO $pdo, int $teacherId, int $studentId): array
{
    $statement = $pdo->prepare('SELECT * FROM students WHERE id = ? AND teacher_id = ? LIMIT 1');
    $statement->execute([$studentId, $teacherId]);
    $student = $statement->fetch();
    if (!$student) {
        json_response(['error' => 'Student not found'], 404);
    }
    return $student;
}

$teacherId = (int) $user['id'];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (($_GET['view'] ?? '') === 'sessions') {
        $statement = $pdo->prepare('SELECT rs.id, rs.student_id AS studentId, rs.book, rs.wpm, rs.accuracy, UNIX_TIMESTAMP(rs.created_at) * 1000 AS ts FROM reading_sessions rs WHERE rs.teacher_id = ? ORDER BY rs.created_at DESC');
        $statement->execute([$teacherId]);
        $sessions = $statement->fetchAll();
        $studentStatement = $pdo->prepare('SELECT s.id, s.name, s.color, s.book, sec.name AS section FROM students s INNER JOIN sections sec ON sec.id = s.section_id WHERE s.teacher_id = ? ORDER BY s.name');
        $studentStatement->execute([$teacherId]);
        $roster = $studentStatement->fetchAll();
        foreach ($sessions as &$session) {
            $session['id'] = (int) $session['id'];
            $session['studentId'] = (int) $session['studentId'];
            $session['wpm'] = (int) $session['wpm'];
            $session['accuracy'] = (int) $session['accuracy'];
            $session['ts'] = (int) $session['ts'];
        }
        foreach ($roster as &$student) {
            $student['id'] = (int) $student['id'];
        }
        json_response(['students' => $roster, 'sessions' => $sessions]);
    }
    if (($_GET['view'] ?? '') === 'reports') {
        $sectionStatement = $pdo->prepare('SELECT id, name FROM sections WHERE teacher_id = ? ORDER BY name');
        $sectionStatement->execute([$teacherId]);
        $reportSections = $sectionStatement->fetchAll();
        $studentStatement = $pdo->prepare('SELECT s.id, s.name, s.color, s.wpm, s.accuracy, sec.name AS section FROM students s INNER JOIN sections sec ON sec.id = s.section_id WHERE s.teacher_id = ? ORDER BY s.name');
        $studentStatement->execute([$teacherId]);
        $studentRows = $studentStatement->fetchAll();
        $sessionStatement = $pdo->prepare('SELECT id, student_id AS studentId, book, wpm, accuracy, UNIX_TIMESTAMP(created_at) * 1000 AS ts FROM reading_sessions WHERE teacher_id = ? ORDER BY created_at DESC');
        $sessionStatement->execute([$teacherId]);
        $reportSessions = $sessionStatement->fetchAll();
        foreach ($reportSessions as &$session) {
            $session['id'] = (int) $session['id'];
            $session['studentId'] = (int) $session['studentId'];
            $session['wpm'] = (int) $session['wpm'];
            $session['accuracy'] = (int) $session['accuracy'];
            $session['ts'] = (int) $session['ts'];
        }
        json_response(['students' => $studentRows, 'sessions' => $reportSessions, 'sections' => $reportSections]);
    }
    if (($_GET['view'] ?? '') === 'resources') {
        $statement = $pdo->prepare('SELECT r.id, r.title, r.author, r.genre, r.level, r.lexile, r.words, r.description AS `desc`, r.tags_json, COUNT(ra.id) AS assigned, (SELECT rm.content_text FROM resource_materials rm WHERE rm.resource_id = r.id AND rm.teacher_id = ? ORDER BY rm.id DESC LIMIT 1) AS content FROM resources r LEFT JOIN resource_assignments ra ON ra.resource_id = r.id AND ra.teacher_id = ? WHERE r.created_by IS NULL OR r.created_by = ? GROUP BY r.id ORDER BY r.title');
        $statement->execute([$teacherId, $teacherId, $teacherId]);
        $resources = $statement->fetchAll();
        foreach ($resources as &$resource) {
            $resource['id'] = (int) $resource['id'];
            $resource['words'] = (int) $resource['words'];
            $resource['assigned'] = (int) $resource['assigned'];
            $resource['canDelete'] = true;
            $resource['tags'] = json_decode($resource['tags_json'], true) ?: [];
            unset($resource['tags_json']);
        }
        json_response(['resources' => $resources]);
    }
    if (($_GET['view'] ?? '') === 'quizzes') {
        $statement = $pdo->prepare('SELECT id, resource_id, title, questions_json FROM teacher_quizzes WHERE teacher_id = ? ORDER BY title');
        $statement->execute([$teacherId]);
        $quizzes = $statement->fetchAll();
        foreach ($quizzes as &$quiz) {
            $quiz['id'] = (int) $quiz['id'];
            $quiz['resource_id'] = $quiz['resource_id'] === null ? null : (int) $quiz['resource_id'];
            $quiz['questions'] = json_decode($quiz['questions_json'], true) ?: [];
            unset($quiz['questions_json']);
        }
        json_response(['quizzes' => $quizzes]);
    }
    if (($_GET['view'] ?? '') === 'dashboard') {
        $studentStatement = $pdo->prepare('SELECT id, name, color, status FROM students WHERE teacher_id = ?');
        $studentStatement->execute([$teacherId]);
        $sessionStatement = $pdo->prepare('SELECT rs.id, rs.student_id AS studentId, s.name AS studentName, s.color, rs.book, rs.wpm, rs.accuracy, UNIX_TIMESTAMP(rs.created_at) * 1000 AS ts FROM reading_sessions rs INNER JOIN students s ON s.id = rs.student_id AND s.teacher_id = rs.teacher_id WHERE rs.teacher_id = ? ORDER BY rs.created_at DESC LIMIT 5');
        $sessionStatement->execute([$teacherId]);
        $summaryStatement = $pdo->prepare('SELECT COUNT(*) AS total, COALESCE(ROUND(AVG(wpm)), 0) AS average_wpm FROM reading_sessions WHERE teacher_id = ?');
        $summaryStatement->execute([$teacherId]);
        json_response(['students' => $studentStatement->fetchAll(), 'sessions' => $sessionStatement->fetchAll(), 'summary' => $summaryStatement->fetch()]);
    }
    if (($_GET['view'] ?? '') === 'struggle') {
        $statement = $pdo->prepare('SELECT rs.tricky_words, s.name AS student_name, s.color FROM reading_sessions rs INNER JOIN students s ON s.id = rs.student_id WHERE rs.teacher_id = ? AND rs.tricky_words <> \'\'');
        $statement->execute([$teacherId]);
        $words = [];
        foreach ($statement->fetchAll() as $row) {
            foreach (array_filter(array_map('trim', explode(',', (string) $row['tricky_words']))) as $word) {
                $key = strtolower($word);
                if (!isset($words[$key])) $words[$key] = ['word' => $word, 'count' => 0, 'students' => [], 'recent' => true];
                $words[$key]['count']++;
                if (!in_array($row['student_name'], $words[$key]['students'], true)) $words[$key]['students'][] = $row['student_name'];
            }
        }
        json_response(['words' => array_values($words)]);
    }
    $sections = $pdo->prepare('SELECT id, name FROM sections WHERE teacher_id = ? ORDER BY name');
    $sections->execute([$teacherId]);
    $students = $pdo->prepare(
        'SELECT s.id, s.name, s.color, s.book, s.wpm, s.accuracy, s.sessions_count AS sessions, s.sessions_this_week AS sessionsThisWeek, s.books_completed AS booksCompleted, s.status, s.last_active AS lastActive, s.notes, sec.name AS section
         FROM students s INNER JOIN sections sec ON sec.id = s.section_id
         WHERE s.teacher_id = ? ORDER BY s.name'
    );
    $students->execute([$teacherId]);
    $rows = $students->fetchAll();
    foreach ($rows as &$row) {
        $row['id'] = (int) $row['id'];
        $row['wpm'] = (int) $row['wpm'];
        $row['accuracy'] = (int) $row['accuracy'];
        $row['sessions'] = (int) $row['sessions'];
        $row['sessionsThisWeek'] = (int) $row['sessionsThisWeek'];
        $row['booksCompleted'] = (int) $row['booksCompleted'];
        $logStatement = $pdo->prepare('SELECT book, DATE_FORMAT(created_at, "%b %e · %l:%i %p") AS date, wpm, accuracy FROM reading_sessions WHERE teacher_id = ? AND student_id = ? ORDER BY created_at DESC LIMIT 5');
        $logStatement->execute([$teacherId, $row['id']]);
        $row['log'] = $logStatement->fetchAll();
    }
    json_response(['sections' => $sections->fetchAll(), 'students' => $rows]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$action = $_POST['action'] ?? '';
try {
    if ($action === 'save_student') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $sectionId = (int) ($_POST['section_id'] ?? 0);
        $color = trim((string) ($_POST['color'] ?? '#6fbf5a'));
        $book = trim((string) ($_POST['book'] ?? ''));
        if ($name === '' || $sectionId < 1) json_response(['error' => 'Name and section are required'], 422);
        $check = $pdo->prepare('SELECT id FROM sections WHERE id = ? AND teacher_id = ?');
        $check->execute([$sectionId, $teacherId]);
        if (!$check->fetchColumn()) json_response(['error' => 'Section does not belong to this teacher'], 403);
        if ($id > 0) {
            owned_student($pdo, $teacherId, $id);
            $statement = $pdo->prepare('UPDATE students SET name = ?, section_id = ?, color = ?, book = ? WHERE id = ? AND teacher_id = ?');
            $statement->execute([$name, $sectionId, $color, $book, $id, $teacherId]);
        } else {
            $statement = $pdo->prepare('INSERT INTO students (teacher_id, section_id, name, color, book) VALUES (?, ?, ?, ?, ?)');
            $statement->execute([$teacherId, $sectionId, $name, $color, $book]);
            $id = (int) $pdo->lastInsertId();
        }
        json_response(['ok' => true, 'id' => $id]);
    }
    if ($action === 'deactivate_student') {
        $id = (int) ($_POST['id'] ?? 0);
        $reason = trim((string) ($_POST['reason'] ?? ''));
        $student = owned_student($pdo, $teacherId, $id);
        if ($reason === '') json_response(['error' => 'A reason is required before deactivating a student'], 422);
        $note = trim((string) $student['notes']);
        $deactivationNote = 'Deactivated ' . date('Y-m-d H:i') . ': ' . $reason;
        $notes = $note === '' ? $deactivationNote : $note . "\n" . $deactivationNote;
        $pdo->prepare('UPDATE students SET status = \'inactive\', notes = ? WHERE id = ? AND teacher_id = ?')->execute([$notes, $id, $teacherId]);
        json_response(['ok' => true]);
    }
    if ($action === 'update_sections') {
        $names = json_decode((string) ($_POST['names'] ?? '[]'), true);
        if (!is_array($names) || count($names) !== 3 || count(array_unique(array_map('strtolower', $names))) !== 3) json_response(['error' => 'Three unique section names are required'], 422);
        $pdo->beginTransaction();
        $current = $pdo->prepare('SELECT id FROM sections WHERE teacher_id = ? ORDER BY id');
        $current->execute([$teacherId]);
        $ids = $current->fetchAll(PDO::FETCH_COLUMN);
        if (count($ids) !== 3) json_response(['error' => 'Teacher sections are not initialized'], 409);
        $update = $pdo->prepare('UPDATE sections SET name = ? WHERE id = ? AND teacher_id = ?');
        foreach ($ids as $index => $sectionId) $update->execute([trim((string) $names[$index]), $sectionId, $teacherId]);
        $pdo->commit();
        json_response(['ok' => true]);
    }
    if ($action === 'add_section') {
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') json_response(['error' => 'Section name is required'], 422);
        if (mb_strlen($name) > 80) json_response(['error' => 'Section name is too long'], 422);
        $duplicate = $pdo->prepare('SELECT id FROM sections WHERE teacher_id = ? AND LOWER(name) = LOWER(?)');
        $duplicate->execute([$teacherId, $name]);
        if ($duplicate->fetchColumn()) json_response(['error' => 'That section already exists'], 409);
        $statement = $pdo->prepare('INSERT INTO sections (teacher_id, name) VALUES (?, ?)');
        $statement->execute([$teacherId, $name]);
        json_response(['ok' => true, 'id' => (int) $pdo->lastInsertId()]);
    }
    if ($action === 'save_note') {
        $id = (int) ($_POST['id'] ?? 0);
        owned_student($pdo, $teacherId, $id);
        $pdo->prepare('UPDATE students SET notes = ? WHERE id = ? AND teacher_id = ?')->execute([(string) ($_POST['notes'] ?? ''), $id, $teacherId]);
        json_response(['ok' => true]);
    }
    if ($action === 'record_session') {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $student = owned_student($pdo, $teacherId, $studentId);
        if ($student['status'] === 'inactive') json_response(['error' => 'Inactive student records cannot receive new sessions'], 409);
        $wpm = max(1, (int) ($_POST['wpm'] ?? 0));
        $accuracy = min(100, max(0, (int) ($_POST['accuracy'] ?? 0)));
        $book = trim((string) ($_POST['book'] ?? 'Free Reading'));
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO reading_sessions (teacher_id, student_id, book, wpm, accuracy, duration_seconds, tricky_words) VALUES (?, ?, ?, ?, ?, ?, ?)')->execute([$teacherId, $studentId, $book, $wpm, $accuracy, (int) ($_POST['duration_seconds'] ?? 0), (string) ($_POST['tricky_words'] ?? '')]);
        $sessionId = (int) $pdo->lastInsertId(); // NEW: needed so the recording can be linked to this session
        $newCount = (int) $student['sessions_count'] + 1;
        $newWpm = (int) round((((int) $student['wpm'] * (int) $student['sessions_count']) + $wpm) / $newCount);
        $newAccuracy = (int) round((((int) $student['accuracy'] * (int) $student['sessions_count']) + $accuracy) / $newCount);
        $pdo->prepare('UPDATE students SET wpm = ?, accuracy = ?, sessions_count = ?, sessions_this_week = sessions_this_week + 1, last_active = NOW(), status = ? WHERE id = ? AND teacher_id = ?')->execute([$newWpm, $newAccuracy, $newCount, $newAccuracy < 82 ? 'support' : 'ontrack', $studentId, $teacherId]);
        $pdo->commit();
        json_response(['ok' => true, 'session_id' => $sessionId]); // CHANGED: now returns session_id
    }
    if ($action === 'record_quiz') {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        owned_student($pdo, $teacherId, $studentId);
        $title = trim((string) ($_POST['title'] ?? 'Quiz'));
        $score = max(0, (int) ($_POST['score'] ?? 0));
        $total = max(0, (int) ($_POST['total_questions'] ?? 0));
        $pdo->prepare('INSERT INTO quiz_attempts (teacher_id, student_id, title, score, total_questions) VALUES (?, ?, ?, ?, ?)')->execute([$teacherId, $studentId, $title, $score, $total]);
        json_response(['ok' => true]);
    }
    if ($action === 'delete_session') {
        $sessionId = (int) ($_POST['id'] ?? 0);
        if ($sessionId < 1) json_response(['error' => 'Session not found'], 422);

        // NEW: look up the cloud recording (if any) before the session row goes away
        $recordingLookup = $pdo->prepare('SELECT storage_key FROM session_recordings WHERE session_id = ? AND teacher_id = ?');
        $recordingLookup->execute([$sessionId, $teacherId]);
        $recordingKey = $recordingLookup->fetchColumn();

        $statement = $pdo->prepare('DELETE FROM reading_sessions WHERE id = ? AND teacher_id = ?');
        $statement->execute([$sessionId, $teacherId]);
        if ($statement->rowCount() !== 1) json_response(['error' => 'Session not found'], 404);

        // NEW: remove the recording row and the file in the bucket too
        if ($recordingKey) {
            $pdo->prepare('DELETE FROM session_recordings WHERE session_id = ? AND teacher_id = ?')->execute([$sessionId, $teacherId]);
            try {
                require_once __DIR__ . '/storage.php';
                delete_recording_object((string) $recordingKey);
            } catch (Throwable $storageError) {
                // Session is already deleted; don't fail the request if the cloud delete hiccups.
            }
        }
        json_response(['ok' => true]);
    }
    if ($action === 'assign_resource') {
        $resourceId = (int) ($_POST['resource_id'] ?? 0);
        $studentId = (int) ($_POST['student_id'] ?? 0);
        owned_student($pdo, $teacherId, $studentId);
        $resourceCheck = $pdo->prepare('SELECT id FROM resources WHERE id = ?');
        $resourceCheck->execute([$resourceId]);
        if (!$resourceCheck->fetchColumn()) json_response(['error' => 'Resource not found'], 404);
        $pdo->prepare('INSERT INTO resource_assignments (teacher_id, resource_id, student_id) VALUES (?, ?, ?)')->execute([$teacherId, $resourceId, $studentId]);
        json_response(['ok' => true]);
    }
    if ($action === 'delete_resource') {
        $resourceId = (int) ($_POST['resource_id'] ?? 0);
        if ($resourceId < 1) json_response(['error' => 'Resource not found'], 422);
        $check = $pdo->prepare('SELECT created_by FROM resources WHERE id = ? LIMIT 1');
        $check->execute([$resourceId]);
        if ($check->fetchColumn() === false) json_response(['error' => 'Resource not found'], 404);
        $delete = $pdo->prepare('DELETE FROM resources WHERE id = ?');
        $delete->execute([$resourceId]);
        if ($delete->rowCount() !== 1) json_response(['error' => 'Resource could not be removed'], 409);
        json_response(['ok' => true]);
    }
    if ($action === 'save_resource') {
        $title = trim((string) ($_POST['title'] ?? ''));
        if ($title === '') json_response(['error' => 'Resource title is required'], 422);
        $content = trim((string) ($_POST['content'] ?? ''));
        if ($content === '') json_response(['error' => 'No readable text was extracted from this file'], 422);
        if (mb_strlen($content, 'UTF-8') > 1000000) json_response(['error' => 'Extracted text is too long. Please upload a smaller document.'], 413);
        $pdo->beginTransaction();
        $statement = $pdo->prepare('INSERT INTO resources (title, author, genre, level, lexile, words, description, tags_json, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $statement->execute([$title, trim((string) ($_POST['author'] ?? 'Uploaded material')), trim((string) ($_POST['genre'] ?? 'Uploaded')), trim((string) ($_POST['level'] ?? '')), trim((string) ($_POST['lexile'] ?? '')), max(0, (int) ($_POST['words'] ?? 0)), trim((string) ($_POST['description'] ?? '')), '[]', $teacherId]);
        $resourceId = (int) $pdo->lastInsertId();
        $material = $pdo->prepare('INSERT INTO resource_materials (resource_id, teacher_id, file_name, mime_type, content_text) VALUES (?, ?, ?, ?, ?)');
        $material->execute([$resourceId, $teacherId, trim((string) ($_POST['file_name'] ?? $title)), trim((string) ($_POST['mime_type'] ?? 'text/plain')), $content]);
        $pdo->commit();
        json_response(['ok' => true, 'id' => $resourceId]);
    }
    if ($action === 'save_quiz') {
        $resourceId = (int) ($_POST['resource_id'] ?? 0);
        $resourceId = $resourceId > 0 ? $resourceId : null;
        $title = trim((string) ($_POST['title'] ?? ''));
        $questions = json_decode((string) ($_POST['questions'] ?? '[]'), true);
        if ($title === '' || !is_array($questions) || !$questions) json_response(['error' => 'Quiz title and questions are required'], 422);
        if ($resourceId !== null) {
            $check = $pdo->prepare('SELECT id FROM resources WHERE id = ? AND (created_by IS NULL OR created_by = ?)');
            $check->execute([$resourceId, $teacherId]);
            if (!$check->fetchColumn()) json_response(['error' => 'Resource does not belong to this teacher'], 403);
        }
        $statement = $pdo->prepare('INSERT INTO teacher_quizzes (teacher_id, resource_id, title, questions_json) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), questions_json = VALUES(questions_json)');
        $statement->execute([$teacherId, $resourceId, $title, json_encode($questions)]);
        json_response(['ok' => true]);
    }
    json_response(['error' => 'Unknown action'], 400);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_response(['error' => 'Unable to save student data'], 500);
}