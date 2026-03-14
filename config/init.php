<?php
// config/init.php

// ---- Detect HTTPS safely ----
function is_https(): bool {
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') return true;
    if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') return true;
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
    if (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) return true;
    return false;
}

$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = ($host === 'localhost' || $host === '127.0.0.1');

// ---- Start Session (NO forced secure flag on shared hosting) ----
if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => false, // 🔥 IMPORTANT: disable secure flag
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

// ---- BASE_URL ----
$scheme = is_https() ? "https" : "http";
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$parts  = explode('/', trim($script, '/'));
$baseFolder = $parts[0] ?? '';

$basePath = ($isLocal) ? '/' . $baseFolder : '';

define('BASE_URL', $scheme . '://' . $host . $basePath);
