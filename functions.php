<?php

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$_SESSION['csrf'] = $_SESSION['csrf_token'];

function clean($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function verifyCsrf(): bool
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }

    $token = $_POST['csrf_token'] ?? $_POST['csrf'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? $_SESSION['csrf'] ?? '';

    if (!is_string($token) || !is_string($expected)) {
        return false;
    }

    return hash_equals($expected, $token);
}

function requireLogin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id']) && empty($_SESSION['username'])) {
        header('Location: login.php');
        exit();
    }
}

function requireRole(array $allowedRoles): void
{
    requireLogin();

    if (empty($allowedRoles)) {
        return;
    }

    $currentRole = strtolower((string) currentUserRole());
    $allowed = array_map('strtolower', $allowedRoles);

    if (!in_array($currentRole, $allowed, true)) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'text' => 'You do not have permission to access this page.',
        ];
        header('Location: dashboard.php');
        exit();
    }
}

function currentUserRole(): string
{
    if (empty($_SESSION['role']) && empty($_SESSION['user_role'])) {
        return 'member';
    }

    $role = $_SESSION['role'] ?? $_SESSION['user_role'];

    return is_string($role) && $role !== '' ? $role : 'member';
}

function setMessage(string $message, string $type = 'success'): void
{
    $_SESSION['flash_message'] = [
        'type' => in_array($type, ['success', 'error', 'info'], true) ? $type : 'success',
        'text' => $message,
    ];
}

function showMessage(): void
{
    if (empty($_SESSION['flash_message'])) {
        return;
    }

    $message = $_SESSION['flash_message'];
    $type = $message['type'] ?? 'success';
    $text = $message['text'] ?? '';

    echo '<div class="alert ' . clean($type) . '">' . clean($text) . '</div>';

    unset($_SESSION['flash_message']);
}

function authEscape($value): string
{
    return clean($value);
}

function redirect(string $location): void
{
    header('Location: ' . $location);
    exit();
}
