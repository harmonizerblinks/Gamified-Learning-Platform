<?php
require_once '../includes/header.php';

$page_title = "Reset Password - " . SITE_NAME;

// In production, you would validate the reset token from URL
$token = isset($_GET['token']) ? $_GET['token'] : '';

include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-lock fa-3x text-purple mb-3"></i>
                        <h3>Reset Password</h3>
                        <p class="text-muted">Enter your new password</p>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This feature is under development. Please contact admin to reset your password.
                    </div>

                    <div class="text-center">
                        <a href="/auth/login.php" class="btn btn-purple">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
