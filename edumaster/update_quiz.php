<?php
session_start();
require 'db.php';
header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

$quizId = filter_input(INPUT_POST, 'quiz_id', FILTER_VALIDATE_INT);
$title = trim($_POST['title'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$topic = trim($_POST['topic'] ?? '');
$questions = $_POST['questions'] ?? [];

if (!$quizId || $title === '' || $subject === '' || $topic === '') {
    echo json_encode(['error' => 'Missing required fields.']);
    exit;
}

try {
    if ($role === 'teacher') {
        $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = :id AND teacher_id = :tid");
        $stmt->execute([':id' => $quizId, ':tid' => $userId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = :id");
        $stmt->execute([':id' => $quizId]);
    }

    $quiz = $stmt->fetch();
    if (!$quiz) {
        echo json_encode(['error' => 'Quiz not found or access denied.']);
        exit;
    }

    $pdo->beginTransaction();

    $updateQuiz = $pdo->prepare("UPDATE quizzes SET title = :title, subject = :subject, topic = :topic WHERE id = :id");
    $updateQuiz->execute([
        ':title' => $title,
        ':subject' => $subject,
        ':topic' => $topic,
        ':id' => $quizId
    ]);

    $existingQ = $pdo->prepare("SELECT id FROM questions WHERE quiz_id = :qid");
    $existingQ->execute([':qid' => $quizId]);
    $existingQuestionIds = array_column($existingQ->fetchAll(), 'id');
    $submittedQuestionIds = [];

    foreach ($questions as $q) {
        $qId = isset($q['id']) ? (int)$q['id'] : null;
        $qText = trim($q['text'] ?? '');
        $opts = $q['options'] ?? [];
        $correct = intval($q['correct'] ?? -1);

        if ($qText === '' || count($opts) !== 4 || $correct < 0 || $correct > 3) continue;

        if ($qId) {
            $stmt = $pdo->prepare("UPDATE questions SET question_text = :text WHERE id = :id AND quiz_id = :qid");
            $stmt->execute([':text' => $qText, ':id' => $qId, ':qid' => $quizId]);

            $pdo->prepare("DELETE FROM options WHERE question_id = :qid")->execute([':qid' => $qId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (:qid, :text) RETURNING id");
            $stmt->execute([':qid' => $quizId, ':text' => $qText]);
            $qId = $stmt->fetchColumn();
        }

        $submittedQuestionIds[] = (int)$qId;

        foreach ($opts as $index => $optText) {
            $stmt = $pdo->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (:qid, :opt, :correct)");
            $stmt->execute([
                ':qid' => $qId,
                ':opt' => trim($optText),
                ':correct' => ($index === $correct ? 1 : 0)
            ]);
        }
    }

    $toDelete = array_diff($existingQuestionIds, $submittedQuestionIds);
    if (!empty($toDelete)) {
        $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
        $pdo->prepare("DELETE FROM options WHERE question_id IN ($placeholders)")->execute($toDelete);
        $pdo->prepare("DELETE FROM questions WHERE id IN ($placeholders)")->execute($toDelete);
    }

    $pdo->commit();

    echo "<script>alert('Quiz updated successfully.'); window.location.href = 'edit_quiz.php';</script>";
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<pre>Quiz update failed: " . $e->getMessage() . "</pre>";
    exit;
}
