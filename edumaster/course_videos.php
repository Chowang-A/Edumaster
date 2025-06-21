<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?error=login_required");
    exit;
}

$courseId = $_GET['id'] ?? null;
if (!$courseId || !is_numeric($courseId)) {
    die("Course ID is missing or invalid");
}
$courseId = (int)$courseId;
$userId = $_SESSION['user_id'];

$userRole = $_SESSION['user_role'] ?? '';

if ($userRole === 'student') {
    $checkStmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ?");
    $checkStmt->execute([$userId, $courseId]);
    if (!$checkStmt->fetchColumn()) {
        $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)")->execute([$userId, $courseId]);
    }
}


$courseStmt = $pdo->prepare("
    SELECT c.title, c.description, c.category, u.name AS teacher_name, u.email AS teacher_email
    FROM courses c
    JOIN users u ON c.teacher_id = u.id
    WHERE c.id = ?
");
$courseStmt->execute([$courseId]);
$course = $courseStmt->fetch();
if (!$course) {
    die("Course not found.");
}

$videoStmt = $pdo->prepare("SELECT * FROM course_videos WHERE course_id = ?");
$videoStmt->execute([$courseId]);
$videos = $videoStmt->fetchAll();

function getEmbedURL($url) {
    if (preg_match('/youtu\.be\/([\w-]+)/', $url, $matches) ||
        preg_match('/youtube\.com\/.*v=([\w-]+)/', $url, $matches)) {
        return "https://www.youtube.com/embed/" . $matches[1];
    }
    if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
        return "https://player.vimeo.com/video/" . $matches[1];
    }
    return $url;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($course['title']) ?> - Course Videos</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #0d1117;
            color: #c9d1d9;
            padding: 2rem;
            margin: 0;
        }
        h1, h2 {
            color: #f9c349;
        }
        .course-details, .video-card {
            background: #161b22;
            padding: 1rem;
            margin-top: 30px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid #30363d;
        }
        .video-card iframe, .video-card video {
            width: 100%;
            max-height: 450px;
            border-radius: 6px;
        }
        .nav-buttons a {
            background-color: #21262d;
            color: #f9c349;
            border: 1px solid #f9c349;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            margin-right: 0.5rem;
            text-decoration: none;
            transition: 0.2s;
        }
        .nav-buttons a:hover {
            background-color: #f9c349;
            color: #0d1117;
        }
        .search-box input {
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid #30363d;
            width: 100%;
            max-width: 400px;
            margin-bottom: 1rem;
            background: #0d1117;
            color: #c9d1d9;
        }
    </style>
</head>
<body>

<div class="nav-buttons">
    <a href="dashboard.php">&larr; Dashboard</a>
    <a href="courses.php">&larr; Courses</a>
    <a href="quizzes.php?course_id=<?= $courseId ?>">📘 Quizzes</a>
</div>

<div class="course-details">
    <h1><?= htmlspecialchars($course['title']) ?></h1>
    <p><strong>Category:</strong> <?= htmlspecialchars($course['category']) ?></p>
    <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
    <p><em>Instructor:</em> <?= htmlspecialchars($course['teacher_name']) ?> (<?= htmlspecialchars($course['teacher_email']) ?>)</p>
</div>

<h2>Course Videos</h2>
<div class="search-box">
    <input type="text" id="searchInput" placeholder="Search video titles...">
</div>

<div id="videoContainer">
    <?php foreach ($videos as $video): 
        $embed = getEmbedURL($video['video_path']); ?>
        <div class="video-card" data-title="<?= strtolower(htmlspecialchars($video['video_title'])) ?>">
            <h3><?= htmlspecialchars($video['video_title']) ?></h3>
            <?php if (str_contains($embed, 'youtube') || str_contains($embed, 'vimeo')): ?>
                <iframe src="<?= $embed ?>" frameborder="0" allowfullscreen></iframe>
            <?php else: ?>
                <video controls>
                    <source src="<?= $embed ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
    document.getElementById('searchInput').addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.video-card').forEach(card => {
            const title = card.getAttribute('data-title');
            card.style.display = title.includes(term) ? 'block' : 'none';
        });
    });
</script>

</body>
</html>