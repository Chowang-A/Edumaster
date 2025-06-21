<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html?error=Please log in first.");
    exit;
}

$userId = $_SESSION['user_id'];

// Handle delete/unsend
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    $action = $_POST['action'] ?? '';

    $stmtCheck = $pdo->prepare("SELECT sender_id, receiver_id, deleted_by FROM messages WHERE id = :id");
    $stmtCheck->execute(['id' => $deleteId]);
    $message = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($message) {
        $now = date("Y-m-d H:i:s");
        if ($action === 'delete') {
            if ($userId == $message['receiver_id']) {
                $deletedBy = ($message['deleted_by'] === 'sender') ? 'both' : 'receiver';
                $pdo->prepare("UPDATE messages SET visible_to_receiver = FALSE, deleted_by = :deletedBy, deleted_at = :now WHERE id = :id")
                    ->execute(['deletedBy' => $deletedBy, 'now' => $now, 'id' => $deleteId]);
            } elseif ($userId == $message['sender_id']) {
                $deletedBy = ($message['deleted_by'] === 'receiver') ? 'both' : 'sender';
                $pdo->prepare("UPDATE messages SET visible_to_sender = FALSE, deleted_by = :deletedBy, deleted_at = :now WHERE id = :id")
                    ->execute(['deletedBy' => $deletedBy, 'now' => $now, 'id' => $deleteId]);
            }
        } elseif ($action === 'unsend' && $userId == $message['sender_id']) {
            $pdo->prepare("DELETE FROM messages WHERE id = :id")->execute(['id' => $deleteId]);
        }
    }
}

