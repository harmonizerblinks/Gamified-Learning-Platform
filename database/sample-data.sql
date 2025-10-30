Here are comprehensive sample data INSERT queries for all tables:
🗄️ Complete Sample Data Inserts
1. Levels (1-10)
sql
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

2. Users (Admin + Learners)
sql
INSERT INTO users (username, email, password_hash, full_name, role, total_xp, current_level, current_streak, longest_streak, last_login_date, profile_picture) VALUES
-- Admin
('admin', 'admin@learnplatform.com', '$2y$10$abcdefghijklmnopqrstuv', 'Admin User', 'admin', 0, 1, 0, 0, '2025-10-21', 'admin_avatar.png'),

-- Learners at different levels
('john_doe', 'john@email.com', '$2y$10$abcdefghijklmnopqrstuv', 'John Doe', 'learner', 2500, 7, 15, 20, '2025-10-21', 'john_avatar.png'),
('jane_smith', 'jane@email.com', '$2y$10$abcdefghijklmnopqrstuv', 'Jane Smith', 'learner', 5200, 8, 30, 35, '2025-10-21', 'jane_avatar.png'),
('mike_johnson', 'mike@email.com', '$2y$10$abcdefghijklmnopqrstuv', 'Mike Johnson', 'learner', 1200, 5, 7, 10, '2025-10-21', 'mike_avatar.png'),
('sarah_williams', 'sarah@email.com', '$2y$10$abcdefghijklmnopqrstuv', 'Sarah Williams', 'learner', 9500, 10, 45, 50, '2025-10-21', 'sarah_avatar.png'),
('alex_brown', 'alex@email.com', '$2y$10$abcdefghijklmnopqrstuv', 'Alex Brown', 'learner', 450, 3, 5, 8, '2025-10-21', 'alex_avatar.png'),
('emma_davis', 'emma@email.com', '$2y$10$abcdefghijklmnopqrstuv', 'Emma Davis', 'learner', 3200, 7, 12, 15, '2025-10-21', 'emma_avatar.png'),
('chris_miller', 'chris@email.com', '$2y$10$abcdefghijklmnopqrstuv', 'Chris Miller', 'learner', 150, 2, 3, 5, '2025-10-20', 'chris_avatar.png'),
('lisa_wilson', 'lisa@email.com', '$2y$10$abcdefghijklmnopqrstuv', 'Lisa Wilson', 'learner', 7800, 9, 25, 30, '2025-10-21', 'lisa_avatar.png'),
('david_moore', 'david@email.com', '$2y$10$abcdefghijklmnopqrstuv', 'David Moore', 'learner', 650, 4, 2, 6, '2025-10-21', 'david_avatar.png'),
('amy_taylor', 'amy@email.com', '$2y$10$abcdefghijklmnopqrstuv', 'Amy Taylor', 'learner', 50, 1, 1, 1, '2025-10-21', 'amy_avatar.png');

3. Subjects
sql
INSERT INTO subjects (subject_name, description, icon, is_active) VALUES
('Web Development', 'Learn HTML, CSS, JavaScript, and modern web frameworks', 'web_dev_icon.png', TRUE),
('Data Science', 'Master Python, data analysis, machine learning, and AI', 'data_science_icon.png', TRUE),
('Mobile Development', 'Build iOS and Android apps with React Native and Flutter', 'mobile_dev_icon.png', TRUE),
('Cybersecurity', 'Learn ethical hacking, network security, and data protection', 'cybersec_icon.png', TRUE),
('Digital Marketing', 'SEO, social media marketing, and content strategy', 'marketing_icon.png', TRUE),
('Graphic Design', 'UI/UX design, Adobe tools, and visual communication', 'design_icon.png', TRUE),
('Math', 'Numbers and Operations, Geometry, Algebra,and Statistics & Probability Pro', 'math_icon.png', TRUE),
('English', 'Grammar, Creative Writing, and Comprehension', 'english_icon.png', TRUE),
('Science', 'Earth and Space, Life Science, and Physical Science', 'science_icon.png', TRUE);

4. Courses
sql
INSERT INTO courses (subject_id, course_title, description, difficulty, required_level, thumbnail, estimated_duration, xp_reward, created_by) VALUES
-- Web Development Courses
(1, 'HTML & CSS Fundamentals', 'Learn the basics of web structure and styling', 'beginner', 1, 'html_css_thumb.png', 180, 150, 1),
(1, 'JavaScript Essentials', 'Master JavaScript programming from scratch', 'beginner', 1, 'js_essentials_thumb.png', 240, 200, 1),
(1, 'React for Beginners', 'Build interactive UIs with React', 'intermediate', 3, 'react_thumb.png', 300, 300, 1),
(1, 'Advanced Node.js', 'Backend development with Node.js and Express', 'advanced', 5, 'nodejs_thumb.png', 360, 400, 1);

-- Data Science Courses
(2, 'Python Programming Basics', 'Introduction to Python for data science', 'beginner', 1, 'python_basics_thumb.png', 200, 180, 1),
(2, 'Data Analysis with Pandas', 'Manipulate and analyze data using Pandas', 'intermediate', 3, 'pandas_thumb.png', 280, 320, 1),
(2, 'Machine Learning Fundamentals', 'Introduction to ML algorithms and concepts', 'advanced', 6, 'ml_thumb.png', 400, 500, 1);

