<?php 
require_once '../includes/header.php';
require_once INCLUDES_PATH .'navbar.php';

$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if ($subject_id == 0) {
    redirect('/pages/subjects.php');
}

// Get subject details
$stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_id = ? AND is_active = 1");
$stmt->execute([$subject_id]);
$subject = $stmt->fetch();

if (!$subject) {
    redirect('/pages/subjects.php');
}

$page_title = $subject['subject_name'] . " Courses - " . SITE_NAME;

// Get courses for this subject
$stmt = $conn->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM lessons WHERE course_id = c.course_id) as lesson_count,
           (SELECT COUNT(*) FROM user_courses WHERE course_id = c.course_id) as enrolled_count
    FROM courses c
    WHERE c.subject_id = ? AND c.is_published = 1
    ORDER BY c.created_at DESC
");
$stmt->execute([$subject_id]);
$courses = $stmt->fetchAll();
?>

<!-- Navbar -->


<!-- Subject Header -->
<section class="bg-purple text-white py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/" class="text-white">Home</a></li>
                <li class="breadcrumb-item"><a href="/pages/subjects.php" class="text-white">Subjects</a></li>
                <li class="breadcrumb-item active text-white"><?php echo htmlspecialchars($subject['subject_name']); ?></li>
            </ol>
        </nav>
        <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($subject['subject_name']); ?></h1>
        <p class="lead"><?php echo htmlspecialchars($subject['description']); ?></p>
        <div class="mt-3">
            <span class="badge bg-white text-purple fs-6 px-3 py-2">
                <?php echo count($courses); ?> Courses Available
            </span>
        </div>
    </div>
</section>

<!-- Courses List -->
<section class="py-5">
    <div class="container">
        <?php if (empty($courses)): ?>
            <div class="text-center py-5">
                <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
                <h3>No courses available yet</h3>
                <p class="text-muted">Courses for this subject are coming soon!</p>
                <a href="/pages/subjects.php" class="btn btn-primary">Browse Other Subjects</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm hover-lift">
                            <div class="position-relative">
                                <img src="<?php echo UPLOAD_URL; ?>thumbnails/<?php echo $course['thumbnail']; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($course['course_title']); ?>" 
                                     style="height: 200px; object-fit: cover;">
                                <span class="position-absolute top-0 end-0 m-2 badge bg-<?php 
                                    echo $course['difficulty'] == 'beginner' ? 'success' : 
                                        ($course['difficulty'] == 'intermediate' ? 'warning' : 
                                        ($course['difficulty'] == 'advanced' ? 'danger' : 'dark')); 
                                ?>">
                                    <?php echo ucfirst($course['difficulty']); ?>
                                </span>
                                <?php if ($course['required_level'] > 1): ?>
                                    <span class="position-absolute top-0 start-0 m-2 badge bg-dark">
                                        Level <?php echo $course['required_level']; ?>+ Required
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($course['course_title']); ?></h5>
                                <p class="card-text text-muted"><?php echo substr(htmlspecialchars($course['description']), 0, 100); ?>...</p>
                                
                                <div class="d-flex justify-content-between text-muted small mb-3">
                                    <span><i class="fas fa-book-reader me-1"></i> <?php echo $course['lesson_count']; ?> Lessons</span>
                                    <span><i class="fas fa-clock me-1"></i> <?php echo $course['estimated_duration']; ?> min</span>
                                    <span><i class="fas fa-star me-1 text-warning"></i> <?php echo $course['xp_reward']; ?> XP</span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i> <?php echo $course['enrolled_count']; ?> enrolled
                                    </small>
                                    
                                    <?php if (is_logged_in()): ?>
                                        <?php
                                        // Check if already enrolled
                                        $stmt_check = $conn->prepare("SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?");
                                        $stmt_check->execute([get_user_id(), $course['course_id']]);
                                        $is_enrolled = $stmt_check->rowCount() > 0;
                                        ?>
                                        
                                        <?php if ($is_enrolled): ?>
                                            <a href="/dashboard/course-details.php?id=<?php echo $course['course_id']; ?>" 
                                               class="btn btn-sm btn-success">Continue</a>
                                        <?php else: ?>
                                            <a href="/actions/enroll-course.php?id=<?php echo $course['course_id']; ?>" 
                                               class="btn btn-sm btn-primary">Enroll Now</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="/auth/register.php" class="btn btn-sm btn-primary">Enroll Now</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>