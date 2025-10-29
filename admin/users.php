<?php
require_once '../includes/header.php';
$page_title = "Manage Users - " . SITE_NAME;
require_login();

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Handle Delete
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    if ($user_id == get_user_id()) {
        set_error('You cannot delete your own account');
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        if ($stmt->execute([$user_id])) {
            set_success('User deleted successfully');
        } else {
            set_error('Failed to delete user');
        }
    }
    redirect('/admin/users.php');
}

// Handle Role Update
if (isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = clean_input($_POST['role']);

    if ($user_id == get_user_id()) {
        set_error('You cannot change your own role');
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        if ($stmt->execute([$new_role, $user_id])) {
            set_success('User role updated successfully');
        } else {
            set_error('Failed to update user role');
        }
    }
    redirect('/admin/users.php');
}

// Handle Status Toggle
if (isset($_GET['toggle_status'])) {
    $user_id = (int)$_GET['toggle_status'];
    if ($user_id == get_user_id()) {
        set_error('You cannot deactivate your own account');
    } else {
        $stmt = $conn->prepare("UPDATE users SET is_active = NOT is_active WHERE user_id = ?");
        if ($stmt->execute([$user_id])) {
            set_success('User status updated successfully');
        } else {
            set_error('Failed to update user status');
        }
    }
    redirect('/admin/users.php');
}

// Get filters
$role_filter = isset($_GET['role']) ? clean_input($_GET['role']) : 'all';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Build query
$where_clauses = [];
$params = [];

if ($role_filter !== 'all') {
    $where_clauses[] = "u.role = ?";
    $params[] = $role_filter;
}

if (!empty($search)) {
    $where_clauses[] = "(u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get users with stats
$stmt = $conn->prepare("
    SELECT u.*,
           COUNT(DISTINCT uc.course_id) as courses_enrolled,
           COUNT(DISTINCT CASE WHEN uc.is_completed = 1 THEN uc.course_id END) as courses_completed,
           COUNT(DISTINCT ub.badge_id) as badges_earned,
           COUNT(DISTINCT cert.certificate_id) as certificates_earned
    FROM users u
    LEFT JOIN user_courses uc ON u.user_id = uc.user_id
    LEFT JOIN user_badges ub ON u.user_id = ub.user_id
    LEFT JOIN certificates cert ON u.user_id = cert.user_id
    $where_sql
    GROUP BY u.user_id
    ORDER BY u.created_at DESC
");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-users me-2"></i>Manage Users</h1>
                <div>
                    <span class="badge bg-primary fs-6"><?php echo count($users); ?> Users</span>
                </div>
            </div>

            <?php display_messages(); ?>

            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search Users</label>
                            <input type="text" class="form-control" name="search" placeholder="Username, name, or email" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Filter by Role</label>
                            <select class="form-select" name="role">
                                <option value="all" <?php echo $role_filter === 'all' ? 'selected' : ''; ?>>All Roles</option>
                                <option value="learner" <?php echo $role_filter === 'learner' ? 'selected' : ''; ?>>Learners</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admins</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="/admin/users.php" class="btn btn-secondary w-100">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users-slash fa-4x text-muted mb-3"></i>
                            <h5>No users found</h5>
                            <p class="text-muted">Try adjusting your filters</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th class="text-center">Role</th>
                                        <th class="text-center">Level</th>
                                        <th class="text-center">XP</th>
                                        <th class="text-center">Courses</th>
                                        <th class="text-center">Badges</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Joined</th>
                                        <th width="180" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr class="<?php echo !$u['is_active'] ? 'table-secondary' : ''; ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo UPLOAD_URL; ?>avatars/<?php echo $u['profile_picture']; ?>"
                                                         class="rounded-circle me-2"
                                                         width="40"
                                                         height="40"
                                                         alt="<?php echo htmlspecialchars($u['username']); ?>">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($u['full_name']); ?></strong>
                                                        <br><small class="text-muted">@<?php echo htmlspecialchars($u['username']); ?></small>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($u['email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($u['role'] === 'admin'): ?>
                                                    <span class="badge bg-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary">Learner</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-purple">Level <?php echo $u['current_level']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-warning"><i class="fas fa-star"></i> <?php echo number_format($u['total_xp']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?php echo $u['courses_completed']; ?>/<?php echo $u['courses_enrolled']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success"><?php echo $u['badges_earned']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($u['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <small><?php echo format_date($u['created_at']); ?></small>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($u['user_id'] != get_user_id()): ?>
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#roleModal" onclick="setRoleModal(<?php echo $u['user_id']; ?>, '<?php echo $u['username']; ?>', '<?php echo $u['role']; ?>')">
                                                        <i class="fas fa-user-tag"></i>
                                                    </button>
                                                    <a href="?toggle_status=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-<?php echo $u['is_active'] ? 'secondary' : 'success'; ?>" title="Toggle Status">
                                                        <i class="fas fa-<?php echo $u['is_active'] ? 'ban' : 'check'; ?>"></i>
                                                    </a>
                                                    <a href="?delete=<?php echo $u['user_id']; ?>"
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Delete this user and all their data?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-info">You</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Change Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Change User Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="role_user_id">
                    <input type="hidden" name="update_role" value="1">

                    <p>Change role for: <strong id="role_username"></strong></p>

                    <div class="mb-3">
                        <label for="role" class="form-label">Select New Role</label>
                        <select class="form-select" name="role" id="role" required>
                            <option value="learner">Learner</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> Changing a user to admin will give them full access to the admin panel.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setRoleModal(userId, username, currentRole) {
    document.getElementById('role_user_id').value = userId;
    document.getElementById('role_username').textContent = username;
    document.getElementById('role').value = currentRole;
}
</script>

<?php require_once '../includes/footer.php'; ?>
