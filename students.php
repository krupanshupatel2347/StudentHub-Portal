<?php
/**
 * Practical 11: Student Management CRUD with Search and Filter
 * Student: Krupanshu Patel (25DCS088)
 */
require_once "db.php";

$message = "";
$messageType = "";
$editStudent = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add" || $action === "update") {
        $fullName = trim($_POST["full_name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $mobile = trim($_POST["mobile"] ?? "");
        $studentId = (int)($_POST["student_id"] ?? 0);

        if ($fullName === "" || $email === "" || $mobile === "") {
            $message = "Please fill in all fields.";
            $messageType = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
            $messageType = "error";
        } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
            $message = "Mobile number must contain exactly 10 digits.";
            $messageType = "error";
        } else {
            if ($action === "add") {
                $stmt = $conn->prepare("INSERT INTO students (full_name, email, mobile) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sss", $fullName, $email, $mobile);
                }
            } else {
                $stmt = $conn->prepare("UPDATE students SET full_name = ?, email = ?, mobile = ? WHERE student_id = ?");
                if ($stmt) {
                    $stmt->bind_param("sssi", $fullName, $email, $mobile, $studentId);
                }
            }

            if (isset($stmt) && $stmt) {
                if ($stmt->execute()) {
                    if ($action === "add" && $stmt->affected_rows === 1) {
                        $message = "Student added successfully!";
                        $messageType = "success";
                    } elseif ($action === "update") {
                        if ($stmt->affected_rows > 0) {
                            $message = "Student updated successfully!";
                            $messageType = "success";
                        } else {
                            $message = "No changes made, or student record not found.";
                            $messageType = "error";
                        }
                    } else {
                        $message = "Student was not added.";
                        $messageType = "error";
                    }
                } elseif ($stmt->errno === 1062) {
                    $message = "This email is already registered.";
                    $messageType = "error";
                } else {
                    $message = "Database operation failed.";
                    $messageType = "error";
                }
                $stmt->close();
            } else {
                $message = "Unable to prepare the database query.";
                $messageType = "error";
            }
        }
    } elseif ($action === "delete") {
        $studentId = (int)($_POST["student_id"] ?? 0);
        if ($studentId > 0) {
            $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $studentId);
                if ($stmt->execute() && $stmt->affected_rows === 1) {
                    $message = "Student deleted successfully!";
                    $messageType = "success";
                } else {
                    $message = "Student not found or could not be deleted.";
                    $messageType = "error";
                }
                $stmt->close();
            } else {
                $message = "Unable to prepare the delete query.";
                $messageType = "error";
            }
        } else {
            $message = "Invalid student ID.";
            $messageType = "error";
        }
    }
}

