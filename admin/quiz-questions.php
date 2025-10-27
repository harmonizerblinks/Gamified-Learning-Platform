<?php
require_once '../includes/header.php';
$page_title = "Manage Quiz Questions - " . SITE_NAME;
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if ($quiz_id == 0) {
    set_error('Invalid quiz ID');
    redirect('/admin/quizzes.php');
}

// Get quiz details
$stmt = $conn->prepare("SELECT q.*, c.course_title FROM quizzes q JOIN courses c ON q.course_id = c.course_id WHERE q.quiz_id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    set_error('Quiz not found');
    redirect('/admin/quizzes.php');
}

// Handle Delete Question
if (isset($_GET['delete_question'])) {
    $question_id = (int)$_GET['delete_question'];
    $stmt = $conn->prepare("DELETE FROM quiz_questions WHERE question_id = ?");
    if ($stmt->execute([$question_id])) {
        set_success('Question deleted successfully');
    } else {
        set_error('Failed to delete question');
    }
    redirect('/admin/quiz-questions.php?quiz_id=' . $quiz_id);
}

// Handle Add/Edit Question with Answers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_question'])) {
    $question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
    $question_text = clean_input($_POST['question_text']);
    $question_type = clean_input($_POST['question_type']);
    $points = (int)$_POST['points'];
    $question_order = (int)$_POST['question_order'];

    if (empty($question_text)) {
        set_error('Question text is required');
    } else {
        try {
            $conn->beginTransaction();

            if ($question_id > 0) {
                // Update existing question
                $stmt = $conn->prepare("UPDATE quiz_questions SET question_text = ?, question_type = ?, points = ?, question_order = ? WHERE question_id = ?");
                $stmt->execute([$question_text, $question_type, $points, $question_order, $question_id]);

                // Delete existing answers
                $stmt = $conn->prepare("DELETE FROM quiz_answers WHERE question_id = ?");
                $stmt->execute([$question_id]);
            } else {
                // Add new question
                $stmt = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question_text, question_type, points, question_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$quiz_id, $question_text, $question_type, $points, $question_order]);
                $question_id = $conn->lastInsertId();
            }

            // Add answers
            if (isset($_POST['answers']) && is_array($_POST['answers'])) {
                foreach ($_POST['answers'] as $order => $answer_data) {
                    $answer_text = clean_input($answer_data['text']);
                    $is_correct = isset($answer_data['is_correct']) ? 1 : 0;

                    if (!empty($answer_text)) {
                        $stmt = $conn->prepare("INSERT INTO quiz_answers (question_id, answer_text, is_correct, answer_order) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$question_id, $answer_text, $is_correct, $order]);
                    }
                }
            }

            $conn->commit();
            set_success('Question saved successfully');
        } catch (Exception $e) {
            $conn->rollBack();
            set_error('Failed to save question: ' . $e->getMessage());
        }

        redirect('/admin/quiz-questions.php?quiz_id=' . $quiz_id);
    }
}

// Get question for editing
$edit_question = null;
$edit_answers = [];
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE question_id = ?");
    $stmt->execute([$edit_id]);
    $edit_question = $stmt->fetch();

    if ($edit_question) {
        $stmt = $conn->prepare("SELECT * FROM quiz_answers WHERE question_id = ? ORDER BY answer_order ASC");
        $stmt->execute([$edit_id]);
        $edit_answers = $stmt->fetchAll();
    }
}

// Get all questions with answers
$stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_order ASC");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

