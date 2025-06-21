<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html?error=Please log in.");
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];
$quizId = $_GET['id'] ?? null;

if (!$quizId) {
    die("Quiz ID not provided.");
}

// Check last attempt (24-hour rule)
$stmtCheck = $pdo->prepare("SELECT attempted_at FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? ORDER BY attempted_at DESC LIMIT 1");
$stmtCheck->execute([$userId, $quizId]);
$lastAttempt = $stmtCheck->fetchColumn();

if ($lastAttempt && strtotime($lastAttempt) > strtotime("-24 hours")) {
    die("You can only retake this quiz after 24 hours.");
}

// Fetch quiz metadata
$stmtQuiz = $pdo->prepare("
    SELECT q.*, u.name AS teacher_name
    FROM quizzes q
    JOIN users u ON q.teacher_id = u.id
    WHERE q.id = ?
");
$stmtQuiz->execute([$quizId]);
$quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die("Quiz not found.");
}

// Fetch questions and options
$stmtQ = $pdo->prepare("
    SELECT q.id AS question_id, q.question_text, o.id AS option_id, o.option_text, o.is_correct
    FROM questions q
    JOIN options o ON q.id = o.question_id
    WHERE q.quiz_id = ?
    ORDER BY q.id, o.id
");
$stmtQ->execute([$quizId]);

$questions = [];
while ($row = $stmtQ->fetch(PDO::FETCH_ASSOC)) {
    $qid = $row['question_id'];
    if (!isset($questions[$qid])) {
        $questions[$qid] = [
            'text' => $row['question_text'],
            'options' => []
        ];
    }
    $questions[$qid]['options'][$row['option_id']] = [
        'text' => $row['option_text'],
        'is_correct' => $row['is_correct']
    ];
}

$score = null;
$percentage = null;
$totalQuestions = count($questions);

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answers'] ?? [];
    $correctCount = 0;

    foreach ($questions as $qid => $qData) {
        foreach ($qData['options'] as $oid => $opt) {
            if (isset($answers[$qid]) && $answers[$qid] == $oid && $opt['is_correct']) {
                $correctCount++;
            }
        }
    }

    // Calculate percentage
    $percentage = round(($correctCount / $totalQuestions) * 100, 2);
    $score = $percentage; // Save percentage as the score

    // Save attempt
    $stmtInsert = $pdo->prepare("
        INSERT INTO quiz_attempts (user_id, quiz_id, score, total_questions, attempted_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmtInsert->execute([$userId, $quizId, $score, $totalQuestions]);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Take Quiz - <?= htmlspecialchars($quiz['title']) ?></title>
    <style>
        body {
            background: #121212;
            color: #f9d923;
            font-family: Arial, sans-serif;
            padding: 2rem;
        }
        h1, h2 {
            color: #f9d923;
        }
        .question {
            background: #1e1e1e;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
        }
        label {
            display: block;
            margin: 0.5rem 0;
            cursor: pointer;
        }
        .submit-btn {
            padding: 0.75rem 1.5rem;
            background: #f9d923;
            color: #000;
            border: none;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }
        .back-btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: #f9d923;
            color: #000;
            text-decoration: none;
            border-radius: 6px;
        }
        .score-box {
            background: #1e1e1e;
            padding: 1rem;
            border: 2px solid #f9d923;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<a class="back-btn" href="quizzes.php">← Back to Quizzes</a>

<h1><?= htmlspecialchars($quiz['title']) ?></h1>
<p><strong>Subject:</strong> <?= htmlspecialchars($quiz['subject']) ?> |
   <strong>Topic:</strong> <?= htmlspecialchars($quiz['topic']) ?> |
   <strong>Teacher:</strong> <?= htmlspecialchars($quiz['teacher_name']) ?> |
   <strong>Created:</strong> <?= date('d M Y, h:i A', strtotime($quiz['created_at'])) ?>
</p>

<?php if ($score !== null): ?>
    <div class="score-box">
        <h2>You scored <?= $score ?>% (<?= $correctCount ?> out of <?= $totalQuestions ?> correct).</h2>
    </div>
<?php else: ?>
    <form method="POST">
        <?php foreach ($questions as $qid => $qData): ?>
            <div class="question">
                <h3><?= htmlspecialchars($qData['text']) ?></h3>
                <?php foreach ($qData['options'] as $oid => $opt): ?>
                    <label>
                        <input type="radio" name="answers[<?= $qid ?>]" value="<?= $oid ?>" required>
                        <?= htmlspecialchars($opt['text']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="submit-btn">Submit Quiz</button>
    </form>
<?php endif; ?>

</body>
</html>
