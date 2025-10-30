<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Manage Quizzes - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get filter parameters
$course_filter = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Fetch quizzes with course info and question count
$query = "
    SELECT q.*, c.course_title, s.subject_name,
           COUNT(DISTINCT qq.question_id) as question_count,
           COUNT(DISTINCT uqa.attempt_id) as attempt_count
    FROM quizzes q
    INNER JOIN courses c ON q.course_id = c.course_id
    INNER JOIN subjects s ON c.subject_id = s.subject_id
    LEFT JOIN quiz_questions qq ON q.quiz_id = qq.quiz_id
    LEFT JOIN user_quiz_attempts uqa ON q.quiz_id = uqa.quiz_id
";

if ($course_filter > 0) {
    $query .= " WHERE q.course_id = ?";
}

$query .= " GROUP BY q.quiz_id ORDER BY c.course_title ASC, q.quiz_title ASC";

if ($course_filter > 0) {
    $stmt = $conn->prepare($query);
    $stmt->execute([$course_filter]);
} else {
    $stmt = $conn->query($query);
}
$quizzes = $stmt->fetchAll();

// Fetch all courses for filter dropdown
$courses_stmt = $conn->query("
    SELECT c.course_id, c.course_title, s.subject_name
    FROM courses c
    INNER JOIN subjects s ON c.subject_id = s.subject_id
    ORDER BY s.subject_name ASC, c.course_title ASC
");
$courses = $courses_stmt->fetchAll();

$page_title = "Manage Quizzes";
include '../../includes/header.php';
include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-question-circle me-2"></i>Manage Quizzes</h2>
            <a href="/admin/quizzes/add.php" class="btn btn-purple">
                <i class="fas fa-plus me-2"></i>Add New Quiz
            </a>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="course_id" class="form-label">Filter by Course</label>
                        <select name="course_id" id="course_id" class="form-select" onchange="this.form.submit()">
                            <option value="0">All Courses</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['course_id']; ?>"
                                    <?php echo $course_filter == $course['course_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['subject_name'] . ' - ' . $course['course_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($course_filter > 0): ?>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="/admin/quizzes/" class="btn btn-secondary">Clear Filter</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                    switch($_GET['success']) {
                        case 'added':
                            echo 'Quiz added successfully!';
                            break;
                        case 'updated':
                            echo 'Quiz updated successfully!';
                            break;
                        case 'deleted':
                            echo 'Quiz deleted successfully!';
                            break;
                        case 'question_added':
                            echo 'Question added successfully!';
                            break;
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($quizzes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No quizzes found. <?php echo $course_filter > 0 ? 'Try selecting a different course.' : 'Start by adding a new quiz.'; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($quizzes as $quiz): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-book me-1"></i>
                                <?php echo htmlspecialchars($quiz['subject_name']); ?> -
                                <?php echo htmlspecialchars($quiz['course_title']); ?>
                            </p>

                            <?php if (!empty($quiz['description'])): ?>
                            <p class="card-text small text-muted">
                                <?php echo htmlspecialchars(substr($quiz['description'], 0, 100)); ?>
                                <?php echo strlen($quiz['description']) > 100 ? '...' : ''; ?>
                            </p>
                            <?php endif; ?>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="small">
                                        <i class="fas fa-question text-primary me-1"></i>
                                        <strong><?php echo $quiz['question_count']; ?></strong> Questions
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small">
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        <strong><?php echo $quiz['passing_score']; ?>%</strong> Pass
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small">
                                        <i class="fas fa-star text-warning me-1"></i>
                                        <strong><?php echo $quiz['xp_reward']; ?></strong> XP
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small">
                                        <i class="fas fa-users text-info me-1"></i>
                                        <strong><?php echo $quiz['attempt_count']; ?></strong> Attempts
                                    </div>
                                </div>
                                <?php if ($quiz['time_limit']): ?>
                                <div class="col-12">
                                    <div class="small">
                                        <i class="fas fa-clock text-danger me-1"></i>
                                        <strong><?php echo $quiz['time_limit']; ?></strong> minutes
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="/admin/quizzes/questions.php?id=<?php echo $quiz['quiz_id']; ?>"
                                   class="btn btn-sm btn-purple">
                                    <i class="fas fa-list me-1"></i>Manage Questions
                                </a>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="/admin/quizzes/edit.php?id=<?php echo $quiz['quiz_id']; ?>"
                                       class="btn btn-outline-primary" title="Edit Quiz">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                    <a href="/admin/quizzes/delete.php?id=<?php echo $quiz['quiz_id']; ?>"
                                       class="btn btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this quiz? All questions and student attempts will be lost!')"
                                       title="Delete Quiz">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light small text-muted">
                            Created: <?php echo date('M d, Y', strtotime($quiz['created_at'])); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
