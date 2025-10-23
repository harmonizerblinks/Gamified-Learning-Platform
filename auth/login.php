<?php 
require_once '../includes/header.php'; 
require_once '../includes/navbar.php';
$page_title = "Login - " . SITE_NAME;

// If already logged in, redirect
if(is_logged_in()) {
    redirect('/dashboard/');
}
?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h2>Welcome Back! 👋</h2>
            <p>Login to continue your learning journey</p>
        </div>
        
        <?php display_messages(); ?>
        
        <form action="/actions/login-process.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Email or Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group checkbox">
                <label>
                    <input type="checkbox" name="remember"> Remember me
                </label>
            </div>
            
            <button type="submit" class="btn-primary btn-block">Login</button>
        </form>
        
        <div class="auth-links">
            <a href="/auth/forgot-password.php">Forgot Password?</a>
            <p>Don't have an account? <a href="/auth/register.php">Sign Up</a></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>