-- Mobile Development Courses
(3, 'React Native Crash Course', 'Build mobile apps with React Native', 'intermediate', 4, 'react_native_thumb.png', 320, 350, 1),
(3, 'Flutter Development', 'Create beautiful apps with Flutter', 'intermediate', 4, 'flutter_thumb.png', 340, 380, 1);

-- Cybersecurity Courses
(4, 'Introduction to Cybersecurity', 'Fundamentals of information security', 'beginner', 1, 'cybersec_intro_thumb.png', 220, 200, 1),
(4, 'Ethical Hacking Basics', 'Learn penetration testing techniques', 'advanced', 7, 'ethical_hack_thumb.png', 450, 550, 1);

-- Digital Marketing Courses
(5, 'SEO Fundamentals', 'Master search engine optimization', 'beginner', 1, 'seo_thumb.png', 150, 150, 1),
(5, 'Social Media Marketing', 'Grow your brand on social platforms', 'intermediate', 3, 'smm_thumb.png', 200, 250, 1),

-- Graphic Design Courses
(6, 'Adobe Photoshop Essentials', 'Photo editing and manipulation basics', 'beginner', 1, 'photoshop_thumb.png', 180, 180, 1),
(6, 'UI/UX Design Principles', 'Create user-friendly interfaces', 'intermediate', 4, 'uiux_thumb.png', 300, 350, 1);


-- Math Courses
(7, 'Addition and Subtraction', 'Multiplication and Division', 'beginner', 1, 'photoshop_thumb.png', 180, 180, 1),
(7, 'Simplifying Fractions', 'Perimeter and Area', 'intermediate', 4, 'uiux_thumb.png', 300, 350, 1),


-- English Courses
(8, 'Parts of Speech', 'Punctuation', 'beginner', 1, 'photoshop_thumb.png', 4, 180, 180, 1),
(8 'Funding the Main Idea', 'Making Inferences', 'intermediate', 4, 'uiux_thumb.png', 300, 350, 1),
(8, 'Story Elements', 'Descriptive Writing', 'Advanced', 4, 'uiux_thumb.png', 300, 350, 1),

-- Science Courses
(9, 'The human Body', 'PLants and Photosynthesis', 'beginner', 1, 'photoshop_thumb.png', 180, 180, 1),
(9, 'Forces and Motion', 'Types of Energy ', 'intermediate', 4, 'uiux_thumb.png', 300, 350, 1),
(9, 'Layers of the Earth', 'The solar sysytem ', 'advanced', 4, 'uiux_thumb.png', 300, 350, 1);


5. Lessons
sql
INSERT INTO lessons (course_id, lesson_title, lesson_order, lesson_type, content_url, content_text, duration, xp_reward) VALUES
-- Course 1: HTML & CSS Fundamentals (5 lessons)
(1, 'Introduction to HTML', 1, 'video', 'https://videos.com/html-intro.mp4', NULL, 15, 10),
(1, 'HTML Tags and Elements', 2, 'video', 'https://videos.com/html-tags.mp4', NULL, 20, 15),
(1, 'CSS Basics and Selectors', 3, 'video', 'https://videos.com/css-basics.mp4', NULL, 25, 15),
(1, 'CSS Layouts with Flexbox', 4, 'video', 'https://videos.com/flexbox.mp4', NULL, 30, 20),
(1, 'Building Your First Webpage', 5, 'mixed', 'https://videos.com/first-webpage.mp4', 'Practice project: Create a personal portfolio page', 40, 25),

-- Course 2: JavaScript Essentials (6 lessons)
(2, 'JavaScript Introduction', 1, 'video', 'https://videos.com/js-intro.mp4', NULL, 18, 12),
(2, 'Variables and Data Types', 2, 'video', 'https://videos.com/js-variables.mp4', NULL, 22, 15),
(2, 'Functions and Scope', 3, 'video', 'https://videos.com/js-functions.mp4', NULL, 28, 18),
(2, 'Arrays and Objects', 4, 'video', 'https://videos.com/js-arrays.mp4', NULL, 30, 20),
(2, 'DOM Manipulation', 5, 'video', 'https://videos.com/js-dom.mp4', NULL, 35, 22),
(2, 'JavaScript Projects', 6, 'mixed', 'https://videos.com/js-projects.mp4', 'Build interactive web components', 45, 30),

-- Course 3: React for Beginners (5 lessons)
(3, 'What is React?', 1, 'video', 'https://videos.com/react-intro.mp4', NULL, 20, 15),
(3, 'Components and Props', 2, 'video', 'https://videos.com/react-components.mp4', NULL, 30, 20),
(3, 'State and Lifecycle', 3, 'video', 'https://videos.com/react-state.mp4', NULL, 35, 25),
(3, 'Handling Events', 4, 'video', 'https://videos.com/react-events.mp4', NULL, 28, 20),
(3, 'Building a React App', 5, 'mixed', 'https://videos.com/react-app.mp4', 'Create a Todo List application', 50, 35),

