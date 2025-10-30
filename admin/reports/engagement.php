<?php
require_once '../../includes/header.php';
require_login();

$page_title = "User Engagement Report - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get date range filter
$days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
$start_date = date('Y-m-d', strtotime("-$days days"));

// Get active users statistics
$active_users = $conn->query("
    SELECT
        COUNT(DISTINCT user_id) as total_users,
        COUNT(DISTINCT CASE WHEN last_login_date >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN user_id END) as daily_active,
        COUNT(DISTINCT CASE WHEN last_login_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN user_id END) as weekly_active,
        COUNT(DISTINCT CASE WHEN last_login_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN user_id END) as monthly_active
    FROM users
")->fetch();

// Get most active users
$most_active = $conn->prepare("
    SELECT u.user_id, u.username, u.full_name, u.total_xp, u.current_level, u.last_login_date,
           COUNT(DISTINCT ds.login_date) as login_days
    FROM users u
    LEFT JOIN daily_streaks ds ON u.user_id = ds.user_id
        AND ds.login_date >= ?
    WHERE u.role = 'user'
    GROUP BY u.user_id
    ORDER BY login_days DESC, u.total_xp DESC
    LIMIT 10
");
$most_active->execute([$start_date]);
$active_users_list = $most_active->fetchAll();

// Get engagement by day
$daily_engagement = $conn->prepare("
    SELECT DATE(login_date) as date,
           COUNT(DISTINCT user_id) as active_users,
           SUM(xp_earned) as total_xp
    FROM daily_streaks
    WHERE login_date >= ?
    GROUP BY DATE(login_date)
    ORDER BY date ASC
");
$daily_engagement->execute([$start_date]);
$engagement_data = $daily_engagement->fetchAll();

// Get content interaction stats
$content_stats = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM user_courses WHERE enrollment_date >= '$start_date') as new_enrollments,
        (SELECT COUNT(*) FROM user_quiz_attempts WHERE attempt_date >= '$start_date') as quiz_attempts,
        (SELECT COUNT(*) FROM certificates WHERE issued_date >= '$start_date') as certificates_issued,
        (SELECT COUNT(*) FROM user_badges WHERE earned_date >= '$start_date') as badges_earned
")->fetch();

// $page_title = "User Engagement Report";
// include '../../includes/header.php';
// include '../../includes/admin-sidebar.php';
?>


<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../../includes/admin-sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-chart-line me-2"></i>User Engagement Report</h2>
                    <a href="/admin/analytics.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Analytics
                    </a>
                </div>

                <!-- Time Range Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="days" class="form-label">Time Period</label>
                                <select class="form-select" id="days" name="days" onchange="this.form.submit()">
                                    <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                                    <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                                    <option value="90" <?php echo $days == 90 ? 'selected' : ''; ?>>Last 90 Days</option>
                                    <option value="365" <?php echo $days == 365 ? 'selected' : ''; ?>>Last Year</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Active Users Stats -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-users fa-2x text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="small text-muted">Total Users</div>
                                        <div class="h4 mb-0"><?php echo number_format($active_users['total_users']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-user-clock fa-2x text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="small text-muted">Daily Active</div>
                                        <div class="h4 mb-0"><?php echo number_format($active_users['daily_active']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-calendar-week fa-2x text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="small text-muted">Weekly Active</div>
                                        <div class="h4 mb-0"><?php echo number_format($active_users['weekly_active']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-calendar-alt fa-2x text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="small text-muted">Monthly Active</div>
                                        <div class="h4 mb-0"><?php echo number_format($active_users['monthly_active']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Interaction Stats -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-user-plus fa-3x text-purple mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($content_stats['new_enrollments']); ?></h3>
                                <small class="text-muted">New Enrollments</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-question-circle fa-3x text-info mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($content_stats['quiz_attempts']); ?></h3>
                                <small class="text-muted">Quiz Attempts</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-certificate fa-3x text-success mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($content_stats['certificates_issued']); ?></h3>
                                <small class="text-muted">Certificates Issued</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-medal fa-3x text-warning mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($content_stats['badges_earned']); ?></h3>
                                <small class="text-muted">Badges Earned</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Most Active Users -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Most Active Users (Last <?php echo $days; ?> Days)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Rank</th>
                                        <th>User</th>
                                        <th>Level</th>
                                        <th>Total XP</th>
                                        <th>Active Days</th>
                                        <th>Last Login</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($active_users_list)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No activity data for this period</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($active_users_list as $index => $active_user): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index == 0): ?>
                                                <i class="fas fa-trophy text-warning"></i>
                                            <?php elseif ($index == 1): ?>
                                                <i class="fas fa-medal text-secondary"></i>
                                            <?php elseif ($index == 2): ?>
                                                <i class="fas fa-medal text-danger"></i>
                                            <?php else: ?>
                                                <?php echo $index + 1; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/admin/users/view.php?id=<?php echo $active_user['user_id']; ?>">
                                                <strong><?php echo htmlspecialchars($active_user['username']); ?></strong>
                                            </a>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($active_user['full_name']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-purple">Level <?php echo $active_user['current_level']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <i class="fas fa-star me-1"></i><?php echo number_format($active_user['total_xp']); ?> XP
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?php echo $active_user['login_days']; ?> days</span>
                                        </td>
                                        <td>
                                            <?php if ($active_user['last_login']): ?>
                                                <?php echo date('M d, Y', strtotime($active_user['last_login'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Daily Engagement Chart -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Daily Engagement Trend</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($engagement_data)): ?>
                            <p class="text-center text-muted">No engagement data available for this period.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Active Users</th>
                                            <th>Total XP Earned</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($engagement_data as $day): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $day['active_users']; ?> users</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-star me-1"></i><?php echo number_format($day['total_xp']); ?> XP
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
