<?php
// includes/session.php - Session Management

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header('Location: /auth/login.php');
        exit();
    }
}

// Redirect if not admin
function require_admin() {
    if (!is_admin()) {
        header('Location: /dashboard/index.php');
        exit();
    }
}

// Get current user ID
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Get current username
function get_username() {
    return $_SESSION['username'] ?? null;
}

// Logout function
function logout() {
    session_unset();
    session_destroy();
    header('Location: /auth/login.php');
    exit();
}
?>