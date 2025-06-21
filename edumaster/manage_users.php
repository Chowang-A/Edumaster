<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.html?error=Access denied.");
    exit;
}

// Handle role change
if (isset($_POST['change_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
    $stmt->execute(['role' => $new_role, 'id' => $user_id]);
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
}

// Search filters
$name = $_GET['name'] ?? '';
$email = $_GET['email'] ?? '';
$role = $_GET['role'] ?? '';

// Build dynamic query
$query = "SELECT id, name, email, role FROM users WHERE 1=1";
$params = [];

if (!empty($name)) {
    $query .= " AND LOWER(name) LIKE :name";
    $params['name'] = '%' . strtolower($name) . '%';
}
if (!empty($email)) {
    $query .= " AND LOWER(email) LIKE :email";
    $params['email'] = '%' . strtolower($email) . '%';
}
if (!empty($role)) {
    $query .= " AND LOWER(role) = :role";
    $params['role'] = strtolower($role);
}

$query .= " ORDER BY id ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 2rem;
            background: linear-gradient(to right, #1a1a1a, #111);
            color: #f5f5f5;
        }

        h1 {
            font-size: 2rem;
            color: #f9c349;
            margin-bottom: 1.5rem;
        }

        a.button-back {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #f9c349;
            color: #111;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        a.button-back:hover {
            background: #ffd861;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-form input,
        .search-form select {
            padding: 10px;
            border-radius: 8px;
            border: none;
            background: #2a2a2a;
            color: #fff;
        }

        .search-form button {
            background: #f9c349;
            border: none;
            color: #111;
            padding: 10px 16px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #1e1e1e;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        th {
            background: #2b2b2b;
            font-weight: 600;
            color: #f9c349;
        }

        td {
            font-size: 0.95rem;
        }

        form.inline {
            display: inline;
        }

        .role-select {
            background: #333;
            color: #fff;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #555;
            font-size: 0.9rem;
        }

        button.action-btn {
            background: #f9c349;
            color: #111;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 6px;
        }

        button.action-btn:hover {
            background: #ffd861;
        }

        a.edit-link {
            color: #6ec1e4;
            font-weight: 600;
            text-decoration: none;
        }

        a.edit-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }

            table, thead, tbody, th, td, tr {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                margin-bottom: 1rem;
                border: 1px solid #333;
                border-radius: 8px;
                padding: 10px;
                background: #2a2a2a;
            }

            td {
                padding: 6px 10px;
                position: relative;
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;
                color: #f9c349;
                position: absolute;
                left: 10px;
                top: 6px;
            }
        }
    </style>
</head>
<body>

<h1>👥 Manage Users</h1>
<a href="dashboard.php" class="button-back">← Back to Dashboard</a>

<form method="GET" class="search-form">
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Search by Name">
    <input type="text" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Search by Email">
    <select name="role">
        <option value="">All Roles</option>
        <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
        <option value="teacher" <?= $role === 'teacher' ? 'selected' : '' ?>>Teacher</option>
        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>
    <button type="submit">Search</button>
</form>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Change Role</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($users) === 0): ?>
            <tr><td colspan="6">No users found.</td></tr>
        <?php endif; ?>
        <?php foreach ($users as $user): ?>
        <tr>
            <td data-label="Name"><?= htmlspecialchars($user['name']) ?></td>
            <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
            <td data-label="Role"><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
            <td data-label="Change Role">
                <form method="POST" class="inline">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <select name="role" class="role-select">
                        <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                        <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <button type="submit" name="change_role" class="action-btn">Update</button>
                </form>
            </td>
            <td data-label="Edit"><a href="profile_settings.php?id=<?= $user['id'] ?>" class="edit-link">Edit</a></td>
            <td data-label="Delete">
                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <button type="submit" name="delete_user" class="action-btn">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
