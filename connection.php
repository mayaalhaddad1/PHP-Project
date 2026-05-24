<?php
// connection.php - centralized DB connection and common helpers
// Usage: include 'connection.php'; then use $conn and helper functions

// Secure session configuration (basic hardening)
if (session_status() === PHP_SESSION_NONE) {
    // Use strict mode to prevent session fixation
    ini_set('session.use_strict_mode', 1);
    // Make cookie accessible only via HTTP (not JS)
    ini_set('session.cookie_httponly', 1);
    // If served over HTTPS enable secure flag (developer may disable on local non-HTTPS)
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname   = "assignment4";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // In production don't echo raw errors. Log them instead.
    error_log('DB connection error: ' . $conn->connect_error);
    die('Database connection failed.');
}

// Ensure UTF8
$conn->set_charset('utf8mb4');

// helper: simple function to escape output
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// CSRF helpers: generate and verify token stored in session
function csrf_token() {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field() {
    $t = csrf_token();
    return '<input type="hidden" name="_csrf" value="' . e($t) . '">';
}

function verify_csrf($token) {
    return isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], (string)$token);
}




