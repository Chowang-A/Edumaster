<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: index.html?error=Access denied.");
    exit;
}

$teacher_id = $_SESSION['user_id'];

// --- Handle Course Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_course'])) {
        $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, category = ? WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$_POST['title'], $_POST['description'], $_POST['category'], $_POST['course_id'], $teacher_id]);
    } elseif (isset($_POST['delete_course'])) {
        $pdo->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?")->execute([$_POST['delete_course'], $teacher_id]);
    } elseif (isset($_POST['update_video_title'])) {
        $pdo->prepare("UPDATE course_videos SET video_title = ? WHERE id = ?")
            ->execute([$_POST['new_title'], $_POST['video_id']]);
    } elseif (isset($_POST['upload_images'])) {
        $course_id = $_POST['course_id'];
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $path = "uploads/images/" . time() . basename($_FILES['images']['name'][$key]);
            if (move_uploaded_file($tmp_name, $path)) {
                $pdo->prepare("INSERT INTO course_images (course_id, image_path) VALUES (?, ?)")->execute([$course_id, $path]);
            }
        }
    } elseif (isset($_POST['upload_video'])) {
        $course_id = $_POST['course_id'];
        $title = $_POST['video_title'];
        $path = "uploads/videos/" . time() . basename($_FILES['video']['name']);
        if (move_uploaded_file($_FILES['video']['tmp_name'], $path)) {
            $pdo->prepare("INSERT INTO course_videos (course_id, video_title, video_path) VALUES (?, ?, ?)")->execute([$course_id, $title, $path]);
        }
    } elseif (isset($_POST['delete_image'])) {
        $pdo->prepare("DELETE FROM course_images WHERE id = ?")->execute([$_POST['delete_image']]);
    } elseif (isset($_POST['delete_video'])) {
        $pdo->prepare("DELETE FROM course_videos WHERE id = ?")->execute([$_POST['delete_video']]);
    } elseif (isset($_POST['bulk_delete_images']) && !empty($_POST['image_ids'])) {
        $stmt = $pdo->prepare("DELETE FROM course_images WHERE id IN (" . implode(',', array_fill(0, count($_POST['image_ids']), '?')) . ")");
        $stmt->execute($_POST['image_ids']);
    } elseif (isset($_POST['bulk_delete_videos']) && !empty($_POST['video_ids'])) {
        $stmt = $pdo->prepare("DELETE FROM course_videos WHERE id IN (" . implode(',', array_fill(0, count($_POST['video_ids']), '?')) . ")");
        $stmt->execute($_POST['video_ids']);
    }
}

$courses = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
$courses->execute([$teacher_id]);
$courses = $courses->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Alter Courses - EduMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background: #0f0f0f; color: #f1f1f1; font-family: 'Poppins', sans-serif; padding: 20px; }
        h2 { color: #facc15; text-align: center; margin-bottom: 20px; }
        .top-bar { text-align: center; margin-bottom: 20px; }
        .top-bar a, .top-bar button { background: #facc15; color: #000; padding: 10px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; border: none; cursor: pointer; }
        .course { background: #1c1c1c; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .course-title { font-size: 20px; font-weight: 600; color: #facc15; cursor: pointer; }
        .course-form { display: none; margin-top: 15px; border-top: 1px solid #444; padding-top: 15px; }
        input, textarea, select { width: 100%; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 6px; padding: 10px; margin-bottom: 12px; }
        button { padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .update-btn { background: #3b82f6; color: #fff; }
        .delete-btn { background: #dc2626; color: #fff; margin-left: 10px; }
        .media-preview { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px; }
        .media-preview div { background: #2d2d2d; padding: 10px; border-radius: 10px; position: relative; width: 240px; }
        .media-preview img, .media-preview video { width: 100%; max-height: 150px; border-radius: 6px; }
        .checkbox { position: absolute; top: 10px; left: 10px; }
        .inline-form { display: flex; gap: 8px; align-items: center; margin-top: 6px; }
        .inline-form input { flex: 1; }
        .search { padding: 10px; width: 100%; border-radius: 6px; border: 1px solid #444; background: #2a2a2a; color: #fff; margin-bottom: 20px; }
    </style>
</head>
<body>
<h2>Manage Your Courses</h2>
<div class="top-bar">
    <a href="dashboard.php">⬅ Back to Dashboard</a>
</div>
<input type="text" id="searchInput" onkeyup="filterCourses()" class="search" placeholder="Search courses...">
<?php foreach ($courses as $course): ?>
    <div class="course" data-title="<?= htmlspecialchars($course['title']) ?>">
        <div class="course-title" onclick="toggleForm(this)"><?= htmlspecialchars($course['title']) ?></div>
        <div class="course-form">
            <form method="post">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <input type="text" name="title" value="<?= htmlspecialchars($course['title']) ?>" required>
                <textarea name="description" required><?= htmlspecialchars($course['description']) ?></textarea>
                <input type="text" name="category" value="<?= htmlspecialchars($course['category']) ?>" required>
                <button type="submit" name="update_course" class="update-btn">Update</button>
                <button type="submit" name="delete_course" value="<?= $course['id'] ?>" class="delete-btn" onclick="return confirm('Delete this course?')">Delete</button>
            </form>

            <!-- Image Upload -->
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <input type="file" name="images[]" accept="image/*" multiple required>
                <button type="submit" name="upload_images">Upload Images</button>
            </form>
            <form method="post">
                <div class="media-preview">
                <?php
                $imgs = $pdo->prepare("SELECT * FROM course_images WHERE course_id = ?");
                $imgs->execute([$course['id']]);
                foreach ($imgs as $img): ?>
                    <div>
                        <input type="checkbox" class="checkbox" name="image_ids[]" value="<?= $img['id'] ?>">
                        <img src="<?= $img['image_path'] ?>">
                        <button type="submit" name="delete_image" value="<?= $img['id'] ?>" class="delete-btn">Delete</button>
                    </div>
                <?php endforeach; ?>
                </div>
                <button type="submit" name="bulk_delete_images">Delete Selected Images</button>
            </form>

            <!-- Video Upload -->
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <input type="text" name="video_title" placeholder="Video Title" required>
                <input type="file" name="video" accept="video/*" required>
                <button type="submit" name="upload_video">Upload Video</button>
            </form>

            <!-- Existing Videos -->
            <form method="post">
                <div class="media-preview">
                <?php
                $videos = $pdo->prepare("SELECT * FROM course_videos WHERE course_id = ?");
                $videos->execute([$course['id']]);
                foreach ($videos as $video): ?>
                    <div>
                        <input type="checkbox" class="checkbox" name="video_ids[]" value="<?= $video['id'] ?>">
                        <video controls src="<?= $video['video_path'] ?>"></video>
                        <div class="inline-form">
                            <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                            <input type="text" name="new_title" value="<?= htmlspecialchars($video['video_title']) ?>">
                            <button type="submit" name="update_video_title">Save</button>
                        </div>
                        <button type="submit" name="delete_video" value="<?= $video['id'] ?>" class="delete-btn">Delete</button>
                    </div>
                <?php endforeach; ?>
                </div>
                <button type="submit" name="bulk_delete_videos">Delete Selected Videos</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
<script>
function toggleForm(titleDiv) {
    const form = titleDiv.nextElementSibling;
    form.style.display = form.style.display === "block" ? "none" : "block";
}
function filterCourses() {
    const term = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.course').forEach(course => {
        const title = course.dataset.title.toLowerCase();
        course.style.display = title.includes(term) ? 'block' : 'none';
    });
}
</script>
</body>
</html>