-- Course 5: Python Programming Basics (4 lessons)
(5, 'Getting Started with Python', 1, 'video', 'https://videos.com/python-intro.mp4', NULL, 25, 15),
(5, 'Python Data Structures', 2, 'video', 'https://videos.com/python-data.mp4', NULL, 30, 20),
(5, 'Control Flow and Loops', 3, 'video', 'https://videos.com/python-loops.mp4', NULL, 28, 18),
(5, 'Python Functions', 4, 'video', 'https://videos.com/python-functions.mp4', NULL, 32, 22),

-- Course 10: Introduction to Cybersecurity (4 lessons)
(10, 'Cybersecurity Overview', 1, 'video', 'https://videos.com/cybersec-overview.mp4', NULL, 20, 12),
(10, 'Common Threats and Attacks', 2, 'video', 'https://videos.com/cyber-threats.mp4', NULL, 25, 15),
(10, 'Security Best Practices', 3, 'text', NULL, 'Learn how to protect your systems and data...', 30, 18),
(10, 'Encryption Basics', 4, 'video', 'https://videos.com/encryption.mp4', NULL, 28, 20);

6. Quizzes
sql
INSERT INTO quizzes (course_id, quiz_title, description, passing_score, xp_reward, bonus_xp_perfect, time_limit) VALUES
(1, 'HTML & CSS Final Quiz', 'Test your knowledge of HTML and CSS', 70, 30, 20, 30),
(2, 'JavaScript Fundamentals Quiz', 'Assess your JavaScript skills', 70, 40, 25, 40),
(3, 'React Concepts Quiz', 'Test your understanding of React', 75, 50, 30, 35),
(5, 'Python Basics Assessment', 'Evaluate your Python knowledge', 70, 35, 20, 30),
(10, 'Cybersecurity Quiz', 'Test your security knowledge', 75, 40, 25, 25);

7. Quiz Questions
sql
INSERT INTO quiz_questions (quiz_id, question_text, question_type, points, question_order) VALUES
-- Quiz 1: HTML & CSS (5 questions)
(1, 'What does HTML stand for?', 'multiple_choice', 1, 1),
(1, 'Which CSS property is used to change text color?', 'multiple_choice', 1, 2),
(1, 'The <div> element is a block-level element.', 'true_false', 1, 3),
(1, 'Which property is used to create spacing between elements?', 'multiple_choice', 1, 4),
(1, 'CSS stands for Cascading Style Sheets.', 'true_false', 1, 5),

-- Quiz 2: JavaScript (5 questions)
(2, 'Which keyword is used to declare a variable in JavaScript?', 'multiple_choice', 1, 1),
(2, 'What is the correct syntax for a function in JavaScript?', 'multiple_choice', 1, 2),
(2, 'JavaScript is a case-sensitive language.', 'true_false', 1, 3),
(2, 'Which method is used to add an element to an array?', 'multiple_choice', 1, 4),
(2, 'The === operator checks for both value and type.', 'true_false', 1, 5),

-- Quiz 3: React (4 questions)
(3, 'What is JSX in React?', 'multiple_choice', 1, 1),
(3, 'Components in React must start with a capital letter.', 'true_false', 1, 2),
(3, 'Which hook is used for managing state?', 'multiple_choice', 1, 3),
(3, 'Props are read-only in React.', 'true_false', 1, 4),

-- Quiz 4: Python (4 questions)
(4, 'Which of the following is the correct way to create a list in Python?', 'multiple_choice', 1, 1),
(4, 'Python uses indentation to define code blocks.', 'true_false', 1, 2),
(4, 'What keyword is used to define a function in Python?', 'multiple_choice', 1, 3),
(4, 'Python is an interpreted language.', 'true_false', 1, 4),

-- Quiz 5: Cybersecurity (4 questions)
(5, 'What does VPN stand for?', 'multiple_choice', 1, 1),
(5, 'Phishing is a type of social engineering attack.', 'true_false', 1, 2),
(5, 'Which of these is the strongest password?', 'multiple_choice', 1, 3),
(5, 'Two-factor authentication adds an extra layer of security.', 'true_false', 1, 4);

8. Quiz Answers
sql
INSERT INTO quiz_answers (question_id, answer_text, is_correct, answer_order) VALUES
-- Question 1 answers
(1, 'HyperText Markup Language', TRUE, 1),
(1, 'High Tech Modern Language', FALSE, 2),
(1, 'Home Tool Markup Language', FALSE, 3),
(1, 'Hyperlinks and Text Markup Language', FALSE, 4),

-- Question 2 answers
(2, 'color', TRUE, 1),
(2, 'text-color', FALSE, 2),
(2, 'font-color', FALSE, 3),
(2, 'text-style', FALSE, 4),

-- Question 3 answers (true/false)
(3, 'True', TRUE, 1),
(3, 'False', FALSE, 2),

-- Question 4 answers
(4, 'margin', TRUE, 1),
(4, 'spacing', FALSE, 2),
(4, 'border-spacing', FALSE, 3),
(4, 'padding-between', FALSE, 4),

-- Question 5 answers (true/false)
(5, 'True', TRUE, 1),
(5, 'False', FALSE, 2),

-- Question 6 answers (JavaScript)
(6, 'var', TRUE, 1),
(6, 'variable', FALSE, 2),
(6, 'v', FALSE, 3),
(6, 'int', FALSE, 4),

