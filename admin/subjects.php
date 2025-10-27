<?php
$page_title = "Manage Subjects - " . SITE_NAME;
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
    $subject_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id = ?");
    if ($stmt->execute([$subject_id])) {
        set_success('Subject deleted successfully');
    } else {
        set_error('Failed to delete subject');
    }
    redirect('/admin/subjects.php');
}

// Handle Add/Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
    $subject_name = clean_input($_POST['subject_name']);
    $description = clean_input($_POST['description']);
    $icon = clean_input($_POST['icon']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($subject_name)) {
        set_error('Subject name is required');
    } else {
        if ($subject_id > 0) {
            // Update existing subject
            $stmt = $conn->prepare("UPDATE subjects SET subject_name = ?, description = ?, icon = ?, is_active = ? WHERE subject_id = ?");
            if ($stmt->execute([$subject_name, $description, $icon, $is_active, $subject_id])) {
                set_success('Subject updated successfully');
            } else {
                set_error('Failed to update subject');
            }
        } else {
            // Add new subject
            $stmt = $conn->prepare("INSERT INTO subjects (subject_name, description, icon, is_active) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$subject_name, $description, $icon, $is_active])) {
                set_success('Subject added successfully');
            } else {
                set_error('Failed to add subject');
            }
        }
        redirect('/admin/subjects.php');
    }
}

// Get subject for editing
$edit_subject = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_id = ?");
    $stmt->execute([$edit_id]);
    $edit_subject = $stmt->fetch();
}

// Get all subjects
$stmt = $conn->query("
    SELECT s.*,
           COUNT(DISTINCT c.course_id) as course_count
    FROM subjects s
    LEFT JOIN courses c ON s.subject_id = c.subject_id
    GROUP BY s.subject_id
    ORDER BY s.subject_name ASC
");
$subjects = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-book me-2"></i>Manage Subjects</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subjectModal" onclick="clearForm()">
                    <i class="fas fa-plus me-2"></i>Add New Subject
                </button>
            </div>

            <?php display_messages(); ?>

            <!-- Subjects Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="60">Icon</th>
                                    <th>Subject Name</th>
                                    <th>Description</th>
                                    <th class="text-center">Courses</th>
                                    <th class="text-center">Status</th>
                                    <th width="150" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($subjects)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No subjects found. Add your first subject!</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td class="text-center">
                                                <?php if ($subject['icon']): ?>
                                                    <i class="<?php echo htmlspecialchars($subject['icon']); ?> fa-2x text-primary"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-book fa-2x text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars(substr($subject['description'], 0, 100)); ?>
                                                <?php echo strlen($subject['description']) > 100 ? '...' : ''; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?php echo $subject['course_count']; ?> courses</span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($subject['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-warning" onclick="editSubject(<?php echo htmlspecialchars(json_encode($subject)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="?delete=<?php echo $subject['subject_id']; ?>"
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Are you sure you want to delete this subject?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add/Edit Subject Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="subject_id" id="subject_id" value="0">

                    <div class="mb-3">
                        <label for="subject_name" class="form-label">Subject Name *</label>
                        <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon Class (Font Awesome)</label>
                        <input type="text" class="form-control" id="icon" name="icon" placeholder="e.g., fas fa-code">
                        <small class="text-muted">Visit <a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a> for icon classes</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function clearForm() {
    document.getElementById('subject_id').value = '0';
    document.getElementById('subject_name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('icon').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('modalTitle').textContent = 'Add New Subject';
}

function editSubject(subject) {
    document.getElementById('subject_id').value = subject.subject_id;
    document.getElementById('subject_name').value = subject.subject_name;
    document.getElementById('description').value = subject.description || '';
    document.getElementById('icon').value = subject.icon || '';
    document.getElementById('is_active').checked = subject.is_active == 1;
    document.getElementById('modalTitle').textContent = 'Edit Subject';

    const modal = new bootstrap.Modal(document.getElementById('subjectModal'));
    modal.show();
}

<?php if ($edit_subject): ?>
editSubject(<?php echo json_encode($edit_subject); ?>);
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
