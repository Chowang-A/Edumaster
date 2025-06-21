<?php
// db.php — Database connection for EduMaster using PDO + PostgreSQL

$host = 'localhost';
$port = '5432'; // Correct port for PostgreSQL
$dbname = 'edumaster';
$user = 'postgres';
$password = 'V/c7bx1#?L'; // Consider using environment variables

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    // echo "Database connection established."; // For debugging only
} catch (PDOException $e) {
    error_log("DB Connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>
