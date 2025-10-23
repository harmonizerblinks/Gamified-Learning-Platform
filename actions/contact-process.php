
<?php
require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $subject = clean_input($_POST['subject']);
    $message = clean_input($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        set_error('All fields are required');
        redirect('/pages/contact.php');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_error('Invalid email address');
        redirect('/pages/contact.php');
    }
    
    // Here you can:
    // 1. Send email to admin
    // 2. Save to database
    // 3. Use a third-party service
    
    // For now, just show success message
    set_success('Thank you for contacting us! We\'ll get back to you soon.');
    redirect('/pages/contact.php');
    
} else {
    redirect('/pages/contact.php');
}
?>