<?php 
require_once '../includes/header.php';
$page_title = "Leaderboard - " . SITE_NAME;
require_login();

$user_id = get_user_id();

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all_time';

// Build query based on filter
$date_filter = '';
if ($filter == 'weekly') {
    $date_filter = "AND u.last_login_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($filter == 'monthly') {
    $date_filter = "AND u.last_login_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

// Get leaderboard data
$stmt = $conn->query("
    SELECT 
        u.user_id,
        u.username,
        u.full_name,
        u.profile_picture,
        u.total_xp,
        u.current_level,
        u.current_streak,
        COUNT(DISTINCT ub.badge_id) as total_badges,
        COUNT(DISTINCT c.certificate_id) as total_certificates,
        COUNT(DISTINCT uc.course_id) as completed_courses,
        RANK() OVER (ORDER BY u.total_xp DESC) as rank_position
    FROM users u
    LEFT JOIN user_badges ub ON u.user_id = ub.user_id
    LEFT JOIN certificates c ON u.user_id = c.user_id
    LEFT JOIN user_courses uc ON u.user_id = uc.user_id AND uc.is_completed = 1
    WHERE u.role = 'learner' AND u.is_active = 1 $date_filter
    GROUP BY u.user_id
    ORDER BY u.total_xp DESC
    LIMIT 100
");
$leaderboard = $stmt->fetchAll();

// Get current user's rank
$current_user_rank = null;
foreach ($leaderboard as $entry) {
    if ($entry['user_id'] == $user_id) {
        $current_user_rank = $entry;
        break;
    }
}

// If user not in top 100, get their rank separately
if (!$current_user_rank) {
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.username,
            u.full_name,
            u.profile_picture,
            u.total_xp,
            u.current_level,
            u.current_streak,
            COUNT(DISTINCT ub.badge_id) as total_badges,
            COUNT(DISTINCT c.certificate_id) as total_certificates,
            COUNT(DISTINCT uc.course_id) as completed_courses,
            (SELECT COUNT(*) + 1 FROM users WHERE role = 'learner' AND is_active = 1 AND total_xp > u.total_xp) as rank_position
        FROM users u
        LEFT JOIN user_badges ub ON u.user_id = ub.user_id
        LEFT JOIN certificates c ON u.user_id = c.user_id
        LEFT JOIN user_courses uc ON u.user_id = uc.user_id AND uc.is_completed = 1
        WHERE u.user_id = ?
        GROUP BY u.user_id
    ");
    $stmt->execute([$user_id]);
    $current_user_rank = $stmt->fetch();
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php 
        $user = get_user_data($user_id);
        include '../includes/sidebar.php'; 
        ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-trophy text-warning me-2"></i>Leaderboard</h1>
            </div>
            
            <?php display_messages(); ?>
            
            <!-- Your Rank Card -->
            <?php if ($current_user_rank): ?>
                <div class="card border-0 shadow-sm mb-4 bg-gradient-purple text-white">
                    <div class="card-body p-4">
                        <h5 class="mb-3"><i class="fas fa-user-circle me-2"></i>Your Ranking</h5>
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <div class="rank-badge">
                                    <div class="rank-number">#<?php echo $current_user_rank['rank_position']; ?></div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <h4 class="mb-2"><?php echo htmlspecialchars($current_user_rank['full_name']); ?></h4>
                                <div class="d-flex gap-4">
                                    <span><i class="fas fa-star me-1"></i> <?php echo number_format($current_user_rank['total_xp']); ?> XP</span>
                                    <span><i class="fas fa-layer-group me-1"></i> Level <?php echo $current_user_rank['current_level']; ?></span>
                                    <span><i class="fas fa-medal me-1"></i> <?php echo $current_user_rank['total_badges']; ?> Badges</span>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <?php if ($current_user_rank['rank_position'] <= 3): ?>
                                    <div class="trophy-icon">
                                        <?php if ($current_user_rank['rank_position'] == 1): ?>
                                            <i class="fas fa-trophy fa-3x text-warning"></i>
                                        <?php elseif ($current_user_rank['rank_position'] == 2): ?>
                                            <i class="fas fa-trophy fa-3x" style="color: silver;"></i>
                                        <?php else: ?>
                                            <i class="fas fa-trophy fa-3x" style="color: #CD7F32;"></i>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter == 'all_time' ? 'active' : ''; ?>" href="?filter=all_time">
                        <i class="fas fa-infinity me-2"></i>All Time
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter == 'monthly' ? 'active' : ''; ?>" href="?filter=monthly">
                        <i class="fas fa-calendar-alt me-2"></i>This Month
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter == 'weekly' ? 'active' : ''; ?>" href="?filter=weekly">
                        <i class="fas fa-calendar-week me-2"></i>This Week
                    </a>
                </li>
            </ul>
            
            <!-- Leaderboard Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80" class="text-center">Rank</th>
                                    <th>Learner</th>
                                    <th class="text-center">Level</th>
                                    <th class="text-center">XP</th>
                                    <th class="text-center">Badges</th>
                                    <th class="text-center">Courses</th>
                                    <th class="text-center">Streak</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($leaderboard)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No learners found</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($leaderboard as $entry): ?>
                                        <tr class="<?php echo $entry['user_id'] == $user_id ? 'table-primary' : ''; ?>">
                                            <td class="text-center align-middle">
                                                <?php if ($entry['rank_position'] == 1): ?>
                                                    <div class="rank-medal gold">
                                                        <i class="fas fa-trophy"></i>
                                                        <span>1</span>
                                                    </div>
                                                <?php elseif ($entry['rank_position'] == 2): ?>
                                                    <div class="rank-medal silver">
                                                        <i class="fas fa-trophy"></i>
                                                        <span>2</span>
                                                    </div>
                                                <?php elseif ($entry['rank_position'] == 3): ?>
                                                    <div class="rank-medal bronze">
                                                        <i class="fas fa-trophy"></i>
                                                        <span>3</span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary fs-6">#<?php echo $entry['rank_position']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo UPLOAD_URL; ?>avatars/<?php echo $entry['profile_picture']; ?>" 
                                                         class="rounded-circle me-3" 
                                                         width="40" 
                                                         height="40" 
                                                         alt="<?php echo htmlspecialchars($entry['username']); ?>">
                                                    <div>
                                                        <div class="fw-bold">
                                                            <?php echo htmlspecialchars($entry['full_name']); ?>
                                                            <?php if ($entry['user_id'] == $user_id): ?>
                                                                <span class="badge bg-primary ms-2">You</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <small class="text-muted">@<?php echo htmlspecialchars($entry['username']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge bg-purple fs-6">Level <?php echo $entry['current_level']; ?></span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <strong class="text-warning">
                                                    <i class="fas fa-star me-1"></i><?php echo number_format($entry['total_xp']); ?>
                                                </strong>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="text-success">
                                                    <i class="fas fa-medal me-1"></i><?php echo $entry['total_badges']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="text-info">
                                                    <i class="fas fa-graduation-cap me-1"></i><?php echo $entry['completed_courses']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="text-danger">
                                                    <i class="fas fa-fire me-1"></i><?php echo $entry['current_streak']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center p-4">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h3><?php echo count($leaderboard); ?></h3>
                        <p class="text-muted mb-0">Active Learners</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center p-4">
                        <i class="fas fa-fire fa-3x text-danger mb-3"></i>
                        <h3>
                            <?php 
                            $max_streak = !empty($leaderboard) ? max(array_column($leaderboard, 'current_streak')) : 0;
                            echo $max_streak;
                            ?>
                        </h3>
                        <p class="text-muted mb-0">Highest Streak</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center p-4">
                        <i class="fas fa-star fa-3x text-warning mb-3"></i>
                        <h3>
                            <?php 
                            $total_xp = !empty($leaderboard) ? array_sum(array_column($leaderboard, 'total_xp')) : 0;
                            echo number_format($total_xp);
                            ?>
                        </h3>
                        <p class="text-muted mb-0">Total XP Earned</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.bg-gradient-purple {
    background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
}

.rank-badge {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.rank-number {
    font-size: 2rem;
    font-weight: bold;
}

.rank-medal {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}

.rank-medal i {
    font-size: 1.5rem;
}

.rank-medal.gold i {
    color: #FFD700;
}

.rank-medal.silver i {
    color: #C0C0C0;
}

.rank-medal.bronze i {
    color: #CD7F32;
}

.rank-medal span {
    font-weight: bold;
    font-size: 0.9rem;
}

.table-primary {
    background-color: rgba(139, 92, 246, 0.1) !important;
}
</style>

<?php require_once '../includes/footer.php'; ?>