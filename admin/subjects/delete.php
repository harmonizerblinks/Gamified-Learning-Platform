<?php
require_once '../../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$subject_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($subject_id == 0) {
    set_error('Invalid subject ID');
    redirect('/admin/subjects/');
}

// Delete the subject
$stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id = ?");
if ($stmt->execute([$subject_id])) {
    set_success('Subject deleted successfully');
} else {
    set_error('Failed to delete subject');
}

redirect('/admin/subjects/');
?>
