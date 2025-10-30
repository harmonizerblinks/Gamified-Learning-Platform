<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Delete Badge - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$badge_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($badge_id > 0) {
    try {
        // Check if badge exists
        $stmt = $conn->prepare("SELECT badge_id FROM badges WHERE badge_id = ?");
        $stmt->execute([$badge_id]);
        $badge = $stmt->fetch();

        if ($badge) {
            // Delete the badge (CASCADE will delete user_badges entries)
            $delete_stmt = $conn->prepare("DELETE FROM badges WHERE badge_id = ?");
            $delete_stmt->execute([$badge_id]);

            redirect('/admin/badges/?success=deleted');
        } else {
            redirect('/admin/badges/');
        }
    } catch (PDOException $e) {
        // Log error and redirect
        error_log('Delete badge error: ' . $e->getMessage());
        redirect('/admin/badges/?error=delete_failed');
    }
} else {
    redirect('/admin/badges/');
}
?>
