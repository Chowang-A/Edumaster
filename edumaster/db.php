<?php
// db.php — Secure PDO connection to PostgreSQL for EduMaster

// Load environment variables (for platforms like Render or dotenv setups)
$host     = getenv('DB_HOST') ?: 'dpg-d1b3obmuk2gs739ato9g-a';
$port     = getenv('DB_PORT') ?: '5432';
$dbname   = getenv('DB_NAME') ?: 'edumaster_db';
$user     = getenv('DB_USER') ?: 'edumaster_db_user';
$password = getenv('DB_PASS') ?: 'wGfVhvj5iiAIuvYvmyV4PxB1PrRr0DOY'; // Empty fallback, set in hosting env

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Log detailed error but show generic message to user
    error_log("Database connection error: " . $e->getMessage());
    die("Unable to connect to the database. Please try again later.");
}
?>
