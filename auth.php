<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$action = $_POST['action'] ?? '';

try {
    $pdo = db();

    if ($action === 'login') {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        $statement = $pdo->prepare('SELECT id, full_name, email, password_hash, role, status, school, grade_level, section_name, bio, profile_image, created_at FROM users WHERE email = ? LIMIT 1');
        $statement->execute([$email]);
        $user = $statement->fetch();

        if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            header('Location: login.php?error=invalid');
            exit;
        }

        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['readpilot_user'] = $user;
        $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
        $pdo->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)')->execute([$user['id'], 'login', 'Successful sign in']);
        redirect_for_role($user['role']);
    }

    if ($action === 'request_access') {
        $role = ($_POST['role'] ?? 'teacher') === 'admin' ? 'admin' : 'teacher';
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $school = trim((string) ($_POST['school'] ?? ''));
        $reason = trim((string) ($_POST['reason'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $school === '' || $reason === '') {
            header('Location: login.php?error=request');
            exit;
        }

        if (strlen($password) < 8 || $password !== $confirm) {
            header('Location: login.php?error=password');
            exit;
        }

        $exists = $pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
        $exists->execute([$email]);
        if ($exists->fetch()) {
            header('Location: login.php?error=exists');
            exit;
        }

        $pending = $pdo->prepare('SELECT 1 FROM access_requests WHERE email = ? AND status = \'pending\' LIMIT 1');
        $pending->execute([$email]);
        if ($pending->fetch()) {
            header('Location: login.php?error=pending');
            exit;
        }

        $statement = $pdo->prepare('INSERT INTO access_requests (full_name, email, password_hash, school, reason, requested_role) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $school, $reason, $role]);
        header('Location: login.php?requested=1');
        exit;
    }

    http_response_code(400);
    exit('Unknown authentication action');
} catch (Throwable $exception) {
    error_log('Authentication action "' . $action . '" failed: ' . $exception->getMessage());
    if ($action === 'request_access') {
        header('Location: login.php?error=database');
        exit;
    }

    http_response_code(500);
    exit('Database connection failed. Import database.sql and check config.php.');
}