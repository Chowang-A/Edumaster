<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html?error=Please log in first.");
    exit;
}

$userId = $_SESSION['user_id'];
$now = date("Y-m-d H:i:s");

// Auto-delete messages older than 7 days in bin
$pdo->prepare("
    DELETE FROM messages 
    WHERE deleted_at IS NOT NULL 
      AND deleted_at <= NOW() - INTERVAL '7 days'
      AND (
        (deleted_by = 'sender' AND sender_id = :uid) OR 
        (deleted_by = 'receiver' AND receiver_id = :uid) OR 
        (deleted_by = 'both')
      )
")->execute(['uid' => $userId]);

// Handle form submission for restore or delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message_ids']) && is_array($_POST['message_ids'])) {
        $ids = array_map('intval', $_POST['message_ids']);
        $inQuery = implode(',', array_fill(0, count($ids), '?'));
        $action = $_POST['bulk_action'] ?? '';

        if ($action === 'restore') {
            $stmt = $pdo->prepare("SELECT * FROM messages WHERE id IN ($inQuery)");
            $stmt->execute($ids);
            while ($msg = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($msg['sender_id'] == $userId && $msg['deleted_by'] === 'sender') {
                    $pdo->prepare("UPDATE messages SET deleted_by = NULL, deleted_at = NULL, visible_to_sender = TRUE WHERE id = ?")
                        ->execute([$msg['id']]);
                } elseif ($msg['receiver_id'] == $userId && $msg['deleted_by'] === 'receiver') {
                    $pdo->prepare("UPDATE messages SET deleted_by = NULL, deleted_at = NULL, visible_to_receiver = TRUE WHERE id = ?")
                        ->execute([$msg['id']]);
                } elseif ($msg['deleted_by'] === 'both') {
                    $pdo->prepare("UPDATE messages SET deleted_by = CASE WHEN sender_id = ? THEN 'receiver' ELSE 'sender' END, deleted_at = ? WHERE id = ?")
                        ->execute([$userId, $now, $msg['id']]);
                }
            }
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id IN ($inQuery)");
            $stmt->execute($ids);
        }
    }
}

// Fetch messages in the bin
$stmtBin = $pdo->prepare("
    SELECT m.*, 
           u1.name AS sender_name, 
           u2.name AS receiver_name
    FROM messages m
    JOIN users u1 ON m.sender_id = u1.id
    JOIN users u2 ON m.receiver_id = u2.id
    WHERE ((m.sender_id = :uid AND m.deleted_by IN ('sender', 'both')) OR
           (m.receiver_id = :uid AND m.deleted_by IN ('receiver', 'both')))
      AND m.deleted_at >= NOW() - INTERVAL '7 days'
    ORDER BY m.deleted_at DESC
");
$stmtBin->execute(['uid' => $userId]);
$deletedMessages = $stmtBin->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Deleted Messages | EduMaster</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background: #0f172a;
      color: #f1f5f9;
      font-family: 'Inter', sans-serif;
      padding: 2rem;
      margin: 0;
    }
    h1 {
      color: #facc15;
      margin-bottom: 1rem;
    }
    .message-card {
      background: #1e293b;
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 1rem;
      border: 1px solid #334155;
    }
    .message-card:hover {
      border-color: #facc15;
    }
    .message-details {
      font-size: 0.9rem;
      color: #cbd5e1;
      margin-top: 0.5rem;
    }
    .form-controls {
      margin-bottom: 1rem;
    }
    select, button {
      padding: 0.5rem 1rem;
      border-radius: 6px;
      border: none;
      font-size: 1rem;
    }
    select {
      background: #1e293b;
      color: #f1f5f9;
      border: 1px solid #334155;
    }
    button {
      background: #facc15;
      color: #000;
      margin-left: 0.5rem;
      cursor: pointer;
    }
    button:hover {
      background: #eab308;
    }
    .checkbox {
      transform: scale(1.3);
      margin-right: 1rem;
      vertical-align: middle;
    }
    .message-meta {
      font-size: 0.8rem;
      color: #94a3b8;
      margin-top: 0.25rem;
    }
    a.back-btn {
      display: inline-block;
      background: #22d3ee;
      color: #000;
      padding: 0.5rem 1rem;
      text-decoration: none;
      border-radius: 6px;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <h1>🗑️ Deleted Messages Bin</h1>
  <a class="back-btn" href="inbox.php">← Back to Inbox</a>

  <?php if ($deletedMessages): ?>
    <form method="POST">
      <div class="form-controls">
        <label for="bulk_action">Bulk action:</label>
        <select name="bulk_action" id="bulk_action" required>
          <option value="">Select</option>
          <option value="restore">Restore Selected</option>
          <option value="delete">Delete Permanently</option>
        </select>
        <button type="submit">Apply</button>
      </div>

      <?php foreach ($deletedMessages as $msg): ?>
        <div class="message-card">
          <label>
            <input type="checkbox" class="checkbox" name="message_ids[]" value="<?= $msg['id'] ?>">
            <strong>From:</strong> <?= htmlspecialchars($msg['sender_name']) ?> |
            <strong>To:</strong> <?= htmlspecialchars($msg['receiver_name']) ?><br>
            <strong>Subject:</strong> <?= htmlspecialchars($msg['subject']) ?>
          </label>
          <div class="message-details"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
          <div class="message-meta">Deleted at: <?= date("d M Y, h:i A", strtotime($msg['deleted_at'])) ?></div>
        </div>
      <?php endforeach; ?>
    </form>
  <?php else: ?>
    <p>No messages in bin.</p>
  <?php endif; ?>

  <script>
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(box => {
      box.addEventListener('change', () => {
        const selected = document.querySelectorAll('input[type="checkbox"]:checked').length;
        if (selected > 30) {
          box.checked = false;
          alert("You can select up to 30 messages at once.");
        }
      });
    });
  </script>
</body>
</html>
