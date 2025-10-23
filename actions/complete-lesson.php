<?php
require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
    $user_id = get_user_id();
    
    if ($lesson_id == 0) {
        set_error('Invalid lesson');
        redirect('/dashboard/my-courses.php');
    }
    
    // Get lesson details
    $stmt = $conn->prepare("SELECT * FROM lessons WHERE lesson_id = ?");
    $stmt->execute([$lesson_id]);
    $lesson = $stmt->fetch();
    
    if (!$lesson) {
        set_error('Lesson not found');
        redirect('/dashboard/my-courses.php');
    }
    
    // Check if user is enrolled in the course
    $stmt = $conn->prepare("SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $lesson['course_id']]);
    $enrollment = $stmt->fetch();
    
    if (!$enrollment) {
        set_error('You are not enrolled in this course');
        redirect('/dashboard/my-courses.php');
    }
    
    // Check if already completed
    $stmt = $conn->prepare("SELECT * FROM user_lesson_progress WHERE user_id = ? AND lesson_id = ?");
    $stmt->execute([$user_id, $lesson_id]);
    $progress = $stmt->fetch();
    
    if ($progress && $progress['is_completed']) {
        set_error('You have already completed this lesson');
        redirect('/dashboard/lesson.php?id=' . $lesson_id);
    }
    
    // Mark as completed
    if ($progress) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE user_lesson_progress SET is_completed = 1, completion_date = NOW() WHERE user_id = ? AND lesson_id = ?");
        $stmt->execute([$user_id, $lesson_id]);
    } else {
        // Create new record
        $stmt = $conn->prepare("INSERT INTO user_lesson_progress (user_id, lesson_id, is_completed, completion_date) VALUES (?, ?, 1, NOW())");
        $stmt->execute([$user_id, $lesson_id]);
    }
    
    // Award XP
    add_xp($user_id, $lesson['xp_reward'], 'lesson', $lesson_id, 'Completed: ' . $lesson['lesson_title']);
    
    // Update course progress
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_lessons,
               COUNT(CASE WHEN ulp.is_completed = 1 THEN 1 END) as completed_lessons
        FROM lessons l
        LEFT JOIN user_lesson_progress ulp ON l.lesson_id = ulp.lesson_id AND ulp.user_id = ?
        WHERE l.course_id = ?
    ");
    $stmt->execute([$user_id, $lesson['course_id']]);
    $stats = $stmt->fetch();
    
    $progress_percentage = ($stats['completed_lessons'] / $stats['total_lessons']) * 100;
    
    $stmt = $conn->prepare("UPDATE user_courses SET progress_percentage = ? WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$progress_percentage, $user_id, $lesson['course_id']]);
    
    set_success('🎉 Lesson completed! You earned ' . $lesson['xp_reward'] . ' XP!');
    
    // Find next lesson
    $stmt = $conn->prepare("
        SELECT l.lesson_id
        FROM lessons l
        LEFT JOIN user_lesson_progress ulp ON l.lesson_id = ulp.lesson_id AND ulp.user_id = ?
        WHERE l.course_id = ? AND l.lesson_order > ?
        AND (ulp.is_completed IS NULL OR ulp.is_completed = 0)
        ORDER BY l.lesson_order ASC
        LIMIT 1
    ");
    $stmt->execute([$user_id, $lesson['course_id'], $lesson['lesson_order']]);
    $next_lesson = $stmt->fetch();
    
    if ($next_lesson) {
        redirect('/dashboard/lesson.php?id=' . $next_lesson['lesson_id']);
    } else {
        redirect('/dashboard/course-details.php?id=' . $lesson['course_id']);
    }
} else {
    redirect('/dashboard/my-courses.php');
}
?>