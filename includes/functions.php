<?php
// includes/functions.php - Helper Functions

// Sanitize input
// function clean_input($data) {
//     $data = trim($data);
//     $data = stripslashes($data);
//     $data = htmlspecialchars($data);
//     return $data;
// }

// // Format date
// function format_date($date) {
//     return date('F j, Y', strtotime($date));
// }

// // Calculate time ago
// function time_ago($datetime) {
//     $time = strtotime($datetime);
//     $diff = time() - $time;
    
//     if ($diff < 60) return 'just now';
//     if ($diff < 3600) return floor($diff / 60) . ' min ago';
//     if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
//     if ($diff < 604800) return floor($diff / 86400) . ' days ago';
//     return date('M j, Y', $time);
// }

// // Generate random string
// function generate_token($length = 32) {
//     return bin2hex(random_bytes($length));
// }

// // Success message
// function set_success($message) {
//     $_SESSION['success'] = $message;
// }

// // Error message
// function set_error($message) {
//     $_SESSION['error'] = $message;
// }

// // Display messages
// function display_messages() {
//     if (isset($_SESSION['success'])) {
//         echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
//         unset($_SESSION['success']);
//     }
//     if (isset($_SESSION['error'])) {
//         echo '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
//         unset($_SESSION['error']);
//     }
// }

// // Redirect function
// function redirect($url) {
//     header("Location: $url");
//     exit();
// }

// // Calculate level from XP
// function calculate_level($total_xp) {
//     $levels = [
//         1 => 0, 2 => 100, 3 => 300, 4 => 600, 5 => 1100,
//         6 => 1800, 7 => 2800, 8 => 4300, 9 => 6300, 10 => 9000
//     ];
    
//     $current_level = 1;
//     foreach ($levels as $level => $xp_required) {
//         if ($total_xp >= $xp_required) {
//             $current_level = $level;
//         }
//     }
//     return $current_level;
// }

// // Calculate progress percentage
// function calculate_progress($completed, $total) {
//     if ($total == 0) return 0;
//     return round(($completed / $total) * 100, 2);
// }

// // File upload function
// function upload_file($file, $destination) {
//     $target_dir = $destination;
//     $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
//     $new_filename = uniqid() . '.' . $file_extension;
//     $target_file = $target_dir . $new_filename;
    
//     if (move_uploaded_file($file['tmp_name'], $target_file)) {
//         return $new_filename;
//     }
//     return false;
// }
?>

<?php
// includes/functions.php

function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function format_date($date) {
    return date('F j, Y', strtotime($date));
}

function time_ago($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $time);
}

function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

function set_success($message) {
    $_SESSION['success'] = $message;
}

function set_error($message) {
    $_SESSION['error'] = $message;
}

function display_messages() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo $_SESSION['success'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo $_SESSION['error'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['error']);
    }
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function calculate_level($total_xp) {
    global $conn;
    $stmt = $conn->query("SELECT level_number FROM levels WHERE total_xp_required <= $total_xp ORDER BY level_number DESC LIMIT 1");
    $result = $stmt->fetch();
    return $result ? $result['level_number'] : 1;
}

function get_next_level_xp($current_level) {
    global $conn;
    $next_level = $current_level + 1;
    if ($next_level > 10) return null;
    
    $stmt = $conn->prepare("SELECT total_xp_required FROM levels WHERE level_number = ?");
    $stmt->execute([$next_level]);
    $result = $stmt->fetch();
    return $result ? $result['total_xp_required'] : null;
}

function calculate_progress($completed, $total) {
    if ($total == 0) return 0;
    return round(($completed / $total) * 100, 2);
}

