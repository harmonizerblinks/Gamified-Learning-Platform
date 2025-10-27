<?php
require_once '../includes/header.php';
$page_title = "Manage Badges - " . SITE_NAME;
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Handle Delete
if (isset($_GET['delete'])) {
    $badge_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM badges WHERE badge_id = ?");
    if ($stmt->execute([$badge_id])) {
        set_success('Badge deleted successfully');
    } else {
        set_error('Failed to delete badge');
    }
    redirect('/admin/badges.php');
}

// Handle Add/Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $badge_id = isset($_POST['badge_id']) ? (int)$_POST['badge_id'] : 0;
    $badge_name = clean_input($_POST['badge_name']);
    $description = clean_input($_POST['description']);
    $badge_icon = clean_input($_POST['badge_icon']);
    $badge_type = clean_input($_POST['badge_type']);
    $requirement = clean_input($_POST['requirement']);
    $requirement_value = (int)$_POST['requirement_value'];
    $xp_reward = (int)$_POST['xp_reward'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($badge_name)) {
        set_error('Badge name is required');
    } else {
        if ($badge_id > 0) {
            // Update existing badge
            $stmt = $conn->prepare("
                UPDATE badges SET
                    badge_name = ?, description = ?, badge_icon = ?, badge_type = ?,
                    requirement = ?, requirement_value = ?, xp_reward = ?, is_active = ?
                WHERE badge_id = ?
            ");
            if ($stmt->execute([$badge_name, $description, $badge_icon, $badge_type, $requirement, $requirement_value, $xp_reward, $is_active, $badge_id])) {
                set_success('Badge updated successfully');
            } else {
                set_error('Failed to update badge');
            }
        } else {
            // Add new badge
            $stmt = $conn->prepare("
                INSERT INTO badges
                (badge_name, description, badge_icon, badge_type, requirement, requirement_value, xp_reward, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$badge_name, $description, $badge_icon, $badge_type, $requirement, $requirement_value, $xp_reward, $is_active])) {
                set_success('Badge added successfully');
            } else {
                set_error('Failed to add badge');
            }
        }
        redirect('/admin/badges.php');
    }
}

// Get badge for editing
$edit_badge = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM badges WHERE badge_id = ?");
    $stmt->execute([$edit_id]);
    $edit_badge = $stmt->fetch();
}

