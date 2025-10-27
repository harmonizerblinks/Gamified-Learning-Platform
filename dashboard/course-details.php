<?php 
require_once '../includes/header.php';
require_login();

$user_id = get_user_id();
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($course_id == 0) {
    redirect('/dashboard/my-courses.php');
}

// Check if user is enrolled
$stmt = $conn->prepare("SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user_id, $course_id]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    set_error('You are not enrolled in this course');
    redirect('/dashboard/my-courses.php');
}

// Get course details
$stmt = $conn->prepare("
    SELECT c.*, s.subject_name
    FROM courses c
    JOIN subjects s ON c.subject_id = s.subject_id
    WHERE c.course_id = ?
");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    redirect('/dashboard/my-courses.php');
}

$page_title = $course['course_title'] . " - " . SITE_NAME;

// Get all lessons with completion status
$stmt = $conn->prepare("
    SELECT l.*,
           CASE WHEN ulp.is_completed = 1 THEN 1 ELSE 0 END as is_completed
    FROM lessons l
    LEFT JOIN user_lesson_progress ulp ON l.lesson_id = ulp.lesson_id AND ulp.user_id = ?
    WHERE l.course_id = ?
    ORDER BY l.lesson_order ASC
");
$stmt->execute([$user_id, $course_id]);
$lessons = $stmt->fetchAll();

// Calculate stats
$total_lessons = count($lessons);
$completed_lessons = 0;
foreach ($lessons as $lesson) {
    if ($lesson['is_completed']) $completed_lessons++;
}
$progress_percentage = $total_lessons > 0 ? ($completed_lessons / $total_lessons) * 100 : 0;

// Update progress in database
$stmt = $conn->prepare("UPDATE user_courses SET progress_percentage = ? WHERE user_id = ? AND course_id = ?");
$stmt->execute([$progress_percentage, $user_id, $course_id]);

// Check if course is completed
if ($progress_percentage == 100 && !$enrollment['is_completed']) {
    // Mark as completed
    $stmt = $conn->prepare("UPDATE user_courses SET is_completed = 1, completion_date = NOW() WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    
    // Award course completion XP
    add_xp($user_id, $course['xp_reward'], 'course', $course_id, 'Completed: ' . $course['course_title']);
    
    // Generate certificate
    $certificate_code = 'CERT-' . strtoupper(substr($course['course_title'], 0, 4)) . '-' . $user_id . '-' . time();
    $stmt = $conn->prepare("INSERT INTO certificates (user_id, course_id, certificate_code) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $course_id, $certificate_code]);
    
    set_success('🎉 Congratulations! You completed the course and earned ' . $course['xp_reward'] . ' XP!');
}

// Get quiz for this course (if exists)
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE course_id = ? LIMIT 1");
$stmt->execute([$course_id]);
$quiz = $stmt->fetch();

// Get user's best quiz attempt (if any)
$best_attempt = null;
if ($quiz) {
    $stmt = $conn->prepare("
        SELECT * FROM user_quiz_attempts 
        WHERE user_id = ? AND quiz_id = ? 
        ORDER BY score DESC LIMIT 1
    ");
    $stmt->execute([$user_id, $quiz['quiz_id']]);
    $best_attempt = $stmt->fetch();
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
                    <li class="breadcrumb-item"><a href="/dashboard/my-courses.php">My Courses</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($course['course_title']); ?></li>
                </ol>
            </nav>
            
            <?php display_messages(); ?>
            
            <!-- Course Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="<?php echo UPLOAD_URL; ?>thumbnails/<?php echo $course['thumbnail']; ?>" 
                             class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($course['course_title']); ?>"
                             style="height: 100%; object-fit: cover;">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body p-4">
                            <span class="badge bg-purple mb-2"><?php echo htmlspecialchars($course['subject_name']); ?></span>
                            <h2 class="mb-3"><?php echo htmlspecialchars($course['course_title']); ?></h2>
                            <p class="text-muted mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-book-reader fa-2x text-purple mb-2"></i>
                                        <div class="fw-bold"><?php echo $total_lessons; ?> Lessons</div>
                                        <small class="text-muted">Total</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-clock fa-2x text-info mb-2"></i>
                                        <div class="fw-bold"><?php echo $course['estimated_duration']; ?> min</div>
                                        <small class="text-muted">Duration</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-signal fa-2x text-warning mb-2"></i>
                                        <div class="fw-bold"><?php echo ucfirst($course['difficulty']); ?></div>
                                        <small class="text-muted">Difficulty</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-star fa-2x text-warning mb-2"></i>
                                        <div class="fw-bold"><?php echo $course['xp_reward']; ?> XP</div>
                                        <small class="text-muted">Reward</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">Course Progress</span>
                                    <span class="text-muted"><?php echo $completed_lessons; ?>/<?php echo $total_lessons; ?> Lessons</span>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar mt-0 mb-0 bg-success" role="progressbar" 
                                         style="height: 20px; width: <?php echo $progress_percentage; ?>%">
                                        <?php echo round($progress_percentage); ?>%
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($enrollment['is_completed']): ?>
                                <a href="/dashboard/certificate-view.php?course_id=<?php echo $course_id; ?>" 
                                   class="btn btn-success">
                                    <i class="fas fa-certificate me-2"></i>View Certificate
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Course Content -->
            <div class="row">
                <!-- Lessons List -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h4 class="mb-0">Course Lessons</h4>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($lessons as $index => $lesson): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <div class="me-3">
                                                <?php if ($lesson['is_completed']): ?>
                                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-light text-muted d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <?php echo $index + 1; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($lesson['lesson_title']); ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-<?php echo $lesson['lesson_type'] == 'video' ? 'play-circle' : 'file-alt'; ?> me-1"></i>
                                                    <?php echo ucfirst($lesson['lesson_type']); ?>
                                                    <?php if ($lesson['duration']): ?>
                                                        • <?php echo $lesson['duration']; ?> min
                                                    <?php endif; ?>
                                                    • <?php echo $lesson['xp_reward']; ?> XP
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <a href="/dashboard/lesson.php?id=<?php echo $lesson['lesson_id']; ?>" 
                                           class="btn btn-sm btn-<?php echo $lesson['is_completed'] ? 'outline-secondary' : 'primary'; ?>">
                                            <?php echo $lesson['is_completed'] ? 'Review' : 'Start'; ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Quiz Section -->
                    <?php if ($quiz): ?>
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <div class="rounded-circle bg-warning-light text-warning d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-question fa-lg"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h5>
                                        <small class="text-muted"><?php echo $quiz['xp_reward']; ?> XP • Passing Score: <?php echo $quiz['passing_score']; ?>%</small>
                                    </div>
                                </div>
                                
                                <?php if ($best_attempt): ?>
                                    <div class="alert alert-<?php echo $best_attempt['passed'] ? 'success' : 'warning'; ?> mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>
                                                <i class="fas fa-chart-line me-2"></i>
                                                Best Score: <strong><?php echo round($best_attempt['score']); ?>%</strong>
                                            </span>
                                            <span>
                                                <?php echo $best_attempt['passed'] ? '✓ Passed' : '✗ Not Passed'; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($quiz['description']); ?></p>
                                
                                <a href="/dashboard/quiz.php?id=<?php echo $quiz['quiz_id']; ?>" 
                                   class="btn btn-warning">
                                    <i class="fas fa-play-circle me-2"></i>
                                    <?php echo $best_attempt ? 'Retake Quiz' : 'Take Quiz'; ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar Info -->
                <div class="col-lg-4">
                    <!-- Course Stats -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Your Progress</h5>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Lessons Completed</small>
                                    <small class="fw-bold"><?php echo $completed_lessons; ?>/<?php echo $total_lessons; ?></small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $progress_percentage; ?>%"></div>
                                </div>
                            </div>
                            
                            <?php if ($quiz && $best_attempt): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Quiz Score</small>
                                        <small class="fw-bold"><?php echo round($best_attempt['score']); ?>%</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: <?php echo $best_attempt['score']; ?>%"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Enrolled</span>
                                <span class="fw-bold"><?php echo format_date($enrollment['enrollment_date']); ?></span>
                            </div>
                            
                            <?php if ($enrollment['is_completed']): ?>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Completed</span>
                                    <span class="fw-bold text-success"><?php echo format_date($enrollment['completion_date']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Next Steps -->
                    <?php if (!$enrollment['is_completed']): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="mb-3">Next Steps</h5>
                                
                                <?php
                                // Find next incomplete lesson
                                $next_lesson = null;
                                foreach ($lessons as $lesson) {
                                    if (!$lesson['is_completed']) {
                                        $next_lesson = $lesson;
                                        break;
                                    }
                                }
                                ?>
                                
                                <?php if ($next_lesson): ?>
                                    <p class="text-muted mb-3">Continue with:</p>
                                    <div class="bg-light p-3 rounded mb-3">
                                        <h6 class="mb-2"><?php echo htmlspecialchars($next_lesson['lesson_title']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i><?php echo $next_lesson['duration']; ?> min
                                        </small>
                                    </div>
                                    <a href="/dashboard/lesson.php?id=<?php echo $next_lesson['lesson_id']; ?>" 
                                       class="btn btn-primary w-100">
                                        Continue Learning
                                    </a>
                                <?php elseif ($quiz && (!$best_attempt || !$best_attempt['passed'])): ?>
                                    <p class="text-muted mb-3">All lessons completed! Take the quiz to finish:</p>
                                    <a href="/dashboard/quiz.php?id=<?php echo $quiz['quiz_id']; ?>" 
                                       class="btn btn-warning w-100">
                                        Take Final Quiz
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>