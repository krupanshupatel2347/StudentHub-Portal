<?php
/**
 * Database connectivity verification (fetch.php)
 * Student: Krupanshu Patel (25DCS088)
 */
require_once "db.php";

if (!isset($pdo)) {
    die("Database connection failed or not established.");
}

try {
    $sql = "SELECT student_id, full_name, email, mobile FROM students";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $students = $stmt->fetchAll();

    echo "<h2>Student Details</h2>";
    foreach ($students as $student) {
        echo "ID: " . htmlspecialchars((string)$student["student_id"]) . "<br>";
        echo "Name: " . htmlspecialchars($student["full_name"]) . "<br>";
        echo "Email: " . htmlspecialchars($student["email"]) . "<br>";
        echo "Mobile: " . htmlspecialchars($student["mobile"]) . "<br><hr>";
    }
} catch (Exception $e) {
    echo "Error fetching records: " . htmlspecialchars($e->getMessage());
}
?>
