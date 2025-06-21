<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.html?error=Access denied.");
    exit;
}

// Handle course deletion
if (isset($_POST['delete_course'])) {
    $course_id = $_POST['course_id'];

    $pdo->prepare("DELETE FROM videos WHERE course_id = :course_id")->execute(['course_id' => $course_id]);
    $pdo->prepare("DELETE FROM enrollments WHERE course_id = :course_id")->execute(['course_id' => $course_id]);
    $pdo->prepare("DELETE FROM courses WHERE id = :id")->execute(['id' => $course_id]);

    echo "<script>alert('Course deleted successfully'); window.location.href='admin_manage_courses.php';</script>";
    exit;
}

// Handle filters
$title = $_GET['title'] ?? '';
$courseId = $_GET['course_id'] ?? '';
$teacher = $_GET['teacher'] ?? '';

$query = "
    SELECT 
        courses.id, 
        courses.title, 
        courses.category, 
        courses.created_at, 
        users.name AS teacher_name,
        COUNT(enrollments.id) AS student_count
    FROM courses
    JOIN users ON courses.teacher_id = users.id
    LEFT JOIN enrollments ON courses.id = enrollments.course_id
    WHERE 1=1
";

$params = [];

if (!empty($title)) {
    $query .= " AND LOWER(courses.title) LIKE :title";
    $params['title'] = '%' . strtolower($title) . '%';
}
if (!empty($courseId)) {
    $query .= " AND courses.id = :course_id";
    $params['course_id'] = $courseId;
}
if (!empty($teacher)) {
    $query .= " AND LOWER(users.name) LIKE :teacher";
    $params['teacher'] = '%' . strtolower($teacher) . '%';
}

$query .= " GROUP BY courses.id, users.name ORDER BY courses.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses - Admin Panel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem;
            background-color: #111;
            color: #fff;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        h1 {
            color: #f9c349;
            font-size: 2rem;
        }
        .btn {
            background-color: #f9c349;
            color: #111;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background-color: #e6b835;
        }
        .search-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .search-form input[type="text"] {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #333;
            background-color: #1c1c1c;
            color: #fff;
            width: 200px;
        }
        .search-form button {
            padding: 10px 16px;
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
        form.inline {
            display: inline;
        }
    </style>
</head>
<body>

<header>
    <h1>Manage All Courses</h1>
    <a href="dashboard.php" class="btn">⬅ Back to Dashboard</a>
</header>

<form method="GET" class="search-form">
    <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" placeholder="Search by Title">
    <input type="text" name="teacher" value="<?= htmlspecialchars($teacher) ?>" placeholder="Search by Teacher Name">
    <button type="submit" class="btn">🔍 Search</button>
</form>

<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Teacher</th>
            <th>Created At</th>
            <th>Students</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($courses) === 0): ?>
            <tr><td colspan="7" style="text-align:center;">No courses found.</td></tr>
        <?php endif; ?>
        <?php foreach ($courses as $course): ?>
            <tr>
                <td><?= htmlspecialchars($course['title']) ?></td>
                <td><?= htmlspecialchars($course['category']) ?></td>
                <td><?= htmlspecialchars($course['teacher_name']) ?></td>
                <td><?= htmlspecialchars($course['created_at']) ?></td>
                <td>
                    <a class="btn" href="view_students.php?course_id=<?= $course['id'] ?>" target="_blank">
                        View (<?= $course['student_count'] ?>)
                    </a>
                </td>
                <td>
                    <a class="btn" href="admin_alter_courses.php?id=<?= $course['id'] ?>">✏️ Edit</a>
                </td>
                <td>
                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this course?');">
                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                        <button type="submit" name="delete_course" class="btn" style="background-color: crimson; color: white;">🗑 Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
