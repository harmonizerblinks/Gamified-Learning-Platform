<?php
require_once '../includes/header.php';
require_login();

$page_title = "Notifications - " . SITE_NAME;

$user_id = get_user_id();
$user = get_user_data($user_id);

// For now, we'll create a simple notification system
// In a full implementation, you would have a notifications table in the database
// This is a placeholder that shows recent activity

// Get recent achievements
$recent_badges = $conn->prepare("
    SELECT b.badge_name, b.badge_icon, ub.earned_date
    FROM user_badges ub
    INNER JOIN badges b ON ub.badge_id = b.badge_id
    WHERE ub.user_id = ?
    ORDER BY ub.earned_date DESC
    LIMIT 5
");
$recent_badges->execute([$user_id]);
$badges = $recent_badges->fetchAll();

// Get recent certificates
$recent_certificates = $conn->prepare("
    SELECT c.course_title, cert.issued_date, cert.certificate_code
    FROM certificates cert
    INNER JOIN courses c ON cert.course_id = c.course_id
    WHERE cert.user_id = ?
    ORDER BY cert.issued_date DESC
    LIMIT 5
");
$recent_certificates->execute([$user_id]);
$certificates = $recent_certificates->fetchAll();

// Get recent course enrollments
$recent_enrollments = $conn->prepare("
    SELECT c.course_title, uc.enrollment_date
    FROM user_courses uc
    INNER JOIN courses c ON uc.course_id = c.course_id
    WHERE uc.user_id = ?
    ORDER BY uc.enrollment_date DESC
    LIMIT 5
");
$recent_enrollments->execute([$user_id]);
$enrollments = $recent_enrollments->fetchAll();

// Get recent quiz attempts
$recent_quizzes = $conn->prepare("
    SELECT q.quiz_title, c.course_title, uqa.score, uqa.passed, uqa.xp_earned, uqa.attempt_date
    FROM user_quiz_attempts uqa
    INNER JOIN quizzes q ON uqa.quiz_id = q.quiz_id
    INNER JOIN courses c ON q.course_id = c.course_id
    WHERE uqa.user_id = ?
    ORDER BY uqa.attempt_date DESC
    LIMIT 5
");
$recent_quizzes->execute([$user_id]);
$quiz_attempts = $recent_quizzes->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <h2 class="mb-4"><i class="fas fa-bell me-2"></i>Notifications</h2>

        <?php if (empty($badges) && empty($certificates) && empty($enrollments) && empty($quiz_attempts)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No notifications yet. Start learning to see your activity here!
            </div>
        <?php else: ?>

        <!-- Badges Earned -->
        <?php if (!empty($badges)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-medal me-2 text-warning"></i>Badges Earned</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($badges as $badge): ?>
                <div class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="<?php echo htmlspecialchars($badge['badge_icon'] ?: 'fas fa-medal'); ?> fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0"><‰ Badge Unlocked: <?php echo htmlspecialchars($badge['badge_name']); ?></h6>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo date('M d, Y \a\t H:i', strtotime($badge['earned_date'])); ?>
                            </small>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="/dashboard/achievements.php" class="btn btn-sm btn-outline-primary">
                                View Badges
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Certificates Issued -->
        <?php if (!empty($certificates)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-certificate me-2 text-success"></i>Certificates Issued</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($certificates as $cert): ?>
                <div class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-certificate fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0"><Æ Certificate Awarded: <?php echo htmlspecialchars($cert['course_title']); ?></h6>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo date('M d, Y \a\t H:i', strtotime($cert['issued_date'])); ?>
                            </small>
                            <br>
                            <small><code><?php echo htmlspecialchars($cert['certificate_code']); ?></code></small>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="/dashboard/certificates.php" class="btn btn-sm btn-outline-success">
                                View Certificates
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quiz Results -->
        <?php if (!empty($quiz_attempts)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-check-circle me-2 text-info"></i>Recent Quiz Results</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($quiz_attempts as $quiz): ?>
                <div class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-<?php echo $quiz['passed'] ? 'check-circle text-success' : 'times-circle text-danger'; ?> fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">
                                <?php echo $quiz['passed'] ? ' Passed' : 'L Failed'; ?>:
                                <?php echo htmlspecialchars($quiz['quiz_title']); ?>
                            </h6>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($quiz['course_title']); ?> "
                                Score: <?php echo round($quiz['score'], 1); ?>% "
                                +<?php echo $quiz['xp_earned']; ?> XP
                            </small>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo date('M d, Y \a\t H:i', strtotime($quiz['attempt_date'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Course Enrollments -->
        <?php if (!empty($enrollments)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2 text-primary"></i>Course Enrollments</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($enrollments as $enrollment): ?>
                <div class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-graduation-cap fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">=Ú Enrolled in: <?php echo htmlspecialchars($enrollment['course_title']); ?></h6>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo date('M d, Y \a\t H:i', strtotime($enrollment['enrollment_date'])); ?>
                            </small>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="/dashboard/my-courses.php" class="btn btn-sm btn-outline-primary">
                                View Courses
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php endif; ?>

        <!-- Info Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>About Notifications</h5>
                <p class="small text-muted mb-0">
                    This page shows your recent activity including badges earned, certificates received, quiz results, and course enrollments.
                    Keep learning to see more notifications here!
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
