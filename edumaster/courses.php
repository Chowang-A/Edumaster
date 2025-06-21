<?php 
session_start();
require 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);

$stmt = $pdo->query("
    SELECT c.id, c.title, c.description, c.category, u.name AS teacher_name
    FROM courses c
    JOIN users u ON c.teacher_id = u.id
    ORDER BY c.id DESC
");
$courses = $stmt->fetchAll();

$imageStmt = $pdo->prepare("SELECT image_path FROM course_images WHERE course_id = ?");
$courseImages = [];
foreach ($courses as $c) {
    $imageStmt->execute([$c['id']]);
    $courseImages[$c['id']] = $imageStmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EduMaster • Courses</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <style>
    :root {
      --bg: #121212;
      --card: #1F1F1F;
      --accent: #FACC15;
      --text: #EDEDED;
      --muted: #888;
      --hover: #2A2A2A;
      --btn-text: #FFFFFF;
    }
    * { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', sans-serif; }
    body {
      background: var(--bg);
      color: var(--text);
      padding: 1rem;
    }
    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 0;
    }
    .top-bar h1 {
      color: var(--accent);
    }
    .top-bar a {
      background: transparent;
      border: 2px solid var(--accent);
      color: var(--accent);
      padding: .5rem 1rem;
      border-radius: 25px;
      text-decoration: none;
      font-weight: bold;
      transition: background .3s, color .3s;
    }
    .top-bar a:hover {
      background: var(--accent);
      color: var(--bg);
    }

    .filters {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px,1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .filters input, .filters select {
      background: var(--card);
      border: none;
      padding: .75rem 1rem;
      border-radius: 8px;
      color: var(--text);
      font-size: .95rem;
      outline: none;
      transition: background .3s;
    }
    .filters input::placeholder, .filters select {
      color: var(--muted);
    }
    .filters input:focus, .filters select:focus {
      background: var(--hover);
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px,1fr));
      gap: 1.5rem;
    }
    .card {
      background: var(--card);
      border-radius: 12px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: 0 4px 12px rgba(0,0,0,0.4);
      transition: transform .3s;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .carousel {
      position: relative;
      height: 160px;
      overflow: hidden;
    }
    .carousel img {
      position: absolute;
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0;
      transition: opacity 1s ease-in-out;
    }
    .carousel img.active {
      opacity: 1;
    }
    .info {
      padding: 1rem;
      flex:1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .info h2 {
      font-size: 1.2rem;
      margin-bottom: .5rem;
      color: var(--accent);
    }
    .info p {
      font-size: .95rem;
      margin-bottom: .75rem;
      line-height: 1.4;
      color: var(--text);
      flex:1;
    }
    .info .meta {
      font-size: .85rem;
      color: var(--muted);
      margin-bottom: .75rem;
    }
    .btn-join {
      display: block;
      text-align: center;
      background: var(--accent);
      color: var(--bg);
      font-weight: bold;
      border: none;
      padding: .8rem 0;
      border-radius: 30px;
      transition: transform .3s, box-shadow .3s;
      text-decoration: none;
      margin-top: .5rem;
    }
    .btn-join:hover {
      transform: scale(1.02);
      box-shadow: 0 0 10px rgba(250,204,21,0.6);
    }

    .popup {
      position: fixed;
      top: 1rem;
      right: 1rem;
      background: #E63757;
      color: #fff;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      display: none;
      box-shadow: 0 4px 12px rgba(0,0,0,0.5);
      z-index: 1000;
      max-width: 240px;
    }
  </style>
</head>
<body>

  <div class="top-bar">
    <h1>All Available Courses</h1>
    <?php if ($isLoggedIn): ?>
      <a href="dashboard.php">Back to Dashboard</a>
    <?php else: ?>
      <div style="display:flex;gap:1rem;">
        <a href="login.html">Login</a>
        <a href="index.html">Home</a>
      </div>
    <?php endif; ?>
  </div>

  <div class="filters">
    <input type="text" id="searchTitle" placeholder="Search by title…" oninput="filterCourses()">
    <input type="text" id="searchTeacher" placeholder="Search by instructor…" oninput="filterCourses()">
    <select id="filterCategory" onchange="filterCourses()">
      <option value="">All categories</option>
      <?php
      $cats = $pdo->query("SELECT DISTINCT category FROM courses")->fetchAll();
      foreach($cats as $cat) {
        $c = htmlspecialchars($cat['category']);
        echo "<option value=\"".strtolower($c)."\">{$c}</option>";
      }
      ?>
    </select>
  </div>

  <div class="grid" id="courseGrid">
    <?php foreach ($courses as $c): ?>
      <div class="card" data-title="<?= strtolower($c['title']) ?>"
                   data-teacher="<?= strtolower($c['teacher_name']) ?>"
                   data-category="<?= strtolower($c['category']); ?>">
        <div class="carousel">
          <?php foreach ($courseImages[$c['id']] as $i => $img): ?>
            <img src="<?= htmlspecialchars($img) ?>" class="<?= $i===0?'active':'' ?>">
          <?php endforeach; ?>
          <?php if (empty($courseImages[$c['id']])): // placeholder ?>
            <img src="assets/placeholder.jpg" class="active">
          <?php endif; ?>
        </div>
        <div class="info">
          <h2><?= htmlspecialchars($c['title']) ?></h2>
          <p><?= nl2br(htmlspecialchars(substr($c['description'],0,200))) ?>…</p>
          <div class="meta">
            <span><?= htmlspecialchars($c['category']) ?></span> • 
            <span><?= htmlspecialchars($c['teacher_name']) ?></span>
          </div>
          <?php if ($isLoggedIn): ?>
            <a href="course_videos.php?id=<?= $c['id'] ?>" class="btn-join">Join Course</a>
          <?php else: ?>
            <button class="btn-join" onclick="showPopup('Please log in to enroll')">Join Course</button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div id="popup" class="popup"></div>

  <script>
    const popup = document.getElementById('popup');

    function filterCourses(){
      const t = document.getElementById('searchTitle').value.toLowerCase(),
            instr = document.getElementById('searchTeacher').value.toLowerCase(),
            cat = document.getElementById('filterCategory').value,
            cards = document.querySelectorAll('.card');

      cards.forEach(c=>{
        const match = c.dataset.title.includes(t)
                   && c.dataset.teacher.includes(instr)
                   && (!cat || c.dataset.category===cat);
        c.style.display = match ? 'flex' : 'none';
      });
    }

    function showPopup(msg){
      popup.textContent = msg;
      popup.style.display = 'block';
      setTimeout(()=> popup.style.display = 'none', 3000);
    }

    document.querySelectorAll('.carousel').forEach(car=>{
      const imgs = Array.from(car.querySelectorAll('img'));
      if (imgs.length > 1) {
        let idx = 0;
        setInterval(()=>{
          imgs[idx].classList.remove('active');
          idx = (idx +1) % imgs.length;
          imgs[idx].classList.add('active');
        },3000);
      }
    });
  </script>

</body>
</html>
