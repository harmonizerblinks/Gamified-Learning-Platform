<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Manage Levels - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Fetch all levels with user counts
$stmt = $conn->query("
    SELECT l.*,
           COUNT(DISTINCT u.user_id) as user_count
    FROM levels l
    LEFT JOIN users u ON l.level_number = u.current_level
    GROUP BY l.level_id
    ORDER BY l.level_number ASC
");
$levels = $stmt->fetchAll();

// $page_title = "Manage Levels";
// include '../../includes/header.php';
// include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">


<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../../includes/admin-sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-layer-group me-2"></i>Manage Levels</h2>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Level updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($levels)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No levels found in the database. Please run the database seed script to create default levels.
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Level</th>
                                            <th>Title</th>
                                            <th>XP Required (for this level)</th>
                                            <th>Total XP Required</th>
                                            <th>Users at this Level</th>
                                            <th>Unlocks</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($levels as $level): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-purple fs-6">
                                                    Level <?php echo $level['level_number']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($level['title']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-star me-1"></i>
                                                    <?php echo number_format($level['xp_required']); ?> XP
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">
                                                    <?php echo number_format($level['total_xp_required']); ?> XP
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-users me-1"></i>
                                                    <?php echo $level['user_count']; ?> users
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($level['unlocks'])): ?>
                                                    <small class="text-muted">
                                                        <?php
                                                        $unlocks = htmlspecialchars($level['unlocks']);
                                                        echo strlen($unlocks) > 50 ? substr($unlocks, 0, 50) . '...' : $unlocks;
                                                        ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">-</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="/admin/levels/edit.php?id=<?php echo $level['level_id']; ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Level Progress Visualization -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Level Progression System</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php
                                $colors = ['primary', 'info', 'success', 'warning', 'purple', 'danger', 'secondary', 'dark'];
                                foreach ($levels as $index => $level):
                                    $color = $colors[$index % count($colors)];
                                ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border-<?php echo $color; ?>">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h5 class="card-title text-<?php echo $color; ?> mb-0">
                                                    <i class="fas fa-trophy me-1"></i>
                                                    Level <?php echo $level['level_number']; ?>
                                                </h5>
                                                <span class="badge bg-<?php echo $color; ?>">
                                                    <?php echo $level['user_count']; ?> users
                                                </span>
                                            </div>
                                            <h6><?php echo htmlspecialchars($level['title']); ?></h6>
                                            <p class="small text-muted mb-2">
                                                <strong>XP Needed:</strong> <?php echo number_format($level['xp_required']); ?>
                                                <br>
                                                <strong>Total XP:</strong> <?php echo number_format($level['total_xp_required']); ?>
                                            </p>
                                            <?php if (!empty($level['unlocks'])): ?>
                                            <p class="small mb-0">
                                                <strong>Unlocks:</strong> <?php echo htmlspecialchars($level['unlocks']); ?>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>About Levels</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>How Levels Work</h6>
                                    <ul class="small">
                                        <li>Users earn XP by completing lessons, passing quizzes, and finishing courses</li>
                                        <li>Each level requires a certain amount of XP to unlock</li>
                                        <li>Total XP Required is cumulative from Level 1</li>
                                        <li>Users progress automatically when they reach the required XP</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Level Management</h6>
                                    <ul class="small">
                                        <li>You can edit level titles, XP requirements, and unlocks</li>
                                        <li>Be careful when changing XP requirements - it affects all users</li>
                                        <li>The "Unlocks" field describes what features/content become available</li>
                                        <li>Level numbers should remain sequential (1-10)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
