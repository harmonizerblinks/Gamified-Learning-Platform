<?php
require_once '../includes/header.php';
require_once '../includes/navbar.php';
$page_title = "Browse Subjects - " . SITE_NAME;

// Get all active subjects with course count
$stmt = $conn->query("
    SELECT s.*, COUNT(c.course_id) as course_count
    FROM subjects s
    LEFT JOIN courses c ON s.subject_id = c.subject_id AND c.is_published = 1
    WHERE s.is_active = 1
    GROUP BY s.subject_id
    ORDER BY s.subject_name ASC
");
$subjects = $stmt->fetchAll();
?>



<!-- Hero Section -->
<section class="bg-purple text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Explore Our Subjects 📚</h1>
        <p class="lead">Choose from a wide range of subjects and start your learning journey</p>
    </div>
</section>

<!-- Subjects Grid -->
<section class="py-5">
    <div class="container">
        <?php if (empty($subjects)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h3>No subjects available yet</h3>
                <p class="text-muted">Check back soon for new learning content!</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($subjects as $subject): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm hover-lift">
                            <div class="card-body text-center p-4">
                                <div class="course-thumbnail">
                                    <?php if (!empty($subject['icon'])): ?>

                                        <img src="<?php echo UPLOAD_URL; ?>icons/<?php echo $subject['icon']; ?>"
                                            class="mb-3" alt="<?php echo htmlspecialchars($subject['subject_name']); ?>">

                                    <?php else: ?>
                                        <div class="subject-icon-placeholder mb-3">
                                            <i class="fas fa-book fa-4x text-purple"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="course-content">

                                    <h4 class="mb-3"><?php echo htmlspecialchars($subject['subject_name']); ?></h4>
                                    <p class="text-muted mb-3"><?php echo htmlspecialchars($subject['description']); ?></p>

                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                        <span class="badge bg-purple-light text-purple me-2">
                                            <i class="fas fa-book-open me-1"></i>
                                            <?php echo $subject['course_count']; ?> Courses
                                        </span>
                                    </div>

                                    <a href="/pages/courses.php?subject_id=<?php echo $subject['subject_id']; ?>"
                                        class="btn btn-primary w-100">View Courses</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>