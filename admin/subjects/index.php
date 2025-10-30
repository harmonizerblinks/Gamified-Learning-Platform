<?php
require_once '../../includes/header.php';
require_login();
$page_title = "Manage Subjects - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get all subjects with course count
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

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../../includes/admin-sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-book me-2"></i>Manage Subjects</h1>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Subject
                    </a>
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
                                                    <a href="edit.php?id=<?php echo $subject['subject_id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?php echo $subject['subject_id']; ?>"
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
</div>

<?php require_once '../../includes/footer.php'; ?>
