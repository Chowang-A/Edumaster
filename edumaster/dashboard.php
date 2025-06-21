<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.html?error=login_required");
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

if ($role === 'student') {
    // Enrolled courses
    $stmt = $pdo->prepare("SELECT c.id, c.title, c.category, u.name AS teacher, u.id AS teacher_id
                           FROM courses c
                           JOIN enrollments e ON c.id = e.course_id
                           JOIN users u ON c.teacher_id = u.id
                           WHERE e.user_id = ?");
    $stmt->execute([$userId]);
    $enrolledCourses = $stmt->fetchAll();

    // Attempted quizzes
    $quizStmt = $pdo->prepare("SELECT qa.score, qa.attempted_at, q.id AS quiz_id, q.title, q.topic, q.subject
                               FROM quiz_attempts qa
                               JOIN quizzes q ON qa.quiz_id = q.id
                               WHERE qa.user_id = ?
                               ORDER BY qa.attempted_at DESC");
    $quizStmt->execute([$userId]);
    $attemptedQuizzes = $quizStmt->fetchAll();
}

if ($role === 'teacher') {
    $stmt = $pdo->prepare("SELECT id, title, category FROM courses WHERE teacher_id = ?");
    $stmt->execute([$userId]);
    $teacherCourses = $stmt->fetchAll();
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" rel="stylesheet">

    <style>
        :root {
            --main-bg: #0f0f0f;
            --card-bg: #1c1c1c;
            --highlight: #f9c349;
            --text: #ffffff;
            --muted: #cccccc;
            --danger: #dc3545;
            --danger-hover: #b52a3c;
        }

        /* Light Theme Variables */
        body.light-mode {
            --main-bg: #f2f2f2;
            --card-bg: #ffffff;
            --highlight: #ffc107;
            --text: #000000;
            --muted: #666666;
            --danger: #dc3545;
            --danger-hover: #c82333;
        }

        #theme-toggle {
            background: none;
            border: none;
            font-size: 22px;
            color: var(--highlight);
            cursor: pointer;
        }

        #theme-toggle:hover {
            opacity: 0.8;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--main-bg);
            color: var(--text);
            margin: 0;
            padding: 0;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #1e293b;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            transform: translateX(0);
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
        }

        .sidebar.show {
            transform: translateX(-100%);
        }

        .menu-toggle {
            font-size: 24px;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            margin-right: 20px;
            background-color: #444;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .dashboard .main-content {
            margin-left: 250px;
            transition: margin-left 0.3s ease-in-out;
        }

        .sidebar.show+.main-content {
            margin-left: 0;
        }


        /* Responsive fix */
        @media (max-width: 768px) {
            .dashboard .main-content {
                margin-left: 0 !important;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }
        }


        .sidebar .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--highlight);
            margin-bottom: 2rem;
            margin-top: 2rem;
            text-align: center;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin: 1rem 0;
        }

        .sidebar ul li a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            display: block;
            padding: 10px 15px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #222;
            color: var(--highlight);
        }

        .main-content {
            flex-grow: 1;
            margin-left: 250px;
            background: var(--main-bg);
            transition: margin 0.3s ease;
        }

        .dashboard-header {
            background: #1e293b;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #333;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .menu-toggle {
            display: none;
            background: none;
            color: var(--highlight);
            font-size: 24px;
            border: none;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }
        }



        :root {
            --main-bg: #0f0f0f;
            --card-bg: #1c1c1c;
            --highlight: #f9c349;
            --text: #ffffff;
            --muted: #cccccc;
            --danger: #dc3545;
            --danger-hover: #b52a3c;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--main-bg);
            color: var(--text);
            margin: 0;
            padding: 0;
        }

        nav {
            position: sticky;
            top: 0;
            background: #121212;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--highlight);
            z-index: 1000;
        }

        nav a {
            color: var(--highlight);
            text-decoration: none;
            margin: 0 1rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        nav a:hover {
            color: #fff;
        }

        .container {
            padding: 2rem;
            max-width: 1200px;
            margin: auto;
        }

        h1,
        h2 {
            color: var(--highlight);
            margin-bottom: 0.5rem;
        }

        .search-bar {
            margin: 1.5rem 0;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-bar input,
        .search-bar select {
            padding: 0.6rem 1rem;
            font-size: 15px;
            border: none;
            border-radius: 6px;
            background: #2b2b2b;
            color: var(--text);
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 1rem;
        }

        .course-card {
            background: var(--card-bg);
            border: 1px solid var(--highlight);
            border-radius: 12px;
            padding: 1.2rem;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 12px var(--highlight);
        }

        .carousel-container {
            position: relative;
            margin-bottom: 1rem;
        }

        .carousel {
            display: flex;
            overflow: hidden;
        }

        .carousel img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .carousel-buttons {
            position: absolute;
            top: 40%;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }

        .carousel-buttons button {
            background: rgba(0, 0, 0, 0.6);
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            padding: 5px 12px;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        .carousel-buttons button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .view-btn,
        .edit-btn {
            background: var(--highlight);
            color: #000;
            padding: 8px 14px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            margin-top: 10px;
            margin-right: 8px;
        }

        .leave-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .leave-btn:hover {
            background-color: var(--danger-hover);
        }

        table {
            width: 100%;
            margin-top: 1.5rem;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        th {
            background: #2c2c2c;
            color: var(--highlight);
            font-weight: bold;
        }

        tr:hover {
            background: #222;
        }
    </style>

</head>

<body>
    <div class="dashboard">
        <aside class="sidebar hide" id="sidebar">
            <div class="logo">EduMaster</div>
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="courses.php">All Courses</a></li>
                <li><a href="quizzes.php">All Quizzes</a></li>
                <li><a href="inbox.php">Inbox</a></li>
                <li><a href="send_message.php">Send Message</a></li>
                <?php if ($role === 'student'): ?>
                    <li><a href="#attempted-quizzes">Quiz Scores</a></li>
                <?php endif; ?>
                <?php if ($role === 'teacher'): ?>
                    <li><a href="create_course.php">Create Course</a></li>
                    <li><a href="create_quiz.php">Create Quiz</a></li>
                    <li><a href="edit_quiz.php">Edit Quiz</a></li>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                    <li><a href="manage_users.php">Manage Users</a></li>
                    <li><a href="manage_courses.php">Manage Courses</a></li>
                    <li><a href="edit_quiz.php">Manage Quizzes</a></li>
                <?php endif; ?>
                <li><a href="profile_settings.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>

        <div class="main-content">
            <header class="dashboard-header">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= ucfirst($_SESSION['user_role']) ?>)</h1>

                <!-- Theme toggle button -->
                <button id="theme-toggle" title="Toggle Light/Dark Mode">
                    <i class="uil uil-moon" id="theme-icon"></i>
                </button>
            </header>

            <div class="container">
                <!-- Your existing PHP role-based content here -->

                <?php if ($role === 'student'): ?>
                    <h2>Your Enrolled Courses</h2>
                    <div class="search-bar">
                        <input type="text" id="searchTitle" placeholder="Search by Title">
                        <input type="text" id="searchTeacher" placeholder="Search by Teacher">
                        <select id="searchCategory">
                            <option value="">All Categories</option>
                            <?php
                            $catStmt = $pdo->query("SELECT DISTINCT category FROM courses");
                            foreach ($catStmt->fetchAll() as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>"><?= htmlspecialchars($cat['category']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (count($enrolledCourses) > 0): ?>
                        <div class="course-grid" id="courseGrid">
                            <?php foreach ($enrolledCourses as $course): ?>
                                <div class="course-card"
                                    data-title="<?= strtolower($course['title']) ?>"
                                    data-category="<?= strtolower($course['category']) ?>"
                                    data-teacher="<?= strtolower($course['teacher']) ?>">
                                    <div class="carousel-container">
                                        <div class="carousel" id="carousel-<?= $course['id'] ?>">
                                            <?php
                                            $imgStmt = $pdo->prepare("SELECT image_path FROM course_images WHERE course_id = ?");
                                            $imgStmt->execute([$course['id']]);
                                            $images = $imgStmt->fetchAll();
                                            if ($images):
                                                foreach ($images as $img): ?>
                                                    <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="Course Image">
                                                <?php endforeach;
                                            else: ?>
                                                <img src="default_course.jpg" alt="Default Course Image">
                                            <?php endif; ?>
                                        </div>
                                        <?php if (count($images) > 1): ?>
                                            <div class="carousel-buttons">
                                                <button onclick="slideLeft('carousel-<?= $course['id'] ?>')">&#10094;</button>
                                                <button onclick="slideRight('carousel-<?= $course['id'] ?>')">&#10095;</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <h3><?= htmlspecialchars($course['title']) ?></h3>
                                    <p><strong>Teacher:</strong> <?= htmlspecialchars($course['teacher']) ?></p>
                                    <p><strong>Category:</strong> <?= htmlspecialchars($course['category']) ?></p>
                                    <a class="view-btn" href="course_videos.php?id=<?= $course['id'] ?>">View Course</a>
                                    <form method="POST" action="leave_course.php" onsubmit="return confirm('Are you sure you want to leave this course?');">
                                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                        <input type="hidden" name="teacher_id" value="<?= $course['teacher_id'] ?>">
                                        <button type="submit" class="leave-btn">Leave Course</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>You have not enrolled in any courses yet.</p>
                    <?php endif; ?>

                    <h2 id="attempted-quizzes">Your Attempted Quizzes</h2>
                    <?php if (count($attemptedQuizzes) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Topic</th>
                                    <th>Subject</th>
                                    <th>Score</th>
                                    <th>Attempted On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attemptedQuizzes as $quiz): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($quiz['title']) ?></td>
                                        <td><?= htmlspecialchars($quiz['topic']) ?></td>
                                        <td><?= htmlspecialchars($quiz['subject']) ?></td>
                                        <td><?= htmlspecialchars($quiz['score']) ?></td>
                                        <td><?= date('d M Y, h:i A', strtotime($quiz['attempted_at'])) ?></td>
                                        <td><a class="view-btn" href="view_quiz.php?quiz_id=<?= $quiz['quiz_id'] ?>">Retake</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>You haven't attempted any quizzes yet.</p>
                    <?php endif; ?>

                <?php elseif ($role === 'teacher'): ?>
                    <h2>Your Uploaded Courses</h2>
                    <div class="search-bar">
                        <input type="text" id="teacherSearchTitle" placeholder="Search by Title">
                        <select id="teacherSearchCategory">
                            <option value="">All Categories</option>
                            <?php
                            $catStmt = $pdo->prepare("SELECT DISTINCT category FROM courses WHERE teacher_id = ?");
                            $catStmt->execute([$userId]);
                            foreach ($catStmt->fetchAll() as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>"><?= htmlspecialchars($cat['category']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (count($teacherCourses) > 0): ?>
                        <div class="course-grid" id="teacherCourseGrid">
                            <?php foreach ($teacherCourses as $course): ?>
                                <div class="course-card"
                                    data-title="<?= strtolower($course['title']) ?>"
                                    data-category="<?= strtolower($course['category']) ?>">
                                    <div class="carousel-container">
                                        <div class="carousel" id="carousel-<?= $course['id'] ?>">
                                            <?php
                                            $imgStmt = $pdo->prepare("SELECT image_path FROM course_images WHERE course_id = ?");
                                            $imgStmt->execute([$course['id']]);
                                            $images = $imgStmt->fetchAll();
                                            if ($images):
                                                foreach ($images as $img): ?>
                                                    <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="Course Image">
                                                <?php endforeach;
                                            else: ?>
                                                <img src="default_course.jpg" alt="Default Course Image">
                                            <?php endif; ?>
                                        </div>
                                        <?php if (count($images) > 1): ?>
                                            <div class="carousel-buttons">
                                                <button onclick="slideLeft('carousel-<?= $course['id'] ?>')">&#10094;</button>
                                                <button onclick="slideRight('carousel-<?= $course['id'] ?>')">&#10095;</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <h3><?= htmlspecialchars($course['title']) ?></h3>
                                    <p><strong>Category:</strong> <?= htmlspecialchars($course['category']) ?></p>
                                    <?php
                                    $enrollStmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
                                    $enrollStmt->execute([$course['id']]);
                                    $count = $enrollStmt->fetchColumn();
                                    ?>
                                    <p><strong>Enrolled Students:</strong> <?= $count ?></p>
                                    <a class="view-btn" href="view_students.php?course_id=<?= $course['id'] ?>" target="_blank">View Students</a>
                                    <a class="edit-btn" href="alter_courses.php?edit_course=<?= $course['id'] ?>">Edit Course</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>You haven't uploaded any courses yet.</p>
                    <?php endif; ?>

                <?php elseif ($role === 'admin'): ?>
                    <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= ucfirst($_SESSION['user_role']) ?>)</h1>

                    <p>Use the navigation bar to manage users and courses.</p>
                <?php endif; ?>
            </div> <!-- container -->
        </div> <!-- main-content -->
    </div> <!-- dashboard -->

    <script>
        function slideLeft(id) {
            const carousel = document.getElementById(id);
            carousel.scrollBy({
                left: -carousel.offsetWidth,
                behavior: 'smooth'
            });
        }

        function slideRight(id) {
            const carousel = document.getElementById(id);
            carousel.scrollBy({
                left: carousel.offsetWidth,
                behavior: 'smooth'
            });
        }

        <?php if ($role === 'student'): ?>
            const titleInput = document.getElementById('searchTitle');
            const teacherInput = document.getElementById('searchTeacher');
            const categorySelect = document.getElementById('searchCategory');
            const courseCards = document.querySelectorAll('.course-card');

            function filterCourses() {
                const titleVal = titleInput.value.toLowerCase();
                const teacherVal = teacherInput.value.toLowerCase();
                const catVal = categorySelect.value.toLowerCase();

                courseCards.forEach(card => {
                    const cardTitle = card.dataset.title;
                    const cardCat = card.dataset.category;
                    const cardTeacher = card.dataset.teacher;

                    const matchTitle = !titleVal || cardTitle.includes(titleVal);
                    const matchTeacher = !teacherVal || cardTeacher.includes(teacherVal);
                    const matchCat = !catVal || cardCat === catVal;

                    card.style.display = matchTitle && matchTeacher && matchCat ? 'block' : 'none';
                });
            }
            titleInput.addEventListener('input', filterCourses);
            teacherInput.addEventListener('input', filterCourses);
            categorySelect.addEventListener('change', filterCourses);
        <?php endif; ?>

        <?php if ($role === 'teacher'): ?>
            const teacherTitleInput = document.getElementById('teacherSearchTitle');
            const teacherCategorySelect = document.getElementById('teacherSearchCategory');
            const teacherCards = document.querySelectorAll('#teacherCourseGrid .course-card');

            function filterTeacherCourses() {
                const titleVal = teacherTitleInput.value.toLowerCase();
                const catVal = teacherCategorySelect.value.toLowerCase();

                teacherCards.forEach(card => {
                    const cardTitle = card.dataset.title;
                    const cardCat = card.dataset.category;

                    const matchTitle = !titleVal || cardTitle.includes(titleVal);
                    const matchCat = !catVal || cardCat === catVal;

                    card.style.display = matchTitle && matchCat ? 'block' : 'none';
                });
            }
            teacherTitleInput.addEventListener('input', filterTeacherCourses);
            teacherCategorySelect.addEventListener('change', filterTeacherCourses);
        <?php endif; ?>

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hide');
        }

        // Set theme on page load
        window.addEventListener('DOMContentLoaded', () => {
            const theme = localStorage.getItem('theme') || 'dark';
            document.body.classList.toggle('light-mode', theme === 'light');
            updateThemeIcon(theme);
        });

        function updateThemeIcon(theme) {
            const icon = document.getElementById('theme-icon');
            icon.className = theme === 'light' ? 'uil uil-sun' : 'uil uil-moon';
        }

        // Toggle theme on click
        document.getElementById('theme-toggle').addEventListener('click', () => {
            const isLight = document.body.classList.toggle('light-mode');
            const newTheme = isLight ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    </script>

</body>

</html>