-- Question 7 answers
(7, 'function myFunc() {}', TRUE, 1),
(7, 'def myFunc():', FALSE, 2),
(7, 'func myFunc() {}', FALSE, 3),
(7, 'function: myFunc() {}', FALSE, 4),

-- Question 8 answers (true/false)
(8, 'True', TRUE, 1),
(8, 'False', FALSE, 2),

-- Question 9 answers
(9, 'push()', TRUE, 1),
(9, 'add()', FALSE, 2),
(9, 'append()', FALSE, 3),
(9, 'insert()', FALSE, 4),

-- Question 10 answers (true/false)
(10, 'True', TRUE, 1),
(10, 'False', FALSE, 2),

-- Question 11 answers (React)
(11, 'JavaScript XML - a syntax extension', TRUE, 1),
(11, 'Java Syntax Extension', FALSE, 2),
(11, 'JavaScript Cross-platform Language', FALSE, 3),
(11, 'Just Simple XML', FALSE, 4),

-- Question 12 answers (true/false)
(12, 'True', TRUE, 1),
(12, 'False', FALSE, 2),

-- Question 13 answers
(13, 'useState', TRUE, 1),
(13, 'useEffect', FALSE, 2),
(13, 'useContext', FALSE, 3),
(13, 'useReducer', FALSE, 4),

-- Question 14 answers (true/false)
(14, 'True', TRUE, 1),
(14, 'False', FALSE, 2),

-- Question 15 answers (Python)
(15, '[1, 2, 3]', TRUE, 1),
(15, '(1, 2, 3)', FALSE, 2),
(15, '{1, 2, 3}', FALSE, 3),
(15, 'list(1, 2, 3)', FALSE, 4),

-- Question 16 answers (true/false)
(16, 'True', TRUE, 1),
(16, 'False', FALSE, 2),

-- Question 17 answers
(17, 'def', TRUE, 1),
(17, 'function', FALSE, 2),
(17, 'func', FALSE, 3),
(17, 'define', FALSE, 4),

-- Question 18 answers (true/false)
(18, 'True', TRUE, 1),
(18, 'False', FALSE, 2),

-- Question 19 answers (Cybersecurity)
(19, 'Virtual Private Network', TRUE, 1),
(19, 'Very Private Network', FALSE, 2),
(19, 'Virtual Public Network', FALSE, 3),
(19, 'Verified Private Network', FALSE, 4),

-- Question 20 answers (true/false)
(20, 'True', TRUE, 1),
(20, 'False', FALSE, 2),

-- Question 21 answers
(21, 'Tr0ng$P@ssw0rd!2024', TRUE, 1),
(21, 'password123', FALSE, 2),
(21, '12345678', FALSE, 3),
(21, 'myname', FALSE, 4),

-- Question 22 answers (true/false)
(22, 'True', TRUE, 1),
(22, 'False', FALSE, 2);

9. User Course Enrollments
sql
INSERT INTO user_courses (user_id, course_id, enrollment_date, completion_date, progress_percentage, is_completed) VALUES
-- Sarah (Level 10) - 5 completed courses
(5, 1, '2025-09-01 10:00:00', '2025-09-05 14:30:00', 100.00, TRUE),
(5, 2, '2025-09-06 09:00:00', '2025-09-12 16:00:00', 100.00, TRUE),
(5, 3, '2025-09-13 10:30:00', '2025-09-20 15:00:00', 100.00, TRUE),
(5, 5, '2025-09-21 11:00:00', '2025-09-28 13:00:00', 100.00, TRUE),
(5, 10, '2025-09-29 09:30:00', '2025-10-05 17:00:00', 100.00, TRUE),

-- Jane (Level 8) - 4 completed, 1 in progress
(3, 1, '2025-09-10 11:00:00', '2025-09-15 14:00:00', 100.00, TRUE),
(3, 2, '2025-09-16 10:00:00', '2025-09-23 15:30:00', 100.00, TRUE),
(3, 5, '2025-09-24 09:00:00', '2025-10-01 16:00:00', 100.00, TRUE),
(3, 3, '2025-10-02 10:30:00', NULL, 60.00, FALSE),
(3, 10, '2025-10-10 11:00:00', '2025-10-16 14:00:00', 100.00, TRUE),

-- John (Level 7) - 3 completed, 1 in progress
(2, 1, '2025-09-15 09:30:00', '2025-09-20 13:00:00', 100.00, TRUE),
(2, 2, '2025-09-21 10:00:00', '2025-09-28 14:30:00', 100.00, TRUE),
(2, 5, '2025-09-29 11:30:00', '2025-10-06 15:00:00', 100.00, TRUE),
(2, 3, '2025-10-07 09:00:00', NULL, 40.00, FALSE),

-- Emma (Level 7) - 3 completed
(7, 1, '2025-09-12 10:00:00', '2025-09-18 15:00:00', 100.00, TRUE),
(7, 12, '2025-09-19 09:30:00', '2025-09-24 14:00:00', 100.00, TRUE),
(7, 14, '2025-09-25 11:00:00', '2025-10-02 16:30:00', 100.00, TRUE),

