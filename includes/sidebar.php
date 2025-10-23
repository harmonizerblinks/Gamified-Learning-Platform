<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <img src="<?php echo UPLOAD_URL; ?>avatars/<?php echo $user['profile_picture']; ?>" 
                 class="rounded-circle mb-2" width="80" height="80" alt="Profile">
            <h6 class="mb-0"><?php echo htmlspecialchars($user['username']); ?></h6>
            <small class="text-muted">Level <?php echo $user['current_level']; ?></small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="/dashboard/">
                    <i class="fas fa-home me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/dashboard/my-courses.php">
                    <i class="fas fa-book me-2"></i> My Courses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/pages/subjects.php">
                    <i class="fas fa-search me-2"></i> Browse Courses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/dashboard/leaderboard.php">
                    <i class="fas fa-trophy me-2"></i> Leaderboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/dashboard/achievements.php">
                    <i class="fas fa-medal me-2"></i> Achievements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/dashboard/certificates.php">
                    <i class="fas fa-certificate me-2"></i> Certificates
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/dashboard/profile.php">
                    <i class="fas fa-user me-2"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/dashboard/settings.php">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
            </li>
            <?php if (is_admin()): ?>
                <li class="nav-item mt-3">
                    <a class="nav-link text-danger" href="/admin/">
                        <i class="fas fa-user-shield me-2"></i> Admin Panel
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="/auth/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>