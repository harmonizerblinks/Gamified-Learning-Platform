<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('/admin/login.php');
}

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
