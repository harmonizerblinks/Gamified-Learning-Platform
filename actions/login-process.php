<?php

require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        set_error('Please fill in all fields');
        redirect('/auth/login.php');
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Login successful
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        // Update last login
        $stmt = $conn->prepare("UPDATE users SET last_login_date = CURDATE() WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        
        // Check daily streak
        check_daily_streak($user['user_id']);
        
        // Redirect based on role
        if ($user['role'] === 'admin') {
            redirect('/admin/');
        } else {
            redirect('/dashboard/');
        }
    } else {
        set_error('Invalid username or password');
        redirect('/auth/login.php');
    }
} else {
    redirect('/auth/login.php');
}
?>