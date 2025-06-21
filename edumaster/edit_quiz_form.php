<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    header("Location: login.html?error=access_denied");
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

if (!isset($_GET['quiz_id'])) {
    header("Location: edit_quiz.php?error=invalid_id");
    exit;
}

$quizId = intval($_GET['quiz_id']);

if ($role === 'teacher') {
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$quizId, $userId]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$quizId]);
}

$quiz = $stmt->fetch();
if (!$quiz) {
    die("Quiz not found or access denied.");
}

$questionsStmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$questionsStmt->execute([$quizId]);
$questions = $questionsStmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Quiz</title>
    <style>
        body {
            background-color: #121212;
            color: #f1c40f;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
        }
        nav {
            background: #1c1c1c;
            padding: 15px;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #f1c40f33;
        }
        nav a {
            color: #f1c40f;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
            transition: color 0.3s;
        }
        nav a:hover { color: #ffffff; }
        h1 { text-align: center; color: #f1c40f; margin-bottom: 30px; }
        form {
            max-width: 900px;
            margin: auto;
            background: #1c1c1c;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 15px #f1c40f33;
        }
        input, textarea, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
        }
        input, textarea, select {
            background: #2b2b2b;
            color: #f1c40f;
        }
        input::placeholder, textarea::placeholder { color: #bbbbbb; }
        .question-block {
            background: #2a2a2a;
            margin-top: 25px;
            border-left: 6px solid #f1c40f;
            border-radius: 8px;
            overflow: hidden;
        }
        .question-header {
            padding: 15px;
            font-weight: bold;
            cursor: pointer;
            background: #333;
            color: #f1c40f;
        }
        .question-content {
            display: none;
            padding: 15px;
            border-top: 1px solid #444;
        }
        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            float: right;
            font-weight: bold;
        }
        #addQuestionBtn, #submitQuiz {
            font-weight: bold;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        #addQuestionBtn {
            background-color: #f1c40f;
            color: black;
        }
        #addQuestionBtn:hover {
            background-color: #d4af37;
        }
        #submitQuiz {
            background-color: #27ae60;
            color: white;
        }
        #submitQuiz:hover {
            background-color: #1e8449;
        }
    </style>
</head>
<body>

<nav>
    <a href="edit_quiz.php">← Back to Quizzes</a>
    <a href="dashboard.php">Dashboard</a>
</nav>

<h1>Edit Quiz: <?= htmlspecialchars($quiz['title']) ?></h1>

<form method="POST" action="update_quiz.php" id="quizForm">
    <input type="hidden" name="quiz_id" value="<?= $quizId ?>">
    <input type="text" name="title" value="<?= htmlspecialchars($quiz['title']) ?>" placeholder="Quiz Title" required>
    <input type="text" name="subject" value="<?= htmlspecialchars($quiz['subject']) ?>" placeholder="Subject" required>
    <input type="text" name="topic" value="<?= htmlspecialchars($quiz['topic']) ?>" placeholder="Topic" required>

    <div id="questions">
        <?php foreach ($questions as $index => $question):
            $questionId = $question['id'];
            $optionStmt = $pdo->prepare("SELECT * FROM options WHERE question_id = ?");
            $optionStmt->execute([$questionId]);
            $options = $optionStmt->fetchAll();

            $option_texts = ["", "", "", ""];
            $correct_index = null;
            foreach ($options as $i => $opt) {
                $option_texts[$i] = $opt['option_text'];
                if ($opt['is_correct']) $correct_index = $i;
            }
        ?>
        <div class="question-block" id="question-<?= $index ?>" data-question-id="<?= $questionId ?>">
            <div class="question-header" onclick="toggleContent(this)">
                Question <?= $index + 1 ?>: <?= htmlspecialchars($question['question_text']) ?>
            </div>
            <div class="question-content">
                <input type="hidden" name="questions[<?= $index ?>][id]" value="<?= $questionId ?>">
                <button type="button" class="remove-btn" onclick="removeQuestion(this)">Remove</button>
                <textarea name="questions[<?= $index ?>][text]" required><?= htmlspecialchars($question['question_text']) ?></textarea>
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <input type="text" name="questions[<?= $index ?>][options][<?= $i ?>]" value="<?= htmlspecialchars($option_texts[$i]) ?>" placeholder="Option <?= $i + 1 ?>" required />
                <?php endfor; ?>
                <select name="questions[<?= $index ?>][correct]" required>
                    <option value="">Select Correct Answer</option>
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <option value="<?= $i ?>" <?= ($correct_index === $i ? 'selected' : '') ?>>Option <?= $i + 1 ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <button type="button" id="addQuestionBtn" onclick="addQuestion()">+ Add Question</button>
    <button type="submit" id="submitQuiz">Update Quiz</button>
