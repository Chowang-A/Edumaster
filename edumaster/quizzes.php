<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html?error=Please log in.");
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

$searchTeacher = $_GET['teacher'] ?? '';
$searchTopic = $_GET['topic'] ?? '';

$query = "
    SELECT q.*, u.name AS teacher_name
    FROM quizzes q
    JOIN users u ON q.teacher_id = u.id
    WHERE 1=1
";
$params = [];

if (!empty($searchTeacher)) {
    $query .= " AND LOWER(u.name) LIKE LOWER(?)";
    $params[] = '%' . $searchTeacher . '%';
}
if (!empty($searchTopic)) {
    $query .= " AND LOWER(q.topic) LIKE LOWER(?)";
    $params[] = '%' . $searchTopic . '%';
}

$query .= " ORDER BY q.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EduMaster | All Quizzes</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" rel="stylesheet">
  <style>
    :root {
      --bg: #0f0f0f;
      --card-bg: #1b1b1b;
      --primary: #f9c349;
      --text: #ffffff;
      --muted: #bbbbbb;
      --danger: #e74c3c;
      --radius: 12px;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--bg);
      color: var(--text);
      padding: 2rem;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    header h1 {
      font-size: 2rem;
      color: var(--primary);
    }

    .dashboard-btn {
      background-color: var(--primary);
      color: var(--bg);
      padding: 0.6rem 1.2rem;
      border-radius: var(--radius);
      text-decoration: none;
      font-weight: 600;
      transition: background 0.3s;
    }

    .dashboard-btn:hover {
      background-color: #e0ae2d;
    }

    form.search-bar {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .search-bar input[type="text"] {
      padding: 0.6rem 1rem;
      border-radius: var(--radius);
      border: none;
      background-color: #2a2a2a;
      color: var(--text);
      width: 250px;
    }

    .search-bar input[type="submit"] {
      background-color: var(--primary);
      color: var(--bg);
      border: none;
      border-radius: var(--radius);
      padding: 0.6rem 1.5rem;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
    }

    .search-bar input[type="submit"]:hover {
      background-color: #e0ae2d;
    }

    .quiz-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 1.5rem;
    }

    .quiz-card {
      background-color: var(--card-bg);
      padding: 1.5rem;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      transition: transform 0.2s;
    }

    .quiz-card:hover {
      transform: translateY(-5px);
    }

    .quiz-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 0.5rem;
    }

    .quiz-meta {
      font-size: 0.9rem;
      color: var(--muted);
      margin-bottom: 1rem;
      line-height: 1.4;
    }

    .quiz-actions a {
      display: inline-block;
      margin-right: 1rem;
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }

    .quiz-actions a:hover {
      color: #ffe78f;
    }

    .quiz-actions a.delete {
      color: var(--danger);
    }

    .quiz-actions a.delete:hover {
      color: #ff3c3c;
    }

    @media (max-width: 600px) {
      .search-bar input[type="text"] {
        width: 100%;
      }

      header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1><i class="uil uil-list-ul"></i> All Quizzes</h1>
    <a href="dashboard.php" class="dashboard-btn"><i class="uil uil-estate"></i> Dashboard</a>
  </header>

  <form method="GET" class="search-bar">
    <input type="text" name="teacher" placeholder="Search by Teacher" value="<?= htmlspecialchars($searchTeacher) ?>">
    <input type="text" name="topic" placeholder="Search by Topic" value="<?= htmlspecialchars($searchTopic) ?>">
    <input type="submit" value="Search">
  </form>

  <div class="quiz-list">
    <?php if ($quizzes): ?>
      <?php foreach ($quizzes as $quiz): ?>
        <div class="quiz-card">
          <div class="quiz-title"><?= htmlspecialchars($quiz['title']) ?></div>
          <div class="quiz-meta">
            <strong>Subject:</strong> <?= htmlspecialchars($quiz['subject'] ?? 'N/A') ?><br>
            <strong>Topic:</strong> <?= htmlspecialchars($quiz['topic'] ?? 'N/A') ?><br>
            <strong>Teacher:</strong> <?= htmlspecialchars($quiz['teacher_name']) ?><br>
            <strong>Created:</strong> <?= date('d M Y, h:i A', strtotime($quiz['created_at'])) ?>
          </div>
          <div class="quiz-actions">
            <a href="view_quiz.php?id=<?= $quiz['id'] ?>"><i class="uil uil-edit-alt"></i> Take Quiz</a>
            <?php if ($userRole === 'teacher' && $quiz['teacher_id'] == $userId): ?>
              <a href="edit_quiz.php?id=<?= $quiz['id'] ?>"><i class="uil uil-pen"></i> Edit</a>
              <a href="delete_quiz.php?id=<?= $quiz['id'] ?>" class="delete" onclick="return confirm('Delete this quiz?')"><i class="uil uil-trash-alt"></i> Delete</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No quizzes found.</p>
    <?php endif; ?>
  </div>

</body>
</html>
