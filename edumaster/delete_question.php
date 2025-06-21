<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['question_id']) || !is_numeric($_POST['question_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid question ID']);
    exit;
}

$questionId = intval($_POST['question_id']);

// Check ownership if teacher
if ($_SESSION['user_role'] === 'teacher') {
    $stmt = $pdo->prepare("SELECT q.quiz_id FROM questions q JOIN quizzes z ON q.quiz_id = z.id WHERE q.id = ? AND z.teacher_id = ?");
    $stmt->execute([$questionId, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Access denied.']);
        exit;
    }
}

try {
    $pdo->beginTransaction();

    $pdo->prepare("DELETE FROM options WHERE question_id = ?")->execute([$questionId]);
    $pdo->prepare("DELETE FROM questions WHERE id = ?")->execute([$questionId]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
