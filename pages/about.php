
<?php 
require_once '../includes/header.php';
require_once '../includes/navbar.php';
$page_title = "About Us - " . SITE_NAME;
?>

<!-- Hero Section -->
<section class="bg-purple text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">About <?php echo SITE_NAME; ?></h1>
        <p class="lead">Transforming education through gamification</p>
    </div>
</section>

<!-- Mission Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <h2 class="mb-4">Our Mission</h2>
                <p class="lead text-muted">
                    At <?php echo SITE_NAME; ?>, we believe learning should be fun, engaging, and rewarding. 
                    We're on a mission to revolutionize online education by combining high-quality courses 
                    with game-like mechanics that keep you motivated.
                </p>
                <p class="text-muted">
                    Our platform transforms traditional e-learning into an adventure where every lesson completed, 
                    quiz passed, and skill mastered brings you closer to new achievements. Whether you're learning 
                    to code, mastering data science, or exploring digital marketing, we make the journey as exciting 
                    as the destination.
                </p>
            </div>
            <div class="col-md-6">
                <img src="<?php echo ASSETS_URL; ?>images/about-mission.png" class="img-fluid rounded shadow" alt="Mission">
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Our Core Values</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-lightbulb fa-3x text-purple"></i>
                    </div>
                    <h4>Innovation</h4>
                    <p class="text-muted">We constantly innovate to make learning more effective and enjoyable through cutting-edge gamification techniques.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-purple"></i>
                    </div>
                    <h4>Community</h4>
                    <p class="text-muted">We foster a supportive learning community where students encourage and compete with each other positively.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-check-circle fa-3x text-purple"></i>
                    </div>
                    <h4>Quality</h4>
                    <p class="text-muted">Every course is carefully crafted by experts to ensure you get the best learning experience possible.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Platform Statistics</h2>
        <div class="row text-center g-4">
            <?php
            // Get real stats
            $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'learner'");
            $total_learners = $stmt->fetch()['total'];
            
            $stmt = $conn->query("SELECT COUNT(*) as total FROM courses WHERE is_published = 1");
            $total_courses = $stmt->fetch()['total'];
            
            $stmt = $conn->query("SELECT COUNT(*) as total FROM certificates");
            $total_certificates = $stmt->fetch()['total'];
            
            $stmt = $conn->query("SELECT COUNT(*) as total FROM user_courses WHERE is_completed = 1");
            $total_completions = $stmt->fetch()['total'];
            ?>
            
            <div class="col-md-3">
                <div class="stat-item">
                    <h2 class="display-4 fw-bold text-purple"><?php echo number_format($total_learners); ?>+</h2>
                    <p class="text-muted">Active Learners</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <h2 class="display-4 fw-bold text-purple"><?php echo number_format($total_courses); ?>+</h2>
                    <p class="text-muted">Quality Courses</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <h2 class="display-4 fw-bold text-purple"><?php echo number_format($total_certificates); ?>+</h2>
                    <p class="text-muted">Certificates Issued</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <h2 class="display-4 fw-bold text-purple"><?php echo number_format($total_completions); ?>+</h2>
                    <p class="text-muted">Course Completions</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient-purple text-white">
    <div class="container text-center">
        <h2 class="mb-4">Join Our Learning Community</h2>
        <p class="lead mb-4">Start earning XP, collecting badges, and leveling up today!</p>
        <a href="/auth/register.php" class="btn btn-light btn-lg me-3">Sign Up Free</a>
        <a href="/pages/how-it-works.php" class="btn btn-outline-light btn-lg">Learn More</a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
