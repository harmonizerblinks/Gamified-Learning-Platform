<?php 
require_once '../includes/header.php';
$page_title = "My Certificates - " . SITE_NAME;
require_login();

$user_id = get_user_id();

// Get all certificates
$stmt = $conn->prepare("
    SELECT c.*, co.course_title, co.course_id, s.subject_name
    FROM certificates c
    JOIN courses co ON c.course_id = co.course_id
    JOIN subjects s ON co.subject_id = s.subject_id
    WHERE c.user_id = ?
    ORDER BY c.issued_date DESC
");
$stmt->execute([$user_id]);
$certificates = $stmt->fetchAll();

$user = get_user_data($user_id);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-certificate text-info me-2"></i>My Certificates</h1>
                <?php if (!empty($certificates)): ?>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print All
                    </button>
                <?php endif; ?>
            </div>
            
            <?php display_messages(); ?>
            
            <!-- Certificates Overview -->
            <?php if (!empty($certificates)): ?>
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center p-4">
                            <i class="fas fa-certificate fa-3x text-info mb-3"></i>
                            <h3><?php echo count($certificates); ?></h3>
                            <p class="text-muted mb-0">Total Certificates</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center p-4">
                            <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                            <h3><?php echo date('Y', strtotime($certificates[0]['issued_date'])); ?></h3>
                            <p class="text-muted mb-0">Latest Year</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center p-4">
                            <i class="fas fa-download fa-3x text-warning mb-3"></i>
                            <h3><?php echo count($certificates); ?></h3>
                            <p class="text-muted mb-0">Available Downloads</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Certificates Grid -->
            <?php if (empty($certificates)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-certificate fa-4x text-muted mb-4"></i>
                        <h3>No Certificates Yet</h3>
                        <p class="text-muted mb-4">Complete courses to earn certificates!</p>
                        <a href="/pages/subjects.php" class="btn btn-primary">
                            <i class="fas fa-book me-2"></i>Browse Courses
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($certificates as $cert): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 hover-lift">
                                <div class="certificate-preview position-relative">
                                    <div class="certificate-mock p-4 bg-gradient text-white">
                                        <div class="text-center">
                                            <i class="fas fa-award fa-3x mb-3"></i>
                                            <h5 class="mb-2">Certificate of Completion</h5>
                                            <p class="small mb-3"><?php echo SITE_NAME; ?></p>
                                            <div class="certificate-details bg-white bg-opacity-10 p-3 rounded">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h6>
                                                <p class="small mb-1"><?php echo htmlspecialchars($cert['course_title']); ?></p>
                                                <small><?php echo date('F d, Y', strtotime($cert['issued_date'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="certificate-overlay">
                                        <a href="/dashboard/certificate-view.php?id=<?php echo $cert['certificate_id']; ?>" 
                                           class="btn btn-light btn-sm">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-2">
                                        <span class="badge bg-info me-2"><?php echo htmlspecialchars($cert['subject_name']); ?></span>
                                    </div>
                                    <h6 class="mb-2"><?php echo htmlspecialchars($cert['course_title']); ?></h6>
                                    <div class="d-flex justify-content-between text-muted small mb-3">
                                        <span>
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('M d, Y', strtotime($cert['issued_date'])); ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-hashtag me-1"></i>
                                            <?php echo substr($cert['certificate_code'], -6); ?>
                                        </span>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="/dashboard/certificate-view.php?id=<?php echo $cert['certificate_id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-2"></i>View Certificate
                                        </a>
                                        <a href="/actions/download-certificate.php?id=<?php echo $cert['certificate_id']; ?>" 
                                           class="btn btn-success btn-sm" 
                                           target="_blank">
                                            <i class="fas fa-download me-2"></i>Download PDF
                                        </a>
                                    </div>
                                </div>
                                <div class="card-footer bg-light border-top-0">
                                    <small class="text-muted">
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        Verification Code: <code><?php echo htmlspecialchars($cert['certificate_code']); ?></code>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
.certificate-preview {
    position: relative;
    overflow: hidden;
    border-radius: 0.5rem 0.5rem 0 0;
}

.certificate-mock {
    min-height: 250px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.certificate-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.certificate-preview:hover .certificate-overlay {
    opacity: 1;
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
}

@media print {
    .sidebar, .btn, nav, footer {
        display: none !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>