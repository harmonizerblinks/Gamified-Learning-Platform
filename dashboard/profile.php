<?php 
require_once '../includes/header.php';
$page_title = "My Profile - " . SITE_NAME;
require_login();

$user_id = get_user_id();
$user = get_user_data($user_id);

// Get additional stats
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_courses WHERE user_id = ? AND is_completed = 1");
$stmt->execute([$user_id]);
$completed_courses = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_badges WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_badges = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM certificates WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_certificates = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_courses WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_enrollments = $stmt->fetch()['total'];

// Get user's rank
$stmt = $conn->query("
    SELECT user_id, total_xp,
           RANK() OVER (ORDER BY total_xp DESC) as rank_position
    FROM users
    WHERE role = 'learner' AND is_active = 1
");
$all_users = $stmt->fetchAll();
$user_rank = 0;
foreach ($all_users as $u) {
    if ($u['user_id'] == $user_id) {
        $user_rank = $u['rank_position'];
        break;
    }
}

// Get next level info
$next_level_xp = get_next_level_xp($user['current_level']);
$xp_for_next_level = $next_level_xp ? ($next_level_xp - $user['total_xp']) : 0;

// Get recent activity
$stmt = $conn->prepare("
    SELECT * FROM xp_transactions 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$user_id]);
$recent_activity = $stmt->fetchAll();

// Get recent badges
$stmt = $conn->prepare("
    SELECT b.*, ub.earned_date
    FROM badges b
    JOIN user_badges ub ON b.badge_id = ub.badge_id
    WHERE ub.user_id = ?
    ORDER BY ub.earned_date DESC
    LIMIT 6
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
            <h1 class="mb-4">My Profile</h1>
            
            <?php display_messages(); ?>
            
            <!-- Profile Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <img src="<?php echo UPLOAD_URL; ?>avatars/<?php echo $user['profile_picture']; ?>" 
                                 class="rounded-circle mb-3" 
                                 width="150" 
                                 height="150" 
                                 alt="Profile Picture">
                            <h4 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                            <p class="text-muted mb-3">@<?php echo htmlspecialchars($user['username']); ?></p>
                            <a href="/dashboard/settings.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="row g-4">
                                <!-- Level Card -->
                                <div class="col-md-6">
                                    <div class="card bg-gradient-purple text-white border-0 h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="text-white-50 mb-2">Current Level</h6>
                                                    <h2 class="mb-0">Level <?php echo $user['current_level']; ?></h2>
                                                </div>
                                                <i class="fas fa-layer-group fa-3x opacity-50"></i>
                                            </div>
                                            <?php if ($user['current_level'] < 10): ?>
                                                <div class="progress bg-white bg-opacity-25" style="height: 8px;">
                                                    <div class="progress-bar bg-white" 
                                                         style="width: <?php echo (($user['total_xp'] % 1000) / 10); ?>%"></div>
                                                </div>
                                                <small class="d-block mt-2"><?php echo number_format($xp_for_next_level); ?> XP to Level <?php echo $user['current_level'] + 1; ?></small>
                                            <?php else: ?>
                                                <div class="alert alert-light mb-0 mt-3">
                                                    <i class="fas fa-crown me-2"></i>Max Level Reached!
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- XP Card -->
                                <div class="col-md-6">
                                    <div class="card bg-warning text-dark border-0 h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="text-dark mb-2 opacity-75">Total XP</h6>
                                                    <h2 class="mb-0"><?php echo number_format($user['total_xp']); ?></h2>
                                                </div>
                                                <i class="fas fa-star fa-3x opacity-50"></i>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Leaderboard Rank</span>
                                                <strong>#<?php echo $user_rank; ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Streak Card -->
                                <div class="col-md-6">
                                    <div class="card bg-danger text-white border-0 h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="text-white-50 mb-2">Current Streak</h6>
                                                    <h2 class="mb-0"><?php echo $user['current_streak']; ?> Days</h2>
                                                    <small>Longest: <?php echo $user['longest_streak']; ?> days</small>
                                                </div>
                                                <i class="fas fa-fire fa-3x opacity-50"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Badges Card -->
                                <div class="col-md-6">
                                    <div class="card bg-success text-white border-0 h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="text-white-50 mb-2">Badges Earned</h6>
                                                    <h2 class="mb-0"><?php echo $total_badges; ?></h2>
                                                    <small><?php echo $total_certificates; ?> Certificates</small>
                                                </div>
                                                <i class="fas fa-medal fa-3x opacity-50"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Overview -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-book-reader fa-3x text-primary mb-3"></i>
                            <h3 class="mb-1"><?php echo $total_enrollments; ?></h3>
                            <p class="text-muted mb-0">Enrolled Courses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h3 class="mb-1"><?php echo $completed_courses; ?></h3>
                            <p class="text-muted mb-0">Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-certificate fa-3x text-info mb-3"></i>
                            <h3 class="mb-1"><?php echo $total_certificates; ?></h3>
                            <p class="text-muted mb-0">Certificates</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-medal fa-3x text-warning mb-3"></i>
                            <h3 class="mb-1"><?php echo $total_badges; ?></h3>
                            <p class="text-muted mb-0">Badges</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Activity -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($recent_activity)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No activity yet</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_activity as $activity): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="d-flex align-items-center mb-1">
                                                        <?php
                                                        $icon_class = '';
                                                        $color_class = '';
                                                        switch ($activity['xp_type']) {
                                                            case 'lesson':
                                                                $icon_class = 'fa-book-open';
                                                                $color_class = 'text-primary';
                                                                break;
                                                            case 'quiz':
                                                                $icon_class = 'fa-question-circle';
                                                                $color_class = 'text-warning';
                                                                break;
                                                            case 'course':
                                                                $icon_class = 'fa-graduation-cap';
                                                                $color_class = 'text-success';
                                                                break;
                                                            case 'streak':
                                                                $icon_class = 'fa-fire';
                                                                $color_class = 'text-danger';
                                                                break;
                                                            case 'badge':
                                                                $icon_class = 'fa-medal';
                                                                $color_class = 'text-info';
                                                                break;
                                                            default:
                                                                $icon_class = 'fa-star';
                                                                $color_class = 'text-secondary';
                                                        }
                                                        ?>
                                                        <i class="fas <?php echo $icon_class; ?> <?php echo $color_class; ?> me-2"></i>
                                                        <span><?php echo htmlspecialchars($activity['description']); ?></span>
                                                    </div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i><?php echo time_ago($activity['created_at']); ?>
                                                    </small>
                                                </div>
                                                <span class="badge bg-success">+<?php echo $activity['xp_amount']; ?> XP</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="/dashboard/xp-history.php" class="text-decoration-none">View All Activity</a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Badges -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0"><i class="fas fa-medal me-2"></i>Recent Badges</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_badges)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-award fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No badges earned yet</p>
                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($recent_badges as $badge): ?>
                                        <div class="col-md-6">
                                            <div class="card border h-100 hover-lift">
                                                <div class="card-body text-center p-3">
                                                    <i class="fas fa-medal fa-3x text-warning mb-2"></i>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($badge['badge_name']); ?></h6>
                                                    <small class="text-muted d-block mb-2"><?php echo htmlspecialchars($badge['description']); ?></small>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i><?php echo time_ago($badge['earned_date']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="/dashboard/achievements.php" class="text-decoration-none">View All Badges</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>