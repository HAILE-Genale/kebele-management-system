<?php
// includes/auth.php

// Ensure secure session configuration must be called BEFORE session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS in production

session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /kebele-management-system/login.php");
        exit;
    }
}

// Check if current user is Super Admin
function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin';
}

// Redirect if not Super Admin
function requireSuperAdmin() {
    requireLogin();
    if (!isSuperAdmin()) {
        die("Unauthorized Access. Super Admin role required.");
    }
}
?>
