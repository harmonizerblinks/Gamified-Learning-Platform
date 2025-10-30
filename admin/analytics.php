<?php
require_once '../includes/header.php';
require_login();

$page_title = "Analytics & Reports - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get date range (default: last 30 days)
$days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
$start_date = date('Y-m-d', strtotime("-$days days"));

// Overall Statistics
$overall_stats = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM users WHERE role = 'learner') as total_learners,
        (SELECT COUNT(*) FROM users WHERE role = 'learner' AND is_active = 1) as active_learners,
        (SELECT COUNT(*) FROM courses WHERE is_published = 1) as total_courses,
        (SELECT COUNT(*) FROM lessons) as total_lessons,
        (SELECT COUNT(*) FROM quizzes) as total_quizzes,
        (SELECT COUNT(*) FROM badges) as total_badges,
        (SELECT COUNT(DISTINCT user_id) FROM user_courses) as users_with_enrollments,
        (SELECT COUNT(*) FROM user_courses WHERE is_completed = 1) as completed_courses,
        (SELECT COUNT(*) FROM user_quiz_attempts) as total_quiz_attempts,
        (SELECT COUNT(*) FROM user_quiz_attempts WHERE passed = 1) as passed_quiz_attempts,
        (SELECT COUNT(*) FROM user_badges) as total_badges_earned,
        (SELECT COUNT(*) FROM certificates) as total_certificates_issued
")->fetch();

