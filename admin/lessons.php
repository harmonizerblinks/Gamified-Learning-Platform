<?php
require_once '../includes/header.php';
$page_title = "Manage Lessons - " . SITE_NAME;
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get course ID from URL or form
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Handle Delete
if (isset($_GET['delete'])) {
    $lesson_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM lessons WHERE lesson_id = ?");
    if ($stmt->execute([$lesson_id])) {
        set_success('Lesson deleted successfully');
    } else {
        set_error('Failed to delete lesson');
    }
    redirect('/admin/lessons.php?course_id=' . $course_id);
}

// Handle Add/Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
    $course_id = (int)$_POST['course_id'];
    $lesson_title = clean_input($_POST['lesson_title']);
    $lesson_order = (int)$_POST['lesson_order'];
    $lesson_type = clean_input($_POST['lesson_type']);
    $content_text = $_POST['content_text'] ?? '';
    $duration = (int)$_POST['duration'];
    $xp_reward = (int)$_POST['xp_reward'];

    // Handle file upload (video or PDF)
    $content_url = '';
    if (isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
        if ($lesson_type === 'video') {
            $uploaded = upload_file($_FILES['content_file'], UPLOAD_PATH . 'videos/', ALLOWED_VIDEO_EXT);
        } elseif ($lesson_type === 'pdf') {
            $uploaded = upload_file($_FILES['content_file'], UPLOAD_PATH . 'documents/', ['pdf']);
        }
        if ($uploaded) {
            $content_url = $uploaded;
        }
    } elseif ($lesson_id > 0) {
        // Keep existing content URL
        $stmt = $conn->prepare("SELECT content_url FROM lessons WHERE lesson_id = ?");
        $stmt->execute([$lesson_id]);
        $existing = $stmt->fetch();
        $content_url = $existing['content_url'];
    }

    if (empty($lesson_title) || $course_id == 0) {
        set_error('Lesson title and course are required');
    } else {
        if ($lesson_id > 0) {
            // Update existing lesson
            $stmt = $conn->prepare("
                UPDATE lessons SET
                    course_id = ?, lesson_title = ?, lesson_order = ?, lesson_type = ?,
                    content_url = ?, content_text = ?, duration = ?, xp_reward = ?
                WHERE lesson_id = ?
            ");
            if ($stmt->execute([$course_id, $lesson_title, $lesson_order, $lesson_type, $content_url, $content_text, $duration, $xp_reward, $lesson_id])) {
                set_success('Lesson updated successfully');
            } else {
                set_error('Failed to update lesson');
            }
        } else {
            // Add new lesson
            $stmt = $conn->prepare("
                INSERT INTO lessons
                (course_id, lesson_title, lesson_order, lesson_type, content_url, content_text, duration, xp_reward)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$course_id, $lesson_title, $lesson_order, $lesson_type, $content_url, $content_text, $duration, $xp_reward])) {
                set_success('Lesson added successfully');
            } else {
                set_error('Failed to add lesson');
            }
        }
        redirect('/admin/lessons.php?course_id=' . $course_id);
    }
}

// Get lesson for editing
$edit_lesson = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM lessons WHERE lesson_id = ?");
    $stmt->execute([$edit_id]);
    $edit_lesson = $stmt->fetch();
    if ($edit_lesson) {
        $course_id = $edit_lesson['course_id'];
    }
}

// Get course information if course_id is set
$course = null;
if ($course_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
}

// Get all courses for dropdown
$courses = $conn->query("SELECT * FROM courses ORDER BY course_title ASC")->fetchAll();

