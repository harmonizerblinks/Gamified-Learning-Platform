<?php
$page_title = "Reports & Analytics - " . SITE_NAME;
require_once '../includes/header.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get comprehensive statistics
// User stats
$stmt = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN role='learner' THEN 1 ELSE 0 END) as learners, SUM(CASE WHEN role='admin' THEN 1 ELSE 0 END) as admins FROM users WHERE is_active = 1");
$user_stats = $stmt->fetch();

// Course stats
$stmt = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN is_published=1 THEN 1 ELSE 0 END) as published FROM courses");
$course_stats = $stmt->fetch();

// Enrollment stats
$stmt = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN is_completed=1 THEN 1 ELSE 0 END) as completed FROM user_courses");
$enrollment_stats = $stmt->fetch();
$completion_rate = $enrollment_stats['total'] > 0 ? round(($enrollment_stats['completed'] / $enrollment_stats['total']) * 100, 1) : 0;

// Quiz stats
$stmt = $conn->query("SELECT COUNT(*) as total_attempts, SUM(CASE WHEN passed=1 THEN 1 ELSE 0 END) as passed_attempts, AVG(score) as avg_score FROM user_quiz_attempts");
$quiz_stats = $stmt->fetch();
$pass_rate = $quiz_stats['total_attempts'] > 0 ? round(($quiz_stats['passed_attempts'] / $quiz_stats['total_attempts']) * 100, 1) : 0;

// XP stats
$stmt = $conn->query("SELECT SUM(total_xp) as total_xp, AVG(total_xp) as avg_xp, MAX(total_xp) as max_xp FROM users WHERE role='learner'");
$xp_stats = $stmt->fetch();

// Badge stats
$stmt = $conn->query("SELECT COUNT(*) as total_badges FROM badges WHERE is_active=1");
$badge_count = $stmt->fetch()['total_badges'];

$stmt = $conn->query("SELECT COUNT(*) as badges_earned FROM user_badges");
$badges_earned = $stmt->fetch()['badges_earned'];

// Certificate stats
$stmt = $conn->query("SELECT COUNT(*) as total FROM certificates");
$cert_count = $stmt->fetch()['total'];

// Top performing courses
$stmt = $conn->query("
    SELECT c.course_title, COUNT(DISTINCT uc.user_id) as enrollments,
           AVG(CASE WHEN uqa.passed=1 THEN uqa.score ELSE NULL END) as avg_quiz_score
    FROM courses c
    LEFT JOIN user_courses uc ON c.course_id = uc.course_id
    LEFT JOIN quizzes q ON c.course_id = q.course_id
    LEFT JOIN user_quiz_attempts uqa ON q.quiz_id = uqa.quiz_id
    WHERE c.is_published = 1
    GROUP BY c.course_id
    ORDER BY enrollments DESC
    LIMIT 10
");
$top_courses = $stmt->fetchAll();

// Recent activity
$stmt = $conn->query("
    SELECT u.username, u.full_name, uc.enrollment_date, c.course_title, 'Enrollment' as activity_type
    FROM user_courses uc
    JOIN users u ON uc.user_id = u.user_id
    JOIN courses c ON uc.course_id = c.course_id
    ORDER BY uc.enrollment_date DESC
    LIMIT 15
");
$recent_activity = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h1>
                <div>
                    <span class="text-muted">
                        <i class="fas fa-calendar me-1"></i><?php echo date('l, F d, Y'); ?>
                    </span>
                </div>
            </div>

            <?php display_messages(); ?>

            <!-- Overview Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <i class="fas fa-users fa-3x text-primary mb-2"></i>
                        <h3 class="mb-0"><?php echo number_format($user_stats['total']); ?></h3>
                        <small class="text-muted">Total Users</small>
                        <hr>
                        <div class="d-flex justify-content-around text-muted small">
                            <span><i class="fas fa-user"></i> <?php echo $user_stats['learners']; ?> Learners</span>
                            <span><i class="fas fa-user-shield"></i> <?php echo $user_stats['admins']; ?> Admins</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <i class="fas fa-graduation-cap fa-3x text-success mb-2"></i>
                        <h3 class="mb-0"><?php echo number_format($course_stats['total']); ?></h3>
                        <small class="text-muted">Total Courses</small>
                        <hr>
                        <div class="text-muted small">
                            <i class="fas fa-check-circle text-success"></i> <?php echo $course_stats['published']; ?> Published
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <i class="fas fa-user-check fa-3x text-info mb-2"></i>
                        <h3 class="mb-0"><?php echo number_format($enrollment_stats['total']); ?></h3>
                        <small class="text-muted">Total Enrollments</small>
                        <hr>
                        <div class="text-muted small">
                            <i class="fas fa-chart-line text-info"></i> <?php echo $completion_rate; ?>% Completion Rate
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <i class="fas fa-certificate fa-3x text-warning mb-2"></i>
                        <h3 class="mb-0"><?php echo number_format($cert_count); ?></h3>
                        <small class="text-muted">Certificates Issued</small>
                        <hr>
                        <div class="text-muted small">
                            <i class="fas fa-medal text-warning"></i> <?php echo number_format($badges_earned); ?> Badges Earned
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Quiz Performance</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h4 class="text-primary"><?php echo number_format($quiz_stats['total_attempts']); ?></h4>
                                    <small class="text-muted">Total Attempts</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-success"><?php echo $pass_rate; ?>%</h4>
                                    <small class="text-muted">Pass Rate</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-warning"><?php echo round($quiz_stats['avg_score'], 1); ?>%</h4>
                                    <small class="text-muted">Avg Score</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-star me-2"></i>XP Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h4 class="text-warning"><?php echo number_format($xp_stats['total_xp']); ?></h4>
                                    <small class="text-muted">Total XP</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-info"><?php echo number_format($xp_stats['avg_xp']); ?></h4>
                                    <small class="text-muted">Avg per User</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-success"><?php echo number_format($xp_stats['max_xp']); ?></h4>
                                    <small class="text-muted">Highest XP</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Courses Table -->
            <div class="row g-4">
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Performing Courses</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th class="text-center">Enrollments</th>
                                            <th class="text-center">Avg Quiz Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_courses as $course): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-info"><?php echo $course['enrollments']; ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($course['avg_quiz_score']): ?>
                                                        <span class="badge bg-success"><?php echo round($course['avg_quiz_score'], 1); ?>%</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_activity as $activity): ?>
                                    <div class="list-group-item px-0 border-0">
                                        <div class="d-flex align-items-start">
                                            <i class="fas fa-user-plus text-success me-2 mt-1"></i>
                                            <div class="flex-grow-1">
                                                <strong><?php echo htmlspecialchars($activity['full_name']); ?></strong>
                                                <small class="text-muted">enrolled in</small>
                                                <br>
                                                <small><?php echo htmlspecialchars($activity['course_title']); ?></small>
                                                <br>
                                                <small class="text-muted"><?php echo time_ago($activity['enrollment_date']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
