<?php 
require_once '../includes/header.php';
$page_title = "Admin Dashboard - " . SITE_NAME;
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get statistics
// Total Users
$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'learner'");
$total_learners = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$total_admins = $stmt->fetch()['total'];

// Total Courses & Subjects
$stmt = $conn->query("SELECT COUNT(*) as total FROM subjects WHERE is_active = 1");
$total_subjects = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM courses WHERE is_published = 1");
$total_courses = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM courses WHERE is_published = 0");
$draft_courses = $stmt->fetch()['total'];

// Total Lessons & Quizzes
$stmt = $conn->query("SELECT COUNT(*) as total FROM lessons");
$total_lessons = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM quizzes");
$total_quizzes = $stmt->fetch()['total'];

// Total Enrollments & Completions
$stmt = $conn->query("SELECT COUNT(*) as total FROM user_courses");
$total_enrollments = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM user_courses WHERE is_completed = 1");
$total_completions = $stmt->fetch()['total'];

// Total Certificates & Badges
$stmt = $conn->query("SELECT COUNT(*) as total FROM certificates");
$total_certificates = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM badges");
$total_badges = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM user_badges");
$badges_earned = $stmt->fetch()['total'];

// Recent Activity
$stmt = $conn->query("
    SELECT u.username, u.full_name, uc.enrollment_date, c.course_title
    FROM user_courses uc
    JOIN users u ON uc.user_id = u.user_id
    JOIN courses c ON uc.course_id = c.course_id
    ORDER BY uc.enrollment_date DESC
    LIMIT 10
");
$recent_enrollments = $stmt->fetchAll();

// Top Learners
$stmt = $conn->query("
    SELECT u.username, u.full_name, u.total_xp, u.current_level, u.profile_picture
    FROM users u
    WHERE u.role = 'learner'
    ORDER BY u.total_xp DESC
    LIMIT 5
");
$top_learners = $stmt->fetchAll();

// Popular Courses
$stmt = $conn->query("
    SELECT c.course_title, c.course_id, COUNT(uc.user_id) as enrollment_count
    FROM courses c
    LEFT JOIN user_courses uc ON c.course_id = uc.course_id
    WHERE c.is_published = 1
    GROUP BY c.course_id
    ORDER BY enrollment_count DESC
    LIMIT 5
");
$popular_courses = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h1>
                <div>
                    <span class="text-muted me-3">
                        <i class="fas fa-calendar me-1"></i><?php echo date('l, F d, Y'); ?>
                    </span>
                </div>
            </div>
            
            <?php display_messages(); ?>
            
            <!-- Quick Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-2">Total Learners</h6>
                                    <h2 class="mb-0"><?php echo number_format($total_learners); ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-white bg-opacity-10 border-0">
                            <a href="/admin/users.php" class="text-white text-decoration-none">
                                View All <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-2">Active Courses</h6>
                                    <h2 class="mb-0"><?php echo number_format($total_courses); ?></h2>
                                    <small class="text-white-50"><?php echo $draft_courses; ?> drafts</small>
                                </div>
                                <i class="fas fa-graduation-cap fa-3x opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-white bg-opacity-10 border-0">
                            <a href="/admin/courses.php" class="text-white text-decoration-none">
                                Manage Courses <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-2">Total Enrollments</h6>
                                    <h2 class="mb-0"><?php echo number_format($total_enrollments); ?></h2>
                                    <small class="text-white-50"><?php echo $total_completions; ?> completed</small>
                                </div>
                                <i class="fas fa-user-check fa-3x opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-white bg-opacity-10 border-0">
                            <a href="/admin/reports.php" class="text-white text-decoration-none">
                                View Reports <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2 opacity-75">Certificates Issued</h6>
                                    <h2 class="mb-0"><?php echo number_format($total_certificates); ?></h2>
                                    <small class="opacity-75"><?php echo $badges_earned; ?> badges earned</small>
                                </div>
                                <i class="fas fa-certificate fa-3x opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-white bg-opacity-25