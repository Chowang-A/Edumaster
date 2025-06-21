<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$courseId = $data['course_id'] ?? 0;
$studentId = $data['student_id'] ?? 0;
$reason = trim($data['reason'] ?? '');

$teacherId = $_SESSION['user_id'];

try {
    // Remove student from enrollment
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE course_id = ? AND user_id = ?");
    $stmt->execute([$courseId, $studentId]);

    // Send message to student
    $subject = "Removed from course";
    $message = "You have been removed from the course ID $courseId by your teacher.";
    if ($reason !== '') {
        $message .= " Reason: " . htmlspecialchars($reason);
    }

    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, receiver_id, subject, message, send_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$teacherId, $studentId, $subject, $message]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
