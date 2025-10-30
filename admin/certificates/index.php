<?php
require_once '../../includes/header.php';
require_login();

$page_title = "Manage Certificates - " . SITE_NAME;

// Check if user is admin
if (!is_admin()) {
    set_error('Access denied. Admin only.');
    redirect('/dashboard/');
}

$user = get_user_data(get_user_id());

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "
    SELECT c.*, u.username, u.full_name, u.email, cr.course_title, s.subject_name
    FROM certificates c
    INNER JOIN users u ON c.user_id = u.user_id
    INNER JOIN courses cr ON c.course_id = cr.course_id
    INNER JOIN subjects s ON cr.subject_id = s.subject_id
    WHERE 1=1
";

$params = [];

if (!empty($search)) {
    $query .= " AND (u.username LIKE ? OR u.full_name LIKE ? OR cr.course_title LIKE ? OR c.certificate_code LIKE ?)";
    $search_param = '%' . $search . '%';
    $params = array_fill(0, 4, $search_param);
}

$query .= " ORDER BY c.issued_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$certificates = $stmt->fetchAll();

// Get statistics
$stats = $conn->query("
    SELECT
        COUNT(*) as total_certificates,
        COUNT(DISTINCT user_id) as unique_users,
        COUNT(DISTINCT course_id) as unique_courses
    FROM certificates
")->fetch();

$page_title = "Manage Certificates";
include '../../includes/header.php';
include '../../includes/admin-sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-certificate me-2"></i>Manage Certificates</h2>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-certificate fa-2x text-purple"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="small text-muted">Total Certificates</div>
                                <div class="h4 mb-0"><?php echo number_format($stats['total_certificates']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-user-graduate fa-2x text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="small text-muted">Certified Users</div>
                                <div class="h4 mb-0"><?php echo number_format($stats['unique_users']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-graduation-cap fa-2x text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="small text-muted">Courses with Certificates</div>
                                <div class="h4 mb-0"><?php echo number_format($stats['unique_courses']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search Certificates</label>
                        <input type="text" class="form-control" id="search" name="search"
                               placeholder="Search by username, course, or certificate code..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-purple me-2">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                        <?php if (!empty($search)): ?>
                        <a href="/admin/certificates/" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($certificates)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No certificates found. Certificates are automatically issued when users complete courses.
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Certificate Code</th>
                                    <th>User</th>
                                    <th>Course</th>
                                    <th>Issued Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($certificates as $cert): ?>
                                <tr>
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded">
                                            <?php echo htmlspecialchars($cert['certificate_code']); ?>
                                        </code>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($cert['username']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($cert['full_name']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($cert['course_title']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($cert['subject_name']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($cert['issued_date'])); ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo date('H:i A', strtotime($cert['issued_date'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?php if (!empty($cert['certificate_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($cert['certificate_url']); ?>"
                                               target="_blank" class="btn btn-outline-success" title="View Certificate">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <?php endif; ?>
                                            <a href="/admin/users/view.php?id=<?php echo $cert['user_id']; ?>"
                                               class="btn btn-outline-primary" title="View User">
                                                <i class="fas fa-user"></i>
                                            </a>
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

        <!-- Help Section -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>About Certificates</h5>
                <div class="row">
                    <div class="col-md-6">
                        <h6>How Certificates Work</h6>
                        <ul class="small">
                            <li>Certificates are automatically generated when users complete courses</li>
                            <li>Each certificate has a unique verification code</li>
                            <li>Users can view and download their certificates from the dashboard</li>
                            <li>Certificate codes can be verified by employers or institutions</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Certificate Management</h6>
                        <ul class="small">
                            <li>View all issued certificates from this page</li>
                            <li>Search by username, course name, or certificate code</li>
                            <li>Click on user icon to view user details</li>
                            <li>Certificates cannot be manually deleted (data integrity)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
