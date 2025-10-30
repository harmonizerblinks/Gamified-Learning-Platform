<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Edit Level - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$level_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

// Fetch level data
$stmt = $conn->prepare("
    SELECT l.*,
           COUNT(DISTINCT u.user_id) as user_count
    FROM levels l
    LEFT JOIN users u ON l.level_number = u.current_level
    WHERE l.level_id = ?
    GROUP BY l.level_id
");
$stmt->execute([$level_id]);
$level = $stmt->fetch();

if (!$level) {
    redirect('/admin/levels/');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $description = isset($_POST['description']) ? clean_input($_POST['description']) : '';
    $xp_required = isset($_POST['xp_required']) ? (int)$_POST['xp_required'] : 0;
    $total_xp_required = isset($_POST['total_xp_required']) ? (int)$_POST['total_xp_required'] : 0;
    $unlocks = isset($_POST['unlocks']) ? clean_input($_POST['unlocks']) : '';

    // Validation
    if (empty($title)) {
        $errors[] = 'Level title is required.';
    }
    if ($xp_required < 0) {
        $errors[] = 'XP required cannot be negative.';
    }
    if ($total_xp_required < 0) {
        $errors[] = 'Total XP required cannot be negative.';
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                UPDATE levels
                SET title = ?, description = ?, xp_required = ?, total_xp_required = ?, unlocks = ?
                WHERE level_id = ?
            ");

            $stmt->execute([
                $title,
                $description,
                $xp_required,
                $total_xp_required,
                $unlocks,
                $level_id
            ]);

            redirect('/admin/levels/?success=updated');
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

$page_title = "Edit Level";
include '../../includes/header.php';
include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit Level <?php echo $level['level_number']; ?></h2>
            <a href="/admin/levels/" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Levels
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
                        <form method="POST">
                            <!-- Level Title -->
                            <div class="mb-3">
                                <label for="title" class="form-label">Level Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="<?php echo htmlspecialchars($level['title']); ?>"
                                       required maxlength="50">
                                <small class="text-muted">e.g., Beginner, Expert, Master, Legend</small>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($level['description']); ?></textarea>
                                <small class="text-muted">Optional description of this level</small>
                            </div>

                            <div class="row">
                                <!-- XP Required (for this level) -->
                                <div class="col-md-6 mb-3">
                                    <label for="xp_required" class="form-label">XP Required (This Level) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="xp_required" name="xp_required"
                                           value="<?php echo $level['xp_required']; ?>" min="0" required>
                                    <small class="text-muted">XP needed to reach this level from previous</small>
                                </div>

                                <!-- Total XP Required -->
                                <div class="col-md-6 mb-3">
                                    <label for="total_xp_required" class="form-label">Total XP Required <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="total_xp_required" name="total_xp_required"
                                           value="<?php echo $level['total_xp_required']; ?>" min="0" required>
                                    <small class="text-muted">Cumulative XP from Level 1</small>
                                </div>
                            </div>

                            <!-- Unlocks -->
                            <div class="mb-3">
                                <label for="unlocks" class="form-label">Unlocks / Benefits</label>
                                <textarea class="form-control" id="unlocks" name="unlocks" rows="3"><?php echo htmlspecialchars($level['unlocks']); ?></textarea>
                                <small class="text-muted">What features, content, or perks become available at this level</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-purple">
                                    <i class="fas fa-save me-2"></i>Update Level
                                </button>
                                <a href="/admin/levels/" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Level Info</h5>
                        <table class="table table-sm mb-0">
                            <tr>
                                <th>Level Number:</th>
                                <td><span class="badge bg-purple">Level <?php echo $level['level_number']; ?></span></td>
                            </tr>
                            <tr>
                                <th>Users at Level:</th>
                                <td><strong><?php echo $level['user_count']; ?></strong> users</td>
                            </tr>
                            <tr>
                                <th>Level ID:</th>
                                <td><?php echo $level['level_id']; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-calculator me-2"></i>XP Calculation</h5>
                        <p class="small mb-2">
                            <strong>XP Required:</strong> XP needed to advance from the previous level to this level.
                        </p>
                        <p class="small mb-0">
                            <strong>Total XP Required:</strong> Sum of all XP from Level 1 to reach this level.
                        </p>
                        <hr>
                        <p class="small text-muted mb-0">
                            Example: If Level 2 requires 100 XP and Level 3 requires 200 XP:
                            <br>" Level 3 XP Required: 200
                            <br>" Level 3 Total XP: 100 + 200 = 300
                        </p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Warning</h5>
                        <ul class="small mb-0">
                            <li>Changing XP requirements affects level progression for all users</li>
                            <li>Users may level up or down based on new requirements</li>
                            <li>Keep total XP in ascending order</li>
                            <li>Test changes carefully before saving</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