-- Lisa (Level 9) - 4 completed
(9, 1, '2025-08-20 10:00:00', '2025-08-26 14:00:00', 100.00, TRUE),
(9, 2, '2025-08-27 09:00:00', '2025-09-03 15:00:00', 100.00, TRUE),
(9, 3, '2025-09-04 10:30:00', '2025-09-11 16:00:00', 100.00, TRUE),
(9, 6, '2025-09-12 11:00:00', '2025-09-20 17:00:00', 100.00, TRUE),

-- Mike (Level 5) - 2 completed, 1 in progress
(4, 1, '2025-10-01 09:00:00', '2025-10-07 13:00:00', 100.00, TRUE),
(4, 5, '2025-10-08 10:30:00', '2025-10-14 15:30:00', 100.00, TRUE),
(4, 2, '2025-10-15 11:00:00', NULL, 33.33, FALSE),

-- David (Level 4) - 1 completed, 1 in progress
(10, 1, '2025-10-10 10:00:00', '2025-10-16 14:30:00', 100.00, TRUE),
(10, 12, '2025-10-17 09:30:00', NULL, 50.00, FALSE),

-- Alex (Level 3) - 1 in progress
(6, 1, '2025-10-18 11:00:00', NULL, 60.00, FALSE),

-- Chris (Level 2) - 1 in progress
(8, 1, '2025-10-19 10:30:00', NULL, 40.00, FALSE),

-- Amy (Level 1) - just started
(11, 1, '2025-10-21 09:00:00', NULL, 20.00, FALSE);

10. User Lesson Progress
sql
INSERT INTO user_lesson_progress (user_id, lesson_id, is_completed, completion_date, time_spent) VALUES
-- Sarah - completed all lessons in courses 1, 2, 3, 5, 10
(5, 1, TRUE, '2025-09-01 10:30:00', 900),
(5, 2, TRUE, '2025-09-02 11:00:00', 1200),
(5, 3, TRUE, '2025-09-03 14:00:00', 1500),
(5, 4, TRUE, '2025-09-04 15:30:00', 1800),
(5, 5, TRUE, '2025-09-05 14:00:00', 2400),
(5, 6, TRUE, '2025-09-06 10:00:00', 1080),
(5, 7, TRUE, '2025-09-07 11:30:00', 1320),
(5, 8, TRUE, '2025-09-08 13:00:00', 1680),
(5, 9, TRUE, '2025-09-09 14:30:00', 1800),
(5, 10, TRUE, '2025-09-10 16:00:00', 2100),
(5, 11, TRUE, '2025-09-11 17:00:00', 2700),

-- Jane - completed courses 1, 2, 5, 10 and 60% of course 3
(3, 1, TRUE, '2025-09-10 12:00:00', 920),
(3, 2, TRUE, '2025-09-11 13:00:00', 1250),
(3, 3, TRUE, '2025-09-12 14:30:00', 1520),
(3, 4, TRUE, '2025-09-13 15:00:00', 1850),
(3, 5, TRUE, '2025-09-14 16:00:00', 2450),
(3, 6, TRUE, '2025-09-16 11:00:00', 1100),
(3, 7, TRUE, '2025-09-17 12:30:00', 1350),
(3, 8, TRUE, '2025-09-18 14:00:00', 1700),
(3, 16, TRUE, '2025-10-02 10:30:00', 1200),
(3, 17, TRUE, '2025-10-03 11:00:00', 1800),
(3, 18, TRUE, '2025-10-04 12:30:00', 2100),

-- John - completed courses 1, 2, 5 and 40% of course 3
(2, 1, TRUE, '2025-09-15 10:00:00', 910),
(2, 2, TRUE, '2025-09-16 11:30:00', 1230),
(2, 3, TRUE, '2025-09-17 13:00:00', 1510),
(2, 4, TRUE, '2025-09-18 14:30:00', 1820),
(2, 5, TRUE, '2025-09-19 15:30:00', 2420),
(2, 16, TRUE, '2025-10-07 09:30:00', 1220),
(2, 17, TRUE, '2025-10-08 11:00:00', 1850),

-- Alex - 60% of course 1 completed
(6, 1, TRUE, '2025-10-18 11:30:00', 880),
(6, 2, TRUE, '2025-10-19 10:00:00', 1180),
(6, 3, TRUE, '2025-10-20 11:30:00', 1480),

-- Chris - 40% of course 1 completed
(8, 1, TRUE, '2025-10-19 11:00:00', 900),
(8, 2, TRUE, '2025-10-20 12:00:00', 1200),

-- Amy - 20% of course 1 completed
(11, 1, TRUE, '2025-10-21 09:30:00', 920);

11. User Quiz Attempts
sql

INSERT INTO user_quiz_attempts (user_id, quiz_id, score, total_questions, correct_answers, passed, xp_earned, attempt_date, time_taken) VALUES
-- Sarah - perfect scores on all quizzes
(5, 1, 100.00, 5, 5, TRUE, 50, '2025-09-05 15:00:00', 1200),
(5, 2, 100.00, 5, 5, TRUE, 65, '2025-09-12 16:30:00', 1500),
(5, 3, 100.00, 4, 4, TRUE, 80, '2025-09-20 15:30:00', 1400),
(5, 4, 100.00, 4, 4, TRUE, 55, '2025-09-28 13:30:00', 1100),
(5, 5, 100.00, 4, 4, TRUE, 65, '2025-10-05 17:30:00', 1000),

