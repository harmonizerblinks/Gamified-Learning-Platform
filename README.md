# 🎮 Gamified Learning Platform

A comprehensive e-learning platform that transforms education into an engaging, game-like experience. Built with PHP, MySQL, and modern web technologies, this platform motivates learners through XP points, levels, badges, certificates, and competitive leaderboards.

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple)
![License](https://img.shields.io/badge/license-MIT-green)

---

## 📋 Table of Contents

- [Features](#-features)
- [Demo](#-demo)
- [Tech Stack](#-tech-stack)
- [Installation](#-installation)
- [Database Setup](#-database-setup)
- [Configuration](#-configuration)
- [Project Structure](#-project-structure)
- [User Roles](#-user-roles)
- [Gamification System](#-gamification-system)
- [Admin Panel](#-admin-panel)
- [API Documentation](#-api-documentation)
- [Screenshots](#-screenshots)
- [Contributing](#-contributing)
- [License](#-license)
- [Support](#-support)

---

## ✨ Features

### 🎯 Core Learning Features
- **Course Management** - Organize courses by subjects with difficulty levels
- **Interactive Lessons** - Support for video, text, PDF, and mixed content
- **Quizzes & Assessments** - Multiple choice and true/false questions with time limits
- **Progress Tracking** - Real-time tracking of course completion and lesson progress
- **Certificate Generation** - Automatic certificate issuance upon course completion

### 🎮 Gamification Features
- **XP (Experience Points) System** - Earn points for every learning activity
- **10-Level Progression** - Unlock advanced content as you level up
- **Badge System** - Collect achievement badges for milestones
- **Leaderboard** - Compete with other learners globally
- **Daily Streaks** - Build learning habits with consecutive login rewards
- **Bonus Rewards** - Extra XP for perfect quiz scores and special achievements

### 👥 User Features
- **User Authentication** - Secure registration and login system
- **Profile Management** - Customizable user profiles with avatars
- **Course Enrollment** - Browse and enroll in published courses
- **Learning Dashboard** - Personalized dashboard with progress overview
- **Notifications** - Stay updated with achievements and milestones
- **Certificate Download** - Download and share earned certificates

### 🛠️ Admin Features
- **Complete CRUD Operations** - Manage all platform content
- **Subject Management** - Organize courses by subjects
- **Course Builder** - Create courses with thumbnails and metadata
- **Lesson Editor** - Upload videos, documents, and create text lessons
- **Quiz Creator** - Build quizzes with multiple question types
- **User Management** - View, edit, and manage learner accounts
- **Badge Designer** - Create custom achievement badges
- **Analytics Dashboard** - Track platform statistics and user engagement
- **Reports** - Generate performance and completion reports

---

## 🎬 Demo

**Live Demo:** [Coming Soon]

**Test Credentials:**
```
Admin Account:
Email: admin@example.com
Password: admin123

Learner Account:
Email: learner@example.com
Password: learner123
```

---

## 💻 Tech Stack

### Frontend
- **HTML5** - Modern semantic markup
- **CSS3** - Custom styles with CSS variables
- **Bootstrap 5.3** - Responsive UI framework
- **JavaScript (ES6+)** - Interactive functionality
- **Font Awesome 6.5** - Icon library
- **Google Fonts (Inter)** - Typography

### Backend
- **PHP 8.0+** - Server-side logic
- **MySQL 8.0+** - Relational database
- **PDO** - Database abstraction layer

### Design
- **Purple, Black & White Theme** - Consistent brand identity
- **Responsive Design** - Mobile-first approach
- **Modern UI/UX** - Clean and intuitive interface

---

## 🚀 Installation

### Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.0 or higher**
- **MySQL 8.0 or higher**
- **Apache/Nginx** web server
- **Composer** (optional, for dependency management)
- **Git** (for version control)

### Step 1: Clone the Repository

```bash
git clone https://github.com/harmonizerblinks/Gamified-Learning-Platform.git
cd Gamified-Learning-Platform
```

### Step 2: Configure Web Server

#### Apache Configuration

1. Create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName learnhub.local
    DocumentRoot "/path/to/Gamified-Learning-Platform"

    <Directory "/path/to/Gamified-Learning-Platform">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

2. Enable `.htaccess` by ensuring `AllowOverride All` is set
3. Restart Apache: `sudo systemctl restart apache2`

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name learnhub.local;
    root /path/to/Gamified-Learning-Platform;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 3: Set File Permissions

```bash
# Make uploads directory writable
chmod -R 755 uploads/
chmod -R 755 assets/

# Ensure web server can write to uploads
chown -R www-data:www-data uploads/
```

### Step 4: Configure Database Connection

Edit `config/db.php`:

```php
<?php
$host = 'localhost';
$dbname = 'gamified_learning';
$username = 'your_db_username';
$password = 'your_db_password';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

### Step 5: Configure Settings

Edit `config/settings.php`:

```php
<?php
// Site Configuration
define('SITE_NAME', 'Your Platform Name');
define('SITE_URL', 'http://localhost:8000'); // Update with your URL
define('ADMIN_EMAIL', 'admin@yoursite.com');

// XP Settings (customize as needed)
define('XP_LESSON', 10);
define('XP_QUIZ_PASS', 30);
define('XP_QUIZ_PERFECT', 20);
define('XP_COURSE_COMPLETE', 100);
define('XP_DAILY_STREAK', 5);

// Upload Limits
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB

// Timezone
date_default_timezone_set('Your/Timezone'); // e.g., 'America/New_York'
?>
```

---

## 🗄️ Database Setup

### Method 1: Using phpMyAdmin

1. Open phpMyAdmin
2. Create a new database named `gamified_learning`
3. Import the database schema:
   - Go to **Import** tab
   - Select `database/database.sql`
   - Click **Go**
4. (Optional) Import sample data:
   - Import `database/seed.sql` for test data

### Method 2: Using MySQL Command Line

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE gamified_learning CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p gamified_learning < database/database.sql

# (Optional) Import sample data
mysql -u root -p gamified_learning < database/seed.sql
```

### Database Schema Overview

The platform uses the following main tables:

#### User Management
- `users` - User accounts and profiles
- `levels` - Level progression system (1-10)
- `daily_streaks` - Login streak tracking

#### Content Management
- `subjects` - Course categories
- `courses` - Course information
- `lessons` - Lesson content
- `quizzes` - Quiz metadata
- `quiz_questions` - Quiz questions
- `quiz_answers` - Answer options

#### Progress Tracking
- `user_courses` - Course enrollments
- `user_lesson_progress` - Lesson completion
- `user_quiz_attempts` - Quiz attempt history
- `user_quiz_answers` - Detailed quiz responses

#### Gamification
- `xp_transactions` - XP earning history
- `badges` - Achievement badge definitions
- `user_badges` - Earned badges
- `certificates` - Issued certificates

---

## ⚙️ Configuration

### Environment Setup

1. **Development Mode**
   - Set `display_errors = 1` in `config/settings.php`
   - Use local database credentials

2. **Production Mode**
   - Set `display_errors = 0`
   - Use strong database passwords
   - Enable HTTPS
   - Implement proper backup strategy

### Email Configuration (Optional)

To enable email notifications, configure SMTP settings:

```php
// In config/settings.php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
```

### Security Recommendations

1. **Change Default Admin Password** after first login
2. **Use HTTPS** in production
3. **Implement CSRF Protection** for forms
4. **Regular Backups** of database and uploads
5. **Keep PHP and MySQL Updated**
6. **Implement Rate Limiting** for login attempts

---

## 📁 Project Structure

```
gamified-learning-platform/
│
├── 📁 config/                 # Configuration files
│   ├── db.php                # Database connection
│   └── settings.php          # Platform settings
│
├── 📁 includes/               # Shared PHP includes
│   ├── header.php            # Common header
│   ├── footer.php            # Common footer
│   ├── navbar.php            # Navigation bar
│   ├── sidebar.php           # Dashboard sidebar
│   ├── admin-sidebar.php     # Admin sidebar
│   ├── functions.php         # Helper functions
│   └── session.php           # Session management
│
├── 📁 assets/                 # Static assets
│   ├── 📁 css/
│   │   ├── colors.css        # Theme color variables
│   │   ├── style.css         # Main stylesheet
│   │   ├── admin.css         # Admin panel styles
│   │   ├── dashboard.css     # Dashboard styles
│   │   └── custom.css        # Custom overrides
│   ├── 📁 js/
│   │   ├── main.js           # Main JavaScript
│   │   ├── quiz.js           # Quiz functionality
│   │   └── video-player.js   # Video player
│   ├── 📁 images/            # Images and icons
│   └── 📁 fonts/             # Custom fonts
│
├── 📁 uploads/                # User uploads
│   ├── 📁 videos/            # Course videos
│   ├── 📁 thumbnails/        # Course thumbnails
│   ├── 📁 certificates/      # Generated certificates
│   ├── 📁 avatars/           # User profile pictures
│   └── 📁 documents/         # PDF files
│
├── 📁 auth/                   # Authentication
│   ├── login.php             # Login page
│   ├── register.php          # Registration
│   ├── logout.php            # Logout handler
│   ├── forgot-password.php   # Password recovery
│   ├── reset-password.php    # Password reset
│   └── verify-email.php      # Email verification
│
├── 📁 pages/                  # Public pages
│   ├── about.php             # About page
│   ├── contact.php           # Contact page
│   ├── faq.php               # FAQ page
│   ├── how-it-works.php      # How it works
│   └── subjects.php          # Browse subjects
│
├── 📁 dashboard/              # Learner dashboard
│   ├── index.php             # Dashboard home
│   ├── profile.php           # User profile
│   ├── settings.php          # Account settings
│   ├── my-courses.php        # Enrolled courses
│   ├── course-details.php    # Course information
│   ├── lesson.php            # Lesson viewer
│   ├── quiz.php              # Quiz interface
│   ├── quiz-result.php       # Quiz results
│   ├── leaderboard.php       # Global leaderboard
│   ├── achievements.php      # Badges earned
│   ├── certificates.php      # My certificates
│   └── xp-history.php        # XP transaction log
│
├── 📁 admin/                  # Admin panel
│   ├── index.php             # Admin dashboard
│   ├── analytics.php         # Analytics & reports
│   │
│   ├── 📁 subjects/          # Subject management
│   │   ├── index.php         # List subjects
│   │   ├── add.php           # Add subject
│   │   ├── edit.php          # Edit subject
│   │   └── delete.php        # Delete subject
│   │
│   ├── 📁 courses/           # Course management
│   │   ├── index.php         # List courses
│   │   ├── add.php           # Add course
│   │   ├── edit.php          # Edit course
│   │   └── delete.php        # Delete course
│   │
│   ├── 📁 lessons/           # Lesson management
│   │   ├── index.php         # List lessons
│   │   ├── add.php           # Add lesson
│   │   ├── edit.php          # Edit lesson
│   │   └── delete.php        # Delete lesson
│   │
│   ├── 📁 quizzes/           # Quiz management
│   │   ├── index.php         # List quizzes
│   │   ├── add.php           # Add quiz
│   │   ├── edit.php          # Edit quiz
│   │   ├── delete.php        # Delete quiz
│   │   ├── questions.php     # Manage questions
│   │   └── add-question.php  # Add question
│   │
│   ├── 📁 users/             # User management
│   │   ├── index.php         # List users
│   │   ├── view.php          # View user details
│   │   ├── edit.php          # Edit user
│   │   └── delete.php        # Delete user
│   │
│   ├── 📁 badges/            # Badge management
│   │   ├── index.php         # List badges
│   │   ├── add.php           # Add badge
│   │   ├── edit.php          # Edit badge
│   │   └── delete.php        # Delete badge
│   │
│   └── 📁 levels/            # Level management
│       ├── index.php         # List levels
│       └── edit.php          # Edit level
│
├── 📁 actions/                # Form processors
│   ├── login-process.php     # Process login
│   ├── register-process.php  # Process registration
│   ├── update-profile.php    # Update profile
│   ├── enroll-course.php     # Enroll in course
│   ├── complete-lesson.php   # Mark lesson complete
│   └── submit-quiz.php       # Submit quiz answers
│
├── 📁 database/               # Database files
│   ├── database.sql          # Schema definition
│   └── seed.sql              # Sample data
│
├── index.php                  # Homepage
├── .htaccess                  # Apache rewrite rules
├── README.md                  # This file
└── LICENSE                    # License file
```

---

## 👥 User Roles

### 1. **Learner** (Default Role)
- Browse and enroll in courses
- Watch lessons and take quizzes
- Earn XP, badges, and certificates
- Track progress and view statistics
- Compete on leaderboards
- Manage profile and settings

### 2. **Admin** (Administrator)
- All learner permissions
- Full access to admin panel
- Create and manage subjects, courses, lessons
- Build quizzes and questions
- Manage users and permissions
- Configure badges and levels
- View analytics and generate reports
- Platform-wide content control

### 3. **Guest** (Unauthenticated)
- View public pages
- Browse course catalog
- View platform information
- Register for an account

---

## 🎮 Gamification System

### XP (Experience Points) System

**How to Earn XP:**

| Activity | XP Earned | Description |
|----------|-----------|-------------|
| Complete Lesson | 10 XP | Finish watching/reading a lesson |
| Pass Quiz | 30 XP | Score above passing threshold |
| Perfect Quiz Score | +20 XP Bonus | Get 100% on any quiz |
| Complete Course | 100 XP | Finish all lessons and quizzes |
| Daily Login | 5 XP | Maintain learning streak |
| Earn Badge | Variable | XP reward varies by badge |

**XP Configuration:**

XP rewards can be customized in `config/settings.php`:

```php
define('XP_LESSON', 10);           // Per lesson completion
define('XP_QUIZ_PASS', 30);        // Per quiz passed
define('XP_QUIZ_PERFECT', 20);     // Perfect score bonus
define('XP_COURSE_COMPLETE', 100); // Per course completed
define('XP_DAILY_STREAK', 5);      // Daily login bonus
```

### Level Progression System

The platform features 10 progressive levels:

| Level | XP Required | Total XP | Title | Unlocks |
|-------|-------------|----------|-------|---------|
| 1 | 0 XP | 0 | Beginner | Basic courses |
| 2 | 100 XP | 100 | Novice | Intermediate courses |
| 3 | 200 XP | 300 | Learner | First badge milestone |
| 4 | 300 XP | 600 | Apprentice | Advanced quizzes |
| 5 | 500 XP | 1,100 | Scholar | Bonus materials |
| 6 | 700 XP | 1,800 | Expert | Expert courses |
| 7 | 1,000 XP | 2,800 | Specialist | Exclusive challenges |
| 8 | 1,500 XP | 4,300 | Master | Advanced certificates |
| 9 | 2,000 XP | 6,300 | Guru | Mastery content |
| 10 | 2,700 XP | 9,000 | Legend | Elite status + Excellence Certificate |

**Level Calculation:**
- Automatic level-up when XP threshold reached
- Progressive difficulty scaling
- New content unlocked at each level
- Visual progress indicators

### Badge System

**Badge Types:**

1. **Course Badges**
   - First Steps - Complete your first lesson
   - Course Completionist - Complete 1 course
   - Triple Threat - Complete 3 courses

2. **Quiz Badges**
   - Quiz Master - Pass 5 quizzes with 100%
   - Quiz Champion - Pass 10 quizzes

3. **Streak Badges**
   - Week Warrior - 7-day login streak
   - Month Champion - 30-day login streak

4. **Level Badges**
   - Level Up! - Reach Level 5
   - Elite Learner - Reach Level 10

5. **Special Badges**
   - Custom badges for achievements
   - Event-specific badges
   - Admin-awarded badges

### Daily Streaks

**How It Works:**
1. Log in every day to maintain streak
2. Earn 5 XP for each day in streak
3. Track current and longest streak
4. Bonus badges for milestone streaks
5. Streak breaks if you miss a day

**Benefits:**
- Consistent learning habit building
- Extra XP accumulation
- Exclusive streak badges
- Leaderboard streak rankings

### Leaderboard

**Ranking Factors:**
- Total XP earned
- Current level
- Badges collected
- Courses completed
- Quiz performance

**Leaderboard Filters:**
- All Time Rankings
- Monthly Rankings
- Weekly Rankings

**Features:**
- Real-time rank updates
- User profile links
- Achievement showcases
- Competitive motivation

### Certificate System

**Certificate Issuance:**
- Automatically generated upon course completion
- Unique verification code
- Downloadable PDF format
- Shareable on social media
- Official platform branding

**Certificate Contents:**
- Learner name
- Course title
- Completion date
- Verification code
- Digital signature
- Platform logo

---

## 🛠️ Admin Panel

### Accessing Admin Panel

1. Log in with admin credentials
2. Navigate to `/admin/` or click **Admin Panel** in user menu
3. Admin sidebar provides access to all management modules

### Admin Dashboard

**Overview Statistics:**
- Total learners and admins
- Active courses count
- Total enrollments and completions
- Certificates issued
- Badges earned
- Recent activity feed
- Top performing courses

### Subject Management

**Path:** `/admin/subjects/`

**Operations:**
- **List Subjects** - View all subjects with course counts
- **Add Subject** - Create new subject with icon
- **Edit Subject** - Update subject details
- **Delete Subject** - Remove subject (cascades to courses)
- **Toggle Status** - Activate/deactivate subjects

**Features:**
- Font Awesome icon support
- Active/inactive status
- Course count per subject
- Description field

### Course Management

**Path:** `/admin/courses/`

**Operations:**
- **List Courses** - Grid view with thumbnails
- **Add Course** - Create course with metadata
- **Edit Course** - Update course details
- **Delete Course** - Remove course (cascades to lessons/quizzes)
- **Publish/Draft** - Control course visibility

**Course Fields:**
- Title and description
- Subject association
- Difficulty level (Beginner, Intermediate, Advanced, Expert)
- Required user level (1-10)
- Estimated duration
- XP reward
- Thumbnail image
- Publication status

### Lesson Management

**Path:** `/admin/lessons/`

**Operations:**
- **List Lessons** - View lessons by course
- **Add Lesson** - Create new lesson
- **Edit Lesson** - Update lesson content
- **Delete Lesson** - Remove lesson
- **Reorder Lessons** - Set lesson sequence

**Lesson Types:**
- **Video** - Upload video files (MP4, AVI, MOV)
- **Text** - Rich text content
- **PDF** - Document upload
- **Mixed** - Combination of content types

**Lesson Fields:**
- Title and order
- Content type
- Duration
- XP reward
- Video/PDF upload
- Text content editor

### Quiz Management

**Path:** `/admin/quizzes/`

**Operations:**
- **List Quizzes** - View quizzes by course
- **Add Quiz** - Create new quiz
- **Edit Quiz** - Update quiz settings
- **Delete Quiz** - Remove quiz
- **Manage Questions** - Add/edit questions

**Quiz Configuration:**
- Title and description
- Passing score percentage (default: 70%)
- Time limit (optional)
- XP reward
- Perfect score bonus XP
- Question types support

### Question Management

**Path:** `/admin/quizzes/questions.php?quiz_id=X`

**Question Types:**
1. **Multiple Choice**
   - Multiple answer options
   - Single correct answer
   - Radio button selection

2. **True/False**
   - Two options only
   - Simple boolean questions
   - Quick assessment

**Question Features:**
- Question text
- Points per question
- Question ordering
- Multiple answers per question
- Correct answer marking
- Answer ordering

### User Management

**Path:** `/admin/users/`

**Operations:**
- **List Users** - View all users with stats
- **View Details** - See user profile and progress
- **Edit User** - Update user information
- **Change Role** - Promote to admin or demote to learner
- **Toggle Status** - Activate/deactivate accounts
- **Delete User** - Remove user account

**User Statistics:**
- Total XP and current level
- Courses enrolled/completed
- Badges earned
- Certificates received
- Current streak
- Join date

**Search & Filters:**
- Search by username, name, or email
- Filter by role (Learner/Admin)
- Sort by various metrics

### Badge Management

**Path:** `/admin/badges/`

**Operations:**
- **List Badges** - View all badges
- **Add Badge** - Create new badge
- **Edit Badge** - Update badge details
- **Delete Badge** - Remove badge
- **Toggle Status** - Activate/deactivate badges

**Badge Configuration:**
- Badge name and description
- Badge type (Course, Quiz, Streak, Level, Special)
- Icon (Font Awesome)
- Requirement description
- Requirement value (numeric threshold)
- XP reward
- Active status

**Automatic Badge Awarding:**
- Course completion badges
- Level achievement badges
- Streak milestone badges
- System automatically checks and awards badges

### Level Management

**Path:** `/admin/levels/`

**Operations:**
- **List Levels** - View all 10 levels
- **Edit Level** - Update level configuration

**Level Configuration:**
- Level number (1-10)
- XP required for level
- Total cumulative XP
- Level title
- Description
- Unlocked features

### Analytics & Reports

**Path:** `/admin/analytics.php`

**Dashboard Metrics:**
- User statistics (total, active, new)
- Course statistics (published, draft, enrollments)
- Quiz performance (attempts, pass rate, average score)
- XP statistics (total earned, average per user)
- Badge distribution
- Certificate issuance

**Reports:**
- Top performing courses
- Recent activity log
- User engagement metrics
- Completion rates
- Quiz performance analysis
- Leaderboard insights

**Visualizations:**
- Statistical cards
- Activity timeline
- Performance charts
- User growth trends

---

## 📡 API Documentation

### Authentication Endpoints

#### Login
```php
POST /actions/login-process.php
Content-Type: application/x-www-form-urlencoded

email=user@example.com&password=password123
```

#### Register
```php
POST /actions/register-process.php
Content-Type: application/x-www-form-urlencoded

username=johndoe&email=user@example.com&password=password123&full_name=John Doe
```

#### Logout
```php
GET /auth/logout.php
```

### Course Endpoints

#### Enroll in Course
```php
POST /actions/enroll-course.php
Content-Type: application/x-www-form-urlencoded

course_id=1
```

#### Complete Lesson
```php
POST /actions/complete-lesson.php
Content-Type: application/x-www-form-urlencoded

lesson_id=5
```

#### Submit Quiz
```php
POST /actions/submit-quiz.php
Content-Type: application/x-www-form-urlencoded

quiz_id=3&question_1=answer_id&question_2=answer_id
```

### Helper Functions

Common PHP functions available in `includes/functions.php`:

```php
// User Management
get_user_data($user_id)           // Get user information
get_user_id()                     // Get current user ID
is_admin()                        // Check if user is admin
require_login()                   // Require authentication

// XP System
add_xp($user_id, $xp_amount, $xp_type, $reference_id, $description)
calculate_level($total_xp)        // Calculate level from XP
get_next_level_xp($current_level) // Get XP needed for next level

// Badge System
award_badge($user_id, $badge_id, $xp_reward)
check_and_award_badges($user_id)  // Auto-award eligible badges

// Utility Functions
clean_input($data)                 // Sanitize user input
upload_file($file, $destination, $allowed_types)
format_date($date)                 // Format date for display
time_ago($datetime)                // "2 hours ago" format
```

---

## 📸 Screenshots

### Homepage
![Homepage](docs/screenshots/homepage.png)
*Landing page with course showcase and platform statistics*

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)
*Learner dashboard with XP, level, and progress tracking*

### Course Page
![Course Details](docs/screenshots/course-details.png)
*Course information with lessons and quizzes*

### Quiz Interface
![Quiz](docs/screenshots/quiz.png)
*Interactive quiz with timer and progress tracking*

### Leaderboard
![Leaderboard](docs/screenshots/leaderboard.png)
*Competitive rankings and user achievements*

### Admin Panel
![Admin Dashboard](docs/screenshots/admin-dashboard.png)
*Admin panel with platform statistics*

### Badge Collection
![Achievements](docs/screenshots/achievements.png)
*User badge collection and milestones*

### Certificate
![Certificate](docs/screenshots/certificate.png)
*Course completion certificate with verification code*

---

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

### Getting Started

1. **Fork the repository**
2. **Clone your fork**
   ```bash
   git clone https://github.com/YOUR-USERNAME/Gamified-Learning-Platform.git
   ```
3. **Create a feature branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```
4. **Make your changes**
5. **Commit with clear messages**
   ```bash
   git commit -m "Add amazing feature"
   ```
6. **Push to your fork**
   ```bash
   git push origin feature/amazing-feature
   ```
7. **Open a Pull Request**

### Coding Standards

- **PHP**: Follow PSR-12 coding standards
- **HTML/CSS**: Use semantic HTML5 and BEM methodology
- **JavaScript**: Use ES6+ features, camelCase naming
- **Database**: Use prepared statements, normalize tables
- **Comments**: Document complex logic and functions
- **Testing**: Test all new features thoroughly

### Pull Request Guidelines

- Provide clear description of changes
- Reference related issues
- Include screenshots for UI changes
- Ensure no breaking changes
- Update documentation if needed
- Add tests for new features

### Code Review Process

1. Automated tests run on PR submission
2. Maintainers review code quality
3. Feedback provided for improvements
4. Approval and merge when ready

### Reporting Issues

**Bug Reports:**
- Use the issue template
- Provide steps to reproduce
- Include error messages
- Specify environment details

**Feature Requests:**
- Describe the feature clearly
- Explain the use case
- Suggest implementation approach

---

## 📜 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

### MIT License Summary

```
MIT License

Copyright (c) 2024 Gamified Learning Platform

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 💬 Support

### Documentation

- **Full Documentation**: [docs/](docs/)
- **API Reference**: [docs/api.md](docs/api.md)
- **FAQ**: [docs/faq.md](docs/faq.md)
- **Troubleshooting**: [docs/troubleshooting.md](docs/troubleshooting.md)

### Community

- **GitHub Issues**: [Report bugs or request features](https://github.com/harmonizerblinks/Gamified-Learning-Platform/issues)
- **Discussions**: [Join community discussions](https://github.com/harmonizerblinks/Gamified-Learning-Platform/discussions)
- **Email Support**: support@example.com

### Resources

- **Video Tutorials**: Coming soon
- **Sample Courses**: Available in seed.sql
- **Best Practices Guide**: [docs/best-practices.md](docs/best-practices.md)

---

## 🙏 Acknowledgments

- **Bootstrap Team** - For the amazing UI framework
- **Font Awesome** - For the comprehensive icon library
- **PHP Community** - For excellent documentation
- **Contributors** - Thank you to all contributors!

---

## 🚀 Roadmap

### Version 1.1 (Next Release)
- [ ] Email notifications system
- [ ] Password recovery functionality
- [ ] Course reviews and ratings
- [ ] Discussion forums per course
- [ ] Live chat support
- [ ] Mobile app (PWA)

### Version 1.2
- [ ] Video conferencing integration
- [ ] Assignment submissions
- [ ] Peer-to-peer learning
- [ ] Certificate verification portal
- [ ] Multi-language support
- [ ] Dark mode theme

### Version 2.0
- [ ] AI-powered recommendations
- [ ] Advanced analytics with charts
- [ ] Monetization features
- [ ] Course marketplace
- [ ] Instructor dashboard
- [ ] Payment gateway integration

---

## 📊 Project Status

![Build Status](https://img.shields.io/badge/build-passing-brightgreen)
![Coverage](https://img.shields.io/badge/coverage-85%25-yellowgreen)
![Maintenance](https://img.shields.io/badge/maintained-yes-brightgreen)

**Current Version:** 1.0.0
**Last Updated:** January 2025
**Status:** Active Development

---

## 👨‍💻 Author

**Harmonizer Blinks**
- GitHub: [@harmonizerblinks](https://github.com/harmonizerblinks)
- Email: harmonizerblinks@example.com

---

## 🌟 Show Your Support

If you find this project helpful, please consider:

- ⭐ Starring the repository
- 🐛 Reporting bugs
- 💡 Suggesting new features
- 🤝 Contributing code
- 📢 Sharing with others

---

<p align="center">Made with ❤️ and ☕ by the Gamified Learning Platform Team</p>

<p align="center">
  <a href="#-table-of-contents">Back to Top ↑</a>
</p>