// Fetch inbox
$stmtInbox = $pdo->prepare("
    SELECT m.*, u.name AS sender_name 
    FROM messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.receiver_id = :id AND m.visible_to_receiver = TRUE
    ORDER BY m.send_at DESC
");
$stmtInbox->execute(['id' => $userId]);
$inboxMessages = $stmtInbox->fetchAll(PDO::FETCH_ASSOC);

// Fetch sent
$stmtSent = $pdo->prepare("
    SELECT m.*, u.name AS receiver_name 
    FROM messages m 
    JOIN users u ON m.receiver_id = u.id 
    WHERE m.sender_id = :id AND m.visible_to_sender = TRUE
    ORDER BY m.send_at DESC
");
$stmtSent->execute(['id' => $userId]);
$sentMessages = $stmtSent->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EduMaster | Messaging</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0d1117;
      --surface: #161b22;
      --highlight: #facc15;
      --text: #f0f6fc;
      --muted: #8b949e;
      --danger: #ef4444;
      --accent: #38bdf8;
      --radius: 12px;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      background: var(--bg);
      color: var(--text);
      font-family: 'Inter', sans-serif;
      display: flex;
      min-height: 100vh;
    }

    aside {
      width: 280px;
      background: var(--surface);
      padding: 2rem 1.5rem;
      border-right: 1px solid #30363d;
    }

    main {
      flex: 1;
      padding: 2rem;
      overflow-y: auto;
    }

    h1 {
      font-size: 1.8rem;
      margin-bottom: 1.5rem;
      color: var(--highlight);
    }

    .btn {
      display: block;
      background: var(--highlight);
      color: #000;
      font-weight: bold;
      padding: 0.6rem 1rem;
      border-radius: var(--radius);
      text-align: center;
      text-decoration: none;
      margin-bottom: 1rem;
    }

    .tab-btn {
      background: transparent;
      border: none;
      color: var(--muted);
      font-weight: 600;
      padding: 0.75rem 1rem;
      border-radius: var(--radius);
      margin-bottom: 0.5rem;
      text-align: left;
      cursor: pointer;
    }

    .tab-btn.active, .tab-btn:hover {
      background: #21262d;
      color: var(--highlight);
    }

    .search-bar {
      margin-bottom: 1.5rem;
    }

    .search-bar input {
      width: 100%;
      padding: 0.75rem;
      border-radius: var(--radius);
      background: #0d1117;
      color: var(--text);
      border: 1px solid #30363d;
    }

    .message-list {
      display: grid;
      gap: 1rem;
    }

    .message-card {
      background: var(--surface);
      padding: 1rem 1.25rem;
      border-radius: var(--radius);
      border: 1px solid #30363d;
      position: relative;
    }

    .message-header {
      font-weight: 600;
      font-size: 1rem;
      margin-bottom: 0.25rem;
    }

    .message-subject {
      font-size: 0.95rem;
      color: var(--muted);
      margin-bottom: 0.5rem;
    }

    .message-body {
      font-size: 0.9rem;
      color: #c9d1d9;
    }

    .message-meta {
      font-size: 0.8rem;
      color: var(--muted);
      margin-top: 0.5rem;
    }

    .action-buttons {
      position: absolute;
      top: 1rem;
      right: 1rem;
    }

    .action-buttons button {
      background: transparent;
      border: none;
      color: var(--muted);
      font-size: 1rem;
      margin-left: 0.75rem;
      cursor: pointer;
    }

    .action-buttons button:hover {
      color: var(--danger);
    }

    @media (max-width: 768px) {
      aside { display: none; }
      main { padding: 1rem; }
    }
  </style>
</head>
<body>
<aside>
  <h1>📨 EduMaster</h1>
  <a href="dashboard.php" class="btn">🏠 Dashboard</a>
  <a href="send_message.php" class="btn">✉️ Compose</a>
  <a href="bin.php" class="btn" style="background: var(--danger); color: #fff;">🗑️ Bin</a>
  <hr style="margin: 1rem 0; border-color: #30363d;">
  <button class="tab-btn active" onclick="switchTab('inbox')">📥 Inbox</button>
  <button class="tab-btn" onclick="switchTab('sent')">📤 Sent</button>
</aside>

<main>
  <div class="search-bar">
    <input type="text" placeholder="Search messages..." oninput="filterMessages(this.value)">
  </div>

  <div class="message-list" id="messageList">
    <?php foreach ($inboxMessages as $msg): ?>
      <form method="POST" class="message-card" data-tab="inbox">
        <input type="hidden" name="delete_id" value="<?= $msg['id'] ?>">
        <input type="hidden" name="action" value="delete">
        <div class="message-header">From: <?= htmlspecialchars($msg['sender_name']) ?></div>
        <div class="message-subject">Subject: <?= htmlspecialchars($msg['subject']) ?></div>
        <div class="message-body"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
        <div class="message-meta">Sent: <?= date("d M Y, h:i A", strtotime($msg['send_at'])) ?></div>
        <div class="action-buttons">
          <button type="submit" onclick="return confirm('Delete this message?')">🗑️</button>
        </div>
      </form>
    <?php endforeach; ?>

    <?php foreach ($sentMessages as $msg): ?>
      <form method="POST" class="message-card" data-tab="sent" style="display: none;">
        <input type="hidden" name="delete_id" value="<?= $msg['id'] ?>">
        <input type="hidden" name="action" value="unsend">
        <div class="message-header">To: <?= htmlspecialchars($msg['receiver_name']) ?></div>
        <div class="message-subject">Subject: <?= htmlspecialchars($msg['subject']) ?></div>
        <div class="message-body"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
        <div class="message-meta">Sent: <?= date("d M Y, h:i A", strtotime($msg['send_at'])) ?></div>
        <div class="action-buttons">
          <button type="submit" onclick="return confirm('Unsend this message for everyone?')">↩️</button>
          <button type="submit" name="action" value="delete" onclick="return confirm('Delete this message?')">🗑️</button>
        </div>
      </form>
    <?php endforeach; ?>
  </div>
</main>

<script>
  function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`.tab-btn[onclick*="${tab}"]`).classList.add('active');

    document.querySelectorAll('.message-card').forEach(card => {
      const isSent = card.getAttribute('data-tab') === 'sent';
      card.style.display = (tab === 'sent' && isSent) || (tab === 'inbox' && !isSent) ? 'block' : 'none';
    });
  }

  function filterMessages(query) {
    query = query.toLowerCase();
    document.querySelectorAll('.message-card').forEach(card => {
      const text = card.innerText.toLowerCase();
      card.style.display = text.includes(query) ? 'block' : 'none';
    });
  }
</script>
</body>
</html>