-- Jane - high scores
(3, 1, 100.00, 5, 5, TRUE, 50, '2025-09-15 14:30:00', 1300),
(3, 2, 80.00, 5, 4, TRUE, 40, '2025-09-23 16:00:00', 1800),
(3, 4, 100.00, 4, 4, TRUE, 55, '2025-10-01 16:30:00', 1150),
(3, 5, 100.00, 4, 4, TRUE, 65, '2025-10-16 14:30:00', 1050),

-- John - good scores
(2, 1, 80.00, 5, 4, TRUE, 30, '2025-09-20 13:30:00', 1400),
(2, 2, 100.00, 5, 5, TRUE, 65, '2025-09-28 15:00:00', 1600),
(2, 4, 75.00, 4, 3, TRUE, 35, '2025-10-06 15:30:00', 1200),

-- Mike - passing scores
(4, 1, 80.00, 5, 4, TRUE, 30, '2025-10-07 13:30:00', 1350),
(4, 4, 75.00, 4, 3, TRUE, 35, '2025-10-14 16:00:00', 1250),

-- David - one quiz passed
(10, 1, 80.00, 5, 4, TRUE, 30, '2025-10-16 15:00:00', 1420);

12. User Quiz Answers (Sample for Sarahs first quiz)

sql

INSERT INTO user_quiz_answers (attempt_id, question_id, answer_id, is_correct) VALUES
(1, 1, 1, TRUE),
(1, 2, 5, TRUE),
(1, 3, 7, TRUE),
(1, 4, 9, TRUE),
(1, 5, 13, TRUE);

13. XP Transactions
sql

INSERT INTO xp_transactions (user_id, xp_amount, xp_type, reference_id, description, created_at) VALUES
-- Sarah's XP history
(5, 10, 'lesson', 1, 'Completed: Introduction to HTML', '2025-09-01 10:30:00'),
(5, 15, 'lesson', 2, 'Completed: HTML Tags and Elements', '2025-09-02 11:00:00'),
(5, 15, 'lesson', 3, 'Completed: CSS Basics and Selectors', '2025-09-03 14:00:00'),
(5, 20, 'lesson', 4, 'Completed: CSS Layouts with Flexbox', '2025-09-04 15:30:00'),
(5, 25, 'lesson', 5, 'Completed: Building Your First Webpage', '2025-09-05 14:00:00'),
(5, 50, 'quiz', 1, 'Perfect score on HTML & CSS Quiz', '2025-09-05 15:00:00'),
(5, 150, 'course', 1, 'Completed: HTML & CSS Fundamentals', '2025-09-05 15:30:00'),
(5, 10, 'badge', 1, 'Earned badge: First Steps', '2025-09-01 10:30:00'),
(5, 50, 'badge', 2, 'Earned badge: Course Completionist', '2025-09-05 15:30:00'),
(5, 5, 'streak', NULL, 'Daily login bonus - Day 1', '2025-09-01 09:00:00'),
(5, 5, 'streak', NULL, 'Daily login bonus - Day 2', '2025-09-02 09:00:00'),
(5, 100, 'badge', 3, 'Earned badge: Quiz Master', '2025-10-05 17:30:00'),
(5, 500, 'badge', 7, 'Earned badge: Elite Learner (Level 10)', '2025-10-10 10:00:00'),

-- Jane's XP history (partial)
(3, 10, 'lesson', 1, 'Completed: Introduction to HTML', '2025-09-10 12:00:00'),
(3, 15, 'lesson', 2, 'Completed: HTML Tags and Elements', '2025-09-11 13:00:00'),
(3, 50, 'quiz', 1, 'Perfect score on HTML & CSS Quiz', '2025-09-15 14:30:00'),
(3, 150, 'course', 1, 'Completed: HTML & CSS Fundamentals', '2025-09-15 15:00:00'),
(3, 10, 'badge', 1, 'Earned badge: First Steps', '2025-09-10 12:00:00'),
(3, 50, 'badge', 2, 'Earned badge: Course Completionist', '2025-09-15 15:00:00'),
(3, 5, 'streak', NULL, 'Daily login bonus - Day 1', '2025-09-10 09:00:00'),

-- John's XP history (partial)
(2, 10, 'lesson', 1, 'Completed: Introduction to HTML', '2025-09-15 10:00:00'),
(2, 15, 'lesson', 2, 'Completed: HTML Tags and Elements', '2025-09-16 11:30:00'),
(2, 30, 'quiz', 1, 'Passed HTML & CSS Quiz', '2025-09-20 13:30:00'),
(2, 150, 'course', 1, 'Completed: HTML & CSS Fundamentals', '2025-09-20 14:00:00'),
(2, 10, 'badge', 1, 'Earned badge: First Steps', '2025-09-15 10:00:00'),
(2, 50, 'badge', 2, 'Earned badge: Course Completionist', '2025-09-20 14:00:00'),

-- Amy (new user)
(11, 10, 'lesson', 1, 'Completed: Introduction to HTML', '2025-10-21 09:30:00'),
(11, 10, 'badge', 1, 'Earned badge: First Steps', '2025-10-21 09:30:00'),
(11, 5, 'streak', NULL, 'Daily login bonus - Day 1', '2025-10-21 09:00:00');

