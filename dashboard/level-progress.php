<?php
require_once '../includes/header.php';
require_login();

$page_title = "Level Progress - " . SITE_NAME;

$user_id = get_user_id();
$user = get_user_data($user_id);

// Get current level details
$stmt = $conn->prepare("SELECT * FROM levels WHERE level_number = ?");
$stmt->execute([$user['current_level']]);
$current_level = $stmt->fetch();

// Get next level details
$stmt = $conn->prepare("SELECT * FROM levels WHERE level_number = ?");
$stmt->execute([$user['current_level'] + 1]);
$next_level = $stmt->fetch();

// Get all levels
$all_levels = $conn->query("SELECT * FROM levels ORDER BY level_number ASC")->fetchAll();

// Calculate progress to next level
$xp_for_next_level = $next_level ? $next_level['total_xp_required'] : $current_level['total_xp_required'];
$xp_progress = $user['total_xp'];
$xp_needed = $xp_for_next_level - $xp_progress;
$progress_percentage = $next_level ? min(100, ($xp_progress / $xp_for_next_level) * 100) : 100;

// Get XP breakdown
$xp_breakdown = $conn->prepare("
    SELECT xp_type, SUM(xp_amount) as total_xp, COUNT(*) as count
    FROM xp_transactions
    WHERE user_id = ?
    GROUP BY xp_type
    ORDER BY total_xp DESC
");
$xp_breakdown->execute([$user_id]);
$xp_sources = $xp_breakdown->fetchAll();

// Recent XP transactions
$recent_xp = $conn->prepare("
    SELECT * FROM xp_transactions
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 20
");
$recent_xp->execute([$user_id]);
$recent_transactions = $recent_xp->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <h2 class="mb-4"><i class="fas fa-layer-group me-2"></i>Level Progress</h2>

        <!-- Current Level Card -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <div class="level-badge">
                                    <i class="fas fa-trophy fa-4x text-purple"></i>
                                    <h1 class="mt-2 mb-0 text-purple"><?php echo $user['current_level']; ?></h1>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h3><?php echo htmlspecialchars($current_level['title']); ?></h3>
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($current_level['description']); ?></p>

                                <?php if ($next_level): ?>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span><strong>Progress to Level <?php echo $next_level['level_number']; ?>:</strong></span>
                                        <span class="text-purple">
                                            <strong><?php echo number_format($xp_progress); ?></strong> / <?php echo number_format($xp_for_next_level); ?> XP
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-purple" role="progressbar"
                                             style="width: <?php echo $progress_percentage; ?>%">
                                            <?php echo round($progress_percentage, 1); ?>%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?php if ($xp_needed > 0): ?>
                                            <?php echo number_format($xp_needed); ?> XP needed to reach Level <?php echo $next_level['level_number']; ?>
                                        <?php else: ?>
                                            Ready to level up!
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-success mb-0">
                                    <i class="fas fa-crown me-2"></i>
                                    <strong>Maximum Level Reached!</strong> You've achieved the highest level!
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- XP Breakdown -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>XP Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($xp_sources)): ?>
                            <p class="text-muted mb-0">No XP earned yet. Start learning to earn XP!</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($xp_sources as $source): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>
                                            <i class="fas fa-star text-warning me-2"></i>
                                            <strong><?php echo ucfirst($source['xp_type']); ?></strong>
                                        </span>
                                        <span class="badge bg-purple"><?php echo number_format($source['total_xp']); ?> XP</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <?php
                                        $percentage = ($source['total_xp'] / $user['total_xp']) * 100;
                                        ?>
                                        <div class="progress-bar bg-purple" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?php echo $source['count']; ?> activities</small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Next Level Info -->
                <?php if ($next_level): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-purple text-white">
                        <h5 class="mb-0">Next Level</h5>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-trophy fa-3x text-purple mb-3"></i>
                        <h4><?php echo htmlspecialchars($next_level['title']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($next_level['description']); ?></p>
                        <hr>
                        <p class="small mb-0">
                            <strong>Unlocks:</strong><br>
                            <?php echo htmlspecialchars($next_level['unlocks']); ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- All Levels -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Levels</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($all_levels as $level): ?>
                        <div class="d-flex align-items-center mb-3 pb-3 <?php echo $level['level_number'] < count($all_levels) ? 'border-bottom' : ''; ?>">
                            <div class="flex-shrink-0">
                                <div class="level-icon <?php echo $user['current_level'] >= $level['level_number'] ? 'text-purple' : 'text-muted'; ?>">
                                    <?php if ($user['current_level'] >= $level['level_number']): ?>
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    <?php else: ?>
                                        <i class="fas fa-lock fa-2x"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong class="<?php echo $user['current_level'] == $level['level_number'] ? 'text-purple' : ''; ?>">
                                    Level <?php echo $level['level_number']; ?>: <?php echo htmlspecialchars($level['title']); ?>
                                </strong>
                                <br>
                                <small class="text-muted"><?php echo number_format($level['total_xp_required']); ?> XP</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent XP History -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent XP Activity</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_transactions)): ?>
                    <p class="text-muted mb-0">No XP transactions yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Activity</th>
                                    <th>Description</th>
                                    <th class="text-end">XP Earned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_transactions as $transaction): ?>
                                <tr>
                                    <td>
                                        <small><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo ucfirst($transaction['xp_type']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                    <td class="text-end">
                                        <strong class="text-success">+<?php echo $transaction['xp_amount']; ?> XP</strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="/dashboard/xp-history.php" class="btn btn-sm btn-outline-purple">
                            View Full XP History
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
