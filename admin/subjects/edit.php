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
$subject_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($subject_id == 0) {
    set_error('Invalid subject ID');
    redirect('/admin/subjects/');
}

// Get subject data
$stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_id = ?");
$stmt->execute([$subject_id]);
$subject = $stmt->fetch();

if (!$subject) {
    set_error('Subject not found');
    redirect('/admin/subjects/');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = clean_input($_POST['subject_name']);
    $description = clean_input($_POST['description']);
    $icon = clean_input($_POST['icon']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($subject_name)) {
        set_error('Subject name is required');
    } else {
        $stmt = $conn->prepare("UPDATE subjects SET subject_name = ?, description = ?, icon = ?, is_active = ? WHERE subject_id = ?");
        if ($stmt->execute([$subject_name, $description, $icon, $is_active, $subject_id])) {
            set_success('Subject updated successfully');
            redirect('/admin/subjects/');
        } else {
            set_error('Failed to update subject');
        }
    }
}

$page_title = "Edit Subject - " . SITE_NAME;
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
                    <h1><i class="fas fa-book me-2"></i>Edit Subject</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Subjects
                    </a>
                </div>

                <?php display_messages(); ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="subject_name" class="form-label">Subject Name *</label>
                                        <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($subject['description']); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="icon" class="form-label">Icon Class (Font Awesome)</label>
                                        <input type="text" class="form-control" id="icon" name="icon" value="<?php echo htmlspecialchars($subject['icon']); ?>" placeholder="e.g., fas fa-code">
                                        <small class="text-muted">Visit <a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a> for icon classes</small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $subject['is_active'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">
                                                Active
                                            </label>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Subject
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
