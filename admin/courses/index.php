<?php
require_once '../../includes/header.php';
$page_title = "Manage Courses - " . SITE_NAME;
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get all courses with subject info
$stmt = $conn->query("
    SELECT c.*, s.subject_name,
           COUNT(DISTINCT l.lesson_id) as lesson_count,
           COUNT(DISTINCT q.quiz_id) as quiz_count,
           COUNT(DISTINCT uc.user_id) as enrollment_count
    FROM courses c
    LEFT JOIN subjects s ON c.subject_id = s.subject_id
    LEFT JOIN lessons l ON c.course_id = l.course_id
    LEFT JOIN quizzes q ON c.course_id = q.course_id
    LEFT JOIN user_courses uc ON c.course_id = uc.course_id
    GROUP BY c.course_id
    ORDER BY c.created_at DESC
");
$courses = $stmt->fetchAll();
?>

<div class="main-content">
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../../includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-graduation-cap me-2"></i>Manage Courses</h1>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Course
                </a>
            </div>

            <?php display_messages(); ?>

            <!-- Courses Grid -->
            <div class="row g-4">
                <?php if (empty($courses)): ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
                                <h5>No courses found</h5>
                                <p class="text-muted">Add your first course to get started!</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <?php if ($course['thumbnail']): ?>
                                    <img src="<?php echo UPLOAD_URL; ?>thumbnails/<?php echo $course['thumbnail']; ?>"
                                         class="card-img-top" alt="<?php echo htmlspecialchars($course['course_title']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-image fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-purple"><?php echo htmlspecialchars($course['subject_name']); ?></span>
                                        <?php if ($course['is_published']): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Draft</span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($course['course_title']); ?></h5>
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars(substr($course['description'], 0, 80)); ?>...
                                    </p>
                                    <div class="d-flex justify-content-between text-muted small mb-3">
                                        <span><i class="fas fa-signal"></i> <?php echo ucfirst($course['difficulty']); ?></span>
                                        <span><i class="fas fa-clock"></i> <?php echo $course['estimated_duration']; ?> min</span>
                                        <span><i class="fas fa-star text-warning"></i> <?php echo $course['xp_reward']; ?> XP</span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small mb-3">
                                        <span><i class="fas fa-book"></i> <?php echo $course['lesson_count']; ?> lessons</span>
                                        <span><i class="fas fa-question-circle"></i> <?php echo $course['quiz_count']; ?> quizzes</span>
                                        <span><i class="fas fa-users"></i> <?php echo $course['enrollment_count']; ?> enrolled</span>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <div class="d-flex gap-2">
                                        <a href="edit.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-warning flex-fill">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="/admin/lessons/?course_id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-info flex-fill">
                                            <i class="fas fa-book"></i> Lessons
                                        </a>
                                        <a href="delete.php?id=<?php echo $course['course_id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure? This will delete all lessons and quizzes!')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
                                        </div>

<?php require_once '../../includes/footer.php'; ?>