14. Daily Streaks
sql
INSERT INTO daily_streaks (user_id, login_date, xp_earned) VALUES
-- Sarah - 45 day streak (showing last 10 days)
(5, '2025-10-12', 5),
(5, '2025-10-13', 5),
(5, '2025-10-14', 5),
(5, '2025-10-15', 5),
(5, '2025-10-16', 5),
(5, '2025-10-17', 5),
(5, '2025-10-18', 5),
(5, '2025-10-19', 5),
(5, '2025-10-20', 5),
(5, '2025-10-21', 5),

-- Jane - 30 day streak (showing last 7 days)
(3, '2025-10-15', 5),
(3, '2025-10-16', 5),
(3, '2025-10-17', 5),
(3, '2025-10-18', 5),
(3, '2025-10-19', 5),
(3, '2025-10-20', 5),
(3, '2025-10-21', 5),

-- John - 15 day streak (showing last 5 days)
(2, '2025-10-17', 5),
(2, '2025-10-18', 5),
(2, '2025-10-19', 5),
(2, '2025-10-20', 5),
(2, '2025-10-21', 5),

-- Mike - 7 day streak
(4, '2025-10-15', 5),
(4, '2025-10-16', 5),
(4, '2025-10-17', 5),
(4, '2025-10-18', 5),
(4, '2025-10-19', 5),
(4, '2025-10-20', 5),
(4, '2025-10-21', 5),

-- Amy - just started (1 day)
(11, '2025-10-21', 5);

15. Badges (Definitions)
sql
INSERT INTO badges (badge_name, description, badge_icon, badge_type, requirement, requirement_value, xp_reward, is_active) VALUES
('First Steps', 'Complete your first lesson', 'badge_first_steps.png', 'course', 'Complete 1 lesson', 1, 10, TRUE),
('Course Completionist', 'Complete your first course', 'badge_first_course.png', 'course', 'Complete 1 course', 1, 50, TRUE),
('Quiz Master', 'Pass 5 quizzes with 100% score', 'badge_quiz_master.png', 'quiz', 'Perfect quiz scores', 5, 100, TRUE),
('Week Warrior', 'Maintain a 7-day streak', 'badge_week_warrior.png', 'streak', '7-day login streak', 7, 50, TRUE),
('Level Up!', 'Reach Level 5', 'badge_level_5.png', 'level', 'Reach level 5', 5, 100, TRUE),
('Triple Threat', 'Complete 3 courses', 'badge_triple_threat.png', 'course', 'Complete 3 courses', 3, 150, TRUE),
('Elite Learner', 'Reach Level 10', 'badge_level_10.png', 'level', 'Reach level 10', 10, 500, TRUE),
('Dedication', 'Maintain a 30-day streak', 'badge_dedication.png', 'streak', '30-day login streak', 30, 200, TRUE),
('Knowledge Seeker', 'Complete 10 lessons', 'badge_10_lessons.png', 'course', 'Complete 10 lessons', 10, 80, TRUE),
('Perfect Student', 'Get 100% on 10 quizzes', 'badge_perfect_10.png', 'quiz', 'Perfect quiz scores', 10, 250, TRUE);

16. User Badges Earned
sql
INSERT INTO user_badges (user_id, badge_id, earned_date) VALUES
-- Sarah - earned all major badges
(5, 1, '2025-09-01 10:30:00'),  -- First Steps
(5, 2, '2025-09-05 15:30:00'),  -- Course Completionist
(5, 3, '2025-10-05 17:30:00'),  -- Quiz Master
(5, 4, '2025-09-07 09:00:00'),  -- Week Warrior
(5, 5, '2025-09-22 10:00:00'),  -- Level Up (Level 5)
(5, 6, '2025-09-20 15:30:00'),  -- Triple Threat
(5, 7, '2025-10-10 10:00:00'),  -- Elite Learner (Level 10)
(5, 8, '2025-10-01 09:00:00'),  -- Dedication (30-day streak)
(5, 9, '2025-09-11 17:00:00'),  -- Knowledge Seeker

-- Jane - multiple badges
(3, 1, '2025-09-10 12:00:00'),  -- First Steps
(3, 2, '2025-09-15 15:00:00'),  -- Course Completionist
(3, 3, '2025-10-16 14:30:00'),  -- Quiz Master
(3, 4, '2025-09-16 09:00:00'),  -- Week Warrior
(3, 5, '2025-09-30 10:00:00'),  -- Level Up (Level 5)
(3, 6, '2025-10-01 16:30:00'),  -- Triple Threat
(3, 8, '2025-10-10 09:00:00'),  -- Dedication

-- John - several badges
(2, 1, '2025-09-15 10:00:00'),  -- First Steps
(2, 2, '2025-09-20 14:00:00'),  -- Course Completionist
(2, 4, '2025-09-21 09:00:00'),  -- Week Warrior
(2, 5, '2025-10-05 10:00:00'),  -- Level Up (Level 5)
(2, 6, '2025-10-06 15:30:00'),  -- Triple Threat

-- Lisa - advanced badges
(9, 1, '2025-08-20 10:30:00'),  -- First Steps
(9, 2, '2025-08-26 14:30:00'),  -- Course Completionist
(9, 4, '2025-08-26 09:00:00'),  -- Week Warrior
(9, 5, '2025-09-08 10:00:00'),  -- Level Up (Level 5)
(9, 6, '2025-09-11 16:30:00'),  -- Triple Threat
(9, 8, '2025-09-19 09:00:00'),  -- Dedication

