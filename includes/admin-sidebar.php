<nav id="adminSidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4 pb-3 border-bottom border-secondary">
            <img src="<?php echo UPLOAD_URL; ?>avatars/<?php echo $user['profile_picture']; ?>" 
                 class="rounded-circle mb-2" width="60" height="60" alt="Admin">
            <h6 class="text-white mb-0"><?php echo htmlspecialchars($user['username']); ?></h6>
            <small class="text-muted">Administrator</small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/users/">
                    <i class="fas fa-users me-2"></i> Manage Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/subjects/">
                    <i class="fas fa-book me-2"></i> Manage Subjects
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/courses/">
                    <i class="fas fa-graduation-cap me-2"></i> Manage Courses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/lessons/">
                    <i class="fas fa-book-open me-2"></i> Manage Lessons
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/quizzes/">
                    <i class="fas fa-question-circle me-2"></i> Manage Quizzes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/badges/">
                    <i class="fas fa-medal me-2"></i> Manage Badges
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/analytics.php">
                    <i class="fas fa-chart-bar me-2"></i> Reports & Analytics
                </a>
            </li>
            
            <hr class="border-secondary">
            
            <li class="nav-item">
                <a class="nav-link text-white" href="/dashboard/">
                    <i class="fas fa-home me-2"></i> Back to Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="/auth/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
#adminSidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

#adminSidebar .nav-link {
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

#adminSidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
}

#adminSidebar .nav-link.active {
    background: rgba(139, 92, 246, 0.3);
}
</style>