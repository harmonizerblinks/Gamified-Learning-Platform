<?php 
require_once '../includes/header.php';
$page_title = "XP History - " . SITE_NAME;
require_login();

$user_id = get_user_id();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM xp_transactions WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_records = $stmt->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

// Get XP history
$stmt = $conn->prepare("
    SELECT * FROM xp_transactions 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$user_id, $per_page, $offset]);
$transactions = $stmt->fetchAll();

// Get XP stats
$stmt = $conn->prepare("SELECT SUM(xp_amount) as total FROM xp_transactions WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_xp_earned = $stmt->fetch()['total'];

$stmt = $conn->prepare("
    SELECT xp_type, SUM(xp_amount) as total 
    FROM xp_transactions 
    WHERE user_id = ? 
    GROUP BY xp_type
");
$stmt->execute([$user_id]);
$xp_by_type = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$user = get_user_data($user_id);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="mb-4"><i class="fas fa-history me-2"></i>XP History</h1>
            
            <?php display_messages(); ?>
            
            <!-- XP Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <i class="fas fa-star fa-2x text-warning mb-2"></i>
                        <h4><?php echo number_format($user['total_xp']); ?></h4>
                        <small class="text-muted">Total XP</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <i class="fas fa-book-reader fa-2x text-primary mb-2"></i>
                        <h4><?php echo number_format($xp_by_type['lesson'] ?? 0); ?></h4>
                        <small class="text-muted">From Lessons</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <i class="fas fa-question-circle fa-2x text-info mb-2"></i>
                        <h4><?php echo number_format($xp_by_type['quiz'] ?? 0); ?></h4>
                        <small class="text-muted">From Quizzes</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <i class="fas fa-graduation-cap fa-2x text-success mb-2"></i>
                        <h4><?php echo number_format($xp_by_type['course'] ?? 0); ?></h4>
                        <small class="text-muted">From Courses</small>
                    </div>
                </div>
            </div>
            
            <!-- Transaction History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Transaction History</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No XP transactions yet</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th class="text-end">XP Earned</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($transaction['created_at'])); ?>
                                                    <br>
                                                    <?php echo date('h:i A', strtotime($transaction['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php
                                                $badge_class = '';
                                                $icon = '';
                                                switch ($transaction['xp_type']) {
                                                    case 'lesson':
                                                        $badge_class = 'bg-primary';
                                                        $icon = 'fa-book-open';
                                                        break;
                                                    case 'quiz':
                                                        $badge_class = 'bg-info';
                                                        $icon = 'fa-question-circle';
                                                        break;
                                                    case 'course':
                                                        $badge_class = 'bg-success';
                                                        $icon = 'fa-graduation-cap';
                                                        break;
                                                    case 'streak':
                                                        $badge_class = 'bg-danger';
                                                        $icon = 'fa-fire';
                                                        break;
                                                    case 'badge':
                                                        $badge_class = 'bg-warning text-dark';
                                                        $icon = 'fa-medal';
                                                        break;
                                                    default:
                                                        $badge_class = 'bg-secondary';
                                                        $icon = 'fa-star';
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <i class="fas <?php echo $icon; ?> me-1"></i>
                                                    <?php echo ucfirst($transaction['xp_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($transaction['description']); ?>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">
                                                    +<?php echo $transaction['xp_amount']; ?> XP
                                                </strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white">
                        <nav>
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page - 1); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page + 1); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>