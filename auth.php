<?php
/**
 * Practical 10: Authentication, Session Management, and Role-Based Access Control
 * Student: Krupanshu Patel (25DCS088)
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set("session.use_strict_mode", "1");
    session_start();
}

require_once "db.php";
require_once "remember.php";

restoreRememberLogin();

// Inactivity session timeout: 900 seconds (15 minutes)
$timeoutDuration = 900;

function requireLogin()
{
    global $timeoutDuration;
    if (!isset($_SESSION["user_id"])) {
        header("Location: Login.html");
        exit();
    }

    if (
        !isset($_SESSION["last_activity"]) ||
        (time() - $_SESSION["last_activity"]) > $timeoutDuration
    ) {
        clearRememberToken();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $cookieParams = session_get_cookie_params();
            setcookie(session_name(), "", [
                "expires" => time() - 3600,
                "path" => $cookieParams["path"],
                "domain" => $cookieParams["domain"],
                "secure" => $cookieParams["secure"],
                "httponly" => $cookieParams["httponly"],
                "samesite" => "Lax"
            ]);
        }
        session_destroy();
        header("Location: Login.html?timeout=1");
        exit();
    }
    $_SESSION["last_activity"] = time();
}

function requireRole($requiredRole)
{
    requireLogin();
    if (
        !isset($_SESSION["role"]) ||
        $_SESSION["role"] !== $requiredRole
    ) {
        http_response_code(403);
        exit("Access Denied: You do not have permission to access this page.");
    }
}
?>
