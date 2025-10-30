<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Add New Badge - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$errors = [];

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

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO badges (badge_name, description, badge_icon, badge_type, requirement, requirement_value, xp_reward, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $badge_name,
                $description,
                $badge_icon,
                $badge_type,
                $requirement,
                $requirement_value,
                $xp_reward,
                $is_active
            ]);

            redirect('/admin/badges/?success=added');
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// $page_title = "Add New Badge";
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
                    <h2><i class="fas fa-plus-circle me-2"></i>Add New Badge</h2>
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
                                            value="<?php echo isset($_POST['badge_name']) ? htmlspecialchars($_POST['badge_name']) : ''; ?>"
                                            required maxlength="100">
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                        <small class="text-muted">Brief description of what this badge represents</small>
                                    </div>

                                    <div class="row">
                                        <!-- Badge Icon -->
                                        <div class="col-md-6 mb-3">
                                            <label for="badge_icon" class="form-label">Badge Icon (Font Awesome Class)</label>
                                            <input type="text" class="form-control" id="badge_icon" name="badge_icon"
                                                value="<?php echo isset($_POST['badge_icon']) ? htmlspecialchars($_POST['badge_icon']) : 'fas fa-medal'; ?>"
                                                placeholder="fas fa-medal">
                                            <small class="text-muted">Example: fas fa-medal, fas fa-trophy, fas fa-star</small>
                                        </div>

                                        <!-- Badge Type -->
                                        <div class="col-md-6 mb-3">
                                            <label for="badge_type" class="form-label">Badge Type <span class="text-danger">*</span></label>
                                            <select name="badge_type" id="badge_type" class="form-select" required>
                                                <option value="course" <?php echo (isset($_POST['badge_type']) && $_POST['badge_type'] == 'course') ? 'selected' : ''; ?>>Course</option>
                                                <option value="quiz" <?php echo (isset($_POST['badge_type']) && $_POST['badge_type'] == 'quiz') ? 'selected' : ''; ?>>Quiz</option>
                                                <option value="streak" <?php echo (isset($_POST['badge_type']) && $_POST['badge_type'] == 'streak') ? 'selected' : ''; ?>>Streak</option>
                                                <option value="level" <?php echo (isset($_POST['badge_type']) && $_POST['badge_type'] == 'level') ? 'selected' : ''; ?>>Level</option>
                                                <option value="special" <?php echo (isset($_POST['badge_type']) && $_POST['badge_type'] == 'special') ? 'selected' : ''; ?>>Special</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Requirement -->
                                    <div class="mb-3">
                                        <label for="requirement" class="form-label">Requirement Description <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="requirement" name="requirement"
                                            value="<?php echo isset($_POST['requirement']) ? htmlspecialchars($_POST['requirement']) : ''; ?>"
                                            required maxlength="255"
                                            placeholder="e.g., Complete 5 courses, Reach Level 10, 7-day login streak">
                                    </div>

                                    <div class="row">
                                        <!-- Requirement Value -->
                                        <div class="col-md-6 mb-3">
                                            <label for="requirement_value" class="form-label">Requirement Value (Numeric)</label>
                                            <input type="number" class="form-control" id="requirement_value" name="requirement_value"
                                                value="<?php echo isset($_POST['requirement_value']) ? $_POST['requirement_value'] : ''; ?>"
                                                min="1" placeholder="e.g., 5, 10, 100">
                                            <small class="text-muted">Leave empty if not numeric</small>
                                        </div>

                                        <!-- XP Reward -->
                                        <div class="col-md-6 mb-3">
                                            <label for="xp_reward" class="form-label">XP Reward</label>
                                            <input type="number" class="form-control" id="xp_reward" name="xp_reward"
                                                value="<?php echo isset($_POST['xp_reward']) ? $_POST['xp_reward'] : '50'; ?>"
                                                min="0">
                                            <small class="text-muted">XP awarded when badge is earned (default: 50)</small>
                                        </div>
                                    </div>

                                    <!-- Active Status -->
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                                <?php echo (!isset($_POST) || isset($_POST['is_active'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">Badge is Active</label>
                                        </div>
                                        <small class="text-muted">Inactive badges cannot be earned by users</small>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-purple">
                                            <i class="fas fa-save me-2"></i>Add Badge
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
                                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Badge Types</h5>
                                <ul class="small mb-0">
                                    <li><strong>Course:</strong> Earned by completing courses</li>
                                    <li><strong>Quiz:</strong> Earned by passing quizzes</li>
                                    <li><strong>Streak:</strong> Earned by maintaining daily streaks</li>
                                    <li><strong>Level:</strong> Earned by reaching certain levels</li>
                                    <li><strong>Special:</strong> Unique achievements</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-icons me-2"></i>Icon Examples</h5>
                                <div class="row text-center">
                                    <div class="col-4 mb-2">
                                        <i class="fas fa-medal fa-2x text-warning"></i>
                                        <br><small>fa-medal</small>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <i class="fas fa-trophy fa-2x text-warning"></i>
                                        <br><small>fa-trophy</small>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <i class="fas fa-star fa-2x text-warning"></i>
                                        <br><small>fa-star</small>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <i class="fas fa-crown fa-2x text-warning"></i>
                                        <br><small>fa-crown</small>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <i class="fas fa-award fa-2x text-warning"></i>
                                        <br><small>fa-award</small>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <i class="fas fa-gem fa-2x text-warning"></i>
                                        <br><small>fa-gem</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-lightbulb me-2"></i>Tips</h5>
                                <ul class="small mb-0">
                                    <li>Make badge names descriptive and motivating</li>
                                    <li>Set clear, achievable requirements</li>
                                    <li>Higher XP for more difficult badges</li>
                                    <li>Use Font Awesome icons for consistency</li>
                                    <li>Test badge requirements before activating</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
