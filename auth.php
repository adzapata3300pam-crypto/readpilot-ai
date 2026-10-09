<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action']) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    header('Location: login.php?error=request_too_large');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$action = $_POST['action'] ?? '';
$uploadedIdPath = null;
$redirectRequestError = static function (string $error): void {
    $_SESSION['access_request_draft'] = [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'school' => trim((string) ($_POST['school'] ?? '')),
        'reason' => trim((string) ($_POST['reason'] ?? '')),
    ];
    header('Location: login.php?error=' . rawurlencode($error));
    exit;
};

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
        if (!csrf_token_is_valid($_POST['csrf_token'] ?? null)) {
            $redirectRequestError('csrf');
        }

        $role = 'teacher';
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $school = trim((string) ($_POST['school'] ?? ''));
        $reason = trim((string) ($_POST['reason'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $school === '' || $reason === '') {
            $redirectRequestError('request');
        }

        if (strlen($password) < 8 || $password !== $confirm) {
            $redirectRequestError('password');
        }

        $exists = $pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
        $exists->execute([$email]);
        if ($exists->fetch()) {
            $redirectRequestError('exists');
        }

        $pending = $pdo->prepare('SELECT 1 FROM access_requests WHERE email = ? AND status = \'pending\' LIMIT 1');
        $pending->execute([$email]);
        if ($pending->fetch()) {
            $redirectRequestError('pending');
        }

        $file = $_FILES['id_document'] ?? null;
        if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
            $error = is_array($file) ? (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) : UPLOAD_ERR_NO_FILE;
            $errorCode = match ($error) {
                UPLOAD_ERR_NO_FILE => 'id_missing',
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'id_size',
                UPLOAD_ERR_PARTIAL => 'upload_partial',
                UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'upload_server',
                UPLOAD_ERR_EXTENSION => 'upload_blocked',
                default => 'id_document',
            };
            $redirectRequestError($errorCode);
        }

        $maxIdSize = 5 * 1024 * 1024;
        if (!is_uploaded_file((string) $file['tmp_name'])) {
            $redirectRequestError('id_document');
        }
        $actualSize = filesize((string) $file['tmp_name']);
        if ($actualSize === false || (int) $file['size'] <= 0 || (int) $file['size'] > $maxIdSize || $actualSize > $maxIdSize) {
            $redirectRequestError('id_size');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file((string) $file['tmp_name']);
        $extensions = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];
        if (!is_string($mimeType) || !isset($extensions[$mimeType])) {
            $redirectRequestError('id_type');
        }

        $storageDirectory = access_id_storage_directory();
        if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0700, true) && !is_dir($storageDirectory)) {
            error_log('Unable to create private access ID storage directory.');
            $redirectRequestError('id_storage');
        }
        $storedName = bin2hex(random_bytes(32)) . '.' . $extensions[$mimeType];
        $uploadedIdPath = $storedName;
        $destination = $storageDirectory . DIRECTORY_SEPARATOR . $storedName;
        if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
            $uploadedIdPath = null;
            error_log('Unable to move uploaded access ID into private storage.');
            $redirectRequestError('id_storage');
        }
        if (!chmod($destination, 0600)) {
            if (is_file($destination) && !unlink($destination)) {
                error_log('Unable to remove an access ID file after storage permissions could not be set.');
            }
            $uploadedIdPath = null;
            error_log('Unable to set private permissions on an uploaded access ID.');
            $redirectRequestError('id_storage');
        }

        $statement = $pdo->prepare('INSERT INTO access_requests (full_name, email, password_hash, school, reason, requested_role, id_document_path) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $school, $reason, $role, $storedName]);
        header('Location: login.php?requested=1');
        exit;
    }

    http_response_code(400);
    exit('Unknown authentication action');
} catch (Throwable $exception) {
    error_log('Authentication action "' . $action . '" failed: ' . $exception->getMessage());
    if ($uploadedIdPath !== null) {
        $uploadedPath = access_id_storage_directory() . DIRECTORY_SEPARATOR . $uploadedIdPath;
        if (is_file($uploadedPath) && !unlink($uploadedPath)) {
            error_log('Unable to remove an unassociated access ID file after a failed request submission.');
        }
    }
    if ($action === 'request_access') {
        $redirectRequestError(
            stripos($exception->getMessage(), 'id_document_path') !== false
                ? 'database_schema'
                : 'database'
        );
    }

    http_response_code(500);
    exit('Database connection failed. Import database.sql and check config.php.');
}