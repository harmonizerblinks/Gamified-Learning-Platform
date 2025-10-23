<?php 
require_once '../includes/header.php';
$page_title = "Dashboard - " . SITE_NAME;
require_login();

$user_id = get_user_id();
$user = get_user_data($user_id);

// Get stats
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_courses WHERE user_id = ? AND is_completed = 1");
$stmt->execute([$user_id]);
$completed_courses = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_badges WHERE user_id = ?");
$stmt->execute([$user_id]);
$badges_count = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM certificates WHERE user_id = ?");
$stmt->execute([$user_id]);
$certificates_count = $stmt->fetch()['total'];

// Get next level info
$next_level_xp = get_next_level_xp($user['current_level']);
$current_level_xp = $user['total_xp'];
if ($user['current_level'] > 1) {
    $stmt = $conn->prepare("SELECT total_xp_required FROM levels WHERE level_number = ?");
    $stmt->execute([$user['current_level']]);
    $current_level_start_xp = $stmt->fetch()['total_xp_required'];
    $xp_for_next = $next_level_xp - $current_level_start_xp;
    $xp_progress = $current_level_xp - $current_level_start_xp;
    $progress_percentage = ($xp_progress / $xp_for_next) * 100;
} else {
    $progress_percentage = ($current_level_xp / $next_level_xp) * 100;
}

// Get in-progress courses
$stmt = $conn->prepare("
    SELECT c.*, uc.progress_percentage, s.subject_name
    FROM courses c
    JOIN user_courses uc ON c.course_id = uc.course_id
    JOIN subjects s ON c.subject_id = s.subject_id
    WHERE uc.user_id = ? AND uc.is_completed = 0
    ORDER BY uc.enrollment_date DESC
    LIMIT 3
");
$stmt->execute([$user_id]);
$in_progress_courses = $stmt->fetchAll();

// Get recent badges
$stmt = $conn->prepare("
    SELECT b.*, ub.earned_date
    FROM badges b
    JOIN user_badges ub ON b.badge_id = ub.badge_id
    WHERE ub.user_id = ?
    ORDER BY ub.earned_date DESC
    LIMIT 3
");
$stmt->execute([$user_id]);
$recent_badges = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="mb-4">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>! 🎉</h1>
            
            <?php display_messages(); ?>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-purple-light text-purple rounded-circle p-3 me-3">
                                    <i class="fas fa-star fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo number_format($user['total_xp']); ?></h3>
                                    <p class="text-muted mb-0 small">Total XP</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning-light text-warning rounded-circle p-3 me-3">
                                    <i class="fas fa-level-up-alt fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">Level <?php echo $user['current_level']; ?></h3>
                                    <p class="text-muted mb-0 small">Current Level</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-danger-light text-danger rounded-circle p-3 me-3">
                                    <i class="fas fa-fire fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $user['current_streak']; ?></h3>
                                    <p class="text-muted mb-0 small">Day Streak</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success-light text-success rounded-circle p-3 me-3">
                                    <i class="fas fa-trophy fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $badges_count; ?></h3>
                                    <p class="text-muted mb-0 small">Badges Earned</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Level Progress -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Level Progress</h5>
                        <?php if ($user['current_level'] < 10): ?>
                            <span class="badge bg-purple">
                                <?php echo number_format($next_level_xp - $user['total_xp']); ?> XP to Level <?php echo $user['current_level'] + 1; ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-success">Max Level Reached!</span>
                        <?php endif; ?>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-purple mt-0 mb-0" role="progressbar" style="height: 25px; width: <?php echo min($progress_percentage, 100); ?>%">
                            <?php echo round($progress_percentage); ?>%
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Continue Learning -->
            <div class="row">
                <div class="col-md-8">
                    <h4 class="mb-3">Continue Learning</h4>
                    <?php if (empty($in_progress_courses)): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                <h5>No courses in progress</h5>
                                <p class="text-muted">Start learning by enrolling in a course</p>
                                <a href="/pages/subjects.php" class="btn btn-primary">Browse Courses</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($in_progress_courses as $course): ?>
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img src="<?php echo UPLOAD_URL; ?>thumbnails/<?php echo $course['thumbnail']; ?>" 
                                                 class="img-fluid rounded" alt="<?php echo htmlspecialchars($course['course_title']); ?>">
                                        </div>
                                        <div class="col-md-7">
                                            <span class="badge bg-info text-dark mb-2"><?php echo htmlspecialchars($course['subject_name']); ?></span>
                                            <h5 class="mb-2"><?php echo htmlspecialchars($course['course_title']); ?></h5>
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar bg-success mt-0 mb-0" style="height: 8px; width: <?php echo $course['progress_percentage']; ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?php echo round($course['progress_percentage']); ?>% Complete</small>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <a href="/dashboard/course-details.php?id=<?php echo $course['course_id']; ?>" class="btn btn-primary">Continue</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Badges -->
                <div class="col-md-4">
                    <h4 class="mb-3">Recent Badges</h4>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <?php if (empty($recent_badges)): ?>
                                <p class="text-muted text-center">No badges earned yet</p>
                            <?php else: ?>
                                <?php foreach ($recent_badges as $badge): ?>
                                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                        <div class="badge-icon me-3">
                                            <i class="fas fa-medal fa-2x text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($badge['badge_name']); ?></h6>
                                            <small class="text-muted"><?php echo time_ago($badge['earned_date']); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <a href="/dashboard/achievements.php" class="btn btn-outline-primary btn-sm w-100">View All Badges</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>