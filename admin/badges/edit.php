<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Edit Badge - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$badge_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

// Fetch badge data
$stmt = $conn->prepare("
    SELECT b.*,
           COUNT(DISTINCT ub.user_id) as earned_count
    FROM badges b
    LEFT JOIN user_badges ub ON b.badge_id = ub.badge_id
    WHERE b.badge_id = ?
    GROUP BY b.badge_id
");
$stmt->execute([$badge_id]);
$badge = $stmt->fetch();

if (!$badge) {
    redirect('/admin/badges/');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $badge_name = clean_input($_POST['badge_name']);
    $description = clean_input($_POST['description']);
    $badge_icon = clean_input($_POST['badge_icon']);
    $badge_type = clean_input($_POST['badge_type']);
    $requirement = clean_input($_POST['requirement']);
    $requirement_value = isset($_POST['requirement_value']) && !empty($_POST['requirement_value']) ? (int)$_POST['requirement_value'] : null;
    $xp_reward = isset($_POST['xp_reward']) ? (int)$_POST['xp_reward'] : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if (empty($badge_name)) {
        $errors[] = 'Badge name is required.';
    }
    if (!in_array($badge_type, ['course', 'quiz', 'streak', 'level', 'special'])) {
        $errors[] = 'Invalid badge type.';
    }
    if (empty($requirement)) {
        $errors[] = 'Requirement description is required.';
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                UPDATE badges
                SET badge_name = ?, description = ?, badge_icon = ?, badge_type = ?,
                    requirement = ?, requirement_value = ?, xp_reward = ?, is_active = ?
                WHERE badge_id = ?
            ");

            $stmt->execute([
                $badge_name,
                $description,
                $badge_icon,
                $badge_type,
                $requirement,
                $requirement_value,
                $xp_reward,
                $is_active,
                $badge_id
            ]);

            redirect('/admin/badges/?success=updated');
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// $page_title = "Edit Badge";
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
                    <h2><i class="fas fa-edit me-2"></i>Edit Badge</h2>
                    <a href="/admin/badges/" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Badges
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
                                    <!-- Badge Name -->
                                    <div class="mb-3">
                                        <label for="badge_name" class="form-label">Badge Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="badge_name" name="badge_name"
                                            value="<?php echo htmlspecialchars($badge['badge_name']); ?>"
                                            required maxlength="100">
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($badge['description']); ?></textarea>
                                    </div>

                                    <div class="row">
                                        <!-- Badge Icon -->
                                        <div class="col-md-6 mb-3">
                                            <label for="badge_icon" class="form-label">Badge Icon (Font Awesome Class)</label>
                                            <input type="text" class="form-control" id="badge_icon" name="badge_icon"
                                                value="<?php echo htmlspecialchars($badge['badge_icon']); ?>">
                                            <small class="text-muted">Example: fas fa-medal, fas fa-trophy</small>
                                        </div>

                                        <!-- Badge Type -->
                                        <div class="col-md-6 mb-3">
                                            <label for="badge_type" class="form-label">Badge Type <span class="text-danger">*</span></label>
                                            <select name="badge_type" id="badge_type" class="form-select" required>
                                                <option value="course" <?php echo $badge['badge_type'] == 'course' ? 'selected' : ''; ?>>Course</option>
                                                <option value="quiz" <?php echo $badge['badge_type'] == 'quiz' ? 'selected' : ''; ?>>Quiz</option>
                                                <option value="streak" <?php echo $badge['badge_type'] == 'streak' ? 'selected' : ''; ?>>Streak</option>
                                                <option value="level" <?php echo $badge['badge_type'] == 'level' ? 'selected' : ''; ?>>Level</option>
                                                <option value="special" <?php echo $badge['badge_type'] == 'special' ? 'selected' : ''; ?>>Special</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Requirement -->
                                    <div class="mb-3">
                                        <label for="requirement" class="form-label">Requirement Description <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="requirement" name="requirement"
                                            value="<?php echo htmlspecialchars($badge['requirement']); ?>"
                                            required maxlength="255">
                                    </div>

                                    <div class="row">
                                        <!-- Requirement Value -->
                                        <div class="col-md-6 mb-3">
                                            <label for="requirement_value" class="form-label">Requirement Value (Numeric)</label>
                                            <input type="number" class="form-control" id="requirement_value" name="requirement_value"
                                                value="<?php echo $badge['requirement_value']; ?>" min="1">
                                        </div>

                                        <!-- XP Reward -->
                                        <div class="col-md-6 mb-3">
                                            <label for="xp_reward" class="form-label">XP Reward</label>
                                            <input type="number" class="form-control" id="xp_reward" name="xp_reward"
                                                value="<?php echo $badge['xp_reward']; ?>" min="0">
                                        </div>
                                    </div>

                                    <!-- Active Status -->
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                                <?php echo $badge['is_active'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">Badge is Active</label>
                                        </div>
                                        <small class="text-muted">Inactive badges cannot be earned by users</small>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-purple">
                                            <i class="fas fa-save me-2"></i>Update Badge
                                        </button>
                                        <a href="/admin/badges/" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Badge Info</h5>
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <th>Badge ID:</th>
                                        <td><?php echo $badge['badge_id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Created:</th>
                                        <td><?php echo date('M d, Y', strtotime($badge['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Times Earned:</th>
                                        <td><strong><?php echo $badge['earned_count']; ?></strong> users</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Warning</h5>
                                <p class="small mb-0">
                                    Changing badge requirements will not retroactively affect users who have already earned this badge.
                                    New users will need to meet the updated requirements.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
