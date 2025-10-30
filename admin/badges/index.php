<?php
$page_title = "Manage Badges - " . SITE_NAME;
require_once '../../includes/header.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get filter parameters
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';

// Build query
$query = "
    SELECT b.*,
           COUNT(DISTINCT ub.user_id) as earned_count
    FROM badges b
    LEFT JOIN user_badges ub ON b.badge_id = ub.badge_id
";

if ($type_filter !== 'all') {
    $query .= " WHERE b.badge_type = ?";
}

$query .= " GROUP BY b.badge_id ORDER BY b.badge_type ASC, b.badge_name ASC";

if ($type_filter !== 'all') {
    $stmt = $conn->prepare($query);
    $stmt->execute([$type_filter]);
} else {
    $stmt = $conn->query($query);
}
$badges = $stmt->fetchAll();

$page_title = "Manage Badges";
include '../../includes/header.php';
include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-medal me-2"></i>Manage Badges</h2>
            <a href="/admin/badges/add.php" class="btn btn-purple">
                <i class="fas fa-plus me-2"></i>Add New Badge
            </a>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="type" class="form-label">Filter by Type</label>
                        <select name="type" id="type" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>All Types</option>
                            <option value="course" <?php echo $type_filter === 'course' ? 'selected' : ''; ?>>Course Badges</option>
                            <option value="quiz" <?php echo $type_filter === 'quiz' ? 'selected' : ''; ?>>Quiz Badges</option>
                            <option value="streak" <?php echo $type_filter === 'streak' ? 'selected' : ''; ?>>Streak Badges</option>
                            <option value="level" <?php echo $type_filter === 'level' ? 'selected' : ''; ?>>Level Badges</option>
                            <option value="special" <?php echo $type_filter === 'special' ? 'selected' : ''; ?>>Special Badges</option>
                        </select>
                    </div>
                    <?php if ($type_filter !== 'all'): ?>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="/admin/badges/" class="btn btn-secondary">Clear Filter</a>
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
                            echo 'Badge added successfully!';
                            break;
                        case 'updated':
                            echo 'Badge updated successfully!';
                            break;
                        case 'deleted':
                            echo 'Badge deleted successfully!';
                            break;
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($badges)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No badges found. <?php echo $type_filter !== 'all' ? 'Try selecting a different type.' : 'Start by adding a new badge.'; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($badges as $badge): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="badge-icon me-3">
                                    <?php if (!empty($badge['badge_icon'])): ?>
                                        <i class="<?php echo htmlspecialchars($badge['badge_icon']); ?> fa-3x text-warning"></i>
                                    <?php else: ?>
                                        <i class="fas fa-medal fa-3x text-warning"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php
                                    $badge_colors = [
                                        'course' => 'primary',
                                        'quiz' => 'success',
                                        'streak' => 'danger',
                                        'level' => 'purple',
                                        'special' => 'warning'
                                    ];
                                    $color = $badge_colors[$badge['badge_type']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>">
                                        <?php echo ucfirst($badge['badge_type']); ?>
                                    </span>
                                    <?php if (!$badge['is_active']): ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <h5 class="card-title"><?php echo htmlspecialchars($badge['badge_name']); ?></h5>

                            <?php if (!empty($badge['description'])): ?>
                            <p class="card-text small text-muted">
                                <?php echo htmlspecialchars($badge['description']); ?>
                            </p>
                            <?php endif; ?>

                            <div class="mb-3">
                                <div class="small mb-2">
                                    <strong>Requirement:</strong><br>
                                    <?php echo htmlspecialchars($badge['requirement']); ?>
                                </div>
                                <?php if ($badge['requirement_value']): ?>
                                <div class="small">
                                    <strong>Target Value:</strong>
                                    <span class="badge bg-info"><?php echo $badge['requirement_value']; ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="small">
                                        <i class="fas fa-star text-warning me-1"></i>
                                        <strong><?php echo $badge['xp_reward']; ?></strong> XP
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small">
                                        <i class="fas fa-users text-info me-1"></i>
                                        <strong><?php echo $badge['earned_count']; ?></strong> Earned
                                    </div>
                                </div>
                            </div>

                            <div class="btn-group w-100" role="group">
                                <a href="/admin/badges/edit.php?id=<?php echo $badge['badge_id']; ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                <a href="/admin/badges/delete.php?id=<?php echo $badge['badge_id']; ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to delete this badge? Users who earned it will lose it!')">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </a>
                            </div>
                        </div>
                        <div class="card-footer bg-light small text-muted">
                            Created: <?php echo date('M d, Y', strtotime($badge['created_at'])); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
