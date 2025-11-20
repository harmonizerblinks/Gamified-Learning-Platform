<?php
require_once '../includes/header.php';

$page_title = "Verify Email - " . SITE_NAME;

// In production, you would validate the verification token from URL
$token = isset($_GET['token']) ? $_GET['token'] : '';

include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-envelope-open fa-3x text-success mb-3"></i>
                        <h3>Email Verification</h3>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Email verification feature is under development.
                    </div>

                    <div class="text-center">
                        <a href="/auth/login.php" class="btn btn-purple">Go to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
