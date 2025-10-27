<?php 
require_once '../includes/header.php';
$page_title = "Achievements - " . SITE_NAME;
require_login();

$user_id = get_user_id();

// Get all badges with earned status
$stmt = $conn->prepare("
    SELECT b.*, 
           ub.earned_date,
           CASE WHEN ub.badge_id IS NOT NULL THEN 1 ELSE 0 END as is_earned
    FROM badges b
    LEFT JOIN user_badges ub ON b.badge_id = ub.badge_id AND ub.user_id = ?
    ORDER BY b.badge_type, b.requirement_value ASC
");
$stmt->execute([$user_id]);
$all_badges = $stmt->fetchAll();

// Organize badges by type
$badges_by_type = [
    'level' => [],
    'course' => [],
    'quiz' => [],
    'streak' => [],
    'special' => []
];

foreach ($all_badges as $badge) {
    $badges_by_type[$badge['badge_type']][] = $badge;
}

// Count earned badges
$earned_badges = array_filter($all_badges, function($b) { return $b['is_earned']; });
$total_badges = count($all_badges);
$earned_count = count($earned_badges);
$completion_percentage = ($earned_count / $total_badges) * 100;

// Get user stats for progress tracking
$user = get_user_data($user_id);
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_courses WHERE user_id = ? AND is_completed = 1");
$stmt->execute([$user_id]);
$completed_courses = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_quiz_attempts WHERE user_id = ? AND score = 100");
$stmt->execute([$user_id]);
$perfect_quizzes = $stmt->fetch()['total'];
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="mb-4"><i class="fas fa-medal text-warning me-2"></i>Achievements & Badges</h1>
            
            <?php display_messages(); ?>
            
            <!-- Progress Overview -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-3">Badge Collection Progress</h5>
                            <div class="progress mb-2" style="height: 30px;">
                                <div class="progress-bar mt-0 mb-0  bg-success" 
                                     role="progressbar" 
                                     style="height: 30px; width: <?php echo $completion_percentage; ?>%">
                                    <?php echo $earned_count; ?>/<?php echo $total_badges; ?> Badges
                                </div>
                            </div>
                            <p class="text-muted mb-0">
                                <?php echo round($completion_percentage); ?>% Complete • 
                                <?php echo ($total_badges - $earned_count); ?> badges remaining
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-4 text-warning mb-2">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <h3 class="mb-0"><?php echo $earned_count; ?></h3>
                            <p class="text-muted mb-0">Badges Earned</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Level Badges -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <i class="fas fa-layer-group text-purple me-2"></i>Level Badges
                    </h4>
                    <span class="text-muted">Current Level: <?php echo $user['current_level']; ?></span>
                </div>
                <div class="row g-4">
                    <?php if (empty($badges_by_type['level'])): ?>
                        <div class="col-12">
                            <div class="alert alert-info">No level badges available</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($badges_by_type['level'] as $badge): ?>
                            <div class="col-md-4 col-lg-3">
                                <div class="card border-0 shadow-sm h-100 text-center position-relative <?php echo !$badge['is_earned'] ? 'opacity-75' : ''; ?>">
                                    <?php if ($badge['is_earned']): ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <i class="fas fa-check-circle text-success fa-2x"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-4">
                                        <div class="badge-icon mb-3">
                                            <?php if ($badge['is_earned']): ?>
                                                <i class="fas fa-medal fa-4x text-warning"></i>
                                            <?php else: ?>
                                                <i class="fas fa-medal fa-4x text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="mb-2"><?php echo htmlspecialchars($badge['badge_name']); ?></h5>
                                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($badge['description']); ?></p>
                                        
                                        <div class="requirement-box p-2 bg-light rounded mb-2">
                                            <small class="text-muted">Requirement</small>
                                            <div class="fw-bold">Reach Level <?php echo $badge['requirement_value']; ?></div>
                                        </div>
                                        
                                        <?php if ($badge['is_earned']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Earned
                                            </span>
                                            <div class="text-muted small mt-2">
                                                <?php echo time_ago($badge['earned_date']); ?>
                                            </div>
                                        <?php else: ?>
                                            <?php if ($user['current_level'] >= $badge['requirement_value']): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-hourglass-half me-1"></i>Available
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-lock me-1"></i>Locked
                                                </span>
                                                <div class="text-muted small mt-2">
                                                    <?php echo ($badge['requirement_value'] - $user['current_level']); ?> levels to go
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php if ($badge['xp_reward'] > 0): ?>
                                            <div class="mt-2">
                                                <small class="text-warning">
                                                    <i class="fas fa-star me-1"></i>+<?php echo $badge['xp_reward']; ?> XP
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Course Badges -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <i class="fas fa-graduation-cap text-success me-2"></i>Course Completion Badges
                    </h4>
                    <span class="text-muted">Completed: <?php echo $completed_courses; ?> courses</span>
                </div>
                <div class="row g-4">
                    <?php if (empty($badges_by_type['course'])): ?>
                        <div class="col-12">
                            <div class="alert alert-info">No course badges available</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($badges_by_type['course'] as $badge): ?>
                            <div class="col-md-4 col-lg-3">
                                <div class="card border-0 shadow-sm h-100 text-center position-relative <?php echo !$badge['is_earned'] ? 'opacity-75' : ''; ?>">
                                    <?php if ($badge['is_earned']): ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <i class="fas fa-check-circle text-success fa-2x"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-4">
                                        <div class="badge-icon mb-3">
                                            <?php if ($badge['is_earned']): ?>
                                                <i class="fas fa-award fa-4x text-success"></i>
                                            <?php else: ?>
                                                <i class="fas fa-award fa-4x text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="mb-2"><?php echo htmlspecialchars($badge['badge_name']); ?></h5>
                                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($badge['description']); ?></p>
                                        
                                        <div class="requirement-box p-2 bg-light rounded mb-2">
                                            <small class="text-muted">Requirement</small>
                                            <div class="fw-bold">Complete <?php echo $badge['requirement_value']; ?> Course<?php echo $badge['requirement_value'] > 1 ? 's' : ''; ?></div>
                                        </div>
                                        
                                        <?php if ($badge['is_earned']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Earned
                                            </span>
                                            <div class="text-muted small mt-2">
                                                <?php echo time_ago($badge['earned_date']); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="progress mb-2" style="height: 20px;">
                                                <?php 
                                                $progress = min(100, ($completed_courses / $badge['requirement_value']) * 100);
                                                ?>
                                                <div class="progress-bar mt-0 mb-0 bg-success" style=" height: 20px; width: <?php echo $progress; ?>%">
                                                    <?php echo $completed_courses; ?>/<?php echo $badge['requirement_value']; ?>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo ($badge['requirement_value'] - $completed_courses); ?> more to go
                                            </small>
                                        <?php endif; ?>
                                        
                                        <?php if ($badge['xp_reward'] > 0): ?>
                                            <div class="mt-2">
                                                <small class="text-warning">
                                                    <i class="fas fa-star me-1"></i>+<?php echo $badge['xp_reward']; ?> XP
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quiz Badges -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <i class="fas fa-brain text-info me-2"></i>Quiz Master Badges
                    </h4>
                    <span class="text-muted">Perfect Scores: <?php echo $perfect_quizzes; ?></span>
                </div>
                <div class="row g-4">
                    <?php if (empty($badges_by_type['quiz'])): ?>
                        <div class="col-12">
                            <div class="alert alert-info">No quiz badges available</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($badges_by_type['quiz'] as $badge): ?>
                            <div class="col-md-4 col-lg-3">
                                <div class="card border-0 shadow-sm h-100 text-center position-relative <?php echo !$badge['is_earned'] ? 'opacity-75' : ''; ?>">
                                    <?php if ($badge['is_earned']): ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <i class="fas fa-check-circle text-success fa-2x"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-4">
                                        <div class="badge-icon mb-3">
                                            <?php if ($badge['is_earned']): ?>
                                                <i class="fas fa-brain fa-4x text-info"></i>
                                            <?php else: ?>
                                                <i class="fas fa-brain fa-4x text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="mb-2"><?php echo htmlspecialchars($badge['badge_name']); ?></h5>
                                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($badge['description']); ?></p>
                                        
                                        <div class="requirement-box p-2 bg-light rounded mb-2">
                                            <small class="text-muted">Requirement</small>
                                            <div class="fw-bold"><?php echo $badge['requirement_value']; ?> Perfect Quiz<?php echo $badge['requirement_value'] > 1 ? 'zes' : ''; ?></div>
                                        </div>
                                        
                                        <?php if ($badge['is_earned']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Earned
                                            </span>
                                            <div class="text-muted small mt-2">
                                                <?php echo time_ago($badge['earned_date']); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="progress mb-2" style="height: 20px;">
                                                <?php 
                                                $progress = min(100, ($perfect_quizzes / $badge['requirement_value']) * 100);
                                                ?>
                                                <div class="progress-bar bg-info" style="width: <?php echo $progress; ?>%">
                                                    <?php echo $perfect_quizzes; ?>/<?php echo $badge['requirement_value']; ?>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo ($badge['requirement_value'] - $perfect_quizzes); ?> more to go
                                            </small>
                                        <?php endif; ?>
                                        
                                        <?php if ($badge['xp_reward'] > 0): ?>
                                            <div class="mt-2">
                                                <small class="text-warning">
                                                    <i class="fas fa-star me-1"></i>+<?php echo $badge['xp_reward']; ?> XP
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Streak Badges -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <i class="fas fa-fire text-danger me-2"></i>Streak Badges
                    </h4>
                    <span class="text-muted">Current Streak: <?php echo $user['current_streak']; ?> days</span>
                </div>
                <div class="row g-4">
                    <?php if (empty($badges_by_type['streak'])): ?>
                        <div class="col-12">
                            <div class="alert alert-info">No streak badges available</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($badges_by_type['streak'] as $badge): ?>
                            <div class="col-md-4 col-lg-3">
                                <div class="card border-0 shadow-sm h-100 text-center position-relative <?php echo !$badge['is_earned'] ? 'opacity-75' : ''; ?>">
                                    <?php if ($badge['is_earned']): ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <i class="fas fa-check-circle text-success fa-2x"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-4">
                                        <div class="badge-icon mb-3">
                                            <?php if ($badge['is_earned']): ?>
                                                <i class="fas fa-fire fa-4x text-danger"></i>
                                            <?php else: ?>
                                                <i class="fas fa-fire fa-4x text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="mb-2"><?php echo htmlspecialchars($badge['badge_name']); ?></h5>
                                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($badge['description']); ?></p>
                                        
                                        <div class="requirement-box p-2 bg-light rounded mb-2">
                                            <small class="text-muted">Requirement</small>
                                            <div class="fw-bold"><?php echo $badge['requirement_value']; ?> Day Streak</div>
                                        </div>
                                        
                                        <?php if ($badge['is_earned']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Earned
                                            </span>
                                            <div class="text-muted small mt-2">
                                                <?php echo time_ago($badge['earned_date']); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="progress mb-2" style="height: 20px;">
                                                <?php 
                                                $progress = min(100, ($user['current_streak'] / $badge['requirement_value']) * 100);
                                                ?>
                                                <div class="progress-bar mt-0 mb-0 bg-danger" style="height: 20px; width: <?php echo $progress; ?>%">
                                                    <?php echo $user['current_streak']; ?>/<?php echo $badge['requirement_value']; ?>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo ($badge['requirement_value'] - $user['current_streak']); ?> more days
                                            </small>
                                        <?php endif; ?>
                                        
                                        <?php if ($badge['xp_reward'] > 0): ?>
                                            <div class="mt-2">
                                                <small class="text-warning">
                                                    <i class="fas fa-star me-1"></i>+<?php echo $badge['xp_reward']; ?> XP
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Special Badges -->
            <?php if (!empty($badges_by_type['special'])): ?>
                <div class="mb-5">
                    <h4 class="mb-3">
                        <i class="fas fa-crown text-warning me-2"></i>Special Badges
                    </h4>
                    <div class="row g-4">
                        <?php foreach ($badges_by_type['special'] as $badge): ?>
                            <div class="col-md-4 col-lg-3">
                                <div class="card border-0 shadow-sm h-100 text-center position-relative <?php echo !$badge['is_earned'] ? 'opacity-75' : ''; ?>">
                                    <?php if ($badge['is_earned']): ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <i class="fas fa-check-circle text-success fa-2x"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-4">
                                        <div class="badge-icon mb-3">
                                            <?php if ($badge['is_earned']): ?>
                                                <i class="fas fa-crown fa-4x text-warning"></i>
                                            <?php else: ?>
                                                <i class="fas fa-crown fa-4x text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="mb-2"><?php echo htmlspecialchars($badge['badge_name']); ?></h5>
                                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($badge['description']); ?></p>
                                        
                                        <?php if ($badge['is_earned']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Earned
                                            </span>
                                            <div class="text-muted small mt-2">
                                                <?php echo time_ago($badge['earned_date']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-lock me-1"></i>Secret
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($badge['xp_reward'] > 0): ?>
                                            <div class="mt-2">
                                                <small class="text-warning">
                                                    <i class="fas fa-star me-1"></i>+<?php echo $badge['xp_reward']; ?> XP
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
.badge-icon {
    transition: transform 0.3s ease;
}

.card:hover .badge-icon {
    transform: scale(1.1);
}

.requirement-box {
    border: 1px dashed #dee2e6;
}
</style>

<?php require_once '../includes/footer.php'; ?>