// Get all badges with stats
$stmt = $conn->query("
    SELECT b.*,
           COUNT(DISTINCT ub.user_id) as users_earned
    FROM badges b
    LEFT JOIN user_badges ub ON b.badge_id = ub.badge_id
    GROUP BY b.badge_id
    ORDER BY b.badge_type ASC, b.requirement_value ASC
");
$badges = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-medal me-2"></i>Manage Badges</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#badgeModal" onclick="clearForm()">
                    <i class="fas fa-plus me-2"></i>Add New Badge
                </button>
            </div>

            <?php display_messages(); ?>

            <!-- Badges Grid -->
            <?php if (empty($badges)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-medal fa-4x text-muted mb-3"></i>
                        <h5>No badges found</h5>
                        <p class="text-muted">Add your first badge to reward learners!</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($badges as $badge): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 <?php echo !$badge['is_active'] ? 'opacity-50' : ''; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="badge-icon-large text-center">
                                            <?php if ($badge['badge_icon']): ?>
                                                <i class="<?php echo htmlspecialchars($badge['badge_icon']); ?> fa-4x text-warning"></i>
                                            <?php else: ?>
                                                <i class="fas fa-medal fa-4x text-warning"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <?php
                                            $type_colors = [
                                                'course' => 'primary',
                                                'quiz' => 'success',
                                                'streak' => 'danger',
                                                'level' => 'purple',
                                                'special' => 'warning'
                                            ];
                                            $color = $type_colors[$badge['badge_type']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($badge['badge_type']); ?></span>
                                            <?php if (!$badge['is_active']): ?>
                                                <br><span class="badge bg-secondary mt-1">Inactive</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <h5 class="card-title"><?php echo htmlspecialchars($badge['badge_name']); ?></h5>
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars($badge['description']); ?>
                                    </p>

                                    <div class="border-top pt-3 mt-3">
                                        <div class="row text-center small mb-2">
                                            <div class="col-6">
                                                <strong>Requirement:</strong><br>
                                                <?php echo htmlspecialchars($badge['requirement']); ?>
                                            </div>
                                            <div class="col-6">
                                                <strong>Value:</strong><br>
                                                <?php echo $badge['requirement_value']; ?>
                                            </div>
                                        </div>
                                        <div class="row text-center small">
                                            <div class="col-6">
                                                <strong>XP Reward:</strong><br>
                                                <span class="text-warning"><i class="fas fa-star"></i> <?php echo $badge['xp_reward']; ?></span>
                                            </div>
                                            <div class="col-6">
                                                <strong>Users Earned:</strong><br>
                                                <span class="text-success"><i class="fas fa-users"></i> <?php echo $badge['users_earned']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-warning flex-fill" onclick='editBadge(<?php echo json_encode($badge); ?>)'>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="?delete=<?php echo $badge['badge_id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Delete this badge?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Add/Edit Badge Modal -->
<div class="modal fade" id="badgeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Badge</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="badge_id" id="badge_id" value="0">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="badge_name" class="form-label">Badge Name *</label>
                            <input type="text" class="form-control" id="badge_name" name="badge_name" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="badge_type" class="form-label">Badge Type *</label>
                            <select class="form-select" id="badge_type" name="badge_type" required>
                                <option value="course">Course</option>
                                <option value="quiz">Quiz</option>
                                <option value="streak">Streak</option>
                                <option value="level">Level</option>
                                <option value="special">Special</option>
                            </select>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="badge_icon" class="form-label">Icon Class (Font Awesome)</label>
                            <input type="text" class="form-control" id="badge_icon" name="badge_icon" placeholder="e.g., fas fa-trophy">
                            <small class="text-muted">Visit <a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a> for icons</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="xp_reward" class="form-label">XP Reward</label>
                            <input type="number" class="form-control" id="xp_reward" name="xp_reward" value="0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="requirement" class="form-label">Requirement Description</label>
                            <input type="text" class="form-control" id="requirement" name="requirement" placeholder="e.g., Complete 5 courses">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="requirement_value" class="form-label">Requirement Value</label>
                            <input type="number" class="form-control" id="requirement_value" name="requirement_value" value="1">
                            <small class="text-muted">Numeric threshold to earn this badge</small>
                        </div>

                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Active (Badge can be earned)
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Badge Types:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Course:</strong> Earned by completing courses (requirement_value = number of courses)</li>
                                    <li><strong>Quiz:</strong> Earned by passing quizzes (requirement_value = number of quizzes)</li>
                                    <li><strong>Streak:</strong> Earned by login streaks (requirement_value = number of days)</li>
                                    <li><strong>Level:</strong> Earned by reaching levels (requirement_value = level number)</li>
                                    <li><strong>Special:</strong> Manually awarded special badges</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Badge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function clearForm() {
    document.getElementById('badge_id').value = '0';
    document.getElementById('badge_name').value = '';
    document.getElementById('badge_type').value = 'course';
    document.getElementById('description').value = '';
    document.getElementById('badge_icon').value = '';
    document.getElementById('requirement').value = '';
    document.getElementById('requirement_value').value = '1';
    document.getElementById('xp_reward').value = '0';
    document.getElementById('is_active').checked = true;
    document.getElementById('modalTitle').textContent = 'Add New Badge';
}

function editBadge(badge) {
    document.getElementById('badge_id').value = badge.badge_id;
    document.getElementById('badge_name').value = badge.badge_name;
    document.getElementById('badge_type').value = badge.badge_type;
    document.getElementById('description').value = badge.description || '';
    document.getElementById('badge_icon').value = badge.badge_icon || '';
    document.getElementById('requirement').value = badge.requirement || '';
    document.getElementById('requirement_value').value = badge.requirement_value;
    document.getElementById('xp_reward').value = badge.xp_reward;
    document.getElementById('is_active').checked = badge.is_active == 1;
    document.getElementById('modalTitle').textContent = 'Edit Badge';

    const modal = new bootstrap.Modal(document.getElementById('badgeModal'));
    modal.show();
}

<?php if ($edit_badge): ?>
editBadge(<?php echo json_encode($edit_badge); ?>);
<?php endif; ?>
</script>

<style>
.bg-purple {
    background-color: #8B5CF6 !important;
}
</style>

<?php require_once '../includes/footer.php'; ?>