// Get lessons for selected course
$lessons = [];
if ($course_id > 0) {
    $stmt = $conn->prepare("
        SELECT l.*,
               COUNT(DISTINCT ulp.user_id) as completion_count
        FROM lessons l
        LEFT JOIN user_lesson_progress ulp ON l.lesson_id = ulp.lesson_id AND ulp.is_completed = 1
        WHERE l.course_id = ?
        GROUP BY l.lesson_id
        ORDER BY l.lesson_order ASC
    ");
    $stmt->execute([$course_id]);
    $lessons = $stmt->fetchAll();
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-book-open me-2"></i>Manage Lessons</h1>
                    <?php if ($course): ?>
                        <p class="text-muted mb-0">
                            <a href="/admin/courses.php" class="text-decoration-none">Courses</a> /
                            <?php echo htmlspecialchars($course['course_title']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <?php if ($course_id > 0): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#lessonModal" onclick="clearForm()">
                        <i class="fas fa-plus me-2"></i>Add New Lesson
                    </button>
                <?php endif; ?>
            </div>

            <?php display_messages(); ?>

            <!-- Course Selector -->
            <?php if ($course_id == 0): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
                        <h5>Select a Course</h5>
                        <p class="text-muted mb-4">Choose a course to view and manage its lessons</p>
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <select class="form-select" onchange="if(this.value) window.location.href='/admin/lessons.php?course_id=' + this.value">
                                    <option value="">-- Select Course --</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?php echo $c['course_id']; ?>"><?php echo htmlspecialchars($c['course_title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Lessons Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <?php if (empty($lessons)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
                                <h5>No lessons found</h5>
                                <p class="text-muted">Add your first lesson to this course!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="60">Order</th>
                                            <th>Lesson Title</th>
                                            <th class="text-center">Type</th>
                                            <th class="text-center">Duration</th>
                                            <th class="text-center">XP</th>
                                            <th class="text-center">Completions</th>
                                            <th width="150" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lessons as $lesson): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">#<?php echo $lesson['lesson_order']; ?></span>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($lesson['lesson_title']); ?></strong>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $type_icons = [
                                                        'video' => '<i class="fas fa-video text-danger"></i> Video',
                                                        'text' => '<i class="fas fa-align-left text-primary"></i> Text',
                                                        'pdf' => '<i class="fas fa-file-pdf text-danger"></i> PDF',
                                                        'mixed' => '<i class="fas fa-layer-group text-info"></i> Mixed'
                                                    ];
                                                    echo $type_icons[$lesson['lesson_type']] ?? $lesson['lesson_type'];
                                                    ?>
                                                </td>
                                                <td class="text-center"><?php echo $lesson['duration']; ?> min</td>
                                                <td class="text-center">
                                                    <span class="text-warning"><i class="fas fa-star"></i> <?php echo $lesson['xp_reward']; ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info"><?php echo $lesson['completion_count']; ?> users</span>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-warning" onclick='editLesson(<?php echo json_encode($lesson); ?>)'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?course_id=<?php echo $course_id; ?>&delete=<?php echo $lesson['lesson_id']; ?>"
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Are you sure you want to delete this lesson?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Add/Edit Lesson Modal -->
<div class="modal fade" id="lessonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Lesson</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="lesson_id" id="lesson_id" value="0">
                    <input type="hidden" name="course_id" id="course_id_hidden" value="<?php echo $course_id; ?>">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="lesson_title" class="form-label">Lesson Title *</label>
                            <input type="text" class="form-control" id="lesson_title" name="lesson_title" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="lesson_order" class="form-label">Order *</label>
                            <input type="number" class="form-control" id="lesson_order" name="lesson_order" min="1" value="<?php echo count($lessons) + 1; ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="lesson_type" class="form-label">Lesson Type *</label>
                            <select class="form-select" id="lesson_type" name="lesson_type" onchange="toggleContentFields()" required>
                                <option value="text">Text</option>
                                <option value="video">Video</option>
                                <option value="pdf">PDF</option>
                                <option value="mixed">Mixed</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="duration" class="form-label">Duration (min)</label>
                            <input type="number" class="form-control" id="duration" name="duration" value="10">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="xp_reward" class="form-label">XP Reward</label>
                            <input type="number" class="form-control" id="xp_reward" name="xp_reward" value="10">
                        </div>

                        <div class="col-12 mb-3" id="file_upload_section">
                            <label for="content_file" class="form-label">Upload File (Video/PDF)</label>
                            <input type="file" class="form-control" id="content_file" name="content_file">
                            <small class="text-muted">Max 50MB</small>
                        </div>

                        <div class="col-12 mb-3" id="text_content_section">
                            <label for="content_text" class="form-label">Text Content</label>
                            <textarea class="form-control" id="content_text" name="content_text" rows="6"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Lesson</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleContentFields() {
    const type = document.getElementById('lesson_type').value;
    const fileSection = document.getElementById('file_upload_section');
    const textSection = document.getElementById('text_content_section');

    if (type === 'video' || type === 'pdf') {
        fileSection.style.display = 'block';
        textSection.style.display = 'none';
    } else if (type === 'text') {
        fileSection.style.display = 'none';
        textSection.style.display = 'block';
    } else {
        fileSection.style.display = 'block';
        textSection.style.display = 'block';
    }
}

function clearForm() {
    document.getElementById('lesson_id').value = '0';
    document.getElementById('lesson_title').value = '';
    document.getElementById('lesson_type').value = 'text';
    document.getElementById('lesson_order').value = '<?php echo count($lessons) + 1; ?>';
    document.getElementById('duration').value = '10';
    document.getElementById('xp_reward').value = '10';
    document.getElementById('content_text').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Lesson';
    toggleContentFields();
}

function editLesson(lesson) {
    document.getElementById('lesson_id').value = lesson.lesson_id;
    document.getElementById('lesson_title').value = lesson.lesson_title;
    document.getElementById('lesson_type').value = lesson.lesson_type;
    document.getElementById('lesson_order').value = lesson.lesson_order;
    document.getElementById('duration').value = lesson.duration;
    document.getElementById('xp_reward').value = lesson.xp_reward;
    document.getElementById('content_text').value = lesson.content_text || '';
    document.getElementById('modalTitle').textContent = 'Edit Lesson';
    toggleContentFields();

    const modal = new bootstrap.Modal(document.getElementById('lessonModal'));
    modal.show();
}

toggleContentFields();

<?php if ($edit_lesson): ?>
editLesson(<?php echo json_encode($edit_lesson); ?>);
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
