<?php
/**
 * Practical 10: Secure Login Processing with Password Verification and RBAC Redirection
 * Student: Krupanshu Patel (25DCS088)
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set("session.use_strict_mode", "1");
    session_start();
}

require_once "db.php";
require_once "remember.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: Login.html");
    exit();
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$selectedRole = $_POST["role"] ?? "student";

if ($email === "" || $password === "") {
    header("Location: Login.html?error=empty");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($selectedRole, ["student", "admin"], true)) {
    header("Location: Login.html?error=invalid");
    exit();
}

$stmt = $conn->prepare(
    "SELECT u.user_id, u.full_name, u.email, u.password, COALESCE(ur.role, 'student') AS role
     FROM users u
     LEFT JOIN user_roles ur ON ur.user_id = u.user_id
     WHERE u.email = ?"
);

if (!$stmt) {
    error_log($conn->error);
    http_response_code(500);
    exit("Unable to process login right now.");
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Verify credentials and role matching
if (!$user || !password_verify($password, $user["password"]) || $user["role"] !== $selectedRole) {
    header("Location: Login.html?error=access_denied");
    exit();
}

// Regenerate session ID to prevent session fixation attacks
session_regenerate_id(true);

$_SESSION["user_id"] = (int)$user["user_id"];
$_SESSION["full_name"] = $user["full_name"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];
$_SESSION["last_activity"] = time();

// Remember-me token
if (isset($_POST["rememberMe"]) || isset($_POST["remember_me"])) {
    createRememberToken((int)$user["user_id"]);
}

// Role-based redirection
if ($user["role"] === "admin") {
    header("Location: admin_dashboard.php");
} else {
    header("Location: Home.html");
}
exit();
?>
