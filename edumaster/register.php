<?php
session_start();
require 'db.php'; // your PDO connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $question1 = trim($_POST['security_question_1'] ?? '');
    $answer1 = trim($_POST['security_answer_1'] ?? '');
    $question2 = trim($_POST['security_question_2'] ?? '');
    $answer2 = trim($_POST['security_answer_2'] ?? '');

    // Validate required fields
    if (
        empty($name) || empty($email) || empty($password) ||
        empty($question1) || empty($answer1) ||
        empty($question2) || empty($answer2)
    ) {
        header('Location: login.html?error=empty_fields');
        exit;
    }

    // Check that security questions are not the same
    if (strcasecmp($question1, $question2) === 0) {
        header('Location: login.html?error=same_questions');
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: login.html?error=invalid_email');
        exit;
    }

    // Validate password strength
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', $password)) {
        header('Location: login.html?error=invalid_password');
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->fetchColumn() > 0) {
            header('Location: login.html?error=email_exists');
            exit;
        }

        // Hash the password securely
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert the user into the database
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role, security_question_1, security_answer_1, security_question_2, security_answer_2)
            VALUES (:name, :email, :password, 'student', :question1, :answer1, :question2, :answer2)
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
            'question1' => $question1,
            'answer1' => $answer1,
            'question2' => $question2,
            'answer2' => $answer2
        ]);

        header("Location: login.html?signup=success");

        exit;

    } catch (PDOException $e) {
        error_log('Registration error: ' . $e->getMessage());
        header('Location: login.html?error=server_error');
        exit;
    }
} else {
    header('Location: login.html');
    exit;
}
?>
