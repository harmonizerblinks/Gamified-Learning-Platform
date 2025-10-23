<?php
require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = get_user_id();
    $confirm_delete = $_POST['confirm_delete'];
    $password = $_POST['password'];
    
    if ($confirm_delete !== 'DELETE') {
        set_error('Confirmation text does not match');
        redirect('/dashboard/settings.php');
    }
    
    // Verify password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!password_verify($password, $user['password_hash'])) {
        set_error('Incorrect password');
        redirect('/dashboard/settings.php');
    }
    
    // Delete user account (cascade will handle related records)
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Logout
    session_destroy();
    
    set_success('Your account has been deleted. We\'re sorry to see you go!');
    redirect('/');
} else {
    redirect('/dashboard/settings.php');
}
?>