<?php
/**
 * Practical 9: Secure User Registration with Database Insert, Duplicate Email Check, and Password Hashing
 * Student: Krupanshu Patel (25DCS088)
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: Signup.html");
    exit();
}

$fullName = trim($_POST["fullName"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";
$course = trim($_POST["course"] ?? "");
$year = trim($_POST["year"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$terms = $_POST["terms"] ?? "";

$errors = [];

/* Full Name Validation */
if ($fullName === "") {
    $errors[] = "Full name is required.";
} elseif (!preg_match("/^[a-zA-Z ]+$/", $fullName)) {
    $errors[] = "Full name can contain only letters and spaces.";
}

/* Email Validation */
if ($email === "") {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Enter a valid email address.";
}

/* Mobile Validation */
if ($mobile === "") {
    $errors[] = "Mobile number is required.";
} elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
    $errors[] = "Mobile number must contain exactly 10 digits.";
}

/* Password Validation */
if ($password === "") {
    $errors[] = "Password is required.";
} elseif (
    strlen($password) < 8 ||
    !preg_match("/[A-Z]/", $password) ||
    !preg_match("/[a-z]/", $password) ||
    !preg_match("/[0-9]/", $password)
) {
    $errors[] = "Password must be at least 8 characters and include uppercase, lowercase, and a number.";
}

/* Confirm Password */
if ($confirmPassword === "") {
    $errors[] = "Confirm password is required.";
} elseif ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

/* Course Validation */
$allowedCourses = ["B.Tech IT", "B.Tech CSE", "BCA", "MCA"];
if (!in_array($course, $allowedCourses, true)) {
    $errors[] = "Please select a valid course.";
}

/* Year Validation */
if ($year === "" || !ctype_digit($year) || (int)$year < 1 || (int)$year > 4) {
    $errors[] = "Year must be between 1 and 4.";
}

/* Gender Validation */
$allowedGender = ["Male", "Female", "Other"];
if (!in_array($gender, $allowedGender, true)) {
    $errors[] = "Please select a valid gender.";
}

/* Terms Validation */
if ($terms !== "Accepted") {
    $errors[] = "You must accept the Terms and Conditions.";
}

/* Display Validation Errors */
if (!empty($errors)) {
    echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Signup Failed</title><link rel='stylesheet' href='style.css'></head><body>";
    echo "<div class='card' style='max-width:500px; margin: 40px auto; border-left: 5px solid #dc2626;'>";
    echo "<h2 style='color:#dc2626;'>Signup Failed</h2><ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error, ENT_QUOTES, "UTF-8") . "</li>";
    }
    echo "</ul><br><a href='Signup.html' class='button'>Go Back to Signup</a></div></body></html>";
    exit();
}

try {
    /* Check Duplicate Email */
    $checkSql = "SELECT user_id FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        echo "<!DOCTYPE html><html lang='en'><head><title>Signup Failed</title><link rel='stylesheet' href='style.css'></head><body>";
        echo "<div class='card' style='max-width:500px; margin:40px auto; text-align:center;'>";
        echo "<h2 style='color:#dc2626;'>Signup Failed</h2>";
        echo "<p>This email is already registered. Please use another email.</p>";
        echo "<br><a href='Signup.html' class='button'>Go Back to Signup</a></div></body></html>";
        exit();
    }
    $checkStmt->close();

    /* Hash Password */
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    /* Insert User */
    $insertSql = "INSERT INTO users (full_name, email, mobile, password, course, year, gender) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $yearValue = (int)$year;
    $insertStmt->bind_param("sssssis", $fullName, $email, $mobile, $passwordHash, $course, $yearValue, $gender);
    $insertStmt->execute();
    $newUserId = $insertStmt->insert_id;
    $insertStmt->close();

    // Default role assignment as student
    $roleStmt = $conn->prepare("INSERT INTO user_roles (user_id, role) VALUES (?, 'student')");
    if ($roleStmt) {
        $roleStmt->bind_param("i", $newUserId);
        $roleStmt->execute();
        $roleStmt->close();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Successful</title>
</head>
<body>
    <script>
        alert("Signup Successful!");
        window.location.href = "Login.html";
    </script>
</body>
</html>
<?php
    exit();
} catch (mysqli_sql_exception $error) {
    error_log($error->getMessage());
    if ($error->getCode() === 1062) {
        $message = "This email is already registered.";
    } else {
        $message = "Unable to complete registration. Please try again.";
    }
    echo "<!DOCTYPE html><html lang='en'><head><title>Signup Failed</title><link rel='stylesheet' href='style.css'></head><body>";
    echo "<div class='card' style='max-width:500px; margin:40px auto; text-align:center;'>";
    echo "<h2 style='color:#dc2626;'>Signup Failed</h2>";
    echo "<p>" . htmlspecialchars($message, ENT_QUOTES, "UTF-8") . "</p>";
    echo "<br><a href='Signup.html' class='button'>Go Back to Signup</a></div></body></html>";
    exit();
}
?>
