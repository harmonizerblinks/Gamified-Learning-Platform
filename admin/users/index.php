<?php
require_once '../../includes/header.php';
$page_title = "Manage Users - " . SITE_NAME;
require_login();

$page_title = "Manage Users - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get filter and search parameters
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "
    SELECT u.*,
           l.title as level_title,
           COUNT(DISTINCT uc.course_id) as enrolled_courses,
           COUNT(DISTINCT ub.badge_id) as earned_badges,
           COUNT(DISTINCT c.certificate_id) as earned_certificates
    FROM users u
    LEFT JOIN levels l ON u.current_level = l.level_number
    LEFT JOIN user_courses uc ON u.user_id = uc.user_id
    LEFT JOIN user_badges ub ON u.user_id = ub.user_id
    LEFT JOIN certificates c ON u.user_id = c.user_id
    WHERE 1=1
";

$params = [];

if ($role_filter !== 'all') {
    $query .= " AND u.role = ?";
    $params[] = $role_filter;
}

if (!empty($search)) {
    $query .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " GROUP BY u.user_id ORDER BY u.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get user statistics
$stats_query = "
    SELECT
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'learner' THEN 1 ELSE 0 END) as total_learners,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admins,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN DATE(last_login_date) = CURDATE() THEN 1 ELSE 0 END) as active_today
    FROM users
";
$stats = $conn->query($stats_query)->fetch();

$page_title = "Manage Users";
include '../../includes/header.php';
include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users me-2"></i>Manage Users</h2>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users fa-2x text-purple"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="small text-muted">Total Users</div>
                                <div class="h4 mb-0"><?php echo $stats['total_users']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-user-graduate fa-2x text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="small text-muted">Learners</div>
                                <div class="h4 mb-0"><?php echo $stats['total_learners']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle fa-2x text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="small text-muted">Active Users</div>
                                <div class="h4 mb-0"><?php echo $stats['active_users']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-check fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="small text-muted">Active Today</div>
                                <div class="h4 mb-0"><?php echo $stats['active_today']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="role" class="form-label">Filter by Role</label>
                        <select name="role" id="role" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $role_filter === 'all' ? 'selected' : ''; ?>>All Roles</option>
                            <option value="learner" <?php echo $role_filter === 'learner' ? 'selected' : ''; ?>>Learners</option>
                            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admins</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search Users</label>
                        <input type="text" class="form-control" id="search" name="search"
                               placeholder="Search by username, email, or full name..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-purple me-2">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                        <?php if (!empty($search) || $role_filter !== 'all'): ?>
                        <a href="/admin/users/" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                    switch($_GET['success']) {
                        case 'updated':
                            echo 'User updated successfully!';
                            break;
                        case 'deleted':
                            echo 'User deleted successfully!';
                            break;
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($users)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No users found matching your criteria.
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Level & XP</th>
                                    <th>Progress</th>
                                    <th>Last Login</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user_item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo UPLOAD_URL; ?>avatars/<?php echo $user_item['profile_picture'] ?: 'default.png'; ?>"
                                                 class="rounded-circle me-2" width="40" height="40" alt="Avatar">
                                            <div>
                                                <strong><?php echo htmlspecialchars($user_item['username']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($user_item['email']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($user_item['role'] === 'admin'): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Learner</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <i class="fas fa-layer-group text-purple me-1"></i>
                                            Level <?php echo $user_item['current_level']; ?>
                                            <?php if ($user_item['level_title']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($user_item['level_title']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small mt-1">
                                            <i class="fas fa-star text-warning me-1"></i>
                                            <?php echo number_format($user_item['total_xp']); ?> XP
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <i class="fas fa-book text-primary me-1"></i>
                                            <?php echo $user_item['enrolled_courses']; ?> courses
                                        </div>
                                        <div class="small">
                                            <i class="fas fa-medal text-warning me-1"></i>
                                            <?php echo $user_item['earned_badges']; ?> badges
                                        </div>
                                        <div class="small">
                                            <i class="fas fa-certificate text-success me-1"></i>
                                            <?php echo $user_item['earned_certificates']; ?> certificates
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($user_item['last_login_date']): ?>
                                            <small><?php echo date('M d, Y', strtotime($user_item['last_login_date'])); ?></small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-fire text-danger me-1"></i>
                                                <?php echo $user_item['current_streak']; ?> day streak
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">Never</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user_item['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="/admin/users/view.php?id=<?php echo $user_item['user_id']; ?>"
                                               class="btn btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/admin/users/edit.php?id=<?php echo $user_item['user_id']; ?>"
                                               class="btn btn-outline-primary" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user_item['user_id'] != $_SESSION['user_id']): ?>
                                            <a href="/admin/users/delete.php?id=<?php echo $user_item['user_id']; ?>"
                                               class="btn btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this user? All their progress and data will be permanently lost!')"
                                               title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
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
