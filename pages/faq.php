<?php 

require_once '../includes/header.php';
require_once '../includes/navbar.php';
$page_title = "Frequently Asked Questions - " . SITE_NAME;

?>

<!-- Hero Section -->
<section class="bg-purple text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Frequently Asked Questions</h1>
        <p class="lead">Find answers to common questions about <?php echo SITE_NAME; ?></p>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    
                    <!-- General Questions -->
                    <h3 class="mb-4">General Questions</h3>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                What is <?php echo SITE_NAME; ?>?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php echo SITE_NAME; ?> is a gamified e-learning platform that makes education fun and engaging. 
                                You earn XP points, collect badges, level up, and compete on leaderboards while learning valuable skills 
                                through high-quality courses.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Is <?php echo SITE_NAME; ?> free to use?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! Creating an account and accessing most of our courses is completely free. 
                                You can start learning, earning XP, and collecting badges without paying anything.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How do I create an account?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Click the "Sign Up" button at the top of the page, fill in your details (name, email, password), 
                                and you're ready to start learning! The process takes less than a minute.
                            </div>
                        </div>
                    </div>
                    
                    <!-- XP & Levels -->
                    <h3 class="mb-4 mt-5">XP & Levels</h3>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What are XP points and how do I earn them?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                XP (Experience Points) are rewards you earn for learning activities:
                                <ul class="mt-2">
                                    <li>Complete a lesson: 10-20 XP</li>
                                    <li>Pass a quiz: 30 XP</li>
                                    <li>Perfect quiz score: +20 bonus XP</li>
                                    <li>Complete a course: 100 XP</li>
                                    <li>Daily login streak: 5 XP per day</li>
                                    <li>Earn badges: Various XP rewards</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                How many levels are there?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                There are 10 levels in total, from Level 1 (Beginner) to Level 10 (Legend). 
                                As you earn XP, you automatically level up. Higher levels unlock advanced courses and exclusive content. 
                                Reaching Level 10 grants you elite status and a Certificate of Excellence!
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                How much XP do I need to level up?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                XP requirements increase as you progress:
                                <ul class="mt-2">
                                    <li>Level 1 → 2: 100 XP</li>
                                    <li>Level 2 → 3: 200 XP (300 total)</li>
                                    <li>Level 5 → 6: 500 XP (1,800 total)</li>
                                    <li>Level 9 → 10: 2,000 XP (9,000 total)</li>
                                </ul>
                                Your progress bar shows how close you are to the next level!
                            </div>
                        </div>
                    </div>
                    
                    <!-- Badges & Achievements -->
                    <h3 class="mb-4 mt-5">Badges & Achievements</h3>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                What are badges and how do I earn them?
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Badges are special achievements you unlock by completing milestones. Some examples:
                                <ul class="mt-2">
                                    <li><strong>First Steps:</strong> Complete your first lesson</li>
                                    <li><strong>Course Completionist:</strong> Finish your first course</li>
                                    <li><strong>Quiz Master:</strong> Get perfect scores on 5 quizzes</li>
                                    <li><strong>Week Warrior:</strong> Maintain a 7-day login streak</li>
                                    <li><strong>Elite Learner:</strong> Reach Level 10</li>
                                </ul>
                                Badges also award bonus XP when earned!
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                What are daily streaks?
                            </button>
                        </h2>
                        <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                A daily streak tracks consecutive days you log in and engage with learning. 
                                Each day you maintain your streak, you earn 5 bonus XP. Special badges are awarded at 7, 30, and 100-day milestones. 
                                Streaks help build consistent learning habits!
                            </div>
                        </div>
                    </div>
                    
                    <!-- Courses & Learning -->
                    <h3 class="mb-4 mt-5">Courses & Learning</h3>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                                How do I enroll in a course?
                            </button>
                        </h2>
                        <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Browse our subjects, select a course that interests you, and click "Enroll Now". 
                                The course will be added to your dashboard, and you can start learning immediately. 
                                Some advanced courses require you to reach certain levels first.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                                Can I take multiple courses at once?
                            </button>
                        </h2>
                        <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolutely! You can enroll in as many courses as you want and learn at your own pace. 
                                Your dashboard shows all your active courses and tracks progress for each one individually.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq11">
                                Do I get a certificate when I complete a course?
                            </button>
                        </h2>
                        <div id="faq11" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! Upon completing all lessons and passing the final quiz, you'll receive a professional certificate 
                                with your name, course title, and completion date. Certificates can be downloaded as PDFs 
                                and include a unique verification code.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Leaderboard -->
                    <h3 class="mb-4 mt-5">Leaderboard & Competition</h3>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq12">
                                How does the leaderboard work?
                            </button>
                        </h2>
                        <div id="faq12" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                The leaderboard ranks all learners based on their total XP. The more you learn and complete, 
                                the higher you climb! You can view weekly, monthly, or all-time rankings. 
                                It's a fun way to stay motivated and see how you compare with others.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Technical -->
                    <h3 class="mb-4 mt-5">Technical Support</h3>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq13">
                                I forgot my password. What should I do?
                            </button>
                        </h2>
                        <div id="faq13" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Click "Forgot Password" on the login page, enter your email address, 
                                and we'll send you instructions to reset your password.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq14">
                                Who do I contact for more help?
                            </button>
                        </h2>
                        <div id="faq14" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                If you have questions not answered here, please visit our 
                                <a href="/pages/contact.php" class="text-purple">Contact page</a> or email us at 
                                <strong><?php echo ADMIN_EMAIL ?? 'support@learnhub.com'; ?></strong>. 
                                We're here to help!
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h3 class="mb-3">Still have questions?</h3>
        <p class="text-muted mb-4">Our team is here to help you get started</p>
        <a href="/pages/contact.php" class="btn btn-primary btn-lg">Contact Us</a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>