<?php
require_once '../../includes/header.php';
require_login();

$page_title = "User Progress Report - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get user ID from URL
$selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get all users for dropdown
$users_query = "SELECT user_id, username, full_name FROM users WHERE role = 'learner' ORDER BY username ASC";
$all_users = $conn->query($users_query)->fetchAll();

// If searching, filter users
if (!empty($search)) {
    $search_stmt = $conn->prepare("
        SELECT user_id, username, full_name, email, total_xp, current_level, last_login_date
        FROM users
        WHERE role = 'learner' AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)
        ORDER BY username ASC
        LIMIT 20
    ");
    $search_param = '%' . $search . '%';
    $search_stmt->execute([$search_param, $search_param, $search_param]);
    $search_results = $search_stmt->fetchAll();
}

// If user is selected, get detailed progress
$selected_user_data = null;
$course_progress = [];
$quiz_attempts = [];
$badges_earned = [];
$xp_history = [];

if ($selected_user_id > 0) {
    // Get user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$selected_user_id]);
    $selected_user_data = $stmt->fetch();
    
    if ($selected_user_data) {
        // Get course enrollments and progress
        $course_progress_stmt = $conn->prepare("
            SELECT uc.*, c.course_title, s.subject_name,
                   (SELECT COUNT(*) FROM lessons WHERE course_id = c.course_id) as total_lessons,
                   (SELECT COUNT(*) FROM lessons l 
                    LEFT JOIN user_lesson_progress up ON l.lesson_id = up.lesson_id AND up.user_id = uc.user_id
                    WHERE l.course_id = c.course_id AND up.is_completed = 1) as completed_lessons
            FROM user_courses uc
            INNER JOIN courses c ON uc.course_id = c.course_id
            INNER JOIN subjects s ON c.subject_id = s.subject_id
            WHERE uc.user_id = ?
            ORDER BY uc.enrollment_date DESC
        ");
        $course_progress_stmt->execute([$selected_user_id]);
        $course_progress = $course_progress_stmt->fetchAll();
        
        // Get quiz attempts
        $quiz_stmt = $conn->prepare("
            SELECT uqa.*, q.quiz_title, l.lesson_title, c.course_title
            FROM user_quiz_attempts uqa
            INNER JOIN quizzes q ON uqa.quiz_id = q.quiz_id
            INNER JOIN lessons l ON q.course_id = l.course_id
            INNER JOIN courses c ON l.course_id = c.course_id
            WHERE uqa.user_id = ?
            ORDER BY uqa.attempt_date DESC
            LIMIT 10
        ");
        $quiz_stmt->execute([$selected_user_id]);
        $quiz_attempts = $quiz_stmt->fetchAll();
        
        // Get badges earned
        $badges_stmt = $conn->prepare("
            SELECT ub.*, b.badge_name, b.badge_icon, b.description
            FROM user_badges ub
            INNER JOIN badges b ON ub.badge_id = b.badge_id
            WHERE ub.user_id = ?
            ORDER BY ub.earned_date DESC
        ");
        $badges_stmt->execute([$selected_user_id]);
        $badges_earned = $badges_stmt->fetchAll();
        
        // Get recent XP history
        $xp_stmt = $conn->prepare("
            SELECT * FROM xp_transactions
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 15
        ");
        $xp_stmt->execute([$selected_user_id]);
        $xp_history = $xp_stmt->fetchAll();
    }
}

// $page_title = "User Progress Report";
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
                    <h2><i class="fas fa-user-chart me-2"></i>User Progress Report</h2>
                    <a href="/admin/analytics.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Analytics
                    </a>
                </div>

                <!-- User Search/Selection -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <label for="search" class="form-label">Search User</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                    placeholder="Search by username, name, or email..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-purple">
                                    <i class="fas fa-search me-1"></i>Search
                                </button>
                            </div>
                        </form>
                        
                        <?php if (!empty($search) && isset($search_results)): ?>
                        <div class="mt-3">
                            <h6>Search Results:</h6>
                            <div class="list-group">
                                <?php foreach ($search_results as $result): ?>
                                <a href="?user_id=<?php echo $result['user_id']; ?>" 
                                class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($result['username']); ?></h6>
                                        <small class="badge bg-purple">Level <?php echo $result['current_level']; ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($result['full_name']); ?></p>
                                    <small><?php echo number_format($result['total_xp']); ?> XP</small>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($selected_user_data): ?>
                <!-- User Overview -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-purple text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            <?php echo htmlspecialchars($selected_user_data['username']); ?> - Progress Overview
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="text-center">
                                    <i class="fas fa-user-circle fa-3x text-primary mb-2"></i>
                                    <h6><?php echo htmlspecialchars($selected_user_data['full_name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($selected_user_data['email']); ?></small>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-purple mb-0"><?php echo $selected_user_data['current_level']; ?></h4>
                                                <small class="text-muted">Current Level</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-info mb-0"><?php echo number_format($selected_user_data['total_xp']); ?></h4>
                                                <small class="text-muted">Total XP</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-success mb-0"><?php echo count($course_progress); ?></h4>
                                                <small class="text-muted">Enrolled Courses</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-warning mb-0"><?php echo count($badges_earned); ?></h4>
                                                <small class="text-muted">Badges Earned</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Progress -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Course Progress</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($course_progress)): ?>
                            <p class="text-muted text-center">No course enrollments yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Course</th>
                                            <th>Subject</th>
                                            <th>Progress</th>
                                            <th>Status</th>
                                            <th>Enrolled</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($course_progress as $progress): ?>
                                        <?php
                                        $completion = $progress['total_lessons'] > 0 
                                            ? round(($progress['completed_lessons'] / $progress['total_lessons']) * 100) 
                                            : 0;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($progress['course_title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($progress['subject_name']); ?></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-purple" style="width: <?php echo $completion; ?>%">
                                                        <?php echo $completion; ?>%
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo $progress['completed_lessons']; ?> / <?php echo $progress['total_lessons']; ?> lessons
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($progress['is_completed']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Completed
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">In Progress</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($progress['enrollment_date'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <!-- Quiz Performance -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Recent Quiz Attempts</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($quiz_attempts)): ?>
                                    <p class="text-muted text-center">No quiz attempts yet.</p>
                                <?php else: ?>
                                    <div class="list-group">
                                        <?php foreach ($quiz_attempts as $attempt): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($attempt['quiz_title']); ?></h6>
                                                <span class="badge <?php echo $attempt['passed'] ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $attempt['score']; ?>%
                                                </span>
                                            </div>
                                            <p class="mb-1 small text-muted">
                                                <?php echo htmlspecialchars($attempt['course_title']); ?> - <?php echo htmlspecialchars($attempt['lesson_title']); ?>
                                            </p>
                                            <small>
                                                <?php echo date('M d, Y', strtotime($attempt['attempt_date'])); ?>
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

                    <!-- Badges Earned -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-medal me-2"></i>Badges Earned</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($badges_earned)): ?>
                                    <p class="text-muted text-center">No badges earned yet.</p>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($badges_earned as $badge): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-warning">
                                                <div class="card-body text-center">
                                                    <i class="<?php echo htmlspecialchars($badge['badge_icon']); ?> fa-2x text-warning mb-2"></i>
                                                    <h6><?php echo htmlspecialchars($badge['badge_name']); ?></h6>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y', strtotime($badge['earned_date'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- XP History -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent XP Transactions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($xp_history)): ?>
                            <p class="text-muted text-center">No XP transactions yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>XP Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($xp_history as $xp): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y H:i', strtotime($xp['transaction_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($xp['xp_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($xp['description']); ?></td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-star me-1"></i>+<?php echo $xp['xp_amount']; ?> XP
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
                <?php elseif (!empty($search)): ?>
                    <!-- Show search results above -->
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Search for a user to view their detailed progress report.
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
