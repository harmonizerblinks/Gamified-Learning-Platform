<?php 
require_once '../includes/header.php';
$page_title = "My Courses - " . SITE_NAME;
require_login();

$user_id = get_user_id();

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build query based on filter
$query = "
    SELECT c.*, s.subject_name, uc.enrollment_date, uc.progress_percentage, uc.is_completed,
           (SELECT COUNT(*) FROM lessons WHERE course_id = c.course_id) as total_lessons,
           (SELECT COUNT(*) FROM user_lesson_progress ulp 
            WHERE ulp.user_id = uc.user_id AND ulp.lesson_id IN 
            (SELECT lesson_id FROM lessons WHERE course_id = c.course_id) AND ulp.is_completed = 1) as completed_lessons
    FROM courses c
    JOIN user_courses uc ON c.course_id = uc.course_id
    JOIN subjects s ON c.subject_id = s.subject_id
    WHERE uc.user_id = ?
";

if ($filter == 'in_progress') {
    $query .= " AND uc.is_completed = 0 AND uc.progress_percentage > 0";
} elseif ($filter == 'completed') {
    $query .= " AND uc.is_completed = 1";
} elseif ($filter == 'not_started') {
    $query .= " AND uc.progress_percentage = 0";
}

$query .= " ORDER BY uc.enrollment_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll();
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>My Courses</h1>
                <a href="/pages/subjects.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Browse More Courses
                </a>
            </div>
            
            <?php display_messages(); ?>
            
            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter == 'all' ? 'active' : ''; ?>" href="?filter=all">
                        All Courses (<?php echo count($courses); ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter == 'in_progress' ? 'active' : ''; ?>" href="?filter=in_progress">
                        In Progress
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter == 'completed' ? 'active' : ''; ?>" href="?filter=completed">
                        Completed
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter == 'not_started' ? 'active' : ''; ?>" href="?filter=not_started">
                        Not Started
                    </a>
                </li>
            </ul>
            
            <!-- Courses Grid -->
            <?php if (empty($courses)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
                    <h3>No courses found</h3>
                    <p class="text-muted">Start learning by enrolling in a course!</p>
                    <a href="/pages/subjects.php" class="btn btn-primary">Browse Courses</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($courses as $course): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm hover-lift">
                                <div class="position-relative">
                                    <img src="<?php echo UPLOAD_URL; ?>thumbnails/<?php echo $course['thumbnail']; ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($course['course_title']); ?>" 
                                         style="height: 180px; object-fit: cover;">
                                    
                                    <?php if ($course['is_completed']): ?>
                                        <span class="position-absolute top-0 end-0 m-2 badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Completed
                                        </span>
                                    <?php elseif ($course['progress_percentage'] > 0): ?>
                                        <span class="position-absolute top-0 end-0 m-2 badge bg-warning text-dark">
                                            In Progress
                                        </span>
                                    <?php else: ?>
                                        <span class="position-absolute top-0 end-0 m-2 badge bg-info">
                                            Not Started
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body">
                                    <span class="badge bg-purple-light text-purple mb-2"><?php echo htmlspecialchars($course['subject_name']); ?></span>
                                    <h5 class="card-title"><?php echo htmlspecialchars($course['course_title']); ?></h5>
                                    
                                    <!-- Progress Bar -->
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="mb-0 mt-0 progress-bar bg-success" role="progressbar" 
                                             style="height: 8px; width: <?php echo $course['progress_percentage']; ?>%"
                                             aria-valuenow="<?php echo $course['progress_percentage']; ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between text-muted small mb-3">
                                        <span><?php echo round($course['progress_percentage']); ?>% Complete</span>
                                        <span><?php echo $course['completed_lessons']; ?>/<?php echo $course['total_lessons']; ?> Lessons</span>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="/dashboard/course-details.php?id=<?php echo $course['course_id']; ?>" 
                                           class="btn btn-primary flex-grow-1">
                                            <?php echo $course['is_completed'] ? 'Review' : 'Continue'; ?>
                                        </a>
                                        <?php if ($course['is_completed']): ?>
                                            <a href="/dashboard/certificate-view.php?course_id=<?php echo $course['course_id']; ?>" 
                                               class="btn btn-outline-success" title="View Certificate">
                                                <i class="fas fa-certificate"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-transparent border-top-0 text-muted small">
                                    <i class="fas fa-calendar me-1"></i>Enrolled <?php echo time_ago($course['enrollment_date']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>