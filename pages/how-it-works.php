<?php 
require_once '../includes/header.php';
require_once '../includes/navbar.php';
$page_title = "How It Works - " . SITE_NAME;

?>


<!-- Hero Section -->
<section class="bg-purple text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">How <?php echo SITE_NAME; ?> Works 🎮</h1>
        <p class="lead">Learning made fun, rewarding, and engaging</p>
        <!-- <h1 class="display-4 fw-bold mb-3">Explore Our Subjects 📚</h1>
        <p class="lead">Choose from a wide range of subjects and start your learning journey</p> -->
    </div>
</section>
<!-- Hero Section -->
<!-- <section class="bg-gradient-purple text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">How <?php echo SITE_NAME; ?> Works 🎮</h1>
        <p class="lead">Learning made fun, rewarding, and engaging</p>
    </div>
</section> -->

<!-- How It Works Steps -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Step 1 -->
            <div class="col-md-4 text-center">
                <div class="step-number bg-purple text-white rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" 
                     style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                    1
                </div>
                <h3 class="mb-3">Create Your Account</h3>
                <p class="text-muted">Sign up for free in seconds. No credit card required. Start your learning journey immediately.</p>
            </div>
            
            <!-- Step 2 -->
            <div class="col-md-4 text-center">
                <div class="step-number bg-purple text-white rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" 
                     style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                    2
                </div>
                <h3 class="mb-3">Choose Your Courses</h3>
                <p class="text-muted">Browse through our subjects and enroll in courses that interest you. Start with beginner courses or jump to advanced.</p>
            </div>
            
            <!-- Step 3 -->
            <div class="col-md-4 text-center">
                <div class="step-number bg-purple text-white rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" 
                     style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                    3
                </div>
                <h3 class="mb-3">Learn & Earn Rewards</h3>
                <p class="text-muted">Complete lessons, pass quizzes, earn XP, collect badges, and level up from Level 1 to Level 10!</p>
            </div>
        </div>
    </div>
</section>

<!-- Gamification Features -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Gamification Features</h2>
        
        <div class="row g-4">
            <!-- XP Points -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="feature-icon bg-purple-light text-purple rounded-circle p-3 me-3">
                                <i class="fas fa-star fa-2x"></i>
                            </div>
                            <div>
                                <h4>XP Points System</h4>
                                <p class="text-muted mb-2">Earn XP for every action you take:</p>
                                <ul class="text-muted">
                                    <li>Complete a lesson: <strong>10-20 XP</strong></li>
                                    <li>Pass a quiz: <strong>30 XP</strong></li>
                                    <li>Perfect quiz score: <strong>+20 bonus XP</strong></li>
                                    <li>Complete a course: <strong>100 XP</strong></li>
                                    <li>Daily login streak: <strong>5 XP per day</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Levels -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="feature-icon bg-warning-light text-warning rounded-circle p-3 me-3">
                                <i class="fas fa-level-up-alt fa-2x"></i>
                            </div>
                            <div>
                                <h4>10 Levels to Master</h4>
                                <p class="text-muted mb-2">Progress through 10 exciting levels:</p>
                                <ul class="text-muted">
                                    <li><strong>Level 1 (Beginner):</strong> Start your journey</li>
                                    <li><strong>Level 5 (Scholar):</strong> Unlock bonus materials</li>
                                    <li><strong>Level 8 (Master):</strong> Advanced certificates</li>
                                    <li><strong>Level 10 (Legend):</strong> Elite status + Excellence certificate</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Badges -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="feature-icon bg-success-light text-success rounded-circle p-3 me-3">
                                <i class="fas fa-medal fa-2x"></i>
                            </div>
                            <div>
                                <h4>Collectible Badges</h4>
                                <p class="text-muted mb-2">Unlock achievements and show off your skills:</p>
                                <ul class="text-muted">
                                    <li><strong>First Steps:</strong> Complete your first lesson</li>
                                    <li><strong>Course Completionist:</strong> Finish a course</li>
                                    <li><strong>Quiz Master:</strong> 5 perfect quiz scores</li>
                                    <li><strong>Week Warrior:</strong> 7-day login streak</li>
                                    <li><strong>Elite Learner:</strong> Reach Level 10</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Certificates -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="feature-icon bg-info-light text-info rounded-circle p-3 me-3">
                                <i class="fas fa-certificate fa-2x"></i>
                            </div>
                            <div>
                                <h4>Certificates</h4>
                                <p class="text-muted mb-2">Earn professional certificates:</p>
                                <ul class="text-muted">
                                    <li>Issued upon course completion</li>
                                    <li>Include your name and completion date</li>
                                    <li>Unique verification code</li>
                                    <li>Download as PDF</li>
                                    <li>Share on LinkedIn and social media</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Daily Streaks -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="feature-icon bg-danger-light text-danger rounded-circle p-3 me-3">
                                <i class="fas fa-fire fa-2x"></i>
                            </div>
                            <div>
                                <h4>Daily Streaks</h4>
                                <p class="text-muted mb-2">Build consistent learning habits:</p>
                                <ul class="text-muted">
                                    <li>Log in daily to maintain your streak</li>
                                    <li>Earn 5 XP bonus each day</li>
                                    <li>Special badges at 7, 30, and 100 days</li>
                                    <li>Track your longest streak ever</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Leaderboard -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="feature-icon bg-warning-light text-warning rounded-circle p-3 me-3">
                                <i class="fas fa-trophy fa-2x"></i>
                            </div>
                            <div>
                                <h4>Leaderboard Competition</h4>
                                <p class="text-muted mb-2">Compete with other learners:</p>
                                <ul class="text-muted">
                                    <li>Global rankings by total XP</li>
                                    <li>See top learners in real-time</li>
                                    <li>Filter by weekly, monthly, or all-time</li>
                                    <li>Climb the ranks and reach #1</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient-purple text-white">
    <div class="container text-center">
        <h2 class="mb-4">Ready to Start Your Journey?</h2>
        <p class="lead mb-4">Join thousands of learners and start earning XP today!</p>
        <a href="/auth/register.php" class="btn btn-light btn-lg px-5">Get Started Free</a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>