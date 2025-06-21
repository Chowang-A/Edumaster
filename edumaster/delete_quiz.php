<?php
session_start();
require 'db.php';

// Access Control
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.html?error=access_denied");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = strtolower($_SESSION['user_role']);

// Validate quiz ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: edit_quiz.php?error=invalid_quiz_id");
    exit;
}

$quiz_id = (int) $_GET['id'];

// Role-Based Access Check
if ($role === 'teacher') {
    // Teachers can only delete their own quizzes
    $check = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND teacher_id = ?");
    $check->execute([$quiz_id, $user_id]);
} elseif ($role === 'admin') {
    // Admins can delete any quiz
    $check = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $check->execute([$quiz_id]);
} else {
    header("Location: login.html?error=unauthorized");
    exit;
}

$quiz = $check->fetch();

if (!$quiz) {
    header("Location: edit_quiz.php?error=quiz_not_found_or_unauthorized");
    exit;
}

// Start deletion transaction
try {
    $pdo->beginTransaction();

    // Delete options associated with questions in the quiz
    $pdo->prepare("
        DELETE FROM options
        WHERE question_id IN (SELECT id FROM questions WHERE quiz_id = ?)
    ")->execute([$quiz_id]);

    // Delete the questions themselves
    $pdo->prepare("DELETE FROM questions WHERE quiz_id = ?")->execute([$quiz_id]);

    // Finally, delete the quiz
    $pdo->prepare("DELETE FROM quizzes WHERE id = ?")->execute([$quiz_id]);

    $pdo->commit();

    header("Location: edit_quiz.php?success=quiz_deleted");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error deleting quiz ID $quiz_id: " . $e->getMessage());
    header("Location: edit_quiz.php?error=deletion_failed");
    exit;
}
?>
