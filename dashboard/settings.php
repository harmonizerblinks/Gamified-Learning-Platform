<?php 
require_once '../includes/header.php';
$page_title = "Settings - " . SITE_NAME;
require_login();

$user_id = get_user_id();
$user = get_user_data($user_id);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="mb-4">Account Settings</h1>
            
            <?php display_messages(); ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- Profile Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="/actions/update-profile.php" method="POST" enctype="multipart/form-data">
                                <div class="row g-3">
                                    <!-- Profile Picture -->
                                    <div class="col-12 text-center">
                                        <img src="<?php echo UPLOAD_URL; ?>avatars/<?php echo $user['profile_picture']; ?>" 
                                             class="rounded-circle mb-3" 
                                             width="120" 
                                             height="120" 
                                             id="profilePreview"
                                             alt="Profile Picture">
                                        <div>
                                            <label for="profile_picture" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-camera me-2"></i>Change Picture
                                            </label>
                                            <input type="file" 
                                                   id="profile_picture" 
                                                   name="profile_picture" 
                                                   class="d-none" 
                                                   accept="image/*"
                                                   onchange="previewImage(this)">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="full_name" class="form-label">Full Name</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="full_name" 
                                               name="full_name" 
                                               value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                               required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="username" 
                                               name="username" 
                                               value="<?php echo htmlspecialchars($user['username']); ?>" 
                                               required>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" 
                                               required>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="/actions/change-password.php" method="POST">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="current_password" 
                                               name="current_password" 
                                               required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="new_password" 
                                               name="new_password" 
                                               required>
                                        <small class="text-muted">At least 6 characters</small>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               required>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-key me-2"></i>Update Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Danger Zone -->
                    <div class="card border-danger shadow-sm">
                        <div class="card-header bg-danger text-white border-bottom-0">
                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h5>
                        </div>
                        <div class="card-body p-4">
                            <h6 class="text-danger">Delete Account</h6>
                            <p class="text-muted mb-3">
                                Once you delete your account, there is no going back. All your progress, badges, and certificates will be permanently deleted.
                            </p>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                <i class="fas fa-trash me-2"></i>Delete My Account
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Side Info -->
                <div class="col-lg-4">
                    <!-- Account Info -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h6 class="mb-3">Account Information</h6>
                            <div class="mb-3">
                                <small class="text-muted d-block">Member Since</small>
                                <strong><?php echo format_date($user['created_at']); ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Last Login</small>
                                <strong><?php echo $user['last_login_date'] ? format_date($user['last_login_date']) : 'Never'; ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Account Status</small>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Account Role</small>
                                <span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="mb-3">Your Stats</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total XP</span>
                                <strong><?php echo number_format($user['total_xp']); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Current Level</span>
                                <strong>Level <?php echo $user['current_level']; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Current Streak</span>
                                <strong><?php echo $user['current_streak']; ?> days</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Longest Streak</span>
                                <strong><?php echo $user['longest_streak']; ?> days</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/actions/delete-account.php" method="POST">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone!
                    </div>
                    <p>Are you absolutely sure you want to delete your account? This will permanently delete:</p>
                    <ul>
                        <li>All your course progress</li>
                        <li>All earned badges and certificates</li>
                        <li>All XP and levels</li>
                        <li>Your profile and personal information</li>
                    </ul>
                    <div class="mb-3">
                        <label for="confirm_delete" class="form-label">
                            Type <strong>DELETE</strong> to confirm:
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="confirm_delete" 
                               name="confirm_delete" 
                               required 
                               pattern="DELETE">
                    </div>
                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Enter your password:</label>
                        <input type="password" 
                               class="form-control" 
                               id="delete_password" 
                               name="password" 
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Delete My Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>