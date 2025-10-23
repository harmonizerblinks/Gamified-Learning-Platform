
<?php 
require_once '../includes/header.php'; 
require_once '../includes/navbar.php';
$page_title = "Register - " . SITE_NAME;

if(is_logged_in()) {
    redirect('/dashboard/');
}
?>

<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-purple">Create Account 🚀</h2>
                            <p class="text-muted">Join thousands of learners today</p>
                        </div>
                        
                        <?php 
                        display_messages();
                        if (isset($_SESSION['errors'])) {
                            foreach ($_SESSION['errors'] as $error) {
                                echo '<div class="alert alert-danger">' . $error . '</div>';
                            }
                            unset($_SESSION['errors']);
                        }
                        ?>
                        
                        <form action="/actions/register-process.php" method="POST">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control form-control-lg" id="full_name" name="full_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control form-control-lg" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                <small class="text-muted">At least 6 characters</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">Create Account</button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? <a href="/auth/login.php" class="text-purple fw-bold">Login</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>