-- Mike - beginner badges
(4, 1, '2025-10-01 09:30:00'),  -- First Steps
(4, 2, '2025-10-07 13:30:00'),  -- Course Completionist
(4, 4, '2025-10-07 09:00:00'),  -- Week Warrior

-- Alex - first badge
(6, 1, '2025-10-18 11:30:00'),  -- First Steps

-- Chris - first badge
(8, 1, '2025-10-19 11:00:00'),  -- First Steps

-- Amy - first badge
(11, 1, '2025-10-21 09:30:00');  -- First Steps

17. Certificates
sql
INSERT INTO certificates (user_id, course_id, certificate_code, issued_date, certificate_url) VALUES
-- Sarah - 5 certificates
(5, 1, 'CERT-HTML-5-20250905', '2025-09-05 15:30:00', 'certificates/sarah_html_css.pdf'),
(5, 2, 'CERT-JS-5-20250912', '2025-09-12 16:00:00', 'certificates/sarah_javascript.pdf'),
(5, 3, 'CERT-REACT-5-20250920', '2025-09-20 15:00:00', 'certificates/sarah_react.pdf'),
(5, 5, 'CERT-PYTHON-5-20250928', '2025-09-28 13:00:00', 'certificates/sarah_python.pdf'),
(5, 10, 'CERT-CYBER-5-20251005', '2025-10-05 17:00:00', 'certificates/sarah_cybersecurity.pdf'),

-- Jane - 4 certificates
(3, 1, 'CERT-HTML-3-20250915', '2025-09-15 14:00:00', 'certificates/jane_html_css.pdf'),
(3, 2, 'CERT-JS-3-20250923', '2025-09-23 15:30:00', 'certificates/jane_javascript.pdf'),
(3, 5, 'CERT-PYTHON-3-20251001', '2025-10-01 16:00:00', 'certificates/jane_python.pdf'),
(3, 10, 'CERT-CYBER-3-20251016', '2025-10-16 14:00:00', 'certificates/jane_cybersecurity.pdf'),

-- John - 3 certificates
(2, 1, 'CERT-HTML-2-20250920', '2025-09-20 13:00:00', 'certificates/john_html_css.pdf'),
(2, 2, 'CERT-JS-2-20250928', '2025-09-28 14:30:00', 'certificates/john_javascript.pdf'),
(2, 5, 'CERT-PYTHON-2-20251006', '2025-10-06 15:00:00', 'certificates/john_python.pdf'),

-- Lisa - 4 certificates
(9, 1, 'CERT-HTML-9-20250826', '2025-08-26 14:00:00', 'certificates/lisa_html_css.pdf'),
(9, 2, 'CERT-JS-9-20250903', '2025-09-03 15:00:00', 'certificates/lisa_javascript.pdf'),
(9, 3, 'CERT-REACT-9-20250911', '2025-09-11 16:00:00', 'certificates/lisa_react.pdf'),
(9, 6, 'CERT-PANDAS-9-20250920', '2025-09-20 17:00:00', 'certificates/lisa_pandas.pdf'),

-- Mike - 2 certificates
(4, 1, 'CERT-HTML-4-20251007', '2025-10-07 13:00:00', 'certificates/mike_html_css.pdf'),
(4, 5, 'CERT-PYTHON-4-20251014', '2025-10-14 15:30:00', 'certificates/mike_python.pdf'),

-- David - 1 certificate
(10, 1, 'CERT-HTML-10-20251016', '2025-10-16 14:30:00', 'certificates/david_html_css.pdf');

🎯 Quick Verification Queries
After inserting all data, run these to verify:
sql-- Check user levels and XP
SELECT username, total_xp, current_level, current_streak FROM users WHERE role = 'learner' ORDER BY total_xp DESC;

-- Check leaderboard
SELECT * FROM leaderboard LIMIT 10;

-- Check course completions
SELECT u.username, COUNT(uc.course_id) as completed_courses 
FROM users u 
LEFT JOIN user_courses uc ON u.user_id = uc.user_id AND uc.is_completed = TRUE
WHERE u.role = 'learner'
GROUP BY u.user_id
ORDER BY completed_courses DESC;

-- Check badges earned
SELECT u.username, COUNT(ub.badge_id) as total_badges
FROM users u
LEFT JOIN user_badges ub ON u.user_id = ub.user_id
WHERE u.role = 'learner'
GROUP BY u.user_id
ORDER BY total_badges DESC;

-- Check certificates issued
SELECT u.username, COUNT(c.certificate_id) as total_certificates
FROM users u
LEFT JOIN certificates c ON u.user_id = c.user_id
GROUP BY u.user_id
ORDER BY total_certificates DESC;

This gives you a fully populated database with:

✅ 11 users (1 admin, 10 learners at different levels)
✅ 6 subjects, 15 courses, 25+ lessons
✅ 5 quizzes with questions and answers
✅ Complete progress tracking
✅ XP transactions, badges, certificates
✅ Realistic streaks and leaderboard data

Ready to build your gamified learning platform! 🚀🎮

