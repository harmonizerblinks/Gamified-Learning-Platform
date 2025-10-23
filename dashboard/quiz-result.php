<?php 
require_once '../includes/header.php';
require_login();

$user_id = get_user_id();
$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;

if ($attempt_id == 0) {
    redirect('/dashboard/my-courses.php');
}

// Get attempt details
$stmt = $conn->prepare("
    SELECT qa.*, q.quiz_title, q.description, q.passing_score, q.course_id, q.quiz_id,
           c.course_title
    FROM user_quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.quiz_id
    JOIN courses c ON q.course_id = c.course_id
    WHERE qa.attempt_id = ? AND qa.user_id = ?
");
$stmt->execute([$attempt_id, $user_id]);
$attempt = $stmt->fetch();

if (!$attempt) {
    set_error('Quiz attempt not found');
    redirect('/dashboard/my-courses.php');
}

$page_title = "Quiz Results - " . SITE_NAME;

// Get all questions with user's answers
$stmt = $conn->prepare("
    SELECT qq.*, uqa.answer_id as user_answer_id, uqa.is_correct as user_is_correct
    FROM quiz_questions qq
    LEFT JOIN user_quiz_answers uqa ON qq.question_id = uqa.question_id AND uqa.attempt_id = ?
    WHERE qq.quiz_id = ?
    ORDER BY qq.question_order ASC
");
$stmt->execute([$attempt_id, $attempt['quiz_id']]);
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
                    <li class="breadcrumb-item"><a href="/dashboard/course-details.php?id=<?php echo $attempt['course_id']; ?>">
                        <?php echo htmlspecialchars($attempt['course_title']); ?>
                    </a></li>
                    <li class="breadcrumb-item"><a href="/dashboard/quiz.php?id=<?php echo $attempt['quiz_id']; ?>">
                        <?php echo htmlspecialchars($attempt['quiz_title']); ?>
                    </a></li>
                    <li class="breadcrumb-item active">Results</li>
                </ol>
            </nav>
            
            <?php display_messages(); ?>
            
            <!-- Results Header -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-4 bg-<?php echo $attempt['passed'] ? 'success' : 'danger'; ?> text-white p-5 text-center">
                            <div class="mb-3">
                                <?php if ($attempt['passed']): ?>
                                    <i class="fas fa-check-circle fa-5x"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle fa-5x"></i>
                                <?php endif; ?>
                            </div>
                            <h2 class="mb-2"><?php echo round($attempt['score']); ?>%</h2>
                            <h4><?php echo $attempt['passed'] ? 'Passed!' : 'Failed'; ?></h4>
                            <?php if ($attempt['passed']): ?>
                                <p class="mb-0 mt-3">You earned <strong><?php echo $attempt['xp_earned']; ?> XP</strong></p>
                            <?php else: ?>
                                <p class="mb-0 mt-3">Required: <?php echo $attempt['passing_score']; ?>%</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-8 p-4">
                            <h3 class="mb-3"><?php echo htmlspecialchars($attempt['quiz_title']); ?></h3>
                            
                            <div class="row g-4">
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-primary-light text-primary me-3">
                                            <i class="fas fa-check-double"></i>
                                        </div>
                                        <div>
                                            <div class="fs-4 fw-bold"><?php echo $attempt['correct_answers']; ?>/<?php echo $attempt['total_questions']; ?></div>
                                            <small class="text-muted">Correct Answers</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-info-light text-info me-3">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <div class="fs-4 fw-bold"><?php echo gmdate("i:s", $attempt['time_taken']); ?></div>
                                            <small class="text-muted">Time Taken</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-warning-light text-warning me-3">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div>
                                            <div class="fs-4 fw-bold"><?php echo $attempt['xp_earned']; ?> XP</div>
                                            <small class="text-muted">XP Earned</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-success-light text-success me-3">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo date('M d, Y', strtotime($attempt['attempt_date'])); ?></div>
                                            <small class="text-muted"><?php echo date('h:i A', strtotime($attempt['attempt_date'])); ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 d-flex gap-2">
                                <?php if (!$attempt['passed']): ?>
                                    <a href="/dashboard/quiz.php?id=<?php echo $attempt['quiz_id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-redo me-2"></i>Retake Quiz
                                    </a>
                                <?php endif; ?>
                                <a href="/dashboard/course-details.php?id=<?php echo $attempt['course_id']; ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Course
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Question Review -->
            <h4 class="mb-4">Question Review</h4>
            
            <?php foreach ($questions as $index => $question): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3">
                                <?php if ($question['user_is_correct']): ?>
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-times"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="mb-0">Question <?php echo $index + 1; ?></h5>
                                    <span class="badge bg-<?php echo $question['user_is_correct'] ? 'success' : 'danger'; ?>">
                                        <?php echo $question['user_is_correct'] ? 'Correct' : 'Incorrect'; ?>
                                    </span>
                                </div>
                                
                                <p class="mb-3"><?php echo htmlspecialchars($question['question_text']); ?></p>
                                
                                <div class="answers-review">
                                    <?php foreach ($question['answers'] as $answer): ?>
                                        <?php 
                                        $is_user_answer = ($answer['answer_id'] == $question['user_answer_id']);
                                        $is_correct_answer = $answer['is_correct'];
                                        
                                        $class = '';
                                        $icon = '';
                                        
                                        if ($is_user_answer && $is_correct_answer) {
                                            $class = 'border-success bg-success-light';
                                            $icon = '<i class="fas fa-check-circle text-success me-2"></i>';
                                        } elseif ($is_user_answer && !$is_correct_answer) {
                                            $class = 'border-danger bg-danger-light';
                                            $icon = '<i class="fas fa-times-circle text-danger me-2"></i>';
                                        } elseif (!$is_user_answer && $is_correct_answer) {
                                            $class = 'border-success';
                                            $icon = '<i class="fas fa-check-circle text-success me-2"></i>';
                                        }
                                        ?>
                                        
                                        <div class="p-3 mb-2 rounded border <?php echo $class; ?>">
                                            <div class="d-flex align-items-center">
                                                <?php echo $icon; ?>
                                                <span><?php echo htmlspecialchars($answer['answer_text']); ?></span>
                                                
                                                <?php if ($is_user_answer): ?>
                                                    <span class="badge bg-primary ms-auto">Your Answer</span>
                                                <?php elseif ($is_correct_answer): ?>
                                                    <span class="badge bg-success ms-auto">Correct Answer</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Actions -->
            <div class="text-center mt-5">
                <?php if (!$attempt['passed']): ?>
                    <a href="/dashboard/quiz.php?id=<?php echo $attempt['quiz_id']; ?>" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-redo me-2"></i>Try Again
                    </a>
                <?php else: ?>
                    <a href="/dashboard/course-details.php?id=<?php echo $attempt['course_id']; ?>" class="btn btn-success btn-lg px-5">
                        <i class="fas fa-check me-2"></i>Continue Learning
                    </a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<style>
.icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.bg-primary-light {
    background-color: rgba(13, 110, 253, 0.1);
}

.bg-success-light {
    background-color: rgba(25, 135, 84, 0.1);
}

.bg-danger-light {
    background-color: rgba(220, 53, 69, 0.1);
}

.bg-warning-light {
    background-color: rgba(255, 193, 7, 0.1);
}

.bg-info-light {
    background-color: rgba(13, 202, 240, 0.1);
}

.answers-review .border-success {
    border-width: 2px !important;
}

.answers-review .border-danger {
    border-width: 2px !important;
}
</style>

<?php require_once '../includes/footer.php'; ?>