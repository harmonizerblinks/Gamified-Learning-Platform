<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Edit Quiz - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

// Fetch quiz data
$stmt = $conn->prepare("
    SELECT q.*, c.course_title, s.subject_name,
           COUNT(DISTINCT qq.question_id) as question_count
    FROM quizzes q
    INNER JOIN courses c ON q.course_id = c.course_id
    INNER JOIN subjects s ON c.subject_id = s.subject_id
    LEFT JOIN quiz_questions qq ON q.quiz_id = qq.quiz_id
    WHERE q.quiz_id = ?
    GROUP BY q.quiz_id
");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    redirect('/admin/quizzes/');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
    $quiz_title = clean_input($_POST['quiz_title']);
    $description = isset($_POST['description']) ? clean_input($_POST['description']) : '';
    $passing_score = isset($_POST['passing_score']) ? (int)$_POST['passing_score'] : 70;
    $xp_reward = isset($_POST['xp_reward']) ? (int)$_POST['xp_reward'] : 30;
    $bonus_xp_perfect = isset($_POST['bonus_xp_perfect']) ? (int)$_POST['bonus_xp_perfect'] : 20;
    $time_limit = isset($_POST['time_limit']) && !empty($_POST['time_limit']) ? (int)$_POST['time_limit'] : null;

    // Validation
    if (empty($quiz_title)) {
        $errors[] = 'Quiz title is required.';
    }
    if ($course_id <= 0) {
        $errors[] = 'Please select a valid course.';
    }
    if ($passing_score < 0 || $passing_score > 100) {
        $errors[] = 'Passing score must be between 0 and 100.';
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                UPDATE quizzes
                SET course_id = ?, quiz_title = ?, description = ?, passing_score = ?,
                    xp_reward = ?, bonus_xp_perfect = ?, time_limit = ?
                WHERE quiz_id = ?
            ");

            $stmt->execute([
                $course_id,
                $quiz_title,
                $description,
                $passing_score,
                $xp_reward,
                $bonus_xp_perfect,
                $time_limit,
                $quiz_id
            ]);

            redirect('/admin/quizzes/?success=updated');
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch all courses for dropdown
$courses_stmt = $conn->query("
    SELECT c.course_id, c.course_title, s.subject_name
    FROM courses c
    INNER JOIN subjects s ON c.subject_id = s.subject_id
    ORDER BY s.subject_name ASC, c.course_title ASC
");
$courses = $courses_stmt->fetchAll();

$page_title = "Edit Quiz";
include '../../includes/header.php';
include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit Quiz</h2>
            <div>
                <a href="/admin/quizzes/questions.php?id=<?php echo $quiz_id; ?>" class="btn btn-purple me-2">
                    <i class="fas fa-list me-2"></i>Manage Questions
                </a>
                <a href="/admin/quizzes/" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Quizzes
                </a>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST">
                            <!-- Course Selection -->
                            <div class="mb-3">
                                <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                                <select name="course_id" id="course_id" class="form-select" required>
                                    <option value="">Select a course...</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['course_id']; ?>"
                                            <?php echo $quiz['course_id'] == $course['course_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['subject_name'] . ' - ' . $course['course_title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Quiz Title -->
                            <div class="mb-3">
                                <label for="quiz_title" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quiz_title" name="quiz_title"
                                       value="<?php echo htmlspecialchars($quiz['quiz_title']); ?>"
                                       required maxlength="200">
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($quiz['description']); ?></textarea>
                                <small class="text-muted">Brief description of what this quiz covers</small>
                            </div>

                            <div class="row">
                                <!-- Passing Score -->
                                <div class="col-md-6 mb-3">
                                    <label for="passing_score" class="form-label">Passing Score (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="passing_score" name="passing_score"
                                           value="<?php echo $quiz['passing_score']; ?>"
                                           min="0" max="100" required>
                                    <small class="text-muted">Minimum score to pass</small>
                                </div>

                                <!-- Time Limit -->
                                <div class="col-md-6 mb-3">
                                    <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                    <input type="number" class="form-control" id="time_limit" name="time_limit"
                                           value="<?php echo $quiz['time_limit']; ?>"
                                           min="1" placeholder="No limit">
                                    <small class="text-muted">Leave empty for no time limit</small>
                                </div>
                            </div>

                            <div class="row">
                                <!-- XP Reward -->
                                <div class="col-md-6 mb-3">
                                    <label for="xp_reward" class="form-label">XP Reward <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="xp_reward" name="xp_reward"
                                           value="<?php echo $quiz['xp_reward']; ?>"
                                           min="1" required>
                                    <small class="text-muted">XP for passing</small>
                                </div>

                                <!-- Bonus XP for Perfect Score -->
                                <div class="col-md-6 mb-3">
                                    <label for="bonus_xp_perfect" class="form-label">Bonus XP (100% Score)</label>
                                    <input type="number" class="form-control" id="bonus_xp_perfect" name="bonus_xp_perfect"
                                           value="<?php echo $quiz['bonus_xp_perfect']; ?>"
                                           min="0">
                                    <small class="text-muted">Extra XP for perfect score</small>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-purple">
                                    <i class="fas fa-save me-2"></i>Update Quiz
                                </button>
                                <a href="/admin/quizzes/" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Quiz Info</h5>
                        <table class="table table-sm mb-0">
                            <tr>
                                <th>Quiz ID:</th>
                                <td><?php echo $quiz['quiz_id']; ?></td>
                            </tr>
                            <tr>
                                <th>Questions:</th>
                                <td>
                                    <strong><?php echo $quiz['question_count']; ?></strong>
                                    <a href="/admin/quizzes/questions.php?id=<?php echo $quiz_id; ?>"
                                       class="btn btn-sm btn-outline-primary ms-2">
                                        <i class="fas fa-edit"></i> Manage
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td><?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>Course:</th>
                                <td><?php echo htmlspecialchars($quiz['course_title']); ?></td>
                            </tr>
                            <tr>
                                <th>Subject:</th>
                                <td><?php echo htmlspecialchars($quiz['subject_name']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Warning</h5>
                        <p class="small mb-0">
                            Changing passing score or XP rewards will affect future quiz attempts.
                            Previous attempts will retain their original rewards.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
