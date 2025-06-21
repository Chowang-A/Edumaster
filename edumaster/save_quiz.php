<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: index.html?error=Access denied.");
    exit;
}

$teacherId = $_SESSION['user_id'];
$title = $_POST['title'] ?? '';
$subject = $_POST['subject'] ?? '';
$topic = $_POST['topic'] ?? '';
$questions = $_POST['questions'] ?? [];

if (!$title || empty($questions)) {
    die("Invalid input.");
}

try {
    $pdo->beginTransaction();

    $stmtQuiz = $pdo->prepare("INSERT INTO quizzes (teacher_id, title, subject, topic) VALUES (?, ?, ?, ?) RETURNING id");
    $stmtQuiz->execute([$teacherId, $title, $subject, $topic]);
    $quizId = $stmtQuiz->fetchColumn();

    foreach ($questions as $q) {
        if (!isset($q['text']) || trim($q['text']) === '') continue;

        $stmtQ = $pdo->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (?, ?) RETURNING id");
        $stmtQ->execute([$quizId, $q['text']]);
        $questionId = $stmtQ->fetchColumn();

        foreach ($q['options'] as $idx => $opt) {
            if (trim($opt) === '') continue;

            // Ensure correct is numeric and valid
            $correctIdx = isset($q['correct']) && is_numeric($q['correct']) ? (int)$q['correct'] : -1;
            $isCorrect = ($idx === $correctIdx) ? 'true' : 'false'; // Pass as string 'true'/'false' to PostgreSQL

            $stmtOpt = $pdo->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
            $stmtOpt->execute([$questionId, $opt, $isCorrect]);
        }
    }

    $pdo->commit();
    echo "✅ Quiz saved successfully! <a href='dashboard.php'>Go back</a>";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Error: " . $e->getMessage();
}
