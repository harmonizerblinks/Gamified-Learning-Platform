<?php
$page_title = "Manage Courses - " . SITE_NAME;
require_once '../includes/header.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Handle Delete
if (isset($_GET['delete'])) {
    $course_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
    if ($stmt->execute([$course_id])) {
        set_success('Course deleted successfully');
    } else {
        set_error('Failed to delete course');
    }
    redirect('/admin/courses.php');
}

// Handle Add/Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
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
    } elseif ($course_id > 0) {
        // Keep existing thumbnail
        $stmt = $conn->prepare("SELECT thumbnail FROM courses WHERE course_id = ?");
        $stmt->execute([$course_id]);
        $existing = $stmt->fetch();
        $thumbnail = $existing['thumbnail'];
    }

    if (empty($course_title) || $subject_id == 0) {
        set_error('Course title and subject are required');
    } else {
        if ($course_id > 0) {
            // Update existing course
            $stmt = $conn->prepare("
                UPDATE courses SET
                    subject_id = ?, course_title = ?, description = ?, difficulty = ?,
                    required_level = ?, thumbnail = ?, estimated_duration = ?, xp_reward = ?, is_published = ?
                WHERE course_id = ?
            ");
            if ($stmt->execute([$subject_id, $course_title, $description, $difficulty, $required_level, $thumbnail, $estimated_duration, $xp_reward, $is_published, $course_id])) {
                set_success('Course updated successfully');
            } else {
                set_error('Failed to update course');
            }
        } else {
            // Add new course
            $stmt = $conn->prepare("
                INSERT INTO courses
                (subject_id, course_title, description, difficulty, required_level, thumbnail, estimated_duration, xp_reward, is_published, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$subject_id, $course_title, $description, $difficulty, $required_level, $thumbnail, $estimated_duration, $xp_reward, $is_published, get_user_id()])) {
                set_success('Course added successfully');
            } else {
                set_error('Failed to add course');
            }
        }
        redirect('/admin/courses.php');
    }
}

// Get course for editing
$edit_course = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
    $stmt->execute([$edit_id]);
    $edit_course = $stmt->fetch();
}

// Get all subjects for dropdown
$subjects = $conn->query("SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name ASC")->fetchAll();

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

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-graduation-cap me-2"></i>Manage Courses</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal" onclick="clearForm()">
                    <i class="fas fa-plus me-2"></i>Add New Course
                </button>
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
                                        <button class="btn btn-sm btn-warning flex-fill" onclick='editCourse(<?php echo json_encode($course); ?>)'>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="/admin/lessons.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-info flex-fill">
                                            <i class="fas fa-book"></i> Lessons
                                        </a>
                                        <a href="?delete=<?php echo $course['course_id']; ?>"
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

<!-- Add/Edit Course Modal -->
<div class="modal fade" id="courseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="course_id" id="course_id" value="0">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="course_title" class="form-label">Course Title *</label>
                            <input type="text" class="form-control" id="course_title" name="course_title" required>
                        </div>

                        <div class="col-md-6 mb-3">
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
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function clearForm() {
    document.getElementById('course_id').value = '0';
    document.getElementById('course_title').value = '';
    document.getElementById('subject_id').value = '';
    document.getElementById('description').value = '';
    document.getElementById('difficulty').value = 'beginner';
    document.getElementById('required_level').value = '1';
    document.getElementById('estimated_duration').value = '60';
    document.getElementById('xp_reward').value = '100';
    document.getElementById('is_published').checked = true;
    document.getElementById('modalTitle').textContent = 'Add New Course';
}

function editCourse(course) {
    document.getElementById('course_id').value = course.course_id;
    document.getElementById('course_title').value = course.course_title;
    document.getElementById('subject_id').value = course.subject_id;
    document.getElementById('description').value = course.description || '';
    document.getElementById('difficulty').value = course.difficulty;
    document.getElementById('required_level').value = course.required_level;
    document.getElementById('estimated_duration').value = course.estimated_duration;
    document.getElementById('xp_reward').value = course.xp_reward;
    document.getElementById('is_published').checked = course.is_published == 1;
    document.getElementById('modalTitle').textContent = 'Edit Course';

    const modal = new bootstrap.Modal(document.getElementById('courseModal'));
    modal.show();
}

<?php if ($edit_course): ?>
editCourse(<?php echo json_encode($edit_course); ?>);
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
