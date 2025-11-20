<?php 
require_once '../includes/header.php';
require_login();

$user_id = get_user_id();
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($quiz_id == 0) {
    redirect('/dashboard/my-courses.php');
}

// Get quiz details
$stmt = $conn->prepare("
    SELECT q.*, c.course_title, c.course_id
    FROM quizzes q
    JOIN courses c ON q.course_id = c.course_id
    WHERE q.quiz_id = ?
");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    set_error('Quiz not found');
    redirect('/dashboard/my-courses.php');
}

// Check if user is enrolled in the course
$stmt = $conn->prepare("SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user_id, $quiz['course_id']]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    set_error('You are not enrolled in this course');
    redirect('/dashboard/my-courses.php');
}

$page_title = $quiz['quiz_title'] . " - " . SITE_NAME;

// Get all questions with answers
$stmt = $conn->prepare("
    SELECT q.*, 
           (SELECT COUNT(*) FROM quiz_answers WHERE question_id = q.question_id) as answer_count
    FROM quiz_questions q
    WHERE q.quiz_id = ?
    ORDER BY q.question_order ASC
");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

// Get answers for each question
foreach ($questions as &$question) {
    $stmt = $conn->prepare("
        SELECT * FROM quiz_answers 
        WHERE question_id = ? 
        ORDER BY answer_order ASC
    ");
    $stmt->execute([$question['question_id']]);
    $question['answers'] = $stmt->fetchAll();
}
unset($question);

// Get user's previous attempts
$stmt = $conn->prepare("
    SELECT * FROM user_quiz_attempts 
    WHERE user_id = ? AND quiz_id = ? 
    ORDER BY attempt_date DESC
");
$stmt->execute([$user_id, $quiz_id]);
$attempts = $stmt->fetchAll();

$best_score = 0;
if (!empty($attempts)) {
    $best_score = max(array_column($attempts, 'score'));
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php 
        $user = get_user_data($user_id);
        include '../includes/sidebar.php'; 
        ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard/">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/dashboard/course-details.php?id=<?php echo $quiz['course_id']; ?>">
                        <?php echo htmlspecialchars($quiz['course_title']); ?>
                    </a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($quiz['quiz_title']); ?></li>
                </ol>
            </nav>
            
            <?php display_messages(); ?>
            
            <div class="row">
                <!-- Quiz Content -->
                <div class="col-lg-8">
                    <!-- Quiz Header -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start mb-3">
                                <div class="me-3">
                                    <div class="rounded-circle bg-warning-light text-warning d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-question fa-2x"></i>
                                    </div>
                                </div>
                                <div>
                                    <h2 class="mb-2"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h2>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($quiz['description']); ?></p>
                                </div>
                            </div>
                            
                            <div class="row g-3 text-center">
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <i class="fas fa-list text-primary mb-2"></i>
                                        <div class="fw-bold"><?php echo count($questions); ?></div>
                                        <small class="text-muted">Questions</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <i class="fas fa-chart-line text-success mb-2"></i>
                                        <div class="fw-bold"><?php echo $quiz['passing_score']; ?>%</div>
                                        <small class="text-muted">Pass Score</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <i class="fas fa-star text-warning mb-2"></i>
                                        <div class="fw-bold"><?php echo $quiz['xp_reward']; ?> XP</div>
                                        <small class="text-muted">Reward</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <i class="fas fa-clock text-info mb-2"></i>
                                        <div class="fw-bold">
                                            <?php echo $quiz['time_limit'] ? $quiz['time_limit'] . ' min' : 'No Limit'; ?>
                                        </div>
                                        <small class="text-muted">Time Limit</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quiz Form -->
                    <form action="/actions/submit-quiz.php" method="POST" id="quizForm">
                        <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                        <input type="hidden" name="start_time" value="<?php echo time(); ?>">
                        
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start mb-3">
                                        <span class="badge bg-purple me-3 fs-6">Q<?php echo $index + 1; ?></span>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-3"><?php echo htmlspecialchars($question['question_text']); ?></h5>
                                            
                                            <?php if ($question['question_type'] == 'multiple_choice'): ?>
                                                <!-- Multiple Choice -->
                                                <div class="list-group">
                                                    <?php foreach ($question['answers'] as $answer): ?>
                                                        <label class="list-group-item list-group-item-action">
                                                            <div class="d-flex align-items-center">
                                                                <input type="radio" 
                                                                       class="form-check-input me-3" 
                                                                       name="question_<?php echo $question['question_id']; ?>" 
                                                                       value="<?php echo $answer['answer_id']; ?>"
                                                                       required>
                                                                <span><?php echo htmlspecialchars($answer['answer_text']); ?></span>
                                                            </div>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                                
                                            <?php elseif ($question['question_type'] == 'true_false'): ?>
                                                <!-- True/False -->
                                                <div class="list-group">
                                                    <?php foreach ($question['answers'] as $answer): ?>
                                                        <label class="list-group-item list-group-item-action">
                                                            <div class="d-flex align-items-center">
                                                                <input type="radio" 
                                                                       class="form-check-input me-3" 
                                                                       name="question_<?php echo $question['question_id']; ?>" 
                                                                       value="<?php echo $answer['answer_id']; ?>"
                                                                       required>
                                                                <span class="fw-bold"><?php echo htmlspecialchars($answer['answer_text']); ?></span>
                                                            </div>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Submit Button -->
                        <div class="card border-0 shadow-sm sticky-bottom">
                            <div class="card-body p-4 text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5" id="submitBtn">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Quiz
                                </button>
                                <p class="text-muted mt-2 mb-0">
                                    <small>Make sure all questions are answered before submitting</small>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Sidebar Info -->
                <div class="col-lg-4">
                    <!-- Timer (if time limit exists) -->
                    <?php if ($quiz['time_limit']): ?>
                        <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                            <div class="card-body text-center p-4">
                                <h5 class="mb-3">Time Remaining</h5>
                                <div id="timer" class="display-4 fw-bold text-danger mb-3">
                                    <?php echo $quiz['time_limit']; ?>:00
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div id="timerProgress" class="progress-bar bg-danger" role="progressbar" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Previous Attempts -->
                    <?php if (!empty($attempts)): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">Your Previous Attempts</h5>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($attempts, 0, 5) as $attempt): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-<?php echo $attempt['passed'] ? 'success' : 'danger'; ?>">
                                                <?php echo round($attempt['score']); ?>%
                                            </strong>
                                            <span class="badge bg-<?php echo $attempt['passed'] ? 'success' : 'danger'; ?>">
                                                <?php echo $attempt['passed'] ? 'Passed' : 'Failed'; ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i><?php echo time_ago($attempt['attempt_date']); ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo $attempt['correct_answers']; ?>/<?php echo $attempt['total_questions']; ?> correct
                                        </small>
                                        <a href="/dashboard/quiz-result.php?attempt_id=<?php echo $attempt['attempt_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary mt-2 w-100">
                                            View Details
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($attempts) > 5): ?>
                                <div class="card-footer bg-white text-center">
                                    <small class="text-muted">Showing 5 most recent attempts</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                                <h5>First Attempt</h5>
                                <p class="text-muted mb-0">This is your first time taking this quiz. Good luck!</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php if ($quiz['time_limit']): ?>
<script>
// Timer functionality
let timeLimit = <?php echo $quiz['time_limit'] * 60; ?>; // Convert to seconds
let timeRemaining = timeLimit;
let timerInterval;

function updateTimer() {
    let minutes = Math.floor(timeRemaining / 60);
    let seconds = timeRemaining % 60;
    
    document.getElementById('timer').textContent = 
        minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
    
    let progressPercent = (timeRemaining / timeLimit) * 100;
    document.getElementById('timerProgress').style.width = progressPercent + '%';
    
    if (timeRemaining <= 60) {
        document.getElementById('timer').classList.add('text-danger');
        document.getElementById('timerProgress').classList.remove('bg-warning');
        document.getElementById('timerProgress').classList.add('bg-danger');
    } else if (timeRemaining <= 300) {
        document.getElementById('timer').classList.add('text-warning');
        document.getElementById('timerProgress').classList.add('bg-warning');
    }
    
    if (timeRemaining <= 0) {
        clearInterval(timerInterval);
        alert('Time is up! The quiz will be automatically submitted.');
        document.getElementById('quizForm').submit();
    }
    
    timeRemaining--;
}

// Start timer
timerInterval = setInterval(updateTimer, 1000);
updateTimer();

// Warn before leaving
window.addEventListener('beforeunload', function(e) {
    e.preventDefault();
    e.returnValue = '';
});
</script>
<?php endif; ?>

<script>
// Form validation
document.getElementById('quizForm').addEventListener('submit', function(e) {
    let allAnswered = true;
    let questions = document.querySelectorAll('[name^="question_"]');
    let questionNames = new Set();
    
    questions.forEach(q => questionNames.add(q.name));
    
    questionNames.forEach(name => {
        let checked = document.querySelector(`[name="${name}"]:checked`);
        if (!checked) {
            allAnswered = false;
        }
    });
    
    if (!allAnswered) {
        e.preventDefault();
        alert('Please answer all questions before submitting!');
        return false;
    }
    
    if (!confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.')) {
        e.preventDefault();
        return false;
    }
    
    // Disable submit button to prevent double submission
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
});
</script>

<?php require_once '../includes/footer.php'; ?>