<?php
require_once '../../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = (int)$_POST['subject_id'];
    $course_title = clean_input($_POST['course_title']);
    $description = clean_input($_POST['description']);
    $difficulty = clean_input($_POST['difficulty']);
    $required_level = (int)$_POST['required_level'];
    $estimated_duration = (int)$_POST['estimated_duration'];
    $xp_reward = (int)$_POST['xp_reward'];
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    // Handle thumbnail upload
    $thumbnail = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploaded = upload_file($_FILES['thumbnail'], UPLOAD_PATH . 'thumbnails/', ALLOWED_IMAGE_EXT);
        if ($uploaded) {
            $thumbnail = $uploaded;
        }
    }

    if (empty($course_title) || $subject_id == 0) {
        set_error('Course title and subject are required');
    } else {
        $stmt = $conn->prepare("
            INSERT INTO courses
            (subject_id, course_title, description, difficulty, required_level, thumbnail, estimated_duration, xp_reward, is_published, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$subject_id, $course_title, $description, $difficulty, $required_level, $thumbnail, $estimated_duration, $xp_reward, $is_published, get_user_id()])) {
            set_success('Course added successfully');
            redirect('/admin/courses/');
        } else {
            set_error('Failed to add course');
        }
    }
}

// Get all subjects for dropdown
$subjects = $conn->query("SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name ASC")->fetchAll();

$page_title = "Add Course - " . SITE_NAME;
require_once '../../includes/header.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../../includes/admin-sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-graduation-cap me-2"></i>Add New Course</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Courses
                    </a>
                </div>

                <?php display_messages(); ?>

                <div class="row">
                    <div class="col-md-10">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="course_title" class="form-label">Course Title *</label>
                                            <input type="text" class="form-control" id="course_title" name="course_title" required>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="subject_id" class="form-label">Subject *</label>
                                            <select class="form-select" id="subject_id" name="subject_id" required>
                                                <option value="">Select Subject</option>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <option value="<?php echo $subject['subject_id']; ?>">
                                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="difficulty" class="form-label">Difficulty Level</label>
                                            <select class="form-select" id="difficulty" name="difficulty">
                                                <option value="beginner">Beginner</option>
                                                <option value="intermediate">Intermediate</option>
                                                <option value="advanced">Advanced</option>
                                                <option value="expert">Expert</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="required_level" class="form-label">Required User Level</label>
                                            <input type="number" class="form-control" id="required_level" name="required_level" min="1" max="10" value="1">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="estimated_duration" class="form-label">Estimated Duration (minutes)</label>
                                            <input type="number" class="form-control" id="estimated_duration" name="estimated_duration" value="60">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="xp_reward" class="form-label">XP Reward</label>
                                            <input type="number" class="form-control" id="xp_reward" name="xp_reward" value="100">
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label for="thumbnail" class="form-label">Course Thumbnail</label>
                                            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                                            <small class="text-muted">Recommended: 800x600px, Max 5MB</small>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_published" name="is_published" checked>
                                                <label class="form-check-label" for="is_published">
                                                    Publish Course (Make visible to learners)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Save Course
                                        </button>
                                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<?php require_once '../../includes/admin-footer.php'; ?>
