<?php
require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = get_user_id();
    $full_name = clean_input($_POST['full_name']);
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    
    // Validation
    $errors = [];
    
    if (empty($full_name) || empty($username) || empty($email)) {
        $errors[] = 'All fields are required';
    }
    
    if (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Check if username is taken by another user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Username already taken';
    }
    
    // Check if email is taken by another user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Email already registered';
    }
    
    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $profile_picture = upload_file($_FILES['profile_picture'], UPLOAD_PATH . 'avatars/', ALLOWED_IMAGE_EXT);
        if (!$profile_picture) {
            $errors[] = 'Invalid profile picture. Please upload a valid image (JPG, PNG, GIF)';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        redirect('/dashboard/settings.php');
    }
    
    // Update profile
    if ($profile_picture) {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, profile_picture = ? WHERE user_id = ?");
        $stmt->execute([$full_name, $username, $email, $profile_picture, $user_id]);
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, username = ?, email = ? WHERE user_id = ?");
        $stmt->execute([$full_name, $username, $email, $user_id]);
    }
    
    // Update session
    $_SESSION['username'] = $username;
    $_SESSION['full_name'] = $full_name;
    
    set_success('Profile updated successfully!');
    redirect('/dashboard/settings.php');
} else {
    redirect('/dashboard/settings.php');
}
?>