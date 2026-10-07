<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-guard.php';
require_admin();

header('Content-Type: application/json');

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['view'] ?? '') === 'audit') {
    $statement = $pdo->query('SELECT al.action, al.details, al.created_at, COALESCE(u.full_name, \'System\') AS actor FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.created_at DESC LIMIT 100');
    echo json_encode(['audit' => $statement->fetchAll()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['view'] ?? '') === 'requests') {
    $statement = $pdo->query('SELECT id, full_name, email, school, reason, requested_role, status, created_at FROM access_requests WHERE status = \'pending\' ORDER BY created_at DESC');
    echo json_encode(['requests' => $statement->fetchAll()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';

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