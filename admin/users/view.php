<?php
require_once '../../includes/header.php';
require_login();

$page_title = "View User - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch user data
$stmt = $conn->prepare("
    SELECT u.*, l.title as level_title, l.xp_required
    FROM users u
    LEFT JOIN levels l ON u.current_level = l.level_number
    WHERE u.user_id = ?
");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();

if (!$user_data) {
    redirect('/admin/users/');
}

// Fetch enrolled courses with progress
$courses_stmt = $conn->prepare("
    SELECT c.*, uc.enrollment_date, uc.completion_date, uc.progress_percentage, uc.is_completed,
           s.subject_name
    FROM user_courses uc
    INNER JOIN courses c ON uc.course_id = c.course_id
    INNER JOIN subjects s ON c.subject_id = s.subject_id
    WHERE uc.user_id = ?
    ORDER BY uc.enrollment_date DESC
");
$courses_stmt->execute([$user_id]);
$enrolled_courses = $courses_stmt->fetchAll();

// Fetch earned badges
$badges_stmt = $conn->prepare("
    SELECT b.*, ub.earned_date
    FROM user_badges ub
    INNER JOIN badges b ON ub.badge_id = b.badge_id
    WHERE ub.user_id = ?
    ORDER BY ub.earned_date DESC
");
$badges_stmt->execute([$user_id]);
$earned_badges = $badges_stmt->fetchAll();

// Fetch certificates
$certificates_stmt = $conn->prepare("
    SELECT c.*, cert.certificate_code, cert.issued_date
    FROM certificates cert
    INNER JOIN courses c ON cert.course_id = c.course_id
    WHERE cert.user_id = ?
    ORDER BY cert.issued_date DESC
");
$certificates_stmt->execute([$user_id]);
$certificates = $certificates_stmt->fetchAll();

// Fetch recent quiz attempts
$quiz_attempts_stmt = $conn->prepare("
    SELECT uqa.*, q.quiz_title, c.course_title
    FROM user_quiz_attempts uqa
    INNER JOIN quizzes q ON uqa.quiz_id = q.quiz_id
    INNER JOIN courses c ON q.course_id = c.course_id
    WHERE uqa.user_id = ?
    ORDER BY uqa.attempt_date DESC
    LIMIT 10
");
$quiz_attempts_stmt->execute([$user_id]);
$quiz_attempts = $quiz_attempts_stmt->fetchAll();

// Fetch XP transactions
$xp_transactions_stmt = $conn->prepare("
    SELECT *
    FROM xp_transactions
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 20
");
$xp_transactions_stmt->execute([$user_id]);
$xp_transactions = $xp_transactions_stmt->fetchAll();

$page_title = "View User: " . $user_data['username'];
// include '../../includes/header.php';
// include '../../includes/admin-sidebar.php';
?>


<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            
            <!-- Sidebar -->
            <?php include '../../includes/admin-sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-user me-2"></i>User Details</h2>
                    <div>
                        <a href="/admin/users/edit.php?id=<?php echo $user_id; ?>" class="btn btn-purple me-2">
                            <i class="fas fa-edit me-2"></i>Edit User
                        </a>
                        <a href="/admin/users/" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Users
                        </a>
                    </div>
                </div>

                <!-- User Profile Card -->
                <div class="row mb-4">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <img src="<?php echo UPLOAD_URL; ?>avatars/<?php echo $user_data['profile_picture'] ?: 'default.png'; ?>"
                                    class="rounded-circle mb-3" width="120" height="120" alt="Avatar">
                                <h4><?php echo htmlspecialchars($user_data['full_name']); ?></h4>
                                <p class="text-muted">@<?php echo htmlspecialchars($user_data['username']); ?></p>
                                <p class="mb-2">
                                    <?php if ($user_data['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">Administrator</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Learner</span>
                                    <?php endif; ?>
                                    <?php if ($user_data['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </p>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="h4 mb-0"><?php echo $user_data['current_level']; ?></div>
                                        <small class="text-muted">Level</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h4 mb-0"><?php echo number_format($user_data['total_xp']); ?></div>
                                        <small class="text-muted">Total XP</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h4 mb-0"><?php echo $user_data['current_streak']; ?></div>
                                        <small class="text-muted">Streak</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mt-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Account Information</h5>
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <th>Email:</th>
                                        <td><?php echo htmlspecialchars($user_data['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>User ID:</th>
                                        <td><?php echo $user_data['user_id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Joined:</th>
                                        <td><?php echo date('M d, Y', strtotime($user_data['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Last Login:</th>
                                        <td>
                                            <?php echo $user_data['last_login_date'] ? date('M d, Y', strtotime($user_data['last_login_date'])) : 'Never'; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Longest Streak:</th>
                                        <td><?php echo $user_data['longest_streak']; ?> days</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <!-- Courses Tab -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Enrolled Courses (<?php echo count($enrolled_courses); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($enrolled_courses)): ?>
                                    <p class="text-muted mb-0">No courses enrolled yet.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th>Progress</th>
                                                    <th>Enrolled</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($enrolled_courses as $course): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($course['course_title']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($course['subject_name']); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="mt-0 mb-0 progress-bar bg-purple" role="progressbar"
                                                                style="height:20px; width: <?php echo $course['progress_percentage']; ?>%">
                                                                <?php echo round($course['progress_percentage']); ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($course['enrollment_date'])); ?></td>
                                                    <td>
                                                        <?php if ($course['is_completed']): ?>
                                                            <span class="badge bg-success">Completed</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">In Progress</span>
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

                        <!-- Badges -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-medal me-2"></i>Earned Badges (<?php echo count($earned_badges); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($earned_badges)): ?>
                                    <p class="text-muted mb-0">No badges earned yet.</p>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($earned_badges as $badge): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-medal fa-2x text-warning me-3"></i>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($badge['badge_name']); ?></strong>
                                                    <br><small class="text-muted">Earned: <?php echo date('M d, Y', strtotime($badge['earned_date'])); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Certificates -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Certificates (<?php echo count($certificates); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($certificates)): ?>
                                    <p class="text-muted mb-0">No certificates earned yet.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th>Certificate Code</th>
                                                    <th>Issued Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($certificates as $cert): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($cert['course_title']); ?></td>
                                                    <td><code><?php echo htmlspecialchars($cert['certificate_code']); ?></code></td>
                                                    <td><?php echo date('M d, Y', strtotime($cert['issued_date'])); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Recent Quiz Attempts -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Recent Quiz Attempts</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($quiz_attempts)): ?>
                                    <p class="text-muted mb-0">No quiz attempts yet.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Quiz</th>
                                                    <th>Score</th>
                                                    <th>XP Earned</th>
                                                    <th>Date</th>
                                                    <th>Result</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($quiz_attempts as $attempt): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($attempt['quiz_title']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($attempt['course_title']); ?></small>
                                                    </td>
                                                    <td><?php echo round($attempt['score'], 2); ?>%</td>
                                                    <td><span class="badge bg-success"><?php echo $attempt['xp_earned']; ?> XP</span></td>
                                                    <td><?php echo date('M d, Y', strtotime($attempt['attempt_date'])); ?></td>
                                                    <td>
                                                        <?php if ($attempt['passed']): ?>
                                                            <span class="badge bg-success">Passed</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Failed</span>
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
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