// Get answers for each question
foreach ($questions as &$question) {
    $stmt = $conn->prepare("SELECT * FROM quiz_answers WHERE question_id = ? ORDER BY answer_order ASC");
    $stmt->execute([$question['question_id']]);
    $question['answers'] = $stmt->fetchAll();
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-list me-2"></i>Manage Questions</h1>
                    <p class="text-muted mb-0">
                        <a href="/admin/quizzes.php" class="text-decoration-none">Quizzes</a> /
                        <?php echo htmlspecialchars($quiz['quiz_title']); ?>
                    </p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#questionModal" onclick="clearForm()">
                    <i class="fas fa-plus me-2"></i>Add Question
                </button>
            </div>

            <?php display_messages(); ?>

            <!-- Quiz Info Card -->
            <div class="card border-0 shadow-sm mb-4 bg-light">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-2"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h5>
                            <p class="text-muted mb-0">
                                <strong>Course:</strong> <?php echo htmlspecialchars($quiz['course_title']); ?> |
                                <strong>Passing Score:</strong> <?php echo $quiz['passing_score']; ?>% |
                                <strong>XP:</strong> <?php echo $quiz['xp_reward']; ?> (+<?php echo $quiz['bonus_xp_perfect']; ?> bonus)
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="badge bg-info fs-6"><?php echo count($questions); ?> Question<?php echo count($questions) != 1 ? 's' : ''; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions List -->
            <?php if (empty($questions)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                        <h5>No questions yet</h5>
                        <p class="text-muted">Add your first question to this quiz!</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($questions as $index => $question): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-purple me-2">Q<?php echo $question['question_order']; ?></span>
                                        <span class="badge bg-secondary me-2"><?php echo ucfirst(str_replace('_', ' ', $question['question_type'])); ?></span>
                                        <span class="badge bg-warning"><?php echo $question['points']; ?> point<?php echo $question['points'] != 1 ? 's' : ''; ?></span>
                                    </div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($question['question_text']); ?></h5>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-warning" onclick='editQuestion(<?php echo htmlspecialchars(json_encode($question)); ?>, <?php echo htmlspecialchars(json_encode($question['answers'])); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?quiz_id=<?php echo $quiz_id; ?>&delete_question=<?php echo $question['question_id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Delete this question?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Answers -->
                            <div class="ms-4">
                                <?php foreach ($question['answers'] as $answer): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="<?php echo $question['question_type'] === 'multiple_choice' ? 'radio' : 'checkbox'; ?>" disabled <?php echo $answer['is_correct'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label <?php echo $answer['is_correct'] ? 'text-success fw-bold' : ''; ?>">
                                            <?php echo htmlspecialchars($answer['answer_text']); ?>
                                            <?php if ($answer['is_correct']): ?>
                                                <i class="fas fa-check-circle text-success ms-2"></i>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Add/Edit Question Modal -->
<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="question_id" id="question_id" value="0">
                    <input type="hidden" name="save_question" value="1">

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="question_text" class="form-label">Question Text *</label>
                            <textarea class="form-control" id="question_text" name="question_text" rows="2" required></textarea>
                        </div>

                        <div class="col-md-2">
                            <label for="question_type" class="form-label">Type *</label>
                            <select class="form-select" id="question_type" name="question_type" onchange="updateAnswerSection()" required>
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="true_false">True/False</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="points" class="form-label">Points</label>
                            <input type="number" class="form-control" id="points" name="points" min="1" value="1">
                            <input type="hidden" id="question_order" name="question_order" value="<?php echo count($questions) + 1; ?>">
                        </div>
                    </div>

                    <hr>

                    <div id="answersSection">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Answers</h6>
                            <button type="button" class="btn btn-sm btn-success" onclick="addAnswer()" id="addAnswerBtn">
                                <i class="fas fa-plus"></i> Add Answer
                            </button>
                        </div>

                        <div id="answersList"></div>

                        <small class="text-muted">Check the correct answer(s). For multiple choice, only one can be correct.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let answerCounter = 0;

function clearForm() {
    document.getElementById('question_id').value = '0';
    document.getElementById('question_text').value = '';
    document.getElementById('question_type').value = 'multiple_choice';
    document.getElementById('points').value = '1';
    document.getElementById('question_order').value = '<?php echo count($questions) + 1; ?>';
    document.getElementById('modalTitle').textContent = 'Add New Question';
    document.getElementById('answersList').innerHTML = '';
    answerCounter = 0;

    // Add 4 default answers for multiple choice
    for (let i = 0; i < 4; i++) {
        addAnswer();
    }
}

function addAnswer() {
    const type = document.getElementById('question_type').value;
    const inputType = type === 'multiple_choice' ? 'radio' : 'checkbox';
    const answerHtml = `
        <div class="card mb-2" id="answer_${answerCounter}">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-1 text-center">
                        <input type="${inputType}" name="answers[${answerCounter}][is_correct]" value="1" class="form-check-input" ${inputType === 'radio' ? 'name="correct_answer"' : ''}>
                    </div>
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="answers[${answerCounter}][text]" placeholder="Answer text" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeAnswer(${answerCounter})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.getElementById('answersList').insertAdjacentHTML('beforeend', answerHtml);
    answerCounter++;
}

function removeAnswer(id) {
    const element = document.getElementById('answer_' + id);
    if (element) element.remove();
}

function updateAnswerSection() {
    const type = document.getElementById('question_type').value;
    const addBtn = document.getElementById('addAnswerBtn');

    if (type === 'true_false') {
        // Clear and add only True/False
        document.getElementById('answersList').innerHTML = '';
        answerCounter = 0;

        addAnswerWithText('True');
        addAnswerWithText('False');

        addBtn.style.display = 'none';
    } else {
        addBtn.style.display = 'inline-block';
    }
}

function addAnswerWithText(text) {
    const inputType = 'radio';
    const answerHtml = `
        <div class="card mb-2" id="answer_${answerCounter}">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-1 text-center">
                        <input type="${inputType}" name="correct_answer" value="${answerCounter}" class="form-check-input">
                        <input type="hidden" name="answers[${answerCounter}][is_correct]" value="0" class="correct-flag-${answerCounter}">
                    </div>
                    <div class="col-md-11">
                        <input type="text" class="form-control" name="answers[${answerCounter}][text]" value="${text}" readonly>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.getElementById('answersList').insertAdjacentHTML('beforeend', answerHtml);
    answerCounter++;
}

function editQuestion(question, answers) {
    document.getElementById('question_id').value = question.question_id;
    document.getElementById('question_text').value = question.question_text;
    document.getElementById('question_type').value = question.question_type;
    document.getElementById('points').value = question.points;
    document.getElementById('question_order').value = question.question_order;
    document.getElementById('modalTitle').textContent = 'Edit Question';

    // Clear answers
    document.getElementById('answersList').innerHTML = '';
    answerCounter = 0;

    // Add existing answers
    answers.forEach((answer, index) => {
        const type = question.question_type;
        const inputType = type === 'multiple_choice' ? 'radio' : 'checkbox';
        const answerHtml = `
            <div class="card mb-2" id="answer_${answerCounter}">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">
                            <input type="${inputType}" name="answers[${answerCounter}][is_correct]" value="1" class="form-check-input" ${inputType === 'radio' ? 'name="correct_answer"' : ''} ${answer.is_correct ? 'checked' : ''}>
                        </div>
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="answers[${answerCounter}][text]" value="${answer.answer_text}" required>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeAnswer(${answerCounter})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('answersList').insertAdjacentHTML('beforeend', answerHtml);
        answerCounter++;
    });

    updateAnswerSection();

    const modal = new bootstrap.Modal(document.getElementById('questionModal'));
    modal.show();
}

<?php if ($edit_question): ?>
editQuestion(<?php echo json_encode($edit_question); ?>, <?php echo json_encode($edit_answers); ?>);
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
