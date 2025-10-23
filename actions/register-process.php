<?php
require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $full_name = clean_input($_POST['full_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $errors[] = 'All fields are required';
    }
    
    if (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if username exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Username already taken';
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Email already registered';
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        redirect('/auth/register.php');
    }
    
    // Create user
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, password_hash, role) VALUES (?, ?, ?, ?, 'learner')");
    
    if ($stmt->execute([$username, $email, $full_name, $password_hash])) {
        $user_id = $conn->lastInsertId();
        
        // Auto login
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'learner';
        $_SESSION['full_name'] = $full_name;
        
        set_success('Account created successfully! Welcome to ' . SITE_NAME);
        redirect('/dashboard/');
    } else {
        set_error('Registration failed. Please try again.');
        redirect('/auth/register.php');
    }
} else {
    redirect('/auth/register.php');
}
?>
