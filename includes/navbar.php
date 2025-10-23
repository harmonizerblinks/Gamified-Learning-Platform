<nav class="navbar">
    <div class="container">
        <div class="nav-brand">
            <a href="/">
                <img src="<?php echo ASSETS_URL; ?>images/logo.png" alt="<?php echo SITE_NAME; ?>">
                <span><?php echo SITE_NAME; ?></span>
            </a>
        </div>
        
        <ul class="nav-menu">
            <?php if(is_logged_in()): ?>
                <li><a href="/dashboard/">Dashboard</a></li>
                <li><a href="/dashboard/my-courses.php">My Courses</a></li>
                <li><a href="/dashboard/leaderboard.php">Leaderboard</a></li>
                
                <li class="user-menu">
                    <span><?php echo get_username(); ?></span>
                    <ul class="dropdown">
                        <li><a href="/dashboard/profile.php">Profile</a></li>
                        <li><a href="/dashboard/settings.php">Settings</a></li>
                        <?php if(is_admin()): ?>
                            <li><a href="/admin/">Admin Panel</a></li>
                        <?php endif; ?>
                        <li><a href="/auth/logout.php">Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="/pages/subjects.php">Subjects</a></li>
                <li><a href="/pages/how-it-works.php">How It Works</a></li>
                <!-- <li><a href="/auth/login.php" class="btn-secondary">Login</a></li> -->
                <li><a href="/auth/login.php" class="btn-primary">Login</a></li>
                <li><a href="/auth/register.php" class="btn-primary">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>