// Edit student fetch
if (isset($_GET["edit"])) {
    $editId = (int)$_GET["edit"];
    if ($editId > 0) {
        $stmt = $conn->prepare("SELECT student_id, full_name, email, mobile FROM students WHERE student_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $editId);
            $stmt->execute();
            $editStudent = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
        if (!$editStudent) {
            $message = "Student record not found.";
            $messageType = "error";
        }
    }
}

// Search and Filter
$search = trim($_GET["search"] ?? "");
$filter = $_GET["filter"] ?? "all";

$sql = "SELECT student_id, full_name, email, mobile FROM students WHERE 1=1";
$params = [];
$types = "";

if ($search !== "") {
    $searchTerm = "%" . $search . "%";
    if ($filter === "name") {
        $sql .= " AND full_name LIKE ?";
        $params[] = $searchTerm;
        $types .= "s";
    } elseif ($filter === "email") {
        $sql .= " AND email LIKE ?";
        $params[] = $searchTerm;
        $types .= "s";
    } elseif ($filter === "mobile") {
        $sql .= " AND mobile LIKE ?";
        $params[] = $searchTerm;
        $types .= "s";
    } else {
        $sql .= " AND (full_name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }
}
$sql .= " ORDER BY student_id DESC";

$stmt = $conn->prepare($sql);
$result = false;
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if ($stmt->execute()) {
        $result = $stmt->get_result();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Practical 11</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 30px 15px; color: #1f2937; }
        .container { max-width: 1100px; margin: auto; }
        h1 { text-align: center; color: #2563eb; }
        .subtitle { text-align: center; margin-bottom: 20px; color: #64748b; }
        .top-actions { text-align: center; margin: 20px 0; }
        .dashboard-link { display: inline-block; background: #2563eb; color: white; padding: 11px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background 0.2s; }
        .dashboard-link:hover { background: #1d4ed8; }
        .card { background: white; padding: 24px; margin: 22px 0; border-radius: 12px; box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        label { display: block; margin-bottom: 7px; font-weight: bold; }
        input, select { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px; }
        button, .button { display: inline-block; padding: 10px 15px; border: none; border-radius: 6px; background: #2563eb; color: white; font-size: 14px; cursor: pointer; text-decoration: none; }
        button:hover, .button:hover { background: #1d4ed8; }
        .submit-button { margin-top: 18px; }
        .cancel { background: #64748b; }
        .cancel:hover { background: #475569; }
        .delete { background: #dc2626; }
        .delete:hover { background: #b91c1c; }
        .message { padding: 12px; border-radius: 6px; margin: 15px 0; }
        .success { color: #166534; background: #dcfce7; }
        .error { color: #991b1b; background: #fee2e2; }
        .search-form { display: grid; grid-template-columns: 2fr 1fr auto auto; gap: 10px; align-items: end; }
        .table-wrap { overflow-x: auto; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb; }
        th { background: #2563eb; color: white; }
        tr:hover td { background: #f8fafc; }
        .actions { display: flex; gap: 7px; align-items: center; }
        .actions form { margin: 0; }
        @media (max-width: 650px) {
            .search-form { grid-template-columns: 1fr; }
            .card { padding: 16px; }
            .actions { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Management System</h1>
        <p class="subtitle">Practical 11: Student Management CRUD with MySQLi</p>

        <div class="top-actions">
            <a href="admin_dashboard.php" class="dashboard-link">&#8592; Back to Admin Dashboard</a>
        </div>

        <?php if ($message !== ""): ?>
            <div class="message <?= htmlspecialchars($messageType) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2><?= $editStudent ? "Edit Student" : "Add New Student" ?></h2>
            <form method="POST" action="students.php">
                <input type="hidden" name="action" value="<?= $editStudent ? "update" : "add" ?>">
                <?php if ($editStudent): ?>
                    <input type="hidden" name="student_id" value="<?= (int)$editStudent["student_id"] ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div>
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" maxlength="100" required value="<?= htmlspecialchars($editStudent["full_name"] ?? "") ?>">
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" maxlength="100" required value="<?= htmlspecialchars($editStudent["email"] ?? "") ?>">
                    </div>
                    <div>
                        <label for="mobile">Mobile Number</label>
                        <input type="text" id="mobile" name="mobile" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" required value="<?= htmlspecialchars($editStudent["mobile"] ?? "") ?>">
                    </div>
                </div>

                <button type="submit" class="submit-button"><?= $editStudent ? "Update Student" : "Add Student" ?></button>
                <?php if ($editStudent): ?>
                    <a href="students.php" class="button cancel">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h2>Search and Filter Students</h2>
            <form method="GET" action="students.php" class="search-form">
                <div>
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" placeholder="Enter name, email or mobile" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div>
                    <label for="filter">Search By</label>
                    <select id="filter" name="filter">
                        <option value="all" <?= $filter === "all" ? "selected" : "" ?>>All Fields</option>
                        <option value="name" <?= $filter === "name" ? "selected" : "" ?>>Name</option>
                        <option value="email" <?= $filter === "email" ? "selected" : "" ?>>Email</option>
                        <option value="mobile" <?= $filter === "mobile" ? "selected" : "" ?>>Mobile</option>
                    </select>
                </div>
                <button type="submit">Search</button>
                <a href="students.php" class="button cancel">Reset</a>
            </form>
        </div>

        <div class="card">
            <h2>Student Records</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($student = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= (int)$student["student_id"] ?></td>
                                    <td><?= htmlspecialchars($student["full_name"]) ?></td>
                                    <td><?= htmlspecialchars($student["email"]) ?></td>
                                    <td><?= htmlspecialchars($student["mobile"]) ?></td>
                                    <td>
                                        <div class="actions">
                                            <a class="button" href="students.php?edit=<?= (int)$student["student_id"] ?>">Edit</a>
                                            <form method="POST" action="students.php" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="student_id" value="<?= (int)$student["student_id"] ?>">
                                                <button type="submit" class="delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 20px;">No student records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php if ($conn) $conn->close(); ?>