function add_xp($user_id, $xp_amount, $xp_type, $reference_id = null, $description = '') {
    global $conn;
    
    // Add XP transaction
    $stmt = $conn->prepare("INSERT INTO xp_transactions (user_id, xp_amount, xp_type, reference_id, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $xp_amount, $xp_type, $reference_id, $description]);
    
    // Update user's total XP
    $stmt = $conn->prepare("UPDATE users SET total_xp = total_xp + ? WHERE user_id = ?");
    $stmt->execute([$xp_amount, $user_id]);
    
    // Recalculate level
    $stmt = $conn->prepare("SELECT total_xp FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $new_level = calculate_level($user['total_xp']);
    
    $stmt = $conn->prepare("UPDATE users SET current_level = ? WHERE user_id = ?");
    $stmt->execute([$new_level, $user_id]);
    
    // Check for level-based badges
    check_and_award_badges($user_id);
    
    return true;
}

function check_and_award_badges($user_id) {
    global $conn;
    
    // Get user stats
    $stmt = $conn->prepare("SELECT total_xp, current_level FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    // Check level badges
    $stmt = $conn->prepare("
        SELECT b.badge_id, b.badge_name, b.xp_reward
        FROM badges b
        WHERE b.badge_type = 'level' 
        AND b.requirement_value <= ?
        AND b.badge_id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = ?)
    ");
    $stmt->execute([$user['current_level'], $user_id]);
    $level_badges = $stmt->fetchAll();
    
    foreach ($level_badges as $badge) {
        award_badge($user_id, $badge['badge_id'], $badge['xp_reward']);
    }
    
    // Check course completion badges
    $stmt = $conn->prepare("SELECT COUNT(*) as completed FROM user_courses WHERE user_id = ? AND is_completed = 1");
    $stmt->execute([$user_id]);
    $completed_courses = $stmt->fetch()['completed'];
    
    $stmt = $conn->prepare("
        SELECT b.badge_id, b.badge_name, b.xp_reward
        FROM badges b
        WHERE b.badge_type = 'course' 
        AND b.requirement_value <= ?
        AND b.badge_id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = ?)
    ");
    $stmt->execute([$completed_courses, $user_id]);
    $course_badges = $stmt->fetchAll();
    
    foreach ($course_badges as $badge) {
        award_badge($user_id, $badge['badge_id'], $badge['xp_reward']);
    }
}

function award_badge($user_id, $badge_id, $xp_reward = 0) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $badge_id]);
        
        if ($xp_reward > 0) {
            add_xp($user_id, $xp_reward, 'badge', $badge_id, 'Badge earned');
        }
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function check_daily_streak($user_id) {
    global $conn;
    
    $today = date('Y-m-d');
    
    // Check if already logged today
    $stmt = $conn->prepare("SELECT * FROM daily_streaks WHERE user_id = ? AND login_date = ?");
    $stmt->execute([$user_id, $today]);
    
    if ($stmt->rowCount() > 0) {
        return; // Already logged today
    }
    
    // Insert today's login
    $stmt = $conn->prepare("INSERT INTO daily_streaks (user_id, login_date, xp_earned) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $today, XP_DAILY_STREAK]);
    
    // Calculate current streak
    $stmt = $conn->prepare("
        SELECT COUNT(*) as streak
        FROM daily_streaks
        WHERE user_id = ?
        AND login_date >= DATE_SUB(?, INTERVAL 30 DAY)
        ORDER BY login_date DESC
    ");
    $stmt->execute([$user_id, $today]);
    $streak = $stmt->fetch()['streak'];
    
    // Update user streak
    $stmt = $conn->prepare("UPDATE users SET current_streak = ?, longest_streak = GREATEST(longest_streak, ?) WHERE user_id = ?");
    $stmt->execute([$streak, $streak, $user_id]);
    
    // Award XP for streak
    add_xp($user_id, XP_DAILY_STREAK, 'streak', null, 'Daily login streak');
    
    // Check streak badges
    $stmt = $conn->prepare("
        SELECT b.badge_id, b.xp_reward
        FROM badges b
        WHERE b.badge_type = 'streak' 
        AND b.requirement_value <= ?
        AND b.badge_id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = ?)
    ");
    $stmt->execute([$streak, $user_id]);
    $badges = $stmt->fetchAll();
    
    foreach ($badges as $badge) {
        award_badge($user_id, $badge['badge_id'], $badge['xp_reward']);
    }
}

function upload_file($file, $destination, $allowed_extensions) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_path = $destination . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $new_filename;
    }
    
    return false;
}

function get_user_data($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}
function get_quiz_stats($user_id, $quiz_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_attempts,
               MAX(score) as best_score,
               MIN(score) as worst_score,
               AVG(score) as avg_score,
               SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed_attempts
        FROM user_quiz_attempts
        WHERE user_id = ? AND quiz_id = ?
    ");
    $stmt->execute([$user_id, $quiz_id]);
    return $stmt->fetch();
}
?>
