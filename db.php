<?php
// db.php
// define('DB_DSN', 'mysql:host=localhost;dbname=lodur_test;charset=utf8');
// define('DB_USER', 'root');
// define('DB_PASS', '');

//   try {
//         $conn = new PDO("mysql:host=localhost;dbname=lodur_test;charset=utf8", "root", "");
//         $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     } catch (PDOException $e) {
//         die("Connection failed: " . $e->getMessage());
//     }


// db.php
// Use environment variables in production; here is a default fallback.
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('DB_NAME') ?: 'lodur_test';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
try {
    $conn = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // In production log this instead of echoing
    die("Database connection failed: " . $e->getMessage());
}

?>
