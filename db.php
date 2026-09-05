<?php
/**
 * Database Connection (db.php)
 * Student: Krupanshu Patel (25DCS088)
 */
$host = "localhost";
$dbname = "studenthub_db";
$username = "root";
$password = "";

// PDO Connection
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $error) {
    error_log($error->getMessage());
    // Fallback or warning if MySQL server is offline during static checks
}

// MySQLi Connection
$conn = @new mysqli($host, $username, $password, $dbname);
if ($conn && !$conn->connect_error) {
    $conn->set_charset("utf8mb4");
}
?>