// Recent Activity (last N days)
$recent_activity = $conn->prepare("
    SELECT
        (SELECT COUNT(*) FROM users WHERE DATE(created_at) >= ?) as new_users,
        (SELECT COUNT(*) FROM user_courses WHERE DATE(enrollment_date) >= ?) as new_enrollments,
        (SELECT COUNT(*) FROM user_quiz_attempts WHERE DATE(attempt_date) >= ?) as recent_quiz_attempts,
        (SELECT COUNT(*) FROM user_badges WHERE DATE(earned_date) >= ?) as recent_badges_earned,
        (SELECT COUNT(*) FROM certificates WHERE DATE(issued_date) >= ?) as recent_certificates
");
$recent_activity->execute([$start_date, $start_date, $start_date, $start_date, $start_date]);
$activity_stats = $recent_activity->fetch();

// Top Courses by Enrollment
$top_courses = $conn->query("
    SELECT c.course_title, s.subject_name, COUNT(uc.user_id) as enrollment_count
    FROM courses c
    INNER JOIN subjects s ON c.subject_id = s.subject_id
    LEFT JOIN user_courses uc ON c.course_id = uc.course_id
    WHERE c.is_published = 1
    GROUP BY c.course_id
    ORDER BY enrollment_count DESC
    LIMIT 10
")->fetchAll();

// Top Performing Students
$top_students = $conn->query("
    SELECT u.username, u.full_name, u.total_xp, u.current_level, l.title as level_title,
           COUNT(DISTINCT ub.badge_id) as badge_count,
           COUNT(DISTINCT c.certificate_id) as certificate_count
    FROM users u
    LEFT JOIN levels l ON u.current_level = l.level_number
    LEFT JOIN user_badges ub ON u.user_id = ub.user_id
    LEFT JOIN certificates c ON u.user_id = c.user_id
    WHERE u.role = 'learner' AND u.is_active = 1
    GROUP BY u.user_id
    ORDER BY u.total_xp DESC
    LIMIT 10
")->fetchAll();

// Quiz Performance Stats
$quiz_performance = $conn->query("
    SELECT
        ROUND(AVG(score), 2) as avg_score,
        ROUND(MIN(score), 2) as min_score,
        ROUND(MAX(score), 2) as max_score,
        ROUND((COUNT(CASE WHEN passed = 1 THEN 1 END) / COUNT(*)) * 100, 2) as pass_rate
    FROM user_quiz_attempts
")->fetch();

// Subject Distribution
$subject_stats = $conn->query("
    SELECT s.subject_name,
           COUNT(DISTINCT c.course_id) as course_count,
           COUNT(DISTINCT uc.user_id) as enrolled_users
    FROM subjects s
    LEFT JOIN courses c ON s.subject_id = c.subject_id
    LEFT JOIN user_courses uc ON c.course_id = uc.course_id
    GROUP BY s.subject_id
    ORDER BY enrolled_users DESC
")->fetchAll();

// Level Distribution
$level_distribution = $conn->query("
    SELECT l.level_number, l.title, COUNT(u.user_id) as user_count
    FROM levels l
    LEFT JOIN users u ON l.level_number = u.current_level AND u.role = 'learner'
    GROUP BY l.level_id
    ORDER BY l.level_number ASC
")->fetchAll();

// Most Earned Badges
$top_badges = $conn->query("
    SELECT b.badge_name, b.badge_type, COUNT(ub.user_id) as earned_count
    FROM badges b
    LEFT JOIN user_badges ub ON b.badge_id = ub.badge_id
    GROUP BY b.badge_id
    ORDER BY earned_count DESC
    LIMIT 10
")->fetchAll();

$page_title = "Analytics & Reports";
include '../includes/header.php';
include '../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-bar me-2"></i>Analytics & Reports</h2>
            <div class="btn-group" role="group">
                <a href="?days=7" class="btn btn-<?php echo $days == 7 ? 'purple' : 'outline-secondary'; ?>">7 Days</a>
                <a href="?days=30" class="btn btn-<?php echo $days == 30 ? 'purple' : 'outline-secondary'; ?>">30 Days</a>
                <a href="?days=90" class="btn btn-<?php echo $days == 90 ? 'purple' : 'outline-secondary'; ?>">90 Days</a>
                <a href="?days=365" class="btn btn-<?php echo $days == 365 ? 'purple' : 'outline-secondary'; ?>">1 Year</a>
            </div>
        </div>

        <!-- Overview Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users fa-3x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Total Learners</div>
                                <div class="h3 mb-0"><?php echo number_format($overall_stats['total_learners']); ?></div>
                                <small class="text-success"><?php echo number_format($overall_stats['active_learners']); ?> active</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-graduation-cap fa-3x text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Total Courses</div>
                                <div class="h3 mb-0"><?php echo number_format($overall_stats['total_courses']); ?></div>
                                <small class="text-muted"><?php echo number_format($overall_stats['total_lessons']); ?> lessons</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-question-circle fa-3x text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Quiz Attempts</div>
                                <div class="h3 mb-0"><?php echo number_format($overall_stats['total_quiz_attempts']); ?></div>
                                <small class="text-success">
                                    <?php echo number_format($overall_stats['passed_quiz_attempts']); ?> passed
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-certificate fa-3x text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Certificates</div>
                                <div class="h3 mb-0"><?php echo number_format($overall_stats['total_certificates_issued']); ?></div>
                                <small class="text-muted"><?php echo number_format($overall_stats['total_badges_earned']); ?> badges</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Activity (Last <?php echo $days; ?> Days)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col">
                                <div class="h4 text-primary"><?php echo number_format($activity_stats['new_users']); ?></div>
                                <div class="small text-muted">New Users</div>
                            </div>
                            <div class="col">
                                <div class="h4 text-success"><?php echo number_format($activity_stats['new_enrollments']); ?></div>
                                <div class="small text-muted">New Enrollments</div>
                            </div>
                            <div class="col">
                                <div class="h4 text-info"><?php echo number_format($activity_stats['recent_quiz_attempts']); ?></div>
                                <div class="small text-muted">Quiz Attempts</div>
                            </div>
                            <div class="col">
                                <div class="h4 text-warning"><?php echo number_format($activity_stats['recent_badges_earned']); ?></div>
                                <div class="small text-muted">Badges Earned</div>
                            </div>
                            <div class="col">
                                <div class="h4 text-danger"><?php echo number_format($activity_stats['recent_certificates']); ?></div>
                                <div class="small text-muted">Certificates Issued</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Quiz Performance -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Quiz Performance</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="h3 text-success"><?php echo $quiz_performance['avg_score']; ?>%</div>
                                <div class="small text-muted">Average Score</div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="h3 text-primary"><?php echo $quiz_performance['pass_rate']; ?>%</div>
                                <div class="small text-muted">Pass Rate</div>
                            </div>
                            <div class="col-6">
                                <div class="h4 text-danger"><?php echo $quiz_performance['min_score']; ?>%</div>
                                <div class="small text-muted">Lowest Score</div>
                            </div>
                            <div class="col-6">
                                <div class="h4 text-warning"><?php echo $quiz_performance['max_score']; ?>%</div>
                                <div class="small text-muted">Highest Score</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completion Stats -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Completion Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>Course Completion Rate</span>
                                <strong>
                                    <?php
                                    $total_enrollments = $overall_stats['users_with_enrollments'] > 0 ? $overall_stats['users_with_enrollments'] : 1;
                                    $completion_rate = round(($overall_stats['completed_courses'] / $total_enrollments) * 100, 1);
                                    echo $completion_rate;
                                    ?>%
                                </strong>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $completion_rate; ?>%"></div>
                            </div>
                        </div>

                        <div class="row text-center mt-4">
                            <div class="col-4">
                                <div class="h4"><?php echo number_format($overall_stats['users_with_enrollments']); ?></div>
                                <div class="small text-muted">Enrolled Users</div>
                            </div>
                            <div class="col-4">
                                <div class="h4"><?php echo number_format($overall_stats['completed_courses']); ?></div>
                                <div class="small text-muted">Completed</div>
                            </div>
                            <div class="col-4">
                                <div class="h4"><?php echo number_format($overall_stats['total_certificates_issued']); ?></div>
                                <div class="small text-muted">Certified</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Top Courses -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Courses by Enrollment</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Subject</th>
                                        <th class="text-end">Enrollments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_courses as $course): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($course['subject_name']); ?></small></td>
                                        <td class="text-end"><span class="badge bg-primary"><?php echo $course['enrollment_count']; ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Students -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Top Performing Students</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Level</th>
                                        <th class="text-end">XP</th>
                                        <th class="text-end">Badges</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_students as $student): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($student['username']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($student['full_name']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-purple">L<?php echo $student['current_level']; ?></span>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($student['level_title']); ?></small>
                                        </td>
                                        <td class="text-end"><?php echo number_format($student['total_xp']); ?></td>
                                        <td class="text-end">
                                            <i class="fas fa-medal text-warning"></i> <?php echo $student['badge_count']; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Level Distribution -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Level Distribution</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($level_distribution as $level): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>
                                    <span class="badge bg-purple">Level <?php echo $level['level_number']; ?></span>
                                    <?php echo htmlspecialchars($level['title']); ?>
                                </span>
                                <strong><?php echo $level['user_count']; ?> users</strong>
                            </div>
                            <div class="progress" style="height: 15px;">
                                <?php
                                $max_users = max(array_column($level_distribution, 'user_count'));
                                $percentage = $max_users > 0 ? ($level['user_count'] / $max_users) * 100 : 0;
                                ?>
                                <div class="progress-bar bg-purple" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Most Earned Badges & Subject Stats -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-medal me-2"></i>Most Earned Badges</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Badge</th>
                                        <th>Type</th>
                                        <th class="text-end">Earned</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($top_badges, 0, 5) as $badge): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($badge['badge_name']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo ucfirst($badge['badge_type']); ?></span></td>
                                        <td class="text-end"><?php echo $badge['earned_count']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Subject Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th class="text-end">Courses</th>
                                        <th class="text-end">Enrollments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($subject_stats, 0, 5) as $subject): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                        <td class="text-end"><?php echo $subject['course_count']; ?></td>
                                        <td class="text-end"><span class="badge bg-info"><?php echo $subject['enrolled_users']; ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>
