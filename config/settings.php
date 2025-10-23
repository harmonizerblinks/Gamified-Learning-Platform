
<?php
// config/settings.php - Site Settings

// Define base path (project root directory)
define('BASE_PATH', dirname(__DIR__)); // Points to gamified/ folder

// Site Information
define('SITE_NAME', 'Gamified Learning Platform');
define('SITE_URL', 'http://localhost:8000'); // Adjust port if needed

define('ADMIN_EMAIL', 'ewurabenakorsah10@gmail.com');

// Directory Paths (absolute)
define('CONFIG_PATH', BASE_PATH . '/config/');
define('INCLUDES_PATH', BASE_PATH . '/includes/');
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('ASSETS_PATH', BASE_PATH . '/assets/');

// Web URLs (relative to site root)
define('UPLOAD_URL', '/uploads/');
define('ASSETS_URL', '/assets/');

// XP Settings
define('XP_LESSON', 10);
define('XP_QUIZ_PASS', 30);
define('XP_QUIZ_PERFECT', 20);
define('XP_COURSE_COMPLETE', 100);
define('XP_DAILY_STREAK', 5);

// Upload Limits
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_VIDEO_EXT', ['mp4', 'avi', 'mov']);
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination
define('ITEMS_PER_PAGE', 12);

// Timezone
date_default_timezone_set('Africa/Accra');

// Error Reporting (turn off in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>