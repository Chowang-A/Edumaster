<?php
session_start();
require 'db.php'; // Your PDO DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $answer1 = trim($_POST['security_answer_1'] ?? '');
    $answer2 = trim($_POST['security_answer_2'] ?? '');

    // Basic validation
    if (empty($email) || empty($answer1) || empty($answer2)) {
        header("Location: forgot_password.php?error=missing_fields");
        exit;
    }

    // Fetch the answers from DB
    $stmt = $pdo->prepare("SELECT id, name, email, role, security_answer_1, security_answer_2 FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        $user &&
        strtolower(trim($user['security_answer_1'])) === strtolower($answer1) &&
        strtolower(trim($user['security_answer_2'])) === strtolower($answer2)
    ) {
        // Login the user and store session info
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        header("Location: dashboard.php");
        exit;
    } else {
        header("Location: forgot-password.php?error=incorrect_answers");
        exit;
    }
} else {
    header("Location: forgot-password.php");
    exit;
}
