<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-guard.php';
require_admin();

header('Content-Type: application/json');

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['view'] ?? '') === 'teacher_activity') {
    $statement = $pdo->query(
        "SELECT activity.kind, activity.teacherName, activity.teacherEmail, activity.summary, activity.occurredAt
         FROM (
            SELECT
                'roster' AS kind,
                u.full_name AS teacherName,
                u.email AS teacherEmail,
                CONCAT('Added student ', s.name, ' to ', sec.name) AS summary,
                s.created_at AS occurredAt
            FROM students s
            INNER JOIN users u ON u.id = s.teacher_id AND u.role = 'teacher'
            INNER JOIN sections sec ON sec.id = s.section_id

            UNION ALL

            SELECT
                'resource' AS kind,
                u.full_name AS teacherName,
                u.email AS teacherEmail,
                CONCAT('Uploaded resource \"', r.title, '\"') AS summary,
                r.created_at AS occurredAt
            FROM resources r
            INNER JOIN users u ON u.id = r.created_by AND u.role = 'teacher'

            UNION ALL

            SELECT
                'quiz' AS kind,
                u.full_name AS teacherName,
                u.email AS teacherEmail,
                CONCAT(IF(q.updated_at > q.created_at, 'Updated quiz \"', 'Created quiz \"'), q.title, '\"') AS summary,
                IF(q.updated_at > q.created_at, q.updated_at, q.created_at) AS occurredAt
            FROM teacher_quizzes q
            INNER JOIN users u ON u.id = q.teacher_id AND u.role = 'teacher'

            UNION ALL

            SELECT
                'reading' AS kind,
                u.full_name AS teacherName,
                u.email AS teacherEmail,
                CONCAT('Recorded ', rs.book, ' for ', s.name, ' (', rs.wpm, ' WPM, ', rs.accuracy, '% accuracy)') AS summary,
                rs.created_at AS occurredAt
            FROM reading_sessions rs
            INNER JOIN users u ON u.id = rs.teacher_id AND u.role = 'teacher'
            INNER JOIN students s ON s.id = rs.student_id

            UNION ALL

            SELECT
                'quiz' AS kind,
                u.full_name AS teacherName,
                u.email AS teacherEmail,
                CONCAT('Recorded quiz result for ', s.name, ': ', qa.title, ' (', qa.score, '/', qa.total_questions, ')') AS summary,
                qa.created_at AS occurredAt
            FROM quiz_attempts qa
            INNER JOIN users u ON u.id = qa.teacher_id AND u.role = 'teacher'
            INNER JOIN students s ON s.id = qa.student_id

            UNION ALL

            SELECT
                'resource' AS kind,
                u.full_name AS teacherName,
                u.email AS teacherEmail,
                CONCAT('Assigned \"', r.title, '\" to ', s.name) AS summary,
                ra.assigned_at AS occurredAt
            FROM resource_assignments ra
            INNER JOIN users u ON u.id = ra.teacher_id AND u.role = 'teacher'
            INNER JOIN resources r ON r.id = ra.resource_id
            INNER JOIN students s ON s.id = ra.student_id
         ) activity
         ORDER BY activity.occurredAt DESC
         LIMIT 250"
    );
    echo json_encode(['activities' => $statement->fetchAll()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['view'] ?? '') === 'audit') {
    $statement = $pdo->query('SELECT al.action, al.details, al.created_at, COALESCE(u.full_name, \'System\') AS actor FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.created_at DESC LIMIT 100');
    echo json_encode(['audit' => $statement->fetchAll()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['view'] ?? '') === 'requests') {
    $statement = $pdo->query('SELECT id, full_name, email, school, reason, requested_role, status, created_at, id_document_path FROM access_requests WHERE status = \'pending\' ORDER BY created_at DESC');
    $requests = $statement->fetchAll();
    foreach ($requests as &$request) {
        $documentPath = $request['id_document_path'];
        $request['has_id_document'] = is_string($documentPath) && $documentPath !== '';
        $request['id_document_type'] = $request['has_id_document'] ? strtolower((string) pathinfo($documentPath, PATHINFO_EXTENSION)) : '';
        unset($request['id_document_path']);
    }
    unset($request);
    echo json_encode(['requests' => $requests]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['view'] ?? '') === 'id_documents') {
    $statement = $pdo->query('SELECT id, full_name, email, school, status, created_at FROM access_requests WHERE id_document_path IS NOT NULL ORDER BY created_at DESC');
    echo json_encode(['documents' => $statement->fetchAll()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['view'] ?? '') === 'id_document') {
    $requestId = filter_var($_GET['request_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$requestId || $requestId < 1) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid access request.']);
        exit;
    }

    $statement = $pdo->prepare('SELECT id_document_path FROM access_requests WHERE id = ? LIMIT 1');
    $statement->execute([$requestId]);
    $storedName = $statement->fetchColumn();
    if (!is_string($storedName) || !preg_match('/\A[a-f0-9]{64}\.(pdf|jpg|png)\z/', $storedName)) {
        http_response_code(404);
        echo json_encode(['error' => 'ID document not found.']);
        exit;
    }

    $storageDirectory = realpath(access_id_storage_directory());
    $documentPath = realpath(access_id_storage_directory() . DIRECTORY_SEPARATOR . $storedName);
    if ($storageDirectory === false || $documentPath === false || dirname($documentPath) !== $storageDirectory || !is_file($documentPath)) {
        http_response_code(404);
        echo json_encode(['error' => 'ID document is unavailable.']);
        exit;
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($documentPath);
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!is_string($mimeType) || !in_array($mimeType, $allowedTypes, true)) {
        error_log('Stored access ID has an unexpected MIME type for request ' . $requestId . '.');
        http_response_code(415);
        echo json_encode(['error' => 'ID document has an unsupported file type.']);
        exit;
    }

    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="access-request-' . $requestId . '.' . pathinfo($storedName, PATHINFO_EXTENSION) . '"');
    header('Content-Length: ' . (string) filesize($documentPath));
    header('Cache-Control: private, no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    if (readfile($documentPath) === false) {
        error_log('Unable to read access ID for request ' . $requestId . '.');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'delete_access_id') {
    if (!csrf_token_is_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['error' => 'Your session expired. Refresh the page and try again.']);
        exit;
    }

    $requestId = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$requestId || $requestId < 1) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid access request.']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        $statement = $pdo->prepare('SELECT id_document_path FROM access_requests WHERE id = ? LIMIT 1 FOR UPDATE');
        $statement->execute([$requestId]);
        $storedName = $statement->fetchColumn();
        if (!is_string($storedName) || !preg_match('/\A[a-f0-9]{64}\.(pdf|jpg|png)\z/', $storedName)) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['error' => 'ID document not found.']);
            exit;
        }

        $storageDirectory = realpath(access_id_storage_directory());
        $documentPath = realpath(access_id_storage_directory() . DIRECTORY_SEPARATOR . $storedName);
        if ($storageDirectory === false || $documentPath === false || dirname($documentPath) !== $storageDirectory || !is_file($documentPath)) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['error' => 'ID document is unavailable.']);
            exit;
        }
        if (!unlink($documentPath)) {
            $pdo->rollBack();
            error_log('Unable to delete access ID for request ' . $requestId . '.');
            http_response_code(500);
            echo json_encode(['error' => 'The ID file could not be deleted. Check server storage permissions and try again.']);
            exit;
        }

        $pdo->prepare('UPDATE access_requests SET id_document_path = NULL WHERE id = ?')->execute([$requestId]);
        $pdo->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)')
            ->execute([current_user()['id'] ?? null, 'access_id_deleted', 'Deleted ID document for access request #' . $requestId]);
        $pdo->commit();
        echo json_encode(['ok' => true]);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Access ID deletion failed for request ' . $requestId . ': ' . $exception->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'The file was deleted, but its record could not be updated. Contact support.']);
    }
    exit;
}

