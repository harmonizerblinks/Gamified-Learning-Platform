<?php 
require_once '../includes/header.php';
require_login();

$user_id = get_user_id();
$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($lesson_id == 0) {
    redirect('/dashboard/my-courses.php');
}

// Get lesson details
$stmt = $conn->prepare("
    SELECT l.*, c.course_title, c.course_id
    FROM lessons l
    JOIN courses c ON l.course_id = c.course_id
    WHERE l.lesson_id = ?
");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    set_error('Lesson not found');
    redirect('/dashboard/my-courses.php');
}

// Check if user is enrolled in the course
$stmt = $conn->prepare("SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user_id, $lesson['course_id']]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    set_error('You are not enrolled in this course');
    redirect('/dashboard/my-courses.php');
}

$page_title = $lesson['lesson_title'] . " - " . SITE_NAME;

// Check if lesson is completed
$stmt = $conn->prepare("SELECT * FROM user_lesson_progress WHERE user_id = ? AND lesson_id = ?");
$stmt->execute([$user_id, $lesson_id]);
$progress = $stmt->fetch();
$is_completed = $progress && $progress['is_completed'];

// Get all lessons in this course for navigation
$stmt = $conn->prepare("
    SELECT l.lesson_id, l.lesson_title, l.lesson_order,
           CASE WHEN ulp.is_completed = 1 THEN 1 ELSE 0 END as is_completed
    FROM lessons l
    LEFT JOIN user_lesson_progress ulp ON l.lesson_id = ulp.lesson_id AND ulp.user_id = ?
    WHERE l.course_id = ?
    ORDER BY l.lesson_order ASC
");
$stmt->execute([$user_id, $lesson['course_id']]);
$all_lessons = $stmt->fetchAll();

// Find previous and next lessons
$current_index = 0;
foreach ($all_lessons as $index => $l) {
    if ($l['lesson_id'] == $lesson_id) {
        $current_index = $index;
        break;
    }
}
$prev_lesson = $current_index > 0 ? $all_lessons[$current_index - 1] : null;
$next_lesson = $current_index < count($all_lessons) - 1 ? $all_lessons[$current_index + 1] : null;
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
                    <li class="breadcrumb-item"><a href="/dashboard/course-details.php?id=<?php echo $lesson['course_id']; ?>">
                        <?php echo htmlspecialchars($lesson['course_title']); ?>
                    </a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($lesson['lesson_title']); ?></li>
                </ol>
            </nav>
            
            <?php display_messages(); ?>
            
            <!-- Lesson Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="mb-2"><?php echo htmlspecialchars($lesson['lesson_title']); ?></h2>
                            <div class="text-muted">
                                <span class="me-3">
                                    <i class="fas fa-<?php echo $lesson['lesson_type'] == 'video' ? 'play-circle' : 'file-alt'; ?> me-1"></i>
                                    <?php echo ucfirst($lesson['lesson_type']); ?>
                                </span>
                                <?php if ($lesson['duration']): ?>
                                    <span class="me-3">
                                        <i class="fas fa-clock me-1"></i><?php echo $lesson['duration']; ?> minutes
                                    </span>
                                <?php endif; ?>
                                <span>
                                    <i class="fas fa-star me-1 text-warning"></i><?php echo $lesson['xp_reward']; ?> XP
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($is_completed): ?>
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-check-circle me-1"></i>Completed
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Lesson Content -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <?php if ($lesson['lesson_type'] == 'video' && $lesson['content_url']): ?>
                                <!-- Video Player -->
                                <div class="ratio ratio-16x9 mb-4">
                                    <video controls class="rounded" id="lessonVideo">
                                        <source src="<?php echo UPLOAD_URL; ?>videos/<?php echo $lesson['content_url']; ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($lesson['content_text']): ?>
                                <!-- Text Content -->
                                <div class="lesson-content">
                                    <?php echo nl2br(htmlspecialchars($lesson['content_text'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($lesson['lesson_type'] == 'pdf' && $lesson['content_url']): ?>
                                <!-- PDF Viewer -->
                                <div class="text-center mb-4">
                                    <a href="<?php echo UPLOAD_URL; ?>documents/<?php echo $lesson['content_url']; ?>" 
                                       class="btn btn-primary" target="_blank">
                                        <i class="fas fa-file-pdf me-2"></i>Open PDF Document
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Mark as Complete Button -->
                    <?php if (!$is_completed): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4 text-center">
                                <h5 class="mb-3">Ready to move on?</h5>
                                <form action="/actions/complete-lesson.php" method="POST" id="completeForm">
                                    <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-check-circle me-2"></i>Mark as Complete & Earn <?php echo $lesson['xp_reward']; ?> XP
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Lesson Navigation -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">Course Lessons</h5>
                        </div>
                        <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                            <?php foreach ($all_lessons as $index => $l): ?>
                                <a href="/dashboard/lesson.php?id=<?php echo $l['lesson_id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $l['lesson_id'] == $lesson_id ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <?php if ($l['is_completed']): ?>
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                            <?php else: ?>
                                                <span class="badge bg-secondary me-2"><?php echo $index + 1; ?></span>
                                            <?php endif; ?>
                                            <small><?php echo htmlspecialchars($l['lesson_title']); ?></small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Navigation Buttons -->
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between gap-2">
                                <?php if ($prev_lesson): ?>
                                    <a href="/dashboard/lesson.php?id=<?php echo $prev_lesson['lesson_id']; ?>" 
                                       class="btn btn-outline-secondary btn-sm flex-grow-1">
                                        <i class="fas fa-arrow-left me-1"></i>Previous
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary btn-sm flex-grow-1" disabled>
                                        <i class="fas fa-arrow-left me-1"></i>Previous
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($next_lesson): ?>
                                    <a href="/dashboard/lesson.php?id=<?php echo $next_lesson['lesson_id']; ?>" 
                                       class="btn btn-primary btn-sm flex-grow-1">
                                        Next<i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="/dashboard/course-details.php?id=<?php echo $lesson['course_id']; ?>" 
                                       class="btn btn-success btn-sm flex-grow-1">
                                        Finish<i class="fas fa-check ms-1"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Auto-mark video as complete when it ends
document.getElementById('lessonVideo')?.addEventListener('ended', function() {
    if (confirm('Great job! Mark this lesson as complete?')) {
        document.getElementById('completeForm')?.submit();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>