</form>

<script>
let questionCount = <?= count($questions) ?>;

function addQuestion() {
    const qDiv = document.createElement('div');
    qDiv.className = 'question-block';
    qDiv.id = `question-${questionCount}`;
    qDiv.dataset.questionId = "";

    qDiv.innerHTML = `
        <div class="question-header" onclick="toggleContent(this)">
            Question ${questionCount + 1}: (Click to edit)
        </div>
        <div class="question-content">
            <input type="hidden" name="questions[${questionCount}][id]" value="">
            <button type="button" class="remove-btn" onclick="removeQuestion(this)">Remove</button>
            <textarea name="questions[${questionCount}][text]" placeholder="Enter question" required></textarea>
            <input type="text" name="questions[${questionCount}][options][0]" placeholder="Option 1" required />
            <input type="text" name="questions[${questionCount}][options][1]" placeholder="Option 2" required />
            <input type="text" name="questions[${questionCount}][options][2]" placeholder="Option 3" required />
            <input type="text" name="questions[${questionCount}][options][3]" placeholder="Option 4" required />
            <select name="questions[${questionCount}][correct]" required>
                <option value="">Select Correct Answer</option>
                <option value="0">Option 1</option>
                <option value="1">Option 2</option>
                <option value="2">Option 3</option>
                <option value="3">Option 4</option>
            </select>
        </div>
    `;
    document.getElementById('questions').appendChild(qDiv);
    questionCount++;
}

function removeQuestion(button) {
    const block = button.closest('.question-block');
    const questionId = block.dataset.questionId;

    // Unsaved question (no ID): just remove
    if (!questionId || questionId === "0") {
        block.remove();
        reIndexQuestions();
        return;
    }

    if (!confirm("Are you sure you want to delete this saved question? This cannot be undone.")) return;

    fetch('delete_question.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `question_id=${encodeURIComponent(questionId)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            block.remove();
            reIndexQuestions();
        } else {
            alert(data.error || "Failed to delete the question.");
        }
    })
    .catch(err => {
        console.error(err);
        alert("Server error deleting question.");
    });
}

function toggleContent(header) {
    const content = header.nextElementSibling;
    content.style.display = content.style.display === 'block' ? 'none' : 'block';
}

function reIndexQuestions() {
    const blocks = document.querySelectorAll('.question-block');
    blocks.forEach((block, i) => {
        block.id = `question-${i}`;
        block.querySelector('.question-header').innerText = `Question ${i + 1}: (Click to edit)`;
        const inputs = block.querySelectorAll('[name]');
        inputs.forEach(input => {
            input.name = input.name.replace(/questions\[\d+]/, `questions[${i}]`);
        });
    });
    questionCount = blocks.length;
}

function validateForm() {
    const questionBlocks = document.querySelectorAll('.question-block');
    for (const block of questionBlocks) {
        const inputs = block.querySelectorAll('.question-content input[type="text"]');
        const seen = new Set();
        for (const input of inputs) {
            const val = input.value.trim().toLowerCase();
            if (seen.has(val)) {
                alert("Duplicate option found in a question: " + val);
                input.focus();
                return false;
            }
            seen.add(val);
        }
    }
    return true;
}

document.getElementById('quizForm').addEventListener('submit', function (e) {
    if (!validateForm()) e.preventDefault();
});
</script>


</body>
</html>
