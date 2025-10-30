<?php
$page_title = "Manage Lessons - " . SITE_NAME;
require_once '../../includes/header.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get filter parameters
$course_filter = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Fetch lessons with course info
$query = "
    SELECT l.*, c.course_title, s.subject_name
    FROM lessons l
    INNER JOIN courses c ON l.course_id = c.course_id
    INNER JOIN subjects s ON c.subject_id = s.subject_id
";

if ($course_filter > 0) {
    $query .= " WHERE l.course_id = ?";
}

$query .= " ORDER BY c.course_title ASC, l.lesson_order ASC";

if ($course_filter > 0) {
    $stmt = $conn->prepare($query);
    $stmt->execute([$course_filter]);
} else {
    $stmt = $conn->query($query);
}
$lessons = $stmt->fetchAll();

// Fetch all courses for filter dropdown
$courses_stmt = $conn->query("
    SELECT c.course_id, c.course_title, s.subject_name
    FROM courses c
    INNER JOIN subjects s ON c.subject_id = s.subject_id
    ORDER BY s.subject_name ASC, c.course_title ASC
");
$courses = $courses_stmt->fetchAll();

$page_title = "Manage Lessons";
include '../../includes/header.php';
include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-book-reader me-2"></i>Manage Lessons</h2>
            <a href="/admin/lessons/add.php" class="btn btn-purple">
                <i class="fas fa-plus me-2"></i>Add New Lesson
            </a>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="course_id" class="form-label">Filter by Course</label>
                        <select name="course_id" id="course_id" class="form-select" onchange="this.form.submit()">
                            <option value="0">All Courses</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['course_id']; ?>"
                                    <?php echo $course_filter == $course['course_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['subject_name'] . ' - ' . $course['course_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($course_filter > 0): ?>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="/admin/lessons/" class="btn btn-secondary">Clear Filter</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                    switch($_GET['success']) {
                        case 'added':
                            echo 'Lesson added successfully!';
                            break;
                        case 'updated':
                            echo 'Lesson updated successfully!';
                            break;
                        case 'deleted':
                            echo 'Lesson deleted successfully!';
                            break;
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($lessons)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No lessons found. <?php echo $course_filter > 0 ? 'Try selecting a different course.' : 'Start by adding a new lesson.'; ?>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Order</th>
                                    <th>Lesson Title</th>
                                    <th>Course</th>
                                    <th>Type</th>
                                    <th>Duration</th>
                                    <th>XP Reward</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lessons as $lesson): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">#<?php echo $lesson['lesson_order']; ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lesson['lesson_title']); ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo htmlspecialchars($lesson['subject_name']); ?></small><br>
                                        <?php echo htmlspecialchars($lesson['course_title']); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $type_icons = [
                                            'video' => '<i class="fas fa-video text-danger"></i>',
                                            'text' => '<i class="fas fa-file-alt text-primary"></i>',
                                            'pdf' => '<i class="fas fa-file-pdf text-danger"></i>',
                                            'mixed' => '<i class="fas fa-layer-group text-info"></i>'
                                        ];
                                        echo $type_icons[$lesson['lesson_type']] ?? '';
                                        echo ' ' . ucfirst($lesson['lesson_type']);
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($lesson['duration']): ?>
                                            <i class="fas fa-clock text-muted me-1"></i>
                                            <?php echo $lesson['duration']; ?> min
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-star me-1"></i><?php echo $lesson['xp_reward']; ?> XP
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($lesson['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="/admin/lessons/edit.php?id=<?php echo $lesson['lesson_id']; ?>"
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/admin/lessons/delete.php?id=<?php echo $lesson['lesson_id']; ?>"
                                               class="btn btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this lesson? This action cannot be undone.')"
                                               title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
