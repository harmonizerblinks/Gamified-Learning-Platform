
Here is a complete database design for your gamified learning platform:
🗄️ Database Schema Design
1. Users & Authentication
sql-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'learner', 'guest') DEFAULT 'learner',
    profile_picture VARCHAR(255),
    total_xp INT DEFAULT 0,
    current_level INT DEFAULT 1,
    current_streak INT DEFAULT 0,
    longest_streak INT DEFAULT 0,
    last_login_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Levels definition table
CREATE TABLE levels (
    level_id INT PRIMARY KEY AUTO_INCREMENT,
    level_number INT UNIQUE NOT NULL,
    xp_required INT NOT NULL,
    total_xp_required INT NOT NULL,
    title VARCHAR(50),
    description TEXT,
    unlocks TEXT
);

2. Courses & Content
sql-- Subjects table
CREATE TABLE subjects (
    subject_id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT NOT NULL,
    course_title VARCHAR(200) NOT NULL,
    description TEXT,
    difficulty ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'beginner',
    required_level INT DEFAULT 1,
    thumbnail VARCHAR(255),
    estimated_duration INT, -- in minutes
    xp_reward INT DEFAULT 100,
    is_published BOOLEAN DEFAULT TRUE,
    created_by INT, -- admin user_id
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Lessons table
CREATE TABLE lessons (
    lesson_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    lesson_title VARCHAR(200) NOT NULL,
    lesson_order INT NOT NULL,
    lesson_type ENUM('video', 'text', 'pdf', 'mixed') DEFAULT 'text',
    content_url VARCHAR(255), -- video or PDF URL
    content_text TEXT, -- text content
    duration INT, -- in minutes
    xp_reward INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

3. Quizzes & Assessments
sql-- Quizzes table
CREATE TABLE quizzes (
    quiz_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    quiz_title VARCHAR(200) NOT NULL,
    description TEXT,
    passing_score INT DEFAULT 70, -- percentage
    xp_reward INT DEFAULT 30,
    bonus_xp_perfect INT DEFAULT 20, -- bonus for 100% score
    time_limit INT, -- in minutes (NULL = no limit)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

-- Quiz questions table
CREATE TABLE quiz_questions (
    question_id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'true_false', 'fill_blank') DEFAULT 'multiple_choice',
    points INT DEFAULT 1,
    question_order INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id) ON DELETE CASCADE
);

-- Quiz answers table
CREATE TABLE quiz_answers (
    answer_id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    answer_order INT,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(question_id) ON DELETE CASCADE
);

4. User Progress & Enrollment
sql-- User course enrollment
CREATE TABLE user_courses (
    enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completion_date TIMESTAMP NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    is_completed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, course_id)
);

-- User lesson progress
CREATE TABLE user_lesson_progress (
    progress_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    completion_date TIMESTAMP NULL,
    time_spent INT DEFAULT 0, -- in seconds
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (user_id, lesson_id)
);

-- User quiz attempts
CREATE TABLE user_quiz_attempts (
    attempt_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    total_questions INT NOT NULL,
    correct_answers INT NOT NULL,
    passed BOOLEAN DEFAULT FALSE,
    xp_earned INT DEFAULT 0,
    attempt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    time_taken INT, -- in seconds
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id) ON DELETE CASCADE
);

