<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    header("Location: login.html?error=access_denied");
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

$searchTitle = $_GET['title'] ?? '';
$searchSubject = $_GET['subject'] ?? '';
$searchTeacher = $_GET['teacher'] ?? '';

$successMsg = '';
$errorMsg = '';

// Handle Delete Quiz POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_quiz_id'])) {
    $quizId = intval($_POST['delete_quiz_id']);

    // Ownership check if teacher
    $query = ($role === 'admin')
        ? "DELETE FROM quizzes WHERE id = :id"
        : "DELETE FROM quizzes WHERE id = :id AND teacher_id = :tid";

    $stmt = $pdo->prepare($query);
    $params = ['id' => $quizId];
    if ($role === 'teacher') $params['tid'] = $userId;

    $stmt->execute($params);

    if ($stmt->rowCount()) {
        $successMsg = "Quiz deleted successfully.";
    } else {
        $errorMsg = "Failed to delete quiz. It may not belong to you.";
    }
}

// Fetch filtered quizzes
$query = "SELECT q.id, q.title, q.subject, q.topic, u.name AS teacher_name
          FROM quizzes q
          JOIN users u ON q.teacher_id = u.id
          WHERE 1=1";
$params = [];

if ($role === 'teacher') {
    $query .= " AND q.teacher_id = ?";
    $params[] = $userId;
}
if (!empty($searchTitle)) {
    $query .= " AND LOWER(q.title) LIKE ?";
    $params[] = "%" . strtolower($searchTitle) . "%";
}
if (!empty($searchSubject)) {
    $query .= " AND LOWER(q.subject) LIKE ?";
    $params[] = "%" . strtolower($searchSubject) . "%";
}
if ($role === 'admin' && !empty($searchTeacher)) {
    $query .= " AND LOWER(u.name) LIKE ?";
    $params[] = "%" . strtolower($searchTeacher) . "%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$quizzes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Quizzes - EduMaster</title>
    <style>
        body {
            background: #0f0f0f;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem;
        }

        h1 {
            color: #f9c349;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .btn {
            background-color: #f9c349;
            color: #111;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background-color: #e6b835;
        }

        .btn-danger {
            background-color: crimson;
            color: #fff;
        }

        .btn-danger:hover {
            background-color: #a60000;
        }

        .search-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .search-bar input {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #444;
            background-color: #1c1c1c;
            color: #fff;
            flex: 1;
        }

        .search-bar button {
            flex-shrink: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #1c1c1c;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        th {
            background-color: #2c2c2c;
            color: #f9c349;
        }

        td {
            color: #f0f0f0;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .message {
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .success {
            background-color: #14532d;
            color: #bbf7d0;
        }

        .error {
            background-color: #7f1d1d;
            color: #fee2e2;
        }

        p {
            text-align: center;
            color: #ccc;
            margin-top: 2rem;
        }
    </style>
</head>
<body>

<div class="top-bar">
    <h1>Edit Quizzes</h1>
    <a href="dashboard.php" class="btn">⬅ Back to Dashboard</a>
</div>

<?php if ($successMsg): ?>
    <div class="message success"><?= htmlspecialchars($successMsg) ?></div>
<?php endif; ?>
<?php if ($errorMsg): ?>
    <div class="message error"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<form method="get" class="search-bar">
    <input type="text" name="title" placeholder="Search Title" value="<?= htmlspecialchars($searchTitle) ?>">
    <input type="text" name="subject" placeholder="Search Subject" value="<?= htmlspecialchars($searchSubject) ?>">
    <?php if ($role === 'admin'): ?>
        <input type="text" name="teacher" placeholder="Search Teacher" value="<?= htmlspecialchars($searchTeacher) ?>">
    <?php endif; ?>
    <button type="submit" class="btn">🔍 Search</button>
</form>

<?php if (count($quizzes) > 0): ?>
    <table>
        <thead>
        <tr>
            <th>Title</th>
            <th>Subject</th>
            <th>Topic</th>
            <th>Teacher</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($quizzes as $quiz): ?>
            <tr>
                <td><?= htmlspecialchars($quiz['title']) ?></td>
                <td><?= htmlspecialchars($quiz['subject']) ?></td>
                <td><?= htmlspecialchars($quiz['topic']) ?></td>
                <td><?= htmlspecialchars($quiz['teacher_name']) ?></td>
                <td class="action-buttons">
                    <a class="btn" href="edit_quiz_form.php?quiz_id=<?= $quiz['id'] ?>">✏️ Edit</a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this quiz?');">
                        <input type="hidden" name="delete_quiz_id" value="<?= $quiz['id'] ?>">
                        <button type="submit" class="btn btn-danger">🗑 Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No quizzes found matching your criteria.</p>
<?php endif; ?>

</body>
</html>
