<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-guard.php';

$user = current_user();
if ($user) {
    db()->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)')->execute([$user['id'], 'logout', 'Signed out']);
}
setcookie('readpilot_remember', '', time() - 3600, '/', '', false, true);

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
header('Location: login.php?logged_out=1');
exit;
