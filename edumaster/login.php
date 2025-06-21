<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header('Location: login.html?error=empty_fields');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                header('Location: dashboard.php');
                exit;
            } else {
                header('Location: login.html?error=incorrect_password');
                exit;
            }
        } else {
            header('Location: login.html?error=user_not_found');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        header('Location: login.html?error=server_error');
        exit;
    }
} else {
    header('Location: login.html');
    exit;
}
?>
