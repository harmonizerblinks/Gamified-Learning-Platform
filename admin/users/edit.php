<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Edit User - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();

if (!$user_data) {
    redirect('/admin/users/');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $full_name = clean_input($_POST['full_name']);
    $role = isset($_POST['role']) ? $_POST['role'] : 'learner';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $current_level = isset($_POST['current_level']) ? (int)$_POST['current_level'] : 1;
    $total_xp = isset($_POST['total_xp']) ? (int)$_POST['total_xp'] : 0;
    $current_streak = isset($_POST['current_streak']) ? (int)$_POST['current_streak'] : 0;

    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (empty($full_name)) {
        $errors[] = 'Full name is required.';
    }
    if (!in_array($role, ['admin', 'learner'])) {
        $errors[] = 'Invalid role selected.';
    }

    // Check if username or email already exists for other users
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
    $check_stmt->execute([$username, $email, $user_id]);
    if ($check_stmt->fetch()) {
        $errors[] = 'Username or email already exists.';
    }

    // Handle password update if provided
    $password_update = '';
    $password_params = [];
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $errors[] = 'Passwords do not match.';
        } elseif (strlen($_POST['new_password']) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } else {
            $password_update = ', password_hash = ?';
            $password_params[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            $query = "
                UPDATE users
                SET username = ?, email = ?, full_name = ?, role = ?, is_active = ?,
                    current_level = ?, total_xp = ?, current_streak = ?" . $password_update . "
                WHERE user_id = ?
            ";

            $params = array_merge(
                [$username, $email, $full_name, $role, $is_active, $current_level, $total_xp, $current_streak],
                $password_params,
                [$user_id]
            );

            $stmt = $conn->prepare($query);
            $stmt->execute($params);

            redirect('/admin/users/?success=updated');
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    // Pre-populate form with existing data
    $_POST = $user_data;
}

$page_title = "Edit User";
include '../../includes/header.php';
include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit User</h2>
            <div>
                <a href="/admin/users/view.php?id=<?php echo $user_id; ?>" class="btn btn-info me-2">
                    <i class="fas fa-eye me-2"></i>View Details
                </a>
                <a href="/admin/users/" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
            </div>
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
                            <h5 class="mb-3">Basic Information</h5>

                            <!-- Username -->
                            <div class="mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username"
                                       value="<?php echo htmlspecialchars($user_data['username']); ?>"
                                       required maxlength="50">
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($user_data['email']); ?>"
                                       required maxlength="100">
                            </div>

                            <!-- Full Name -->
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name"
                                       value="<?php echo htmlspecialchars($user_data['full_name']); ?>"
                                       required maxlength="100">
                            </div>

                            <div class="row">
                                <!-- Role -->
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select name="role" id="role" class="form-select" required>
                                        <option value="learner" <?php echo $user_data['role'] === 'learner' ? 'selected' : ''; ?>>Learner</option>
                                        <option value="admin" <?php echo $user_data['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </div>

                                <!-- Active Status -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">Account Status</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                               <?php echo $user_data['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">Active Account</label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3">Password Update (Optional)</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                    <small class="text-muted">Leave blank to keep current password</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3">Gamification Settings</h5>

                            <div class="row">
                                <!-- Current Level -->
                                <div class="col-md-4 mb-3">
                                    <label for="current_level" class="form-label">Current Level</label>
                                    <input type="number" class="form-control" id="current_level" name="current_level"
                                           value="<?php echo $user_data['current_level']; ?>" min="1" max="10">
                                </div>

                                <!-- Total XP -->
                                <div class="col-md-4 mb-3">
                                    <label for="total_xp" class="form-label">Total XP</label>
                                    <input type="number" class="form-control" id="total_xp" name="total_xp"
                                           value="<?php echo $user_data['total_xp']; ?>" min="0">
                                </div>

                                <!-- Current Streak -->
                                <div class="col-md-4 mb-3">
                                    <label for="current_streak" class="form-label">Current Streak</label>
                                    <input type="number" class="form-control" id="current_streak" name="current_streak"
                                           value="<?php echo $user_data['current_streak']; ?>" min="0">
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-purple">
                                    <i class="fas fa-save me-2"></i>Update User
                                </button>
                                <a href="/admin/users/" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>User Info</h5>
                        <table class="table table-sm mb-0">
                            <tr>
                                <th>User ID:</th>
                                <td><?php echo $user_data['user_id']; ?></td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td><?php echo date('M d, Y', strtotime($user_data['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>Last Login:</th>
                                <td>
                                    <?php echo $user_data['last_login_date'] ? date('M d, Y', strtotime($user_data['last_login_date'])) : 'Never'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Longest Streak:</th>
                                <td><?php echo $user_data['longest_streak']; ?> days</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Editing Tips</h5>
                        <ul class="small mb-0">
                            <li>Username and email must be unique</li>
                            <li>Leave password fields blank to keep current password</li>
                            <li>Admins have full system access</li>
                            <li>Deactivating an account prevents login</li>
                            <li>Manually adjust XP/level if needed for corrections</li>
                            <li>Changes take effect immediately</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
