<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html?error=Please log in first.");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch current user role
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
$role = $stmt->fetchColumn();

// Define allowed recipient roles
$allowed_roles = match ($role) {
    'student' => ['student', 'teacher'],
    'teacher' => ['student', 'teacher', 'admin'],
    'admin'   => ['student', 'teacher', 'admin'],
    default   => []
};

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_email = trim($_POST['receiver_email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($receiver_email && $subject && $message) {
        $stmt = $pdo->prepare("SELECT id, role FROM users WHERE email = :email");
        $stmt->execute(['email' => $receiver_email]);
        $receiver = $stmt->fetch();

        if ($receiver && in_array($receiver['role'], $allowed_roles)) {
            $stmt = $pdo->prepare("
                INSERT INTO messages (sender_id, receiver_id, subject, message, send_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $receiver['id'], $subject, $message]);
            $success = "Message sent successfully!";
        } else {
            $error = "Recipient not found or not allowed.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Send Message | EduMaster</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"/>
  <link href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" rel="stylesheet"/>
  <style>
    :root {
      --bg: #0f0f0f;
      --card: #1c1c1c;
      --highlight: #f9c349;
      --text: #ffffff;
      --error: #ff4d4d;
      --success: #4dff88;
      --radius: 10px;
      --transition: all 0.3s ease;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--bg);
      color: var(--text);
      padding: 2rem;
      line-height: 1.6;
    }

    h1 {
      font-size: 2rem;
      margin-bottom: 1.5rem;
      color: var(--highlight);
    }

    .nav-links {
      margin-bottom: 2rem;
    }

    .nav-links a {
      text-decoration: none;
      background: var(--highlight);
      color: var(--bg);
      padding: 0.5rem 1rem;
      border-radius: var(--radius);
      margin-right: 1rem;
      font-weight: 600;
      display: inline-block;
      transition: var(--transition);
    }

    .nav-links a:hover {
      background: #e5b933;
    }

    .form-container {
      background-color: var(--card);
      padding: 2rem;
      border-radius: var(--radius);
      max-width: 600px;
      margin: auto;
      box-shadow: 0 0 12px rgba(249, 195, 73, 0.2);
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
    }

    input, textarea {
      width: 100%;
      padding: 0.8rem;
      background-color: #2e2e2e;
      border: none;
      border-radius: var(--radius);
      color: var(--text);
      margin-bottom: 1.5rem;
      font-size: 1rem;
    }

    button {
      padding: 0.8rem 1.5rem;
      background-color: var(--highlight);
      color: var(--bg);
      border: none;
      border-radius: var(--radius);
      font-weight: bold;
      font-size: 1rem;
      cursor: pointer;
      transition: var(--transition);
    }

    button:hover {
      background-color: #e0b631;
    }

    .message {
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: var(--radius);
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .error {
      background-color: rgba(255, 77, 77, 0.1);
      color: var(--error);
    }

    .success {
      background-color: rgba(77, 255, 136, 0.1);
      color: var(--success);
    }

    .message i {
      font-size: 1.4rem;
    }

    @media (max-width: 600px) {
      .form-container {
        padding: 1.5rem;
      }

      input, textarea {
        font-size: 0.95rem;
      }
    }
  </style>
</head>
<body>

  <h1><i class="uil uil-message"></i> Send a Message</h1>

  <div class="nav-links">
    <a href="dashboard.php"><i class="uil uil-estate"></i> Dashboard</a>
    <a href="inbox.php"><i class="uil uil-inbox"></i> Inbox</a>
  </div>

  <div class="form-container">
    <?php if ($error): ?>
      <div class="message error"><i class="uil uil-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
      <div class="message success"><i class="uil uil-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label for="receiver_email">To (Email):</label>
      <input type="email" name="receiver_email" id="receiver_email" placeholder="Recipient's email..." required>

      <label for="subject">Subject:</label>
      <input type="text" name="subject" id="subject" placeholder="Message subject..." required>

      <label for="message">Message:</label>
      <textarea name="message" id="message" rows="5" placeholder="Type your message here..." required></textarea>

      <button type="submit"><i class="uil uil-message"></i> Send Message</button>
    </form>
  </div>

</body>
</html>
