<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: index.html?error=Access denied.");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $category = htmlspecialchars(trim($_POST['category']));
    $video_titles = $_POST['video_titles'] ?? [];
    $video_links = $_POST['video_links'] ?? [];
    $video_files = $_FILES['video_files'] ?? [];
    $image_files = $_FILES['course_images'] ?? [];

    $allowed_video_types = ['video/mp4', 'video/webm', 'video/ogg'];
    $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_video_size = 100 * 1024 * 1024;
    $max_image_size = 5 * 1024 * 1024;
    $upload_dir_video = 'uploads/videos/';
    $upload_dir_image = 'uploads/images/';

    if (!is_dir($upload_dir_video)) mkdir($upload_dir_video, 0777, true);
    if (!is_dir($upload_dir_image)) mkdir($upload_dir_image, 0777, true);

    if (empty($title) || empty($description) || empty($category)) {
        $error = "All course fields are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE title = :title AND teacher_id = :teacher_id");
            $stmt->execute(['title' => $title, 'teacher_id' => $_SESSION['user_id']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("You already have a course with this title.");
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO courses (title, description, category, teacher_id) VALUES (:title, :description, :category, :teacher_id)");
            $stmt->execute([
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'teacher_id' => $_SESSION['user_id']
            ]);
            $course_id = $pdo->lastInsertId();

            // Process videos
            for ($i = 0; $i < count($video_titles); $i++) {
                $v_title = htmlspecialchars(trim($video_titles[$i]));
                $v_link = htmlspecialchars(trim($video_links[$i] ?? ''));
                $v_file = $video_files['tmp_name'][$i] ?? '';

                if (!$v_title) continue;

                if ($v_link) {
                    $stmt = $pdo->prepare("INSERT INTO course_videos (course_id, video_title, video_path) VALUES (?, ?, ?)");
                    $stmt->execute([$course_id, $v_title, $v_link]);
                } elseif ($v_file) {
                    $type = mime_content_type($v_file);
                    $size = $video_files['size'][$i];
                    if (!in_array($type, $allowed_video_types)) throw new Exception("Invalid video type");
                    if ($size > $max_video_size) throw new Exception("Video too large");

                    $safe_name = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $video_files['name'][$i]);
                    $target_path = $upload_dir_video . $safe_name;

                    if (!move_uploaded_file($v_file, $target_path)) {
                        throw new Exception("Failed to upload video");
                    }

                    $stmt = $pdo->prepare("INSERT INTO course_videos (course_id, video_title, video_path) VALUES (?, ?, ?)");
                    $stmt->execute([$course_id, $v_title, $target_path]);
                }
            }

            // Process images
            foreach ($image_files['tmp_name'] as $i => $tmp_name) {
                if (!$tmp_name) continue;
                $type = mime_content_type($tmp_name);
                $size = $image_files['size'][$i];
                if (!in_array($type, $allowed_image_types)) throw new Exception("Invalid image type");
                if ($size > $max_image_size) throw new Exception("Image too large");

                $safe_name = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $image_files['name'][$i]);
                $target_path = $upload_dir_image . $safe_name;

                if (!move_uploaded_file($tmp_name, $target_path)) {
                    throw new Exception("Failed to upload image");
                }

                $stmt = $pdo->prepare("INSERT INTO course_images (course_id, image_path) VALUES (?, ?)");
                $stmt->execute([$course_id, $target_path]);
            }

            $pdo->commit();
            $success = "Course created successfully!";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Create Course</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        body {
            background: #111;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            padding: 2rem;
        }

        form {
            background: #1c1c1c;
            padding: 2rem;
            border-radius: 10px;
            max-width: 800px;
            margin: auto;
        }

        h1 {
            color: #facc15;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 1rem;
        }

        input[type="text"],
        input[type="url"],
        textarea,
        select {
            width: 97%;
            padding: 0.7rem;
            background: #2a2a2a;
            border: 1px solid #3a3a3a;
            color: #fff;
            border-radius: 5px;
        }

        .video-block,
        .image-block {
            background: #2e2e2e;
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 8px;
            position: relative;
        }

        .video-block button,
        .image-block button {
            position: absolute;
            top: 8px;
            right: 10px;
            background: crimson;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .add-button,
        button[type="submit"] {
            background: #facc15;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 1rem;
        }

        .toggle-section {
            margin-top: 0.5rem;
        }

        .error,
        .success {
            max-width: 800px;
            margin: 1rem auto;
            padding: 1rem;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
        }

        .error {
            background: #dc2626;
        }

        .success {
            background: #16a34a;
        }

        .back-link {
            display: inline-block;
            color: #facc15;
            background-color: #1f1f1f;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: 2px solid #facc15;
            transition: background 0.3s, color 0.3s;
        }

        .back-link:hover {
            background-color: #facc15;
            color: #111;
        }
    </style>
</head>

<body>

    <h1>Create New Course</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="title">Course Title:</label>
        <input type="text" name="title" id="title" required />

        <label for="description">Description:</label>
        <textarea name="description" id="description" rows="4" required></textarea>

        <label for="category">Category:</label>
        <input type="text" name="category" id="category" required />

        <h3>Course Videos</h3>
        <div id="video-container">
            <div class="video-block">
                <label>Video Title:</label>
                <input type="text" name="video_titles[]" required />

                <div class="toggle-section">
                    <label><input type="radio" name="video_type_0" value="link" checked onchange="toggleInput(this)"> YouTube Link</label>
                    <input type="url" name="video_links[]" placeholder="https://youtube.com/..." required />
                    <label><input type="radio" name="video_type_0" value="file" onchange="toggleInput(this)"> Upload File</label>
                    <input type="file" name="video_files[]" accept="video/*" style="display:none;" />
                </div>
                <button type="button" onclick="removeBlock(this)">Remove</button>
            </div>
        </div>
        <button type="button" class="add-button" onclick="addVideoBlock()">+ Add Video</button>

        <h3>Course Images</h3>
        <div id="image-container">
            <div class="image-block">
                <input type="file" name="course_images[]" accept="image/*" required />
                <button type="button" onclick="removeBlock(this)">Remove</button>
            </div>
        </div>
        <button type="button" class="add-button" onclick="addImageBlock()">+ Add Image</button>

        <button type="submit">Create Course</button>
    </form>
    <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>

    <script>
        let videoIndex = 1;

        function addVideoBlock() {
            const container = document.getElementById('video-container');
            const div = document.createElement('div');
            div.className = 'video-block';
            div.innerHTML = `
    <label>Video Title:</label>
    <input type="text" name="video_titles[]" required />
    <div class="toggle-section">
      <label><input type="radio" name="video_type_${videoIndex}" value="link" checked onchange="toggleInput(this)"> YouTube Link</label>
      <input type="url" name="video_links[]" placeholder="https://youtube.com/..." required />
      <label><input type="radio" name="video_type_${videoIndex}" value="file" onchange="toggleInput(this)"> Upload File</label>
      <input type="file" name="video_files[]" accept="video/*" style="display:none;" />
    </div>
    <button type="button" onclick="removeBlock(this)">Remove</button>
  `;
            container.appendChild(div);
            videoIndex++;
        }

        function toggleInput(radio) {
            const block = radio.closest('.video-block');
            const linkInput = block.querySelector('input[type="url"]');
            const fileInput = block.querySelector('input[type="file"]');

            if (radio.value === 'link') {
                linkInput.style.display = 'inline-block';
                fileInput.style.display = 'none';
                linkInput.required = true;
                fileInput.required = false;
            } else {
                fileInput.style.display = 'inline-block';
                linkInput.style.display = 'none';
                fileInput.required = true;
                linkInput.required = false;
            }
        }

        function addImageBlock() {
            const container = document.getElementById('image-container');
            const div = document.createElement('div');
            div.className = 'image-block';
            div.innerHTML = `
    <input type="file" name="course_images[]" accept="image/*" required />
    <button type="button" onclick="removeBlock(this)">Remove</button>
  `;
            container.appendChild(div);
        }

        function removeBlock(btn) {
            btn.parentElement.remove();
        }
    </script>
</body>

</html>