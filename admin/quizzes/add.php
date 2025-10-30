<?php
$page_title = "Add New Quiz - " . SITE_NAME;
require_once '../../includes/header.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$errors = [];

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

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO quizzes (course_id, quiz_title, description, passing_score, xp_reward, bonus_xp_perfect, time_limit)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $course_id,
                $quiz_title,
                $description,
                $passing_score,
                $xp_reward,
                $bonus_xp_perfect,
                $time_limit
            ]);

            $quiz_id = $conn->lastInsertId();
            redirect('/admin/quizzes/questions.php?id=' . $quiz_id . '&success=created');
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

$page_title = "Add New Quiz";
include '../../includes/header.php';
include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus-circle me-2"></i>Add New Quiz</h2>
            <a href="/admin/quizzes/" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Quizzes
            </a>
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
                                            <?php echo (isset($_POST['course_id']) && $_POST['course_id'] == $course['course_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['subject_name'] . ' - ' . $course['course_title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Quiz Title -->
                            <div class="mb-3">
                                <label for="quiz_title" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quiz_title" name="quiz_title"
                                       value="<?php echo isset($_POST['quiz_title']) ? htmlspecialchars($_POST['quiz_title']) : ''; ?>"
                                       required maxlength="200">
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                <small class="text-muted">Brief description of what this quiz covers</small>
                            </div>

                            <div class="row">
                                <!-- Passing Score -->
                                <div class="col-md-6 mb-3">
                                    <label for="passing_score" class="form-label">Passing Score (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="passing_score" name="passing_score"
                                           value="<?php echo isset($_POST['passing_score']) ? $_POST['passing_score'] : '70'; ?>"
                                           min="0" max="100" required>
                                    <small class="text-muted">Minimum score to pass (default: 70%)</small>
                                </div>

                                <!-- Time Limit -->
                                <div class="col-md-6 mb-3">
                                    <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                    <input type="number" class="form-control" id="time_limit" name="time_limit"
                                           value="<?php echo isset($_POST['time_limit']) ? $_POST['time_limit'] : ''; ?>"
                                           min="1" placeholder="No limit">
                                    <small class="text-muted">Leave empty for no time limit</small>
                                </div>
                            </div>

                            <div class="row">
                                <!-- XP Reward -->
                                <div class="col-md-6 mb-3">
                                    <label for="xp_reward" class="form-label">XP Reward <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="xp_reward" name="xp_reward"
                                           value="<?php echo isset($_POST['xp_reward']) ? $_POST['xp_reward'] : '30'; ?>"
                                           min="1" required>
                                    <small class="text-muted">XP for passing (default: 30)</small>
                                </div>

                                <!-- Bonus XP for Perfect Score -->
                                <div class="col-md-6 mb-3">
                                    <label for="bonus_xp_perfect" class="form-label">Bonus XP (100% Score)</label>
                                    <input type="number" class="form-control" id="bonus_xp_perfect" name="bonus_xp_perfect"
                                           value="<?php echo isset($_POST['bonus_xp_perfect']) ? $_POST['bonus_xp_perfect'] : '20'; ?>"
                                           min="0">
                                    <small class="text-muted">Extra XP for perfect score (default: 20)</small>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-purple">
                                    <i class="fas fa-save me-2"></i>Create Quiz & Add Questions
                                </button>
                                <a href="/admin/quizzes/" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Quiz Setup Tips</h5>
                        <ul class="small">
                            <li>After creating the quiz, you'll be taken to add questions</li>
                            <li>Set passing score based on quiz difficulty</li>
                            <li>Time limits add challenge and prevent cheating</li>
                            <li>Bonus XP encourages students to aim for perfect scores</li>
                            <li>Higher XP rewards for more difficult quizzes</li>
                            <li>A good quiz has 5-15 questions</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-lightbulb me-2"></i>XP Guidelines</h5>
                        <table class="table table-sm mb-0">
                            <tr>
                                <td>Short Quiz (5 questions)</td>
                                <td class="text-end"><strong>20-30 XP</strong></td>
                            </tr>
                            <tr>
                                <td>Medium Quiz (10 questions)</td>
                                <td class="text-end"><strong>30-50 XP</strong></td>
                            </tr>
                            <tr>
                                <td>Long Quiz (15+ questions)</td>
                                <td class="text-end"><strong>50-100 XP</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
