<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.html?error=unauthorized");
    exit;
}

$userId = $_SESSION['user_id'];
$systemUserId = 6; // ID of the system user

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'], $_POST['teacher_id'])) {
    $courseId = intval($_POST['course_id']);
    $teacherId = intval($_POST['teacher_id']);

    // Remove the enrollment
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);

    // Fetch student name
    $studentNameStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $studentNameStmt->execute([$userId]);
    $studentName = $studentNameStmt->fetchColumn() ?? 'Unknown Student';

    // Fetch course title
    $courseTitleStmt = $pdo->prepare("SELECT title FROM courses WHERE id = ?");
    $courseTitleStmt->execute([$courseId]);
    $courseTitle = $courseTitleStmt->fetchColumn() ?? 'Unknown Course';

    // Escape HTML special chars for safe display in message
    $safeStudentName = htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8');
    $safeCourseTitle = htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8');

    // Compose and send message to teacher from the system
    $subject = "Student Left Course";
    $message = "Student: $safeStudentName (ID: $userId) has left the course: $safeCourseTitle.";

    $msgStmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message, send_at) VALUES (?, ?, ?, ?, NOW())");
    $msgStmt->execute([$systemUserId, $teacherId, $subject, $message]);

    header("Location: dashboard.php?msg=left_course");
    exit;
} else {
    header("Location: dashboard.php?error=invalid_request");
    exit;
}
