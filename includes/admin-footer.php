    <!-- </div>  -->
    <!-- Close main-content -->
<!-- </div>  -->
<!-- Close container-fluid or main wrapper -->

<!-- Admin Footer -->
<footer class="admin-footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-4">
                <p class="mb-0 text-muted small">
                    &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> Admin Panel. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-muted small">
                    <span class="me-3">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </span>
                    <span class="me-3">
                        <i class="fas fa-clock me-1"></i>
                        <?php echo date('M d, Y H:i'); ?>
                    </span>
                    <a href="/admin/" class="text-decoration-none me-3">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                    <a href="/dashboard/" class="text-decoration-none me-3">
                        <i class="fas fa-home me-1"></i>User Dashboard
                    </a>
                    <a href="/auth/logout.php" class="text-decoration-none text-danger">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
.admin-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 1rem 0;
    margin-top: 3rem;
    position: relative;
    bottom: 0;
    width: 100%;
}

.admin-footer a {
    color: #6c757d;
    font-size: 0.875rem;
}

.admin-footer a:hover {
    color: #8B5CF6;
}

.admin-footer .text-danger:hover {
    color: #dc3545 !important;
}
</style>

<!-- JavaScript -->
<script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
<?php if(isset($extra_js)) echo $extra_js; ?>
</body>
</html>
