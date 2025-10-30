<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Add New Lesson - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
    $lesson_title = clean_input($_POST['lesson_title']);
    $lesson_order = isset($_POST['lesson_order']) ? (int)$_POST['lesson_order'] : 1;
    $lesson_type = clean_input($_POST['lesson_type']);
    $content_text = isset($_POST['content_text']) ? $_POST['content_text'] : '';
    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : null;
    $xp_reward = isset($_POST['xp_reward']) ? (int)$_POST['xp_reward'] : 10;

    // Validation
    if (empty($lesson_title)) {
        $errors[] = 'Lesson title is required.';
    }
    if ($course_id <= 0) {
        $errors[] = 'Please select a valid course.';
    }
    if (!in_array($lesson_type, ['video', 'text', 'pdf', 'mixed'])) {
        $errors[] = 'Invalid lesson type.';
    }

    // Handle file upload for video or PDF
    $content_url = '';
    if (in_array($lesson_type, ['video', 'pdf', 'mixed']) && isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
        if ($lesson_type === 'video') {
            $uploaded = upload_file($_FILES['content_file'], UPLOAD_PATH . 'videos/', ALLOWED_VIDEO_EXT);
        } else {
            $uploaded = upload_file($_FILES['content_file'], UPLOAD_PATH . 'documents/', ALLOWED_DOC_EXT);
        }

        if ($uploaded) {
            $content_url = $uploaded;
        } else {
            $errors[] = 'Failed to upload file. Please check file type and size.';
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO lessons (course_id, lesson_title, lesson_order, lesson_type, content_url, content_text, duration, xp_reward)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $course_id,
                $lesson_title,
                $lesson_order,
                $lesson_type,
                $content_url,
                $content_text,
                $duration,
                $xp_reward
            ]);

            redirect('/admin/lessons/?success=added');
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch all courses for dropdown
$courses_stmt = $conn->query("
    SELECT c.course_id, c.course_title, s.subject_name
    FROM courses c
    INNER JOIN subjects s ON c.subject_id = s.subject_id
    ORDER BY s.subject_name ASC, c.course_title ASC
");
$courses = $courses_stmt->fetchAll();

// $page_title = "Add New Lesson";
// include '../../includes/header.php';
// include '../../includes/admin-sidebar.php';
?>


<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../../includes/admin-sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-plus-circle me-2"></i>Add New Lesson</h2>
                    <a href="/admin/lessons/" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Lessons
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <!-- Course Selection -->
                                    <div class="mb-3">
                                        <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                                        <select name="course_id" id="course_id" class="form-select" required>
                                            <option value="">Select a course...</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?php echo $course['course_id']; ?>"
                                                    <?php echo (isset($_POST['course_id']) && $_POST['course_id'] == $course['course_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($course['subject_name'] . ' - ' . $course['course_title']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Lesson Title -->
                                    <div class="mb-3">
                                        <label for="lesson_title" class="form-label">Lesson Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lesson_title" name="lesson_title"
                                            value="<?php echo isset($_POST['lesson_title']) ? htmlspecialchars($_POST['lesson_title']) : ''; ?>"
                                            required maxlength="200">
                                    </div>

                                    <!-- Lesson Order -->
                                    <div class="mb-3">
                                        <label for="lesson_order" class="form-label">Lesson Order <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="lesson_order" name="lesson_order"
                                            value="<?php echo isset($_POST['lesson_order']) ? $_POST['lesson_order'] : '1'; ?>"
                                            min="1" required>
                                        <small class="text-muted">Order in which this lesson appears in the course</small>
                                    </div>

                                    <!-- Lesson Type -->
                                    <div class="mb-3">
                                        <label for="lesson_type" class="form-label">Lesson Type <span class="text-danger">*</span></label>
                                        <select name="lesson_type" id="lesson_type" class="form-select" required onchange="toggleContentFields()">
                                            <option value="text" <?php echo (isset($_POST['lesson_type']) && $_POST['lesson_type'] == 'text') ? 'selected' : ''; ?>>Text</option>
                                            <option value="video" <?php echo (isset($_POST['lesson_type']) && $_POST['lesson_type'] == 'video') ? 'selected' : ''; ?>>Video</option>
                                            <option value="pdf" <?php echo (isset($_POST['lesson_type']) && $_POST['lesson_type'] == 'pdf') ? 'selected' : ''; ?>>PDF</option>
                                            <option value="mixed" <?php echo (isset($_POST['lesson_type']) && $_POST['lesson_type'] == 'mixed') ? 'selected' : ''; ?>>Mixed</option>
                                        </select>
                                    </div>

                                    <!-- File Upload (for video/pdf/mixed) -->
                                    <div class="mb-3" id="file-upload-section">
                                        <label for="content_file" class="form-label">Upload File</label>
                                        <input type="file" class="form-control" id="content_file" name="content_file">
                                        <small class="text-muted">
                                            Video: MP4, AVI, MOV, WMV (Max 100MB) | Document: PDF, DOC, DOCX (Max 10MB)
                                        </small>
                                    </div>

                                    <!-- Text Content -->
                                    <div class="mb-3" id="text-content-section">
                                        <label for="content_text" class="form-label">Lesson Content</label>
                                        <textarea class="form-control" id="content_text" name="content_text" rows="10"><?php echo isset($_POST['content_text']) ? htmlspecialchars($_POST['content_text']) : ''; ?></textarea>
                                        <small class="text-muted">HTML tags are allowed</small>
                                    </div>

                                    <!-- Duration -->
                                    <div class="mb-3">
                                        <label for="duration" class="form-label">Duration (minutes)</label>
                                        <input type="number" class="form-control" id="duration" name="duration"
                                            value="<?php echo isset($_POST['duration']) ? $_POST['duration'] : ''; ?>"
                                            min="1">
                                        <small class="text-muted">Estimated time to complete this lesson</small>
                                    </div>

                                    <!-- XP Reward -->
                                    <div class="mb-3">
                                        <label for="xp_reward" class="form-label">XP Reward</label>
                                        <input type="number" class="form-control" id="xp_reward" name="xp_reward"
                                            value="<?php echo isset($_POST['xp_reward']) ? $_POST['xp_reward'] : '10'; ?>"
                                            min="1" required>
                                        <small class="text-muted">XP points awarded upon completion (default: 10)</small>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-purple">
                                            <i class="fas fa-save me-2"></i>Add Lesson
                                        </button>
                                        <a href="/admin/lessons/" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Tips</h5>
                                <ul class="small">
                                    <li>Choose lesson type based on your content format</li>
                                    <li><strong>Text:</strong> Written content only</li>
                                    <li><strong>Video:</strong> Video file upload</li>
                                    <li><strong>PDF:</strong> PDF document upload</li>
                                    <li><strong>Mixed:</strong> Combination of text + file</li>
                                    <li>Set appropriate XP rewards based on lesson difficulty</li>
                                    <li>Use lesson order to organize content logically</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>
</div>

<script>
function toggleContentFields() {
    const lessonType = document.getElementById('lesson_type').value;
    const fileSection = document.getElementById('file-upload-section');
    const textSection = document.getElementById('text-content-section');

    if (lessonType === 'text') {
        fileSection.style.display = 'none';
        textSection.style.display = 'block';
    } else if (lessonType === 'video' || lessonType === 'pdf') {
        fileSection.style.display = 'block';
        textSection.style.display = 'none';
    } else if (lessonType === 'mixed') {
        fileSection.style.display = 'block';
        textSection.style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleContentFields();
});
</script>

<?php include '../../includes/admin-footer.php'; ?>
