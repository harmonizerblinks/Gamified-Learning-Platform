<?php
require_once '../../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

require_login();

if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($course_id == 0) {
    set_error('Invalid course ID');
    redirect('/admin/courses/');
}

// Delete the course (cascade will delete lessons, quizzes, etc.)
$stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
if ($stmt->execute([$course_id])) {
    set_success('Course deleted successfully');
} else {
    set_error('Failed to delete course');
}

redirect('/admin/courses/');
?>
