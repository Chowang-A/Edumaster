<?php
session_start();
require 'db.php';

// Ensure admin access
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: index.html?error=Access denied.");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: manage_courses.php?error=Invalid course ID.");
    exit;
}

$course_id = intval($_GET['id']);
$success = '';
$error = '';

// Fetch course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :id");
$stmt->execute(['id' => $course_id]);
$course = $stmt->fetch();

if (!$course) {
    header("Location: manage_courses.php?error=Course not found.");
    exit;
}

// Fetch videos and images
$videos = $pdo->prepare("SELECT * FROM course_videos WHERE course_id = ?");
$videos->execute([$course_id]);
$videos = $videos->fetchAll();

$images = $pdo->prepare("SELECT * FROM course_images WHERE course_id = ?");
$images->execute([$course_id]);
$images = $images->fetchAll();

// Fetch all teachers
$teachers = $pdo->query("SELECT id, name FROM users WHERE role = 'teacher'")->fetchAll();

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $teacher_id = intval($_POST['teacher_id']);

    if ($title && $category && $description && $teacher_id) {
        $update = $pdo->prepare("UPDATE courses SET title = :title, category = :category, description = :description, teacher_id = :teacher_id WHERE id = :id");
        $update->execute([
            'title' => $title,
            'category' => $category,
            'description' => $description,
            'teacher_id' => $teacher_id,
            'id' => $course_id
        ]);
        $success = "Course updated successfully.";
        $stmt->execute(['id' => $course_id]);
        $course = $stmt->fetch();
    } else {
        $error = "All fields are required.";
    }

    if (!empty($_POST['delete_videos'])) {
        $delete = $pdo->prepare("DELETE FROM course_videos WHERE id = ?");
        foreach ($_POST['delete_videos'] as $vid) {
            $delete->execute([$vid]);
        }
    }

    if (!empty($_POST['delete_images'])) {
        $delete = $pdo->prepare("DELETE FROM course_images WHERE id = ?");
        foreach ($_POST['delete_images'] as $img) {
            $delete->execute([$img]);
        }
    }

    if (!empty($_FILES['new_video']['name'])) {
        $videoPath = 'uploads/videos/' . basename($_FILES['new_video']['name']);
        move_uploaded_file($_FILES['new_video']['tmp_name'], $videoPath);
        $pdo->prepare("INSERT INTO course_videos (course_id, video_title, video_path) VALUES (?, ?, ?)")
            ->execute([$course_id, $_POST['new_video_title'], $videoPath]);
    }

    if (!empty($_FILES['new_image']['name'])) {
        $imgPath = 'uploads/images/' . basename($_FILES['new_image']['name']);
        move_uploaded_file($_FILES['new_image']['tmp_name'], $imgPath);
        $pdo->prepare("INSERT INTO course_images (course_id, image_path) VALUES (?, ?)")
            ->execute([$course_id, $imgPath]);
    }

    // Refresh media
    $videos = $pdo->prepare("SELECT * FROM course_videos WHERE course_id = ?");
    $videos->execute([$course_id]);
    $videos = $videos->fetchAll();

    $images = $pdo->prepare("SELECT * FROM course_images WHERE course_id = ?");
    $images->execute([$course_id]);
    $images = $images->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Course - Admin</title>
    <style>
        body { background: #111; color: #fff; font-family: Arial; padding: 2rem; }
        h1 { color: #d4af37; }
        form { background: #1e1e1e; padding: 2rem; border-radius: 10px; max-width: 800px; margin: auto; }
        label { display: block; margin-top: 1rem; }
        input[type="text"], textarea, select {
            width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px;
            border: 1px solid #333; background: #2b2b2b; color: #fff;
        }
        textarea { resize: vertical; height: 100px; }
        .media-block { margin-top: 1rem; }
        .media-block img, .media-block video { max-width: 200px; margin-right: 10px; vertical-align: middle; }
        .media-block label { display: inline; margin-right: 10px; }
        button { margin-top: 1rem; padding: 10px 20px; background: #d4af37; color: #000; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #caa937; }
        .message { color: #0f0; margin-top: 1rem; }
        .error { color: red; }
        .back { display: inline-block; margin-top: 1rem; color: #d4af37; text-decoration: none; }

        #videoModal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); justify-content: center; align-items: center; z-index: 1000;
        }
        #videoModal video {
            max-width: 80%; max-height: 80%;
        }
        #videoModal .close-btn {
            position: absolute; top: 20px; right: 30px; font-size: 30px; color: #fff; cursor: pointer;
        }
    </style>
</head>
<body>

<h1>Edit Course</h1>
<a class="back" href="manage_courses.php">⬅ Back</a>

<form method="POST" enctype="multipart/form-data">
    <label>Course Title</label>
    <input type="text" name="title" value="<?= htmlspecialchars($course['title']) ?>" required>

    <label>Category</label>
    <input type="text" name="category" value="<?= htmlspecialchars($course['category']) ?>" required>

    <label>Description</label>
    <textarea name="description" required><?= htmlspecialchars($course['description']) ?></textarea>

    <label>Teacher</label>
    <select name="teacher_id" required>
        <?php foreach ($teachers as $t): ?>
            <option value="<?= $t['id'] ?>" <?= $course['teacher_id'] == $t['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($t['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div class="media-block">
        <label>Search Videos by Title</label>
        <input type="text" id="videoSearch" onkeyup="filterVideos()" placeholder="Search video title..." style="margin-bottom: 1rem;">

        <label>Existing Videos (check to delete)</label><br>
        <?php foreach ($videos as $video): ?>
            <div class="video-item" data-title="<?= htmlspecialchars($video['video_title']) ?>" style="margin-bottom: 10px;">
                <label>
                    <input type="checkbox" name="delete_videos[]" value="<?= $video['id'] ?>">
                    <?= htmlspecialchars($video['video_title']) ?>
                </label>
                <button type="button" onclick="showVideo('<?= $video['video_path'] ?>')" style="margin-left: 10px;">▶ Preview</button>
            </div>
        <?php endforeach; ?>
    </div>

    <label>New Video Title</label>
    <input type="text" name="new_video_title">
    <label>Upload New Video</label>
    <input type="file" name="new_video" accept="video/*">

    <div class="media-block">
        <label>Existing Images (check to delete)</label><br>
        <?php foreach ($images as $img): ?>
            <label>
                <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>">
                <img src="<?= $img['image_path'] ?>" height="80">
            </label>
        <?php endforeach; ?>
    </div>

    <label>Upload New Image</label>
    <input type="file" name="new_image" accept="image/*">

    <button type="submit">Update Course</button>

    <?php if ($success): ?><p class="message"><?= $success ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
</form>

<!-- Video Modal -->
<div id="videoModal" onclick="closeVideo()">
    <span class="close-btn" onclick="closeVideo()">×</span>
    <video controls></video>
</div>

<script>
    function showVideo(path) {
        const modal = document.getElementById('videoModal');
        const video = modal.querySelector('video');
        video.src = path;
        modal.style.display = 'flex';
        video.play();
    }

    function closeVideo() {
        const modal = document.getElementById('videoModal');
        const video = modal.querySelector('video');
        video.pause();
        video.src = '';
        modal.style.display = 'none';
    }

    function filterVideos() {
        const query = document.getElementById('videoSearch').value.toLowerCase();
        const blocks = document.querySelectorAll('.video-item');
        blocks.forEach(item => {
            const title = item.getAttribute('data-title').toLowerCase();
            item.style.display = title.includes(query) ? '' : 'none';
        });
    }
</script>

</body>
</html>
