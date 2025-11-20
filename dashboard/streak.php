<?php
require_once '../includes/header.php';
require_login();

$page_title = "Daily Streak - " . SITE_NAME;

$user_id = get_user_id();
$user = get_user_data($user_id);

// Get streak history
$streak_history = $conn->prepare("
    SELECT * FROM daily_streaks
    WHERE user_id = ?
    ORDER BY login_date DESC
    LIMIT 30
");
$streak_history->execute([$user_id]);
$streaks = $streak_history->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <h2 class="mb-4"><i class="fas fa-fire me-2 text-danger"></i>Daily Streak</h2>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-5">
                        <i class="fas fa-fire fa-4x text-danger mb-3"></i>
                        <h1 class="display-3 text-danger mb-0"><?php echo $user['current_streak']; ?></h1>
                        <p class="text-muted">Day Streak</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-5">
                        <i class="fas fa-trophy fa-4x text-warning mb-3"></i>
                        <h1 class="display-3 text-warning mb-0"><?php echo $user['longest_streak']; ?></h1>
                        <p class="text-muted">Longest Streak</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Streak History (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($streaks)): ?>
                    <p class="text-muted mb-0">No streak data yet. Log in daily to build your streak!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th class="text-end">XP Earned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($streaks as $streak): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($streak['login_date'])); ?></td>
                                    <td><?php echo date('l', strtotime($streak['login_date'])); ?></td>
                                    <td class="text-end">
                                        <strong class="text-success">+<?php echo $streak['xp_earned']; ?> XP</strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>About Streaks</h5>
                <ul class="small">
                    <li>Log in every day to maintain your streak</li>
                    <li>Earn bonus XP for consecutive daily logins</li>
                    <li>Missing a day resets your streak to 0</li>
                    <li>Your longest streak is saved permanently</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