if ($action === 'approve_request') {
    $requestId = (int) ($_POST['request_id'] ?? 0);
    $grade = trim((string) ($_POST['grade_level'] ?? 'Grade 3'));

    if ($requestId <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid access request.']);
        exit;
    }

    $request = $pdo->prepare('SELECT * FROM access_requests WHERE id = ? AND status = \'pending\' LIMIT 1');
    $request->execute([$requestId]);
    $row = $request->fetch();
    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Access request not found.']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        $exists = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $exists->execute([strtolower($row['email'])]);
        if ($exists->fetch()) {
            throw new RuntimeException('That email address is already in use.');
        }

        // Use the password the person chose on the sign-up form (already hashed).
        // Only fall back to a generated password for older requests that have none.
        $generatedPassword = null;
        $storedHash = (string) ($row['password_hash'] ?? '');
        if ($storedHash !== '') {
            $passwordHash = $storedHash;
        } elseif (!empty($row['google_sub'])) {
            $passwordHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
        } else {
            $generatedPassword = 'Admin123!';
            $passwordHash = password_hash($generatedPassword, PASSWORD_DEFAULT);
        }

        $role = $row['requested_role'];
        $insert = $pdo->prepare('INSERT INTO users (full_name, email, google_sub, password_hash, role, school, grade_level, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $insert->execute([
            $row['full_name'],
            strtolower($row['email']),
            $row['google_sub'] ?? null,
            $passwordHash,
            $role,
            $row['school'],
            $role === 'teacher' ? $grade : null,
            'active',
        ]);

        $userId = (int) $pdo->lastInsertId();
        if ($role === 'teacher') {
            $sectionStatement = $pdo->prepare('INSERT INTO sections (teacher_id, name) VALUES (?, ?)');
            foreach (['Section A', 'Section B', 'Section C'] as $sectionName) {
                $sectionStatement->execute([$userId, $sectionName]);
            }
        }

        $pdo->prepare('UPDATE access_requests SET status = \'approved\', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?')
            ->execute([current_user()['id'] ?? null, $requestId]);

        $pdo->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)')
            ->execute([current_user()['id'] ?? $userId, 'access_approved', 'Approved access request for ' . $row['full_name'] . ' (' . $row['email'] . ')']);

        $pdo->commit();

        $message = 'Access request approved.';
        $response = ['ok' => true, 'message' => $message];
        if ($generatedPassword !== null) {
            $response['password'] = $generatedPassword;
            $response['message'] = $message . ' This older request had no saved password, so the temporary password is ' . $generatedPassword;
        }
        echo json_encode($response);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['error' => $exception->getMessage() ?: 'Unable to approve access request.']);
    }
    exit;
}

