<?php
session_start();
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'teacher') {
    header("Location: index.html?error=Access denied.");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Quiz</title>
    <style>
        body {
            background-color: #121212;
            color: #f1c40f;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #f1c40f;
        }

        form {
            max-width: 800px;
            margin: auto;
            background: #1c1c1c;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #f1c40f33;
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

        input:focus, textarea:focus, select:focus {
            outline: 2px solid #f1c40f;
        }

        .question-block {
            background: #2a2a2a;
            padding: 15px;
            border-left: 5px solid #f1c40f;
            margin-top: 20px;
            border-radius: 8px;
            position: relative;
        }


        .remove-btn {
         background: #e74c3c;
         color: white;
         border: none;
         padding: 6px 10px;
         border-radius: 4px;
         cursor: pointer;
         margin-top: 10px;
        width: fit-content;
     }

     .remove-btn:hover {
       background: #c0392b;
      }


        #addQuestionBtn {
            background-color: #f1c40f;
            color: black;
            font-weight: bold;
            margin-top: 20px;
        }

        #submitQuiz {
            background-color: #27ae60;
            color: white;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h1>Create a New Quiz</h1>
<div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
    <a href="dashboard.php" style="background-color: #f1c40f; color: black; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;">&larr; Back to Dashboard</a>
    <a href="edit_quiz.php" style="background-color: #f1c40f; color: black; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;">&larr;  Edit Quizzes</a>
</div>


<form method="POST" action="save_quiz.php" onsubmit="return validateForm()">
    <input type="text" name="title" placeholder="Quiz Title" required>
    <input type="text" name="subject" placeholder="Subject" required>
    <input type="text" name="topic" placeholder="Topic" required>

    <div id="questions"></div>

    <button type="button" id="addQuestionBtn" onclick="addQuestion()">+ Add Question</button>
    <button type="submit" id="submitQuiz">Save Quiz</button>
</form>

<script>
let questionCount = 0;
const questionsData = [];

function addQuestion() {
    const qDiv = document.createElement('div');
    qDiv.className = 'question-block';
    qDiv.id = `question-${questionCount}`;

    qDiv.innerHTML = `
        <h3>Question ${questionCount + 1}</h3>
        <textarea name="questions[${questionCount}][text]" 
                  placeholder="Enter question" 
                  required 
                  oninput="checkDuplicateQuestions(this)">
        </textarea>
        <input type="text" name="questions[${questionCount}][options][0]" 
               placeholder="Option 1" required 
               oninput="checkDuplicateOptions(this)">
        <input type="text" name="questions[${questionCount}][options][1]" 
               placeholder="Option 2" required 
               oninput="checkDuplicateOptions(this)">
        <input type="text" name="questions[${questionCount}][options][2]" 
               placeholder="Option 3" required 
               oninput="checkDuplicateOptions(this)">
        <input type="text" name="questions[${questionCount}][options][3]" 
               placeholder="Option 4" required 
               oninput="checkDuplicateOptions(this)">
        <select name="questions[${questionCount}][correct]" required>
            <option value="">Select Correct Answer</option>
            <option value="0">Option 1</option>
            <option value="1">Option 2</option>
            <option value="2">Option 3</option>
            <option value="3">Option 4</option>
        </select>
        <button type="button" class="remove-btn" onclick="removeQuestion(${questionCount})">Remove</button>
    `;

    document.getElementById('questions').appendChild(qDiv);
    questionCount++;
}

function removeQuestion(id) {
    const block = document.getElementById(`question-${id}`);
    if (block) {
        block.remove();
    }
}

function checkDuplicateQuestions(currentTextarea) {
    const allQuestions = document.querySelectorAll('textarea[name^="questions"]');
    const currentText = currentTextarea.value.trim().toLowerCase();

    for (const textarea of allQuestions) {
        if (textarea !== currentTextarea && textarea.value.trim().toLowerCase() === currentText && currentText !== '') {
            currentTextarea.setCustomValidity("⚠️ Duplicate question detected.");
            currentTextarea.reportValidity();
            return;
        }
    }
    currentTextarea.setCustomValidity('');
}

function checkDuplicateOptions(currentInput) {
    const parent = currentInput.closest('.question-block');
    const allOptions = parent.querySelectorAll('input[name^="questions"][name*="[options]"]');

    const values = [];
    let duplicate = false;

    for (const input of allOptions) {
        const val = input.value.trim().toLowerCase();
        if (val && values.includes(val)) {
            duplicate = true;
            break;
        }
        if (val) values.push(val);
    }

    for (const input of allOptions) {
        if (duplicate) {
            input.setCustomValidity("⚠️ Duplicate options not allowed.");
            input.reportValidity();
        } else {
            input.setCustomValidity('');
        }
    }
}

function validateForm() {
    const questionBlocks = document.querySelectorAll('.question-block');
    if (questionBlocks.length === 0) {
        alert("Please add at least one question.");
        return false;
    }

    for (const block of questionBlocks) {
        const inputs = block.querySelectorAll('input[type="text"]');
        let filledOptions = 0;
        for (const input of inputs) {
            if (input.value.trim() !== '') filledOptions++;
        }

        if (filledOptions < 2) {
            alert("Each question must have at least 2 options filled.");
            return false;
        }
    }

    return true;
}
</script>


</body>
</html>
