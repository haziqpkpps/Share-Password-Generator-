<?php
// db.php

// 1) Configure session cookie for 1 day
session_set_cookie_params([
    'lifetime' => 86400,
    'path'     => '/',
    'secure'   => true,       // set to true if you serve over HTTPS
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// 2) PDO connection
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=db_name;charset=utf8mb4',
        'db_username',
        'db_password',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Database error');
}

// 3) Admin checks and session expiry
function isAdmin() {
    return (!empty($_SESSION['admin']) && $_SESSION['admin'] === true);
}

function requireAdmin() {
    // expire after 24h
    if (
        !isAdmin()
        || empty($_SESSION['login_time'])
        || (time() - $_SESSION['login_time'] > 86400)
    ) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// 4) CSRF protection
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

