<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Course Completion Report - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get overall statistics
$overall_stats = $conn->query("
    SELECT
        COUNT(DISTINCT uc.user_id) as total_enrollments,
        COUNT(DISTINCT CASE WHEN uc.is_completed = 1 THEN uc.user_id END) as completed_enrollments,
        COUNT(DISTINCT uc.user_id) as unique_users,
        COUNT(DISTINCT uc.course_id) as enrolled_courses,
        AVG(CASE WHEN uc.is_completed = 1 
            THEN DATEDIFF(uc.completion_date, uc.enrollment_date) END) as avg_days_to_complete
    FROM user_courses uc
")->fetch();

$completion_rate = $overall_stats['total_enrollments'] > 0 
    ? round(($overall_stats['completed_enrollments'] / $overall_stats['total_enrollments']) * 100, 1) 
    : 0;

// Get course-by-course statistics
$course_stats = $conn->query("
    SELECT c.course_id, c.course_title, s.subject_name,
           COUNT(DISTINCT uc.user_id) as total_enrollments,
           COUNT(DISTINCT CASE WHEN uc.is_completed = 1 THEN uc.user_id END) as completed_count,
           AVG(CASE WHEN uc.is_completed = 1 
               THEN DATEDIFF(uc.completion_date, uc.enrollment_date) END) as avg_completion_days,
           (SELECT COUNT(*) FROM lessons WHERE course_id = c.course_id) as lesson_count
    FROM courses c
    INNER JOIN subjects s ON c.subject_id = s.subject_id
    LEFT JOIN user_courses uc ON c.course_id = uc.course_id
    GROUP BY c.course_id
    ORDER BY total_enrollments DESC
")->fetchAll();

// Get recent completions
$recent_completions = $conn->query("
    SELECT uc.*, c.course_title, u.username, u.full_name,
           DATEDIFF(uc.completion_date, uc.enrollment_date) as days_taken
    FROM user_courses uc
    INNER JOIN courses c ON uc.course_id = c.course_id
    INNER JOIN users u ON uc.user_id = u.user_id
    WHERE uc.is_completed = 1
    ORDER BY uc.completion_date DESC
    LIMIT 15
")->fetchAll();

// Get monthly completion trend (last 12 months)
$monthly_completions = $conn->query("
    SELECT DATE_FORMAT(completion_date, '%Y-%m') as month,
           COUNT(*) as completions,
           COUNT(DISTINCT user_id) as unique_completers
    FROM user_courses
    WHERE is_completed = 1 
          AND completion_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(completion_date, '%Y-%m')
    ORDER BY month ASC
")->fetchAll();

// $page_title = "Course Completion Report";
// include '../../includes/header.php';
// include '../../includes/admin-sidebar.php';

?>

<!-- <div class="main-content">
    <div class="container-fluid"> -->

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../../includes/admin-sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-chart-pie me-2"></i>Course Completion Report</h2>
                    <a href="/admin/analytics.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Analytics
                    </a>
                </div>

                <!-- Overall Statistics -->
                <div class="row mb-4">
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($overall_stats['total_enrollments']); ?></h3>
                                <small class="text-muted">Total Enrollments</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($overall_stats['completed_enrollments']); ?></h3>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-percentage fa-2x text-purple mb-2"></i>
                                <h3 class="mb-0"><?php echo $completion_rate; ?>%</h3>
                                <small class="text-muted">Completion Rate</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-user-graduate fa-2x text-info mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($overall_stats['unique_users']); ?></h3>
                                <small class="text-muted">Unique Learners</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-book fa-2x text-warning mb-2"></i>
                                <h3 class="mb-0"><?php echo number_format($overall_stats['enrolled_courses']); ?></h3>
                                <small class="text-muted">Active Courses</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x text-danger mb-2"></i>
                                <h3 class="mb-0"><?php echo round($overall_stats['avg_days_to_complete'] ?? 0); ?></h3>
                                <small class="text-muted">Avg Days</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Performance Table -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Course Completion Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($course_stats)): ?>
                            <p class="text-muted text-center">No course data available.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Course</th>
                                            <th>Subject</th>
                                            <th>Lessons</th>
                                            <th>Enrollments</th>
                                            <th>Completed</th>
                                            <th>Completion Rate</th>
                                            <th>Avg Days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($course_stats as $course): ?>
                                        <?php
                                        $course_completion_rate = $course['total_enrollments'] > 0 
                                            ? round(($course['completed_count'] / $course['total_enrollments']) * 100, 1) 
                                            : 0;
                                        
                                        // Color code based on completion rate
                                        if ($course_completion_rate >= 70) {
                                            $badge_class = 'bg-success';
                                        } elseif ($course_completion_rate >= 40) {
                                            $badge_class = 'bg-warning';
                                        } else {
                                            $badge_class = 'bg-danger';
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="/admin/courses/edit.php?id=<?php echo $course['course_id']; ?>">
                                                    <strong><?php echo htmlspecialchars($course['course_title']); ?></strong>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($course['subject_name']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $course['lesson_count']; ?> lessons</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $course['total_enrollments']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?php echo $course['completed_count']; ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 100px; height: 20px;">
                                                        <div class="progress-bar <?php echo $badge_class; ?>" 
                                                            style="width: <?php echo $course_completion_rate; ?>%">
                                                        </div>
                                                    </div>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo $course_completion_rate; ?>%
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($course['avg_completion_days']): ?>
                                                    <span class="badge bg-info">
                                                        <?php echo round($course['avg_completion_days']); ?> days
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
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
                    <!-- Monthly Completion Trend -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Completion Trend</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($monthly_completions)): ?>
                                    <p class="text-muted text-center">No completion data available.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Completions</th>
                                                    <th>Unique Users</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($monthly_completions as $month): ?>
                                                <tr>
                                                    <td><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo $month['completions']; ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $month['unique_completers']; ?> users</span>
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

                    <!-- Recent Completions -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Recent Completions</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_completions)): ?>
                                    <p class="text-muted text-center">No recent completions.</p>
                                <?php else: ?>
                                    <div class="list-group">
                                        <?php foreach ($recent_completions as $completion): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($completion['username']); ?></h6>
                                                <small><?php echo date('M d, Y', strtotime($completion['completion_date'])); ?></small>
                                            </div>
                                            <p class="mb-1 small">
                                                <strong><?php echo htmlspecialchars($completion['course_title']); ?></strong>
                                            </p>
                                            <small class="text-muted">
                                                Completed in <?php echo $completion['days_taken']; ?> days
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
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Insights</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Top Performing Courses</h6>
                                <ul class="small">
                                    <?php
                                    $top_courses = array_slice($course_stats, 0, 3);
                                    foreach ($top_courses as $top):
                                        $rate = $top['total_enrollments'] > 0 
                                            ? round(($top['completed_count'] / $top['total_enrollments']) * 100, 1) 
                                            : 0;
                                    ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($top['course_title']); ?></strong> 
                                        - <?php echo $rate; ?>% completion rate
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Key Metrics</h6>
                                <ul class="small">
                                    <li>Overall completion rate: <strong><?php echo $completion_rate; ?>%</strong></li>
                                    <li>Average completion time: <strong><?php echo round($overall_stats['avg_days_to_complete'] ?? 0); ?> days</strong></li>
                                    <li>Total certificates issued: <strong><?php echo number_format($overall_stats['completed_enrollments']); ?></strong></li>
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
