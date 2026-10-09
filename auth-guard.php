<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function current_user(): ?array
{
    static $loaded = false;
    static $user = null;

    if ($loaded) {
        return $user;
    }

    $loaded = true;
    $user = $_SESSION['readpilot_user'] ?? null;
    if (!$user || empty($user['id'])) {
        return $user;
    }

    $statement = db()->prepare('SELECT full_name, profile_image FROM users WHERE id = ? LIMIT 1');
    $statement->execute([(int) $user['id']]);
    $account = $statement->fetch();

    if ($account) {
        $user['full_name'] = (string) $account['full_name'];
        $user['profile_image'] = (string) ($account['profile_image'] ?? '');
        $_SESSION['readpilot_user']['full_name'] = $user['full_name'];
        $_SESSION['readpilot_user']['profile_image'] = $user['profile_image'];
    }

    return $user;
}

function csrf_token(): string
{
    if (!isset($_SESSION['readpilot_csrf_token'])) {
        $_SESSION['readpilot_csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['readpilot_csrf_token'];
}

function csrf_token_is_valid(mixed $token): bool
{
    $expected = (string) ($_SESSION['readpilot_csrf_token'] ?? '');
    return $expected !== '' && is_string($token) && hash_equals($expected, $token);
}

function require_role(string $role): void
{
    $user = current_user();
    if (!$user || $user['role'] !== $role) {
        header('Location: login.php');
        exit;
    }
}

function require_teacher(): void
{
    require_role('teacher');
}

function require_admin(): void
{
    require_role('admin');
}

function redirect_for_role(string $role): void
{
    header('Location: ' . ($role === 'admin' ? 'admin.php' : 'index.php'));
    exit;
}
