<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About Us | EduMaster</title>
  <link href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" rel="stylesheet">
  <style>
    :root {
      --bg: #f5f5f5;
      --text: #111;
      --accent: #f9c349;
      --card-bg: #fff;
      --muted: #666;
    }
    body.dark {
      --bg: #0f0f0f;
      --text: #fff;
      --accent: #f9c349;
      --card-bg: #1e1e1e;
      --muted: #aaa;
    }

    body {
      margin: 0;
      font-family: "Segoe UI", sans-serif;
      background-color: var(--bg);
      color: var(--text);
      transition: 0.3s;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      background: var(--card-bg);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    header h1 {
      margin: 0;
      font-size: 1.8rem;
      color: var(--accent);
    }

    #theme-toggle {
      background: none;
      border: none;
      font-size: 1.5rem;
      color: var(--text);
      cursor: pointer;
    }

    .container {
      max-width: 1200px;
      margin: auto;
      padding: 2rem;
    }

    .intro {
      text-align: center;
      margin-bottom: 2rem;
    }

    .intro h2 {
      font-size: 2.2rem;
      margin-bottom: 0.5rem;
    }

    .intro p {
      max-width: 700px;
      margin: 0 auto;
      color: var(--muted);
    }

    .section-title {
      font-size: 1.8rem;
      margin-top: 3rem;
      margin-bottom: 1rem;
      text-align: center;
      color: var(--accent);
    }

    .team {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-top: 1rem;
    }

    .card {
      background: var(--card-bg);
      border-radius: 10px;
      padding: 1.5rem;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .card img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 1rem;
      border: 3px solid var(--accent);
    }

    .card h3 {
      margin: 0.5rem 0 0.2rem;
    }

    .card p {
      margin: 0;
      font-size: 0.9rem;
      color: var(--muted);
    }

    footer {
      text-align: center;
      padding: 2rem;
      font-size: 0.9rem;
      color: var(--muted);
    }

    a {
      color: var(--accent);
      text-decoration: none;
    }
  </style>
</head>
<body>

<header>
  <h1>EduMaster</h1>
  <button id="theme-toggle" title="Toggle Light/Dark Mode">
    <i class="uil uil-moon" id="theme-icon"></i>
  </button>
</header>

<div class="container">
  <div class="intro">
    <h2>About Us</h2>
    <p>
      At <strong>EduMaster</strong>, our mission is to make quality education accessible and engaging for learners of all backgrounds. 
      We provide a dynamic platform where students can enroll in expert-led courses, take interactive quizzes, and track their progress in real time.
    </p>
  </div>

  <h2 class="section-title">Our Mission</h2>
  <p class="intro">
    We believe learning should be flexible, fun, and personalized. EduMaster brings together passionate educators and eager learners 
    to create an ecosystem that promotes lifelong learning.
  </p>

  <h2 class="section-title">Meet the Team</h2>
  <div class="team">
    <div class="card">
      <img src="p1.jpg" alt="Teammate1">
      <h3>Surajeet mandal</h3>
      <p>Documentation</p>
    </div>
    <div class="card">
      <img src="p2.jpg" alt="teammate2">
      <h3>Rahul Gupta </h3>
      <p>Frontend</p>
    </div>
    <div class="card">
      <img src="p3.jpg" alt="Teammate">
      <h3>Chowang sherpa</h3>
      <p>Backend</p>
    </div>
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> EduMaster. All rights reserved. | <a href="index.html">Home</a>
</footer>

<script>
  const toggleBtn = document.getElementById('theme-toggle');
  const icon = document.getElementById('theme-icon');
  const body = document.body;

  toggleBtn.addEventListener('click', () => {
    body.classList.toggle('dark');
    localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
    icon.className = body.classList.contains('dark') ? 'uil uil-sun' : 'uil uil-moon';
  });

  // Load saved theme
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') {
    body.classList.add('dark');
    icon.className = 'uil uil-sun';
  }
</script>

</body>
</html>

