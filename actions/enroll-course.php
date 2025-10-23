<?php
require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

require_login();

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = get_user_id();

if ($course_id == 0) {
    set_error('Invalid course');
    redirect('/pages/subjects.php');
}

// Check if course exists
$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ? AND is_published = 1");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    set_error('Course not found');
    redirect('/pages/subjects.php');
}

// Check if user meets level requirement
$user = get_user_data($user_id);
if ($user['current_level'] < $course['required_level']) {
    set_error('You need to reach Level ' . $course['required_level'] . ' to enroll in this course');
    redirect('/pages/courses.php?subject_id=' . $course['subject_id']);
}

// Check if already enrolled
$stmt = $conn->prepare("SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user_id, $course_id]);

if ($stmt->rowCount() > 0) {
    set_error('You are already enrolled in this course');
    redirect('/dashboard/course-details.php?id=' . $course_id);
}

// Enroll user
$stmt = $conn->prepare("INSERT INTO user_courses (user_id, course_id) VALUES (?, ?)");
if ($stmt->execute([$user_id, $course_id])) {
    set_success('Successfully enrolled in ' . $course['course_title'] . '!');
    redirect('/dashboard/course-details.php?id=' . $course_id);
} else {
    set_error('Enrollment failed. Please try again.');
    redirect('/pages/courses.php?subject_id=' . $course['subject_id']);
}
?>