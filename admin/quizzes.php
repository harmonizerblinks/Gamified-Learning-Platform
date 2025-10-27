<?php
$page_title = "Manage Quizzes - " . SITE_NAME;
require_once '../includes/header.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get course ID from URL
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Handle Delete
if (isset($_GET['delete'])) {
    $quiz_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
    if ($stmt->execute([$quiz_id])) {
        set_success('Quiz deleted successfully');
    } else {
        set_error('Failed to delete quiz');
    }
    redirect('/admin/quizzes.php' . ($course_id > 0 ? '?course_id=' . $course_id : ''));
}

// Handle Add/Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
    $course_id = (int)$_POST['course_id'];
    $quiz_title = clean_input($_POST['quiz_title']);
    $description = clean_input($_POST['description']);
    $passing_score = (int)$_POST['passing_score'];
    $xp_reward = (int)$_POST['xp_reward'];
    $bonus_xp_perfect = (int)$_POST['bonus_xp_perfect'];
    $time_limit = !empty($_POST['time_limit']) ? (int)$_POST['time_limit'] : null;

    if (empty($quiz_title) || $course_id == 0) {
        set_error('Quiz title and course are required');
    } else {
        if ($quiz_id > 0) {
            // Update existing quiz
            $stmt = $conn->prepare("
                UPDATE quizzes SET
                    course_id = ?, quiz_title = ?, description = ?, passing_score = ?,
                    xp_reward = ?, bonus_xp_perfect = ?, time_limit = ?
                WHERE quiz_id = ?
            ");
            if ($stmt->execute([$course_id, $quiz_title, $description, $passing_score, $xp_reward, $bonus_xp_perfect, $time_limit, $quiz_id])) {
                set_success('Quiz updated successfully');
            } else {
                set_error('Failed to update quiz');
            }
        } else {
            // Add new quiz
            $stmt = $conn->prepare("
                INSERT INTO quizzes
                (course_id, quiz_title, description, passing_score, xp_reward, bonus_xp_perfect, time_limit)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$course_id, $quiz_title, $description, $passing_score, $xp_reward, $bonus_xp_perfect, $time_limit])) {
                set_success('Quiz added successfully');
            } else {
                set_error('Failed to add quiz');
            }
        }
        redirect('/admin/quizzes.php' . ($course_id > 0 ? '?course_id=' . $course_id : ''));
    }
}

// Get quiz for editing
$edit_quiz = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM quizzes WHERE quiz_id = ?");
    $stmt->execute([$edit_id]);
    $edit_quiz = $stmt->fetch();
    if ($edit_quiz) {
        $course_id = $edit_quiz['course_id'];
    }
}

// Get course information if course_id is set
$course = null;
if ($course_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
}

// Get all courses for dropdown
$courses = $conn->query("SELECT * FROM courses ORDER BY course_title ASC")->fetchAll();

// Get quizzes (all or filtered by course)
if ($course_id > 0) {
    $stmt = $conn->prepare("
        SELECT q.*, c.course_title,
               COUNT(DISTINCT qq.question_id) as question_count,
               COUNT(DISTINCT uqa.attempt_id) as attempt_count
        FROM quizzes q
        LEFT JOIN courses c ON q.course_id = c.course_id
        LEFT JOIN quiz_questions qq ON q.quiz_id = qq.quiz_id
        LEFT JOIN user_quiz_attempts uqa ON q.quiz_id = uqa.quiz_id
        WHERE q.course_id = ?
        GROUP BY q.quiz_id
        ORDER BY q.created_at DESC
    ");
    $stmt->execute([$course_id]);
} else {
    $stmt = $conn->query("
        SELECT q.*, c.course_title,
               COUNT(DISTINCT qq.question_id) as question_count,
               COUNT(DISTINCT uqa.attempt_id) as attempt_count
        FROM quizzes q
        LEFT JOIN courses c ON q.course_id = c.course_id
        LEFT JOIN quiz_questions qq ON q.quiz_id = qq.quiz_id
        LEFT JOIN user_quiz_attempts uqa ON q.quiz_id = uqa.quiz_id
        GROUP BY q.quiz_id
        ORDER BY q.created_at DESC
    ");
}
$quizzes = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-question-circle me-2"></i>Manage Quizzes</h1>
                    <?php if ($course): ?>
                        <p class="text-muted mb-0">
                            <a href="/admin/courses.php" class="text-decoration-none">Courses</a> /
                            <?php echo htmlspecialchars($course['course_title']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quizModal" onclick="clearForm()">
                    <i class="fas fa-plus me-2"></i>Add New Quiz
                </button>
            </div>

            <?php display_messages(); ?>

            <!-- Course Filter -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label class="form-label">Filter by Course:</label>
                            <select class="form-select" onchange="if(this.value) window.location.href='/admin/quizzes.php?course_id=' + this.value; else window.location.href='/admin/quizzes.php';">
                                <option value="">All Courses</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['course_id']; ?>" <?php echo $c['course_id'] == $course_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['course_title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="text-muted">
                                <strong><?php echo count($quizzes); ?></strong> quiz<?php echo count($quizzes) != 1 ? 'zes' : ''; ?> found
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quizzes Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php if (empty($quizzes)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                            <h5>No quizzes found</h5>
                            <p class="text-muted">Add your first quiz!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Quiz Title</th>
                                        <th>Course</th>
                                        <th class="text-center">Questions</th>
                                        <th class="text-center">Pass Score</th>
                                        <th class="text-center">XP Reward</th>
                                        <th class="text-center">Time Limit</th>
                                        <th class="text-center">Attempts</th>
                                        <th width="180" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quizzes as $quiz): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($quiz['quiz_title']); ?></strong>
                                                <?php if ($quiz['description']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($quiz['description'], 0, 60)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($quiz['course_title']); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?php echo $quiz['question_count']; ?> Q's</span>
                                            </td>
                                            <td class="text-center"><?php echo $quiz['passing_score']; ?>%</td>
                                            <td class="text-center">
                                                <span class="text-warning">
                                                    <i class="fas fa-star"></i> <?php echo $quiz['xp_reward']; ?>
                                                    <?php if ($quiz['bonus_xp_perfect'] > 0): ?>
                                                        (+<?php echo $quiz['bonus_xp_perfect']; ?>)
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php echo $quiz['time_limit'] ? $quiz['time_limit'] . ' min' : 'No limit'; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary"><?php echo $quiz['attempt_count']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <a href="/admin/quiz-questions.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-info" title="Manage Questions">
                                                    <i class="fas fa-list"></i>
                                                </a>
                                                <button class="btn btn-sm btn-warning" onclick='editQuiz(<?php echo json_encode($quiz); ?>)' title="Edit Quiz">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="?<?php echo $course_id > 0 ? 'course_id=' . $course_id . '&' : ''; ?>delete=<?php echo $quiz['quiz_id']; ?>"
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Are you sure? This will delete all questions and answers!')"
                                                   title="Delete Quiz">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add/Edit Quiz Modal -->
<div class="modal fade" id="quizModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="quiz_id" id="quiz_id" value="0">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="quiz_title" class="form-label">Quiz Title *</label>
                            <input type="text" class="form-control" id="quiz_title" name="quiz_title" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="course_id" class="form-label">Course *</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['course_id']; ?>" <?php echo $c['course_id'] == $course_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['course_title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="passing_score" class="form-label">Passing Score (%)</label>
                            <input type="number" class="form-control" id="passing_score" name="passing_score" min="0" max="100" value="70" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="xp_reward" class="form-label">XP Reward</label>
                            <input type="number" class="form-control" id="xp_reward" name="xp_reward" value="30">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="bonus_xp_perfect" class="form-label">Bonus XP (Perfect Score)</label>
                            <input type="number" class="form-control" id="bonus_xp_perfect" name="bonus_xp_perfect" value="20">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                            <input type="number" class="form-control" id="time_limit" name="time_limit" placeholder="Leave empty for no limit">
                            <small class="text-muted">Leave empty for unlimited time</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Quiz</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function clearForm() {
    document.getElementById('quiz_id').value = '0';
    document.getElementById('quiz_title').value = '';
    document.getElementById('description').value = '';
    document.getElementById('passing_score').value = '70';
    document.getElementById('xp_reward').value = '30';
    document.getElementById('bonus_xp_perfect').value = '20';
    document.getElementById('time_limit').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Quiz';
}

function editQuiz(quiz) {
    document.getElementById('quiz_id').value = quiz.quiz_id;
    document.getElementById('quiz_title').value = quiz.quiz_title;
    document.getElementById('course_id').value = quiz.course_id;
    document.getElementById('description').value = quiz.description || '';
    document.getElementById('passing_score').value = quiz.passing_score;
    document.getElementById('xp_reward').value = quiz.xp_reward;
    document.getElementById('bonus_xp_perfect').value = quiz.bonus_xp_perfect;
    document.getElementById('time_limit').value = quiz.time_limit || '';
    document.getElementById('modalTitle').textContent = 'Edit Quiz';

    const modal = new bootstrap.Modal(document.getElementById('quizModal'));
    modal.show();
}

<?php if ($edit_quiz): ?>
editQuiz(<?php echo json_encode($edit_quiz); ?>);
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
