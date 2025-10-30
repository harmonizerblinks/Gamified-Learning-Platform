<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Quiz Performance Report - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get overall quiz statistics
$overall_stats = $conn->query("
    SELECT
        COUNT(*) as total_attempts,
        COUNT(DISTINCT user_id) as unique_users,
        COUNT(DISTINCT quiz_id) as quizzes_attempted,
        AVG(score) as average_score,
        COUNT(CASE WHEN passed = 1 THEN 1 END) as total_passed,
        COUNT(CASE WHEN passed = 0 THEN 1 END) as total_failed,
        SUM(xp_earned) as total_xp_awarded
    FROM user_quiz_attempts
")->fetch();

$pass_rate = $overall_stats['total_attempts'] > 0 
    ? round(($overall_stats['total_passed'] / $overall_stats['total_attempts']) * 100, 1) 
    : 0;

// Get quiz-by-quiz performance
$quiz_stats = $conn->query("
    SELECT q.quiz_id, q.quiz_title, q.passing_score, l.lesson_title, c.course_title,
           COUNT(uqa.attempt_id) as attempt_count,
           AVG(uqa.score) as avg_score,
           COUNT(CASE WHEN uqa.passed = 1 THEN 1 END) as passed_count,
           COUNT(CASE WHEN uqa.passed = 0 THEN 1 END) as failed_count,
           MIN(uqa.score) as min_score,
           MAX(uqa.score) as max_score,
           (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.quiz_id) as question_count
    FROM quizzes q
    INNER JOIN lessons l ON q.course_id = l.course_id
    INNER JOIN courses c ON l.course_id = c.course_id
    LEFT JOIN user_quiz_attempts uqa ON q.quiz_id = uqa.quiz_id
    GROUP BY q.quiz_id, q.quiz_title, q.passing_score, l.lesson_title, c.course_title
    ORDER BY attempt_count DESC
")->fetchAll();

// Get recent quiz attempts
$recent_attempts = $conn->query("
    SELECT uqa.*, q.quiz_title, u.username, l.lesson_title, c.course_title
    FROM user_quiz_attempts uqa
    INNER JOIN quizzes q ON uqa.quiz_id = q.quiz_id
    INNER JOIN users u ON uqa.user_id = u.user_id
    INNER JOIN lessons l ON q.course_id = l.course_id
    INNER JOIN courses c ON l.course_id = c.course_id
    ORDER BY uqa.attempt_date DESC
    LIMIT 20
")->fetchAll();

// Get top performers
$top_performers = $conn->query("
    SELECT u.user_id, u.username, u.full_name,
           COUNT(*) as quiz_count,
           AVG(uqa.score) as avg_score,
           COUNT(CASE WHEN uqa.passed = 1 THEN 1 END) as passed_count,
           SUM(uqa.xp_earned) as total_xp
    FROM user_quiz_attempts uqa
    INNER JOIN users u ON uqa.user_id = u.user_id
    GROUP BY u.user_id, u.username, u.full_name
    HAVING quiz_count >= 3
    ORDER BY avg_score DESC, quiz_count DESC
    LIMIT 10
")->fetchAll();

// $page_title = "Quiz Performance Report";
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
                    <h2><i class="fas fa-question-circle me-2"></i>Quiz Performance Report</h2>
                    <a href="/admin/analytics.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Analytics
                    </a>
                </div>

                <!-- Overall Statistics -->
                <div class="row mb-4">
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-tasks fa-2x text-primary mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($overall_stats['total_attempts']); ?></h3>
                                <small class="text-muted">Total Attempts</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($overall_stats['unique_users']); ?></h3>
                                <small class="text-muted">Unique Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-percentage fa-2x text-warning mb-2"></i>
                                <h3 class="mb-0"><?php echo round($overall_stats['average_score'], 1); ?>%</h3>
                                <small class="text-muted">Average Score</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h3 class="mb-0"><?php echo $pass_rate; ?>%</h3>
                                <small class="text-muted">Pass Rate</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($overall_stats['total_failed']); ?></h3>
                                <small class="text-muted">Failed Attempts</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-star fa-2x text-purple mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($overall_stats['total_xp_awarded']); ?></h3>
                                <small class="text-muted">Total XP Awarded</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Performance Table -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Quiz Performance Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($quiz_stats)): ?>
                            <p class="text-muted text-center">No quiz data available.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Quiz</th>
                                            <th>Course/Lesson</th>
                                            <th>Questions</th>
                                            <th>Attempts</th>
                                            <th>Avg Score</th>
                                            <th>Pass Rate</th>
                                            <th>Difficulty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($quiz_stats as $quiz): ?>
                                        <?php
                                        $quiz_pass_rate = $quiz['attempt_count'] > 0 
                                            ? round(($quiz['passed_count'] / $quiz['attempt_count']) * 100, 1) 
                                            : 0;
                                        
                                        // Determine difficulty based on pass rate
                                        if ($quiz_pass_rate >= 70) {
                                            $difficulty = 'Easy';
                                            $difficulty_class = 'success';
                                        } elseif ($quiz_pass_rate >= 40) {
                                            $difficulty = 'Medium';
                                            $difficulty_class = 'warning';
                                        } else {
                                            $difficulty = 'Hard';
                                            $difficulty_class = 'danger';
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="/admin/quizzes/edit.php?id=<?php echo $quiz['quiz_id']; ?>">
                                                    <strong><?php echo htmlspecialchars($quiz['quiz_title']); ?></strong>
                                                </a>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo htmlspecialchars($quiz['course_title']); ?>
                                                    <br>
                                                    <span class="text-muted"><?php echo htmlspecialchars($quiz['lesson_title']); ?></span>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $quiz['question_count']; ?> Q</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $quiz['attempt_count']; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($quiz['attempt_count'] > 0): ?>
                                                    <span class="badge bg-info"><?php echo round($quiz['avg_score'], 1); ?>%</span>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo round($quiz['min_score']); ?>% - <?php echo round($quiz['max_score']); ?>%
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($quiz['attempt_count'] > 0): ?>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress me-2" style="width: 80px; height: 20px;">
                                                            <div class="mt-0 mb-0 progress-bar bg-<?php echo $difficulty_class; ?>" 
                                                                style="height:20px; width: <?php echo $quiz_pass_rate; ?>%">
                                                            </div>
                                                        </div>
                                                        <span class="badge bg-<?php echo $difficulty_class; ?>">
                                                            <?php echo $quiz_pass_rate; ?>%
                                                        </span>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo $quiz['passed_count']; ?>P / <?php echo $quiz['failed_count']; ?>F
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($quiz['attempt_count'] > 0): ?>
                                                    <span class="badge bg-<?php echo $difficulty_class; ?>">
                                                        <?php echo $difficulty; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <!-- Top Performers -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Quiz Performers</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($top_performers)): ?>
                                    <p class="text-muted text-center">No performance data available.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>User</th>
                                                    <th>Quizzes</th>
                                                    <th>Avg Score</th>
                                                    <th>Pass Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($top_performers as $index => $performer): ?>
                                                <?php
                                                $user_pass_rate = $performer['quiz_count'] > 0 
                                                    ? round(($performer['passed_count'] / $performer['quiz_count']) * 100) 
                                                    : 0;
                                                ?>
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
                                                        <a href="/admin/users/view.php?id=<?php echo $performer['user_id']; ?>">
                                                            <?php echo htmlspecialchars($performer['username']); ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo $performer['quiz_count']; ?></td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo round($performer['avg_score'], 1); ?>%</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo $user_pass_rate; ?>%</span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Quiz Attempts -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Quiz Attempts</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_attempts)): ?>
                                    <p class="text-muted text-center">No recent quiz attempts.</p>
                                <?php else: ?>
                                    <div class="list-group" style="max-height: 500px; overflow-y: auto;">
                                        <?php foreach ($recent_attempts as $attempt): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($attempt['username']); ?>
                                                </h6>
                                                <span class="badge <?php echo $attempt['passed'] ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $attempt['score']; ?>%
                                                </span>
                                            </div>
                                            <p class="mb-1 small">
                                                <strong><?php echo htmlspecialchars($attempt['quiz_title']); ?></strong>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($attempt['course_title']); ?>
                                                • <?php echo date('M d, Y H:i', strtotime($attempt['attempt_date'])); ?>
                                                <?php if ($attempt['passed']): ?>
                                                    <span class="text-success ms-2">
                                                        <i class="fas fa-star"></i> +<?php echo $attempt['xp_earned']; ?> XP
                                                    </span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insights -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Quiz Insights</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Most Challenging Quizzes</h6>
                                <ul class="small">
                                    <?php
                                    // Sort by lowest pass rate
                                    usort($quiz_stats, function($a, $b) {
                                        $a_rate = $a['attempt_count'] > 0 ? ($a['passed_count'] / $a['attempt_count']) : 0;
                                        $b_rate = $b['attempt_count'] > 0 ? ($b['passed_count'] / $b['attempt_count']) : 0;
                                        return $a_rate <=> $b_rate;
                                    });
                                    
                                    $challenging = array_slice(array_filter($quiz_stats, function($q) {
                                        return $q['attempt_count'] > 0;
                                    }), 0, 3);
                                    
                                    foreach ($challenging as $hard):
                                        $rate = round(($hard['passed_count'] / $hard['attempt_count']) * 100, 1);
                                    ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($hard['quiz_title']); ?></strong> 
                                        - <?php echo $rate; ?>% pass rate
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6>Most Popular Quizzes</h6>
                                <ul class="small">
                                    <?php
                                    // Already sorted by attempt count
                                    $popular = array_slice(array_filter($quiz_stats, function($q) {
                                        return $q['attempt_count'] > 0;
                                    }), 0, 3);
                                    
                                    foreach ($popular as $pop):
                                    ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($pop['quiz_title']); ?></strong> 
                                        - <?php echo $pop['attempt_count']; ?> attempts
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6>Key Metrics</h6>
                                <ul class="small">
                                    <li>Overall pass rate: <strong><?php echo $pass_rate; ?>%</strong></li>
                                    <li>Average quiz score: <strong><?php echo round($overall_stats['average_score'], 1); ?>%</strong></li>
                                    <li>Total XP awarded: <strong><?php echo number_format($overall_stats['total_xp_awarded']); ?> XP</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
