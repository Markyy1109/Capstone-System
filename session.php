
<?php



if (session_status() === PHP_SESSION_NONE) {

    $isHttps = !empty($_SERVER['HTTPS']) &&
               $_SERVER['HTTPS'] !== 'off';

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(
        random_bytes(32)
    );
}

$_SESSION['csrf'] = $_SESSION['csrf_token'];


function checkAuthCsrf() {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }

    $token = $_POST['csrf_token'] ?? $_POST['csrf'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? $_SESSION['csrf'] ?? '';

    return is_string($token) &&
           is_string($expected) &&
           hash_equals(
               $expected,
               $token
           );
}


function authEscape($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
