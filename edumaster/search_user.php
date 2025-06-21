<?php
require 'db.php';

$term = trim($_GET['q'] ?? '');

if ($term === '') {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT name, email 
    FROM users 
    WHERE LOWER(name) LIKE LOWER(:term) 
    ORDER BY name ASC 
    LIMIT 10
");
$stmt->execute(['term' => $term . '%']);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);
