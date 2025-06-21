<?php 
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html?error=Please log in first.");
    exit;
}

$currentUserId = $_SESSION['user_id'];
$currentUserRole = $_SESSION['user_role'];
$success = $error = '';

// Allow admins to edit other users
$editUserId = $currentUserId;
if ($currentUserRole === 'admin' && isset($_GET['id'])) {
    $editUserId = intval($_GET['id']);
}

// Fetch user data
$stmt = $pdo->prepare("SELECT name, email, password, security_question_1, security_answer_1, security_question_2, security_answer_2 FROM users WHERE id = :id");
$stmt->execute(['id' => $editUserId]);
$user = $stmt->fetch();

if (!$user) {
    if ($currentUserRole === 'admin') {
        header("Location: manage_users.php?error=User not found.");
    } else {
        session_destroy();
        header("Location: index.html?error=User not found.");
    }
    exit;
}

// Handle update or delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $sec_q1 = trim($_POST['security_question_1']);
        $sec_a1 = trim($_POST['security_answer_1']);
        $sec_q2 = trim($_POST['security_question_2']);
        $sec_a2 = trim($_POST['security_answer_2']);

        if (empty($name) || empty($email) || empty($sec_q1) || empty($sec_a1) || empty($sec_q2) || empty($sec_a2)) {
            $error = "All fields are required.";
        } elseif ($sec_q1 === $sec_q2) {
            $error = "Security questions must be different.";
        } else {
            $query = "UPDATE users SET name = :name, email = :email, security_question_1 = :q1, security_answer_1 = :a1, security_question_2 = :q2, security_answer_2 = :a2";
            $params = [
                'name' => $name,
                'email' => $email,
                'q1' => $sec_q1,
                'a1' => $sec_a1,
                'q2' => $sec_q2,
                'a2' => $sec_a2,
                'id' => $editUserId
            ];

            if (!empty($password)) {
                if (password_verify($password, $user['password'])) {
                    $error = "New password must be different from the current password.";
                } elseif (strlen($password) < 8 || strlen($password) > 20) {
                    $error = "Password must be between 8 and 20 characters.";
                } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[\W_]/', $password)) {
                    $error = "Password must contain at least one uppercase letter, one lowercase letter, and one special character.";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $query .= ", password = :password";
                    $params['password'] = $hashed;
                }
            }

            if (empty($error)) {
                $query .= " WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $success = "Profile updated successfully.";

                if ($editUserId === $currentUserId) {
                    $_SESSION['email'] = $email;
                }
            }
        }
    }

    if (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $editUserId]);

        if ($editUserId === $currentUserId) {
            session_destroy();
            header("Location: index.html?success=Account deleted.");
        } else {
            header("Location: manage_users.php?success=User deleted.");
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile Settings - EduMaster</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        :root {
            --bg-dark: #0f0f0f;
            --card-bg: #1c1c1c;
            --highlight: #facc15;
            --text: #ffffff;
            --input-bg: #2a2a2a;
            --danger: #dc2626;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text);
            padding: 2rem;
        }

        .container {
            max-width: 600px;
            background-color: var(--card-bg);
            margin: auto;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 10px #000;
        }

        h2 {
            color: var(--highlight);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-top: 1rem;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            background-color: var(--input-bg);
            color: var(--text);
            border: none;
            border-radius: 6px;
            margin-top: 6px;
        }

        button {
            width: 100%;
            margin-top: 1.2rem;
            padding: 12px;
            background-color: var(--highlight);
            border: none;
            color: #000;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #e3b814;
        }

        .danger {
            background-color: var(--danger);
            color: #fff;
            margin-top: 0.8rem;
        }

        .danger:hover {
            background-color: #b91c1c;
        }

        .success, .error {
            text-align: center;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .success { color: #22c55e; }
        .error { color: #f87171; }

        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            background-color: var(--highlight);
            color: #111;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .show-password {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        small {
            color: #aaa;
        }
    </style>
</head>
<body>

<a href="<?= ($currentUserRole === 'admin') ? 'manage_users.php' : 'dashboard.php' ?>" class="back-link">← Back</a>

<div class="container">
    <h2>Profile Settings</h2>

    <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>New Password</label>
        <input type="password" name="password" id="password" placeholder="Leave blank to keep current">
        <small>Password must be 8–20 chars, include uppercase, lowercase & special character.</small>
        <div class="show-password">
            <input type="checkbox" id="togglePassword"> <label for="togglePassword">Show Password</label>
        </div>

        <label>Security Question 1</label>
        <input type="text" name="security_question_1" value="<?= htmlspecialchars($user['security_question_1']) ?>" required>

        <label>Answer 1</label>
        <input type="text" name="security_answer_1" value="<?= htmlspecialchars($user['security_answer_1']) ?>" required>

        <label>Security Question 2</label>
        <input type="text" name="security_question_2" value="<?= htmlspecialchars($user['security_question_2']) ?>" required>

        <label>Answer 2</label>
        <input type="text" name="security_answer_2" value="<?= htmlspecialchars($user['security_answer_2']) ?>" required>

        <button type="submit" name="update">Update Profile</button>

        <button type="submit" name="delete" class="danger" onclick="return confirm('Are you sure you want to delete this account?');">
            <?= $editUserId === $currentUserId ? 'Delete My Account' : 'Delete This User' ?>
        </button>
    </form>
</div>

<script>
document.getElementById("togglePassword").addEventListener("change", function () {
    const pw = document.getElementById("password");
    pw.type = this.checked ? "text" : "password";
});
</script>

</body>
</html>
