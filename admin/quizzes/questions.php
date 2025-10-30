<?php
$page_title = "Manage Quiz Questions - " . SITE_NAME;
require_once '../../includes/header.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch quiz data
$stmt = $conn->prepare("
    SELECT q.*, c.course_title, s.subject_name
    FROM quizzes q
    INNER JOIN courses c ON q.course_id = c.course_id
    INNER JOIN subjects s ON c.subject_id = s.subject_id
    WHERE q.quiz_id = ?
");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    redirect('/admin/quizzes/');
}

// Handle question deletion
if (isset($_GET['delete_question'])) {
    $question_id = (int)$_GET['delete_question'];
    try {
        $delete_stmt = $conn->prepare("DELETE FROM quiz_questions WHERE question_id = ? AND quiz_id = ?");
        $delete_stmt->execute([$question_id, $quiz_id]);
        redirect('/admin/quizzes/questions.php?id=' . $quiz_id . '&success=question_deleted');
    } catch (PDOException $e) {
        $error = 'Failed to delete question: ' . $e->getMessage();
    }
}

// Fetch all questions for this quiz with their answers
$questions_stmt = $conn->prepare("
    SELECT qq.*,
           GROUP_CONCAT(CONCAT(qa.answer_id, ':::', qa.answer_text, ':::', qa.is_correct) ORDER BY qa.answer_order SEPARATOR '|||') as answers
    FROM quiz_questions qq
    LEFT JOIN quiz_answers qa ON qq.question_id = qa.question_id
    WHERE qq.quiz_id = ?
    GROUP BY qq.question_id
    ORDER BY qq.question_order ASC
");
$questions_stmt->execute([$quiz_id]);
$questions = $questions_stmt->fetchAll();

$page_title = "Manage Quiz Questions";
include '../../includes/header.php';
include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-list me-2"></i>Quiz Questions</h2>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($quiz['quiz_title']); ?></p>
            </div>
            <div>
                <a href="/admin/quizzes/add-question.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-purple me-2">
                    <i class="fas fa-plus me-2"></i>Add Question
                </a>
                <a href="/admin/quizzes/" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Quizzes
                </a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                    switch($_GET['success']) {
                        case 'created':
                            echo 'Quiz created! Now add questions to complete your quiz.';
                            break;
                        case 'question_added':
                            echo 'Question added successfully!';
                            break;
                        case 'question_deleted':
                            echo 'Question deleted successfully!';
                            break;
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-9">
                <?php if (empty($questions)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No questions added yet. Click "Add Question" to start building your quiz.
                    </div>
                <?php else: ?>
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-2">
                                            <span class="badge bg-secondary me-2">Q<?php echo $question['question_order']; ?></span>
                                            <?php echo htmlspecialchars($question['question_text']); ?>
                                        </h5>
                                        <div class="small text-muted">
                                            <span class="me-3">
                                                <i class="fas fa-tag me-1"></i>
                                                <?php echo ucwords(str_replace('_', ' ', $question['question_type'])); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-star me-1"></i>
                                                <?php echo $question['points']; ?> point<?php echo $question['points'] > 1 ? 's' : ''; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/quizzes/questions.php?id=<?php echo $quiz_id; ?>&delete_question=<?php echo $question['question_id']; ?>"
                                           class="btn btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this question?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>

                                <?php if (!empty($question['answers'])): ?>
                                    <div class="answers-list">
                                        <?php
                                        $answers = explode('|||', $question['answers']);
                                        foreach ($answers as $answer_data) {
                                            $parts = explode(':::', $answer_data);
                                            if (count($parts) >= 3) {
                                                $answer_id = $parts[0];
                                                $answer_text = $parts[1];
                                                $is_correct = $parts[2];
                                                ?>
                                                <div class="form-check mb-2">
                                                    <?php if ($question['question_type'] === 'true_false'): ?>
                                                        <input class="form-check-input" type="radio" disabled
                                                               <?php echo $is_correct ? 'checked' : ''; ?>>
                                                    <?php else: ?>
                                                        <input class="form-check-input" type="checkbox" disabled
                                                               <?php echo $is_correct ? 'checked' : ''; ?>>
                                                    <?php endif; ?>
                                                    <label class="form-check-label <?php echo $is_correct ? 'text-success fw-bold' : ''; ?>">
                                                        <?php echo htmlspecialchars($answer_text); ?>
                                                        <?php if ($is_correct): ?>
                                                            <i class="fas fa-check-circle text-success ms-2"></i>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        No answers provided for this question!
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="col-lg-3">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Quiz Info</h5>
                        <table class="table table-sm mb-0">
                            <tr>
                                <th>Course:</th>
                                <td><?php echo htmlspecialchars($quiz['course_title']); ?></td>
                            </tr>
                            <tr>
                                <th>Questions:</th>
                                <td><strong><?php echo count($questions); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Passing Score:</th>
                                <td><?php echo $quiz['passing_score']; ?>%</td>
                            </tr>
                            <tr>
                                <th>XP Reward:</th>
                                <td><?php echo $quiz['xp_reward']; ?> XP</td>
                            </tr>
                            <?php if ($quiz['time_limit']): ?>
                            <tr>
                                <th>Time Limit:</th>
                                <td><?php echo $quiz['time_limit']; ?> min</td>
                            </tr>
                            <?php endif; ?>
                        </table>
                        <a href="/admin/quizzes/edit.php?id=<?php echo $quiz_id; ?>"
                           class="btn btn-sm btn-outline-primary w-100 mt-2">
                            <i class="fas fa-edit me-1"></i>Edit Quiz Settings
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-lightbulb me-2"></i>Tips</h5>
                        <ul class="small mb-0">
                            <li>Questions are displayed in order</li>
                            <li>Mark correct answers with checkmarks</li>
                            <li>Multiple choice can have multiple correct answers</li>
                            <li>True/False has only one correct answer</li>
                            <li>Each question can have custom points</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
