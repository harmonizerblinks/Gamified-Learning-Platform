<?php
$page_title = "Delete Lesson - " . SITE_NAME;
require_once '../../includes/header.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($lesson_id > 0) {
    try {
        // Fetch lesson to get file path for deletion
        $stmt = $conn->prepare("SELECT content_url FROM lessons WHERE lesson_id = ?");
        $stmt->execute([$lesson_id]);
        $lesson = $stmt->fetch();

        if ($lesson) {
            // Delete the lesson from database
            $delete_stmt = $conn->prepare("DELETE FROM lessons WHERE lesson_id = ?");
            $delete_stmt->execute([$lesson_id]);

            // Delete associated file if exists
            if (!empty($lesson['content_url'])) {
                $file_path = UPLOAD_PATH . $lesson['content_url'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            redirect('/admin/lessons/?success=deleted');
        } else {
            redirect('/admin/lessons/');
        }
    } catch (PDOException $e) {
        // Log error and redirect
        error_log('Delete lesson error: ' . $e->getMessage());
        redirect('/admin/lessons/?error=delete_failed');
    }
} else {
    redirect('/admin/lessons/');
}
?>
