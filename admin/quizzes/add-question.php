<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Add Question - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$errors = [];

// Fetch quiz data
$stmt = $conn->prepare("
    SELECT q.*, c.course_title,
           COUNT(DISTINCT qq.question_id) as question_count
    FROM quizzes q
    INNER JOIN courses c ON q.course_id = c.course_id
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
    $question_text = isset($_POST['question_text']) ? trim($_POST['question_text']) : '';
    $question_type = isset($_POST['question_type']) ? $_POST['question_type'] : 'multiple_choice';
    $points = isset($_POST['points']) ? (int)$_POST['points'] : 1;
    $question_order = isset($_POST['question_order']) ? (int)$_POST['question_order'] : ($quiz['question_count'] + 1);

    // Validation
    if (empty($question_text)) {
        $errors[] = 'Question text is required.';
    }
    if (!in_array($question_type, ['multiple_choice', 'true_false', 'fill_blank'])) {
        $errors[] = 'Invalid question type.';
    }

    // Validate answers
    $answers = isset($_POST['answers']) ? $_POST['answers'] : [];
    $correct_answers = isset($_POST['correct_answers']) ? $_POST['correct_answers'] : [];

    if (empty($answers) || count(array_filter($answers)) === 0) {
        $errors[] = 'At least one answer is required.';
    }

    if (empty($correct_answers)) {
        $errors[] = 'At least one correct answer must be selected.';
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            // Insert question
            $stmt = $conn->prepare("
                INSERT INTO quiz_questions (quiz_id, question_text, question_type, points, question_order)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$quiz_id, $question_text, $question_type, $points, $question_order]);
            $question_id = $conn->lastInsertId();

            // Insert answers
            $answer_stmt = $conn->prepare("
                INSERT INTO quiz_answers (question_id, answer_text, is_correct, answer_order)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($answers as $order => $answer_text) {
                $answer_text = trim($answer_text);
                if (!empty($answer_text)) {
                    $is_correct = in_array($order, $correct_answers) ? 1 : 0;
                    $answer_stmt->execute([$question_id, $answer_text, $is_correct, $order + 1]);
                }
            }

            $conn->commit();
            redirect('/admin/quizzes/questions.php?id=' . $quiz_id . '&success=question_added');
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

$page_title = "Add Question";
include '../../includes/header.php';
include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-plus-circle me-2"></i>Add Question</h2>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($quiz['quiz_title']); ?></p>
            </div>
            <a href="/admin/quizzes/questions.php?id=<?php echo $quiz_id; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Questions
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
                        <form method="POST" id="questionForm">
                            <!-- Question Text -->
                            <div class="mb-3">
                                <label for="question_text" class="form-label">Question Text <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="question_text" name="question_text" rows="4" required><?php echo isset($_POST['question_text']) ? htmlspecialchars($_POST['question_text']) : ''; ?></textarea>
                            </div>

                            <div class="row">
                                <!-- Question Type -->
                                <div class="col-md-6 mb-3">
                                    <label for="question_type" class="form-label">Question Type <span class="text-danger">*</span></label>
                                    <select name="question_type" id="question_type" class="form-select" required onchange="updateAnswerFields()">
                                        <option value="multiple_choice" <?php echo (isset($_POST['question_type']) && $_POST['question_type'] == 'multiple_choice') ? 'selected' : ''; ?>>Multiple Choice</option>
                                        <option value="true_false" <?php echo (isset($_POST['question_type']) && $_POST['question_type'] == 'true_false') ? 'selected' : ''; ?>>True/False</option>
                                        <option value="fill_blank" <?php echo (isset($_POST['question_type']) && $_POST['question_type'] == 'fill_blank') ? 'selected' : ''; ?>>Fill in the Blank</option>
                                    </select>
                                </div>

                                <!-- Points -->
                                <div class="col-md-3 mb-3">
                                    <label for="points" class="form-label">Points <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="points" name="points"
                                           value="<?php echo isset($_POST['points']) ? $_POST['points'] : '1'; ?>"
                                           min="1" required>
                                </div>

                                <!-- Question Order -->
                                <div class="col-md-3 mb-3">
                                    <label for="question_order" class="form-label">Order</label>
                                    <input type="number" class="form-control" id="question_order" name="question_order"
                                           value="<?php echo isset($_POST['question_order']) ? $_POST['question_order'] : ($quiz['question_count'] + 1); ?>"
                                           min="1">
                                </div>
                            </div>

                            <!-- Answers Section -->
                            <div class="mb-3">
                                <label class="form-label">Answers <span class="text-danger">*</span></label>
                                <small class="text-muted d-block mb-2">Check the box next to correct answer(s)</small>

                                <div id="answersContainer">
                                    <!-- Answer fields will be dynamically added here -->
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-purple mt-2" onclick="addAnswerField()" id="addAnswerBtn">
                                    <i class="fas fa-plus me-1"></i>Add Answer
                                </button>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-purple">
                                    <i class="fas fa-save me-2"></i>Add Question
                                </button>
                                <a href="/admin/quizzes/questions.php?id=<?php echo $quiz_id; ?>" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Question Types</h5>
                        <div class="mb-3">
                            <h6 class="small fw-bold">Multiple Choice</h6>
                            <p class="small text-muted mb-0">Students select one or more correct answers from the options</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="small fw-bold">True/False</h6>
                            <p class="small text-muted mb-0">Students choose between True and False options</p>
                        </div>
                        <div>
                            <h6 class="small fw-bold">Fill in the Blank</h6>
                            <p class="small text-muted mb-0">Students type the correct answer(s)</p>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-lightbulb me-2"></i>Tips</h5>
                        <ul class="small mb-0">
                            <li>Write clear, unambiguous questions</li>
                            <li>Provide at least 3-4 answer choices for multiple choice</li>
                            <li>Make sure correct answers are properly marked</li>
                            <li>Award more points for difficult questions</li>
                            <li>Review questions before publishing the quiz</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let answerCount = 0;

function updateAnswerFields() {
    const questionType = document.getElementById('question_type').value;
    const container = document.getElementById('answersContainer');
    container.innerHTML = '';
    answerCount = 0;

    if (questionType === 'true_false') {
        // Add True/False options
        addAnswerField('True');
        addAnswerField('False');
        document.getElementById('addAnswerBtn').style.display = 'none';
    } else {
        // Add default answer fields for other types
        document.getElementById('addAnswerBtn').style.display = 'block';
        for (let i = 0; i < 4; i++) {
            addAnswerField();
        }
    }
}

function addAnswerField(defaultText = '') {
    const container = document.getElementById('answersContainer');
    const questionType = document.getElementById('question_type').value;
    const inputType = questionType === 'true_false' ? 'radio' : 'checkbox';
    const index = answerCount++;

    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <div class="input-group-text">
            <input class="form-check-input mt-0" type="${inputType}" name="correct_answers[]" value="${index}" ${inputType === 'radio' ? 'name="correct_answers"' : ''}>
        </div>
        <input type="text" class="form-control" name="answers[]" placeholder="Answer ${index + 1}" value="${defaultText}" ${questionType === 'true_false' ? 'readonly' : ''}>
        ${questionType !== 'true_false' ? `<button type="button" class="btn btn-outline-danger" onclick="removeAnswerField(this)"><i class="fas fa-times"></i></button>` : ''}
    `;
    container.appendChild(div);
}

function removeAnswerField(button) {
    button.closest('.input-group').remove();
}

// Initialize answer fields on page load
document.addEventListener('DOMContentLoaded', function() {
    updateAnswerFields();
});
</script>

<style>
.btn-outline-purple {
    color: #8B5CF6;
    border-color: #8B5CF6;
}
.btn-outline-purple:hover {
    background-color: #8B5CF6;
    border-color: #8B5CF6;
    color: white;
}
</style>

<?php include '../../includes/footer.php'; ?>