-- User quiz answers (detailed tracking)
CREATE TABLE user_quiz_answers (
    user_answer_id INT PRIMARY KEY AUTO_INCREMENT,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_id INT, -- NULL if not answered
    is_correct BOOLEAN,
    FOREIGN KEY (attempt_id) REFERENCES user_quiz_attempts(attempt_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(question_id) ON DELETE CASCADE,
    FOREIGN KEY (answer_id) REFERENCES quiz_answers(answer_id) ON DELETE SET NULL
);

5. XP & Rewards System
sql-- XP transactions log
CREATE TABLE xp_transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    xp_amount INT NOT NULL,
    xp_type ENUM('lesson', 'quiz', 'course', 'streak', 'badge', 'bonus') NOT NULL,
    reference_id INT, -- lesson_id, quiz_id, or course_id
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Daily streaks tracking
CREATE TABLE daily_streaks (
    streak_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    login_date DATE NOT NULL,
    xp_earned INT DEFAULT 5,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_daily_login (user_id, login_date)
);

6. Badges & Achievements
sql-- Badges definition table
CREATE TABLE badges (
    badge_id INT PRIMARY KEY AUTO_INCREMENT,
    badge_name VARCHAR(100) NOT NULL,
    description TEXT,
    badge_icon VARCHAR(255),
    badge_type ENUM('course', 'quiz', 'streak', 'level', 'special') NOT NULL,
    requirement TEXT, -- e.g., "Complete 5 courses", "Reach Level 5"
    requirement_value INT, -- numeric threshold
    xp_reward INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User badges earned
CREATE TABLE user_badges (
    user_badge_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(badge_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_badge (user_id, badge_id)
);

7. Certificates
sql-- Certificates table
CREATE TABLE certificates (
    certificate_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    certificate_code VARCHAR(50) UNIQUE NOT NULL, -- unique verification code
    issued_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    certificate_url VARCHAR(255), -- PDF or image URL
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_certificate (user_id, course_id)
);

8. Leaderboard (View)
sql-- Leaderboard view (calculated dynamically)
CREATE VIEW leaderboard AS
SELECT 
    u.user_id,
    u.username,
    u.full_name,
    u.profile_picture,
    u.total_xp,
    u.current_level,
    u.current_streak,
    COUNT(DISTINCT ub.badge_id) as total_badges,
    COUNT(DISTINCT c.certificate_id) as total_certificates,
    COUNT(DISTINCT uc.course_id) as completed_courses,
    RANK() OVER (ORDER BY u.total_xp DESC) as rank_position
FROM users u
LEFT JOIN user_badges ub ON u.user_id = ub.user_id
LEFT JOIN certificates c ON u.user_id = c.user_id
LEFT JOIN user_courses uc ON u.user_id = uc.user_id AND uc.is_completed = TRUE
WHERE u.role = 'learner' AND u.is_active = TRUE
GROUP BY u.user_id
ORDER BY u.total_xp DESC;

9. Sample Data Inserts
sql-- Insert levels (1-10)
INSERT INTO levels (level_number, xp_required, total_xp_required, title, unlocks) VALUES
(1, 0, 0, 'Beginner', 'Access to beginner courses'),
(2, 100, 100, 'Novice', 'Intermediate courses'),
(3, 200, 300, 'Learner', 'First badge milestone'),
(4, 300, 600, 'Apprentice', 'Advanced quizzes'),
(5, 500, 1100, 'Scholar', 'Bonus learning materials'),
(6, 700, 1800, 'Expert', 'Expert-level courses'),
(7, 1000, 2800, 'Specialist', 'Exclusive challenges'),
(8, 1500, 4300, 'Master', 'Advanced certificates'),
(9, 2000, 6300, 'Guru', 'Mastery content'),
(10, 2700, 9000, 'Legend', 'Certificate of Excellence + Elite status');

-- Sample badges
INSERT INTO badges (badge_name, description, badge_type, requirement, requirement_value, xp_reward) VALUES
('First Steps', 'Complete your first lesson', 'course', 'Complete 1 lesson', 1, 10),
('Course Completionist', 'Complete your first course', 'course', 'Complete 1 course', 1, 50),
('Quiz Master', 'Pass 5 quizzes with 100% score', 'quiz', 'Perfect quiz scores', 5, 100),
('Week Warrior', 'Maintain a 7-day streak', 'streak', '7-day login streak', 7, 50),
('Level Up!', 'Reach Level 5', 'level', 'Reach level 5', 5, 100),
('Triple Threat', 'Complete 3 courses', 'course', 'Complete 3 courses', 3, 150),
('Elite Learner', 'Reach Level 10', 'level', 'Reach level 10', 10, 500);
```

---

## **📊 Key Relationships**
```
users (1) ←→ (Many) user_courses ←→ (1) courses
users (1) ←→ (Many) user_lesson_progress ←→ (1) lessons
users (1) ←→ (Many) user_quiz_attempts ←→ (1) quizzes
users (1) ←→ (Many) user_badges ←→ (1) badges
users (1) ←→ (Many) certificates ←→ (1) courses
users (1) ←→ (Many) xp_transactions
courses (1) ←→ (Many) lessons
courses (1) ←→ (Many) quizzes
quizzes (1) ←→ (Many) quiz_questions ←→ (Many) quiz_answers

🎯 Key Features of This Design
✅ Complete user tracking - XP, levels, streaks, badges
✅ Detailed progress monitoring - lesson, quiz, course completion
✅ Flexible content structure - subjects → courses → lessons/quizzes
✅ Audit trail - XP transactions, quiz attempts history
✅ Scalable - can handle thousands of users and courses
✅ Performance optimized - indexed foreign keys, efficient queries
✅ Leaderboard view - real-time rankings without heavy queries
This schema supports all features from your documentation! Need any modifications or additional tables? 🚀