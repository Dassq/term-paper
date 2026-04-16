<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', 1);
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function check_csrf() {
    if (
        !isset($_POST['csrf'], $_SESSION['csrf']) ||
        !hash_equals($_SESSION['csrf'], $_POST['csrf'])
    ) {
        die('CSRF detected');
    }
}

function require_auth() {
    if (!isset($_SESSION['user_id'])) {
        die("Нет доступа");
    }
}

function require_admin($pdo) {
    require_auth();

    $stmt = $pdo->prepare("SELECT role FROM users WHERE id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['role'] !== 'admin') {
        die("Нет доступа");
    }
}
