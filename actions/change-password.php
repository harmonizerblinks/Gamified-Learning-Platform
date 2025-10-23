<?php
require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = get_user_id();
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $errors[] = 'All fields are required';
    }
    
    if (strlen($new_password) < 6) {
        $errors[] = 'New password must be at least 6 characters';
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = 'New passwords do not match';
    }
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!password_verify($current_password, $user['password_hash'])) {
        $errors[] = 'Current password is incorrect';
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        redirect('/dashboard/settings.php');
    }
    
    // Update password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $stmt->execute([$new_password_hash, $user_id]);
    
    set_success('Password changed successfully!');
    redirect('/dashboard/settings.php');
} else {
    redirect('/dashboard/settings.php');
}
?>