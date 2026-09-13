<?php
/**
 * Practical 10: Secure Logout
 * Student: Krupanshu Patel (25DCS088)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "db.php";
require_once "remember.php";

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
header("Location: Login.html?logout=success");
exit();
?>
