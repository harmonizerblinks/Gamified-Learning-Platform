<?php 
$page_title = "Welcome to LearnHub - Gamified Learning Platform";
require_once 'includes/header.php'; 
require_once 'includes/navbar.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Transform Learning Into An Adventure 🚀</h1>
            <p class="hero-subtitle">Earn XP, unlock badges, climb the leaderboard, and level up your skills with our gamified courses</p>
            <div class="hero-buttons">
                <a href="/auth/register.php" class="btn-primary btn-large">Get Started Free</a>
                <a href="/pages/how-it-works.php" class="btn-secondary btn-large">Learn More</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="<?php echo ASSETS_URL; ?>images/hero-illustration.png" alt="Learning Platform">
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <?php
            // Get platform stats
            $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'learner'");
            $total_users = $stmt->fetch()['total'];
            
            $stmt = $conn->query("SELECT COUNT(*) as total FROM courses WHERE is_published = 1");
            $total_courses = $stmt->fetch()['total'];
            
            $stmt = $conn->query("SELECT COUNT(*) as total FROM certificates");
            $total_certificates = $stmt->fetch()['total'];
            ?>
            <div class="stat-item">
                <h3><?php echo number_format($total_users); ?>+</h3>
                <p>Active Learners</p>
            </div>
            <div class="stat-item">
                <h3><?php echo number_format($total_courses); ?>+</h3>
                <p>Courses Available</p>
            </div>
            <div class="stat-item">
                <h3><?php echo number_format($total_certificates); ?>+</h3>
                <p>Certificates Issued</p>
            </div>
            <div class="stat-item">
                <h3>10</h3>
                <p>Levels to Master</p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <h2 class="section-title">Why Choose <?php echo SITE_NAME; ?>?</h2>
        <p class="section-subtitle">Learning made fun and rewarding</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">⭐</div>
                <h3>Earn XP Points</h3>
                <p>Get rewards for every lesson and quiz you complete. The more you learn, the more you earn!</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3>Level Up (1-10)</h3>
                <p>Progress through 10 exciting levels and unlock advanced content as you grow</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3>Collect Badges</h3>
                <p>Achieve milestones and earn unique badges to showcase your accomplishments</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📜</div>
                <h3>Get Certified</h3>
                <p>Receive professional certificates upon course completion to boost your career</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔥</div>
                <h3>Daily Streaks</h3>
                <p>Build learning habits with daily login streaks and earn bonus XP</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Leaderboard</h3>
                <p>Compete with other learners and climb to the top of the rankings</p>
            </div>
        </div>
    </div>
</section>

<!-- Popular Courses -->
<section class="popular-courses">
    <div class="container">
        <h2 class="section-title">Popular Courses</h2>
        <p class="section-subtitle">Start your learning journey today</p>
        
        <div class="courses-grid">
            <?php
            // Get popular courses
            $stmt = $conn->query("
                SELECT c.*, s.subject_name, COUNT(uc.user_id) as enrolled_count
                FROM courses c
                LEFT JOIN subjects s ON c.subject_id = s.subject_id
                LEFT JOIN user_courses uc ON c.course_id = uc.course_id
                WHERE c.is_published = 1
                GROUP BY c.course_id
                ORDER BY enrolled_count DESC
                LIMIT 6
            ");
            $courses = $stmt->fetchAll();
            
            foreach($courses as $course):
            ?>
                <div class="course-card">
                    <div class="course-thumbnail">
                        <img src="<?php echo UPLOAD_URL; ?>thumbnails/<?php echo $course['thumbnail']; ?>" alt="<?php echo htmlspecialchars($course['course_title']); ?>">
                        <span class="course-difficulty"><?php echo ucfirst($course['difficulty']); ?></span>
                    </div>
                    <div class="course-content">
                        <span class="course-subject"><?php echo htmlspecialchars($course['subject_name']); ?></span>
                        <h3><?php echo htmlspecialchars($course['course_title']); ?></h3>
                        <p><?php echo substr(htmlspecialchars($course['description']), 0, 100); ?>...</p>
                        <div class="course-meta">
                            <span>⏱️ <?php echo $course['estimated_duration']; ?> min</span>
                            <span>⭐ <?php echo $course['xp_reward']; ?> XP</span>
                        </div>
                        <a href="/auth/register.php" class="btn-primary btn-block">Enroll Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center">
            <a href="/pages/subjects.php" class="btn-secondary">View All Courses</a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to Start Your Learning Adventure?</h2>
        <p>Join thousands of learners and start earning XP today!</p>
        <a href="/auth/register.php" class="btn-primary btn-large">Sign Up Now - It's Free!</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>