<?php
$page_title = "Delete User - " . SITE_NAME;
require_once '../../includes/header.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prevent self-deletion
if ($user_id === $_SESSION['user_id']) {
    redirect('/admin/users/?error=cannot_delete_self');
}

if ($user_id > 0) {
    try {
        // Check if user exists
        $stmt = $conn->prepare("SELECT user_id, profile_picture FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user) {
            // Delete the user (CASCADE will delete related records)
            $delete_stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $delete_stmt->execute([$user_id]);

            // Delete profile picture if exists and not default
            if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default.png') {
                $avatar_path = UPLOAD_PATH . 'avatars/' . $user['profile_picture'];
                if (file_exists($avatar_path)) {
                    unlink($avatar_path);
                }
            }

            redirect('/admin/users/?success=deleted');
        } else {
            redirect('/admin/users/');
        }
    } catch (PDOException $e) {
        // Log error and redirect
        error_log('Delete user error: ' . $e->getMessage());
        redirect('/admin/users/?error=delete_failed');
    }
} else {
    redirect('/admin/users/');
}
?>