if ($action === 'decline_request') {
    $requestId = (int) ($_POST['request_id'] ?? 0);
    if ($requestId <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid access request.']);
        exit;
    }

    $update = $pdo->prepare('UPDATE access_requests SET status = \'declined\', reviewed_by = ?, reviewed_at = NOW() WHERE id = ? AND status = \'pending\'');
    $update->execute([current_user()['id'] ?? null, $requestId]);

    if ($update->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Access request not found or already reviewed.']);
        exit;
    }

    $pdo->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)')
        ->execute([current_user()['id'] ?? null, 'access_declined', 'Declined access request']);

    echo json_encode(['ok' => true, 'message' => 'Access request declined.']);
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
$role = ($_POST['role'] ?? '') === 'Admin' ? 'admin' : 'teacher';
$grade = $role === 'teacher' ? trim((string) ($_POST['grade_level'] ?? '')) : null;
$status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    http_response_code(422);
    echo json_encode(['error' => 'Name, valid email, and a password of at least 8 characters are required.']);
    exit;
}

try {
    $pdo->beginTransaction();
    $statement = $pdo->prepare(
        'INSERT INTO users (full_name, email, password_hash, role, school, grade_level, status) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([
        $name,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $role,
        current_user()['school'] ?? '',
        $grade,
        $status,
    ]);

    $userId = (int) $pdo->lastInsertId();
    if ($role === 'teacher') {
        $sectionStatement = $pdo->prepare('INSERT INTO sections (teacher_id, name) VALUES (?, ?)');
        foreach (['Section A', 'Section B', 'Section C'] as $sectionName) {
            $sectionStatement->execute([$userId, $sectionName]);
        }
    }
    $pdo->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)')
        ->execute([current_user()['id'], 'user_created', ucfirst($role) . ' account created: ' . $email]);
    $pdo->commit();

    echo json_encode(['ok' => true, 'id' => $userId]);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code($exception->getCode() === '23000' ? 409 : 500);
    echo json_encode(['error' => $exception->getCode() === '23000' ? 'That email address is already in use.' : 'Unable to create account.']);
}