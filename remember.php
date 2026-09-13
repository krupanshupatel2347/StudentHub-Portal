<?php
/**
 * Practical 10: Remember-Me Token Manager
 * Student: Krupanshu Patel (25DCS088)
 */
require_once "db.php";

function createRememberToken($userId)
{
    global $conn;
    clearRememberToken();

    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $tokenHash = hash("sha256", $validator);
    $expiresAt = date("Y-m-d H:i:s", time() + (30 * 24 * 60 * 60));

    $stmt = $conn->prepare(
        "INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)"
    );
    if ($stmt) {
        $stmt->bind_param("isss", $userId, $selector, $tokenHash, $expiresAt);
        $stmt->execute();
        $stmt->close();
    }

    setcookie("remember_me", $selector . ":" . $validator, [
        "expires" => time() + (30 * 24 * 60 * 60),
        "path" => "/",
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

function restoreRememberLogin()
{
    global $conn;
    if (isset($_SESSION["user_id"]) || empty($_COOKIE["remember_me"])) {
        return;
    }

    $parts = explode(":", $_COOKIE["remember_me"]);
    if (count($parts) !== 2) {
        return;
    }

    [$selector, $validator] = $parts;
    if (
        !ctype_xdigit($selector) ||
        !ctype_xdigit($validator) ||
        strlen($selector) !== 24 ||
        strlen($validator) !== 64
    ) {
        return;
    }

    $stmt = $conn->prepare(
        "SELECT rt.user_id, rt.token_hash, u.full_name, u.email, COALESCE(ur.role, 'student') as role
         FROM remember_tokens rt
         JOIN users u ON u.user_id = rt.user_id
         LEFT JOIN user_roles ur ON ur.user_id = u.user_id
         WHERE rt.selector = ? AND rt.expires_at > NOW()"
    );
    if (!$stmt) return;

    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !hash_equals($user["token_hash"], hash("sha256", $validator))) {
        return;
    }

    session_regenerate_id(true);
    $_SESSION["user_id"] = (int)$user["user_id"];
    $_SESSION["full_name"] = $user["full_name"];
    $_SESSION["email"] = $user["email"];
    $_SESSION["role"] = $user["role"] ?? "student";
    $_SESSION["last_activity"] = time();
}

function clearRememberToken()
{
    global $conn;
    if (!empty($_COOKIE["remember_me"])) {
        $parts = explode(":", $_COOKIE["remember_me"]);
        if (count($parts) === 2) {
            $selector = $parts[0];
            if (ctype_xdigit($selector) && strlen($selector) === 24) {
                $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE selector = ?");
                if ($stmt) {
                    $stmt->bind_param("s", $selector);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
        setcookie("remember_me", "", [
            "expires" => time() - 3600,
            "path" => "/",
            "httponly" => true,
            "samesite" => "Lax"
        ]);
        unset($_COOKIE["remember_me"]);
    }
}
?>
