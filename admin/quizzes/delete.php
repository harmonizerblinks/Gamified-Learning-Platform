<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Delete Quiz - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($quiz_id > 0) {
    try {
        // Check if quiz exists
        $stmt = $conn->prepare("SELECT quiz_id FROM quizzes WHERE quiz_id = ?");
        $stmt->execute([$quiz_id]);
        $quiz = $stmt->fetch();

        if ($quiz) {
            // Delete the quiz (CASCADE will delete questions, answers, and attempts)
            $delete_stmt = $conn->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
            $delete_stmt->execute([$quiz_id]);

            redirect('/admin/quizzes/?success=deleted');
        } else {
            redirect('/admin/quizzes/');
        }
    } catch (PDOException $e) {
        // Log error and redirect
        error_log('Delete quiz error: ' . $e->getMessage());
        redirect('/admin/quizzes/?error=delete_failed');
    }
} else {
    redirect('/admin/quizzes/');
}
?>
