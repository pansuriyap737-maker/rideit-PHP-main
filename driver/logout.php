<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset only driver-specific session keys first (optional safety)
unset($_SESSION['driver_id']);
unset($_SESSION['driver_name']);

// Optionally, clear all session data if drivers are isolated from other roles
$_SESSION = [];

// Destroy session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destroy session
session_destroy();

// Redirect to passenger/driver shared login page
header('Location: ../pages/login.php');
exit;
?>


