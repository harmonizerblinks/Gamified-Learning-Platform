<?php
require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
    $start_time = isset($_POST['start_time']) ? (int)$_POST['start_time'] : time();
    $user_id = get_user_id();
    
    if ($quiz_id == 0) {
        set_error('Invalid quiz');
        redirect('/dashboard/my-courses.php');
    }
    
    // Get quiz details
    $stmt = $conn->prepare("SELECT * FROM quizzes WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch();
    
    if (!$quiz) {
        set_error('Quiz not found');
        redirect('/dashboard/my-courses.php');
    }
    
    // Check if user is enrolled in the course
    $stmt = $conn->prepare("SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $quiz['course_id']]);
    $enrollment = $stmt->fetch();
    
    if (!$enrollment) {
        set_error('You are not enrolled in this course');
        redirect('/dashboard/my-courses.php');
    }
    
    // Calculate time taken
    $time_taken = time() - $start_time;
    
    // Check if time limit exceeded
    if ($quiz['time_limit'] && $time_taken > ($quiz['time_limit'] * 60)) {
        set_error('Time limit exceeded. Your quiz was not submitted.');
        redirect('/dashboard/quiz.php?id=' . $quiz_id);
    }
    
    // Get all questions
    $stmt = $conn->prepare("
        SELECT q.question_id, q.points, qa.answer_id, qa.is_correct
        FROM quiz_questions q
        JOIN quiz_answers qa ON q.question_id = qa.question_id
        WHERE q.quiz_id = ?
        ORDER BY q.question_order ASC
    ");
    $stmt->execute([$quiz_id]);
    $all_answers = $stmt->fetchAll();
    
    // Organize answers by question
    $questions_data = [];
    foreach ($all_answers as $row) {
        if (!isset($questions_data[$row['question_id']])) {
            $questions_data[$row['question_id']] = [
                'points' => $row['points'],
                'answers' => []
            ];
        }
        $questions_data[$row['question_id']]['answers'][$row['answer_id']] = $row['is_correct'];
    }
    
    // Calculate score
    $total_questions = count($questions_data);
    $total_points = 0;
    $earned_points = 0;
    $correct_answers = 0;
    $user_answers = [];
    
    foreach ($questions_data as $question_id => $question_data) {
        $total_points += $question_data['points'];
        
        // Get user's answer
        $user_answer_id = isset($_POST['question_' . $question_id]) ? (int)$_POST['question_' . $question_id] : null;
        
        // Store user answer
        $user_answers[$question_id] = $user_answer_id;
        
        // Check if correct
        if ($user_answer_id && isset($question_data['answers'][$user_answer_id]) && $question_data['answers'][$user_answer_id]) {
            $earned_points += $question_data['points'];
            $correct_answers++;
        }
    }
    
    // Calculate percentage score
    $score_percentage = ($earned_points / $total_points) * 100;
    
    // Check if passed
    $passed = $score_percentage >= $quiz['passing_score'];
    
    // Calculate XP earned
    $xp_earned = 0;
    if ($passed) {
        $xp_earned = $quiz['xp_reward'];
        // Perfect score bonus
        if ($score_percentage == 100) {
            $xp_earned += $quiz['bonus_xp_perfect'];
        }
    }
    
    // Save attempt to database
    $stmt = $conn->prepare("
        INSERT INTO user_quiz_attempts 
        (user_id, quiz_id, score, total_questions, correct_answers, passed, xp_earned, time_taken)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user_id,
        $quiz_id,
        $score_percentage,
        $total_questions,
        $correct_answers,
        $passed ? 1 : 0,
        $xp_earned,
        $time_taken
    ]);
    
    $attempt_id = $conn->lastInsertId();
    
    // Save individual answers
    foreach ($user_answers as $question_id => $answer_id) {
        $is_correct = $answer_id && isset($questions_data[$question_id]['answers'][$answer_id]) 
                      && $questions_data[$question_id]['answers'][$answer_id];
        
        $stmt = $conn->prepare("
            INSERT INTO user_quiz_answers (attempt_id, question_id, answer_id, is_correct)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$attempt_id, $question_id, $answer_id, $is_correct ? 1 : 0]);
    }
    
    // Award XP if passed
    if ($passed && $xp_earned > 0) {
        add_xp($user_id, $xp_earned, 'quiz', $quiz_id, 'Passed quiz: ' . $quiz['quiz_title']);
    }
    
    // Set success/failure message
    if ($passed) {
        if ($score_percentage == 100) {
            set_success('🎉 Perfect Score! You earned ' . $xp_earned . ' XP (including ' . $quiz['bonus_xp_perfect'] . ' bonus XP)!');
        } else {
            set_success('✅ Quiz Passed! You earned ' . $xp_earned . ' XP! Score: ' . round($score_percentage) . '%');
        }
    } else {
        set_error('❌ Quiz Failed. Score: ' . round($score_percentage) . '%. You need ' . $quiz['passing_score'] . '% to pass. Try again!');
    }
    
    // Redirect to results page
    redirect('/dashboard/quiz-result.php?attempt_id=' . $attempt_id);
    
} else {
    redirect('/dashboard/my-courses.php');
}
?>