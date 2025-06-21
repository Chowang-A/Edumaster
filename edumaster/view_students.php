<?php
session_start();
require 'db.php';

// Access control: Only teacher or admin can access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    die("Access denied. Only teachers or admins can view this page.");
}

$courseId = $_GET['course_id'] ?? 0;

if (!$courseId || !is_numeric($courseId)) {
    die("Invalid course ID.");
}

// Fetch enrolled students for the course
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.email 
    FROM enrollments e 
    JOIN users u ON e.user_id = u.id 
    WHERE e.course_id = ?
");
$stmt->execute([$courseId]);
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrolled Students</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #111;
            color: #fff;
            padding: 2rem;
        }
        h1 {
            color: #f9c349;
        }
        .search-box {
            margin-bottom: 15px;
        }
        input[type="text"] {
            padding: 8px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #1f1f1f;
        }
        th, td {
            padding: 12px;
            border: 1px solid #f9c349;
            text-align: left;
        }
        th {
            background-color: #333;
            color: #f9c349;
        }
        tr:nth-child(even) {
            background-color: #2a2a2a;
        }
        .back-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #f9c349;
            border: none;
            cursor: pointer;
            font-weight: bold;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Enrolled Students</h1>

    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search by name or student ID...">
    </div>

    <?php if (count($students) > 0): ?>
        <table id="studentsTable">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['id']) ?></td>
                        <td><?= htmlspecialchars($student['name']) ?></td>
                        <td><?= htmlspecialchars($student['email']) ?></td>
                        <td><button class="remove-btn" onclick="confirmRemove(<?= $student['id'] ?>, '<?= addslashes($student['name']) ?>')">Remove</button></td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No students enrolled in this course.</p>
    <?php endif; ?>

    <button class="back-btn" onclick="window.close()">Close Tab</button>

    <script>
        const searchInput = document.getElementById("searchInput");
        const table = document.getElementById("studentsTable");
        const rows = table.getElementsByTagName("tr");

        searchInput.addEventListener("keyup", function() {
            const filter = this.value.toLowerCase();

            for (let i = 1; i < rows.length; i++) {
                const id = rows[i].cells[0].textContent.toLowerCase();
                const name = rows[i].cells[1].textContent.toLowerCase();

                if (id.includes(filter) || name.includes(filter)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        });
    </script>
</body>
<!-- Modal -->
<div id="removeModal" style="display:none; position:fixed; top:30%; left:50%; transform:translate(-50%,-30%); background:#222; padding:20px; border:2px solid #f9c349; border-radius:10px; z-index:1000; color:white;">
    <h3 id="modalTitle">Remove Student</h3>
    <p>Enter reason (optional):</p>
    <textarea id="removalReason" rows="4" style="width:100%;"></textarea>
    <div style="margin-top:10px; text-align:right;">
        <button onclick="removeStudent()">Confirm</button>
        <button onclick="closeModal()">Cancel</button>
    </div>
</div>

<script>
    let selectedStudentId = null;
    let courseId = <?= json_encode($courseId) ?>;

    function confirmRemove(studentId, studentName) {
        selectedStudentId = studentId;
        document.getElementById('modalTitle').textContent = "Remove " + studentName;
        document.getElementById('removalReason').value = "";
        document.getElementById('removeModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('removeModal').style.display = 'none';
    }

    function removeStudent() {
        const reason = document.getElementById('removalReason').value;

        fetch('remove_student.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ course_id: courseId, student_id: selectedStudentId, reason: reason })
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                alert("Student removed and message sent.");
                location.reload();
            } else {
                alert("Failed to remove student.");
            }
        })
        .catch(err => {
            console.error(err);
            alert("An error occurred.");
        });

        closeModal();
    }
</script>

</html>
