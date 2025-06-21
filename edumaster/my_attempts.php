<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html?error=Login required.");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch quiz attempts
$stmt = $pdo->prepare("
    SELECT qa.*, q.title, q.subject, u.name AS teacher_name
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN users u ON q.teacher_id = u.id
    WHERE qa.user_id = ?
    ORDER BY qa.attempted_at DESC
");
$stmt->execute([$userId]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Quiz Attempts</title>
    <style>
        body {
            background: #121212;
            color: #f9d923;
            font-family: Arial;
            padding: 2rem;
        }
        h1 {
            margin-bottom: 1rem;
        }
        .attempt {
            background: #1e1e1e;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
        }
        .attempt p {
            margin: 0.25rem 0;
        }
    </style>
</head>
<body>
    <h1>My Quiz Attempts</h1>

    <?php if ($attempts): ?>
        <?php foreach ($attempts as $a): ?>
            <div class="attempt">
                <p><strong>Title:</strong> <?= htmlspecialchars($a['title']) ?></p>
                <p><strong>Subject:</strong> <?= htmlspecialchars($a['subject']) ?></p>
                <p><strong>Teacher:</strong> <?= htmlspecialchars($a['teacher_name']) ?></p>
                <p><strong>Score:</strong> <?= $a['score'] ?> / <?= $a['total_questions'] ?></p>
                <p><small>Taken on <?= date("d M Y, h:i A", strtotime($a['attempted_at'])) ?></small></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You haven’t taken any quizzes yet.</p>
    <?php endif; ?>
</body>
</html>
