<?php
/**
 * Practical 14: Admin Dashboard with Role-Based Access Control, Dynamic Menu, and Audit Log
 * Student: Krupanshu Patel (25DCS088)
 */
require_once "auth.php";
requireRole("admin");
require_once "db.php";

mysqli_report(MYSQLI_REPORT_OFF);

$fullName = $_SESSION["full_name"] ?? "Admin";
$userId = (int)($_SESSION["user_id"] ?? 0);
$userRole = $_SESSION["role"] ?? "student";

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

$students = [];
$latestRegistrations = [];
$auditLogs = [];
$error = "";
$totalAccounts = 0;
$totalStudents = 0;
$totalAdmins = 0;
$totalEvents = 0;

// Query all users
$sql = "
SELECT
    u.user_id,
    u.full_name,
    u.email,
    u.mobile,
    u.course,
    u.year,
    u.gender,
    u.created_at,
    COALESCE(ur.role, 'student') AS role
FROM users u
LEFT JOIN user_roles ur ON ur.user_id = u.user_id
ORDER BY u.user_id DESC
";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
} else {
    error_log($conn->error);
    $error = "Unable to load account records.";
}

$totalAccounts = count($students);
foreach ($students as $student) {
    if ($student["role"] === "admin") {
        $totalAdmins++;
    } else {
        $totalStudents++;
    }
}

// Total events count
$eventResult = $conn->query("SELECT COUNT(*) AS total FROM events");
if ($eventResult) {
    $eventRow = $eventResult->fetch_assoc();
    $totalEvents = (int)$eventRow["total"];
} else {
    error_log($conn->error);
}

// Record admin audit log
if ($userId > 0) {
    $action = "Viewed Admin Dashboard";
    $details = "Admin opened the Practical 14 dashboard.";
    $ipAddress = $_SERVER["REMOTE_ADDR"] ?? "127.0.0.1";
    $auditStmt = $conn->prepare("INSERT INTO audit_logs (user_id, user_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)");
    if ($auditStmt) {
        $auditStmt->bind_param("issss", $userId, $fullName, $action, $details, $ipAddress);
        $auditStmt->execute();
        $auditStmt->close();
    }
}

// Latest registrations
$latestResult = $conn->query(
    "SELECT u.user_id, u.full_name, u.email, u.course, u.created_at, COALESCE(ur.role, 'student') AS role
     FROM users u
     LEFT JOIN user_roles ur ON ur.user_id = u.user_id
     ORDER BY u.created_at DESC, u.user_id DESC
     LIMIT 5"
);
if ($latestResult) {
    while ($row = $latestResult->fetch_assoc()) {
        $latestRegistrations[] = $row;
    }
}

// Recent audit logs
$auditResult = $conn->query(
    "SELECT log_id, user_name, action, details, ip_address, created_at
     FROM audit_logs
     ORDER BY log_id DESC
     LIMIT 10"
);
if ($auditResult) {
    while ($row = $auditResult->fetch_assoc()) {
        $auditLogs[] = $row;
    }
}

// Dynamic RBAC Menu
$menuItems = [];
if ($userRole === "admin") {
    $menuItems = [
        ["label" => "Manage Students", "url" => "students.php"],
        ["label" => "Manage Events", "url" => "events.php"],
        ["label" => "API CRUD Events", "url" => "index.html"],
        ["label" => "Home", "url" => "Home.html"]
    ];
} elseif ($userRole === "student") {
    $menuItems = [
        ["label" => "Home", "url" => "Home.html"],
        ["label" => "Events", "url" => "event.html"]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 14 | StudentHub Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f1f5f9; color: #1e293b; font-family: Arial, sans-serif; }
        .admin-header { background: #2563eb; color: white; padding: 22px 5%; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; }
        .admin-header h1 { margin: 0; font-size: 26px; }
        .admin-header p { margin: 8px 0 0; }
        .header-actions { display: flex; gap: 9px; flex-wrap: wrap; align-items: center; }
        .btn { display: inline-block; padding: 11px 15px; border: none; border-radius: 8px; text-decoration: none; cursor: pointer; font-family: inherit; font-size: 14px; font-weight: bold; }
        .btn-light { background: white; color: #2563eb; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn:hover { opacity: .88; }
        .admin-container { max-width: 1250px; margin: 28px auto; padding: 0 20px; }
        .page-title { margin: 0 0 8px; font-size: 25px; color: #1e293b; }
        .page-description { color: #64748b; margin-bottom: 24px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 18px; margin-bottom: 24px; }
        .stat-card, .panel { background: white; border-radius: 14px; padding: 22px; box-shadow: 0 4px 18px rgba(15, 23, 42, .06); margin-bottom: 24px; }
        .stat-card p { color: #64748b; margin: 0 0 10px; }
        .stat-card h2 { margin: 0; font-size: 32px; color: #2563eb; }
        .panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
        .panel-heading h2 { margin: 0; font-size: 21px; }
        .panel-heading p { color: #64748b; margin: 8px 0 0; }
        .table-wrap { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { padding: 13px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        th { background: #eff6ff; color: #1e40af; }
        .role-badge { display: inline-block; border-radius: 20px; padding: 5px 10px; background: #dcfce7; color: #166534; font-size: 12px; font-weight: bold; }
        .role-admin { background: #dbeafe; color: #1d4ed8; }
        .empty-message { padding: 15px 0; color: #64748b; }
        .search-box { width: 300px; max-width: 100%; padding: 11px 13px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; margin-bottom: 18px; }
        .modal[hidden] { display: none !important; }
        .modal { position: fixed; inset: 0; z-index: 1000; background: rgba(15, 23, 42, .65); display: flex; align-items: center; justify-content: center; padding: 18px; }
        .modal-content { background: white; width: 100%; max-width: 850px; max-height: 85vh; overflow-y: auto; border-radius: 16px; padding: 24px; }
        .modal-top { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 15px; }
        .modal-top h2 { margin: 0; color: #2563eb; }
        .close-btn { border: 0; background: #eff6ff; border-radius: 8px; padding: 8px 12px; font-size: 20px; cursor: pointer; }
        .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin: 20px 0; }
        .detail-item { padding: 13px; border: 1px solid #e2e8f0; border-radius: 9px; overflow-wrap: anywhere; }
        .detail-item small { display: block; color: #64748b; margin-bottom: 7px; }
        body.modal-open { overflow: hidden; }
        footer { text-align: center; color: #64748b; padding: 20px; }
        @media (max-width: 600px) {
            .admin-header { padding: 20px; }
            .admin-container { padding: 0 12px; }
            .panel, .stat-card { padding: 16px; }
            .search-box { width: 100%; }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div>
            <h1>StudentHub Admin</h1>
            <p>Welcome, <?= e($fullName) ?>!</p>
            <p>Current Role: <strong><?= e(ucfirst($userRole)) ?></strong></p>
        </div>
        <nav class="header-actions" aria-label="Dashboard menu">
            <?php foreach ($menuItems as $item): ?>
                <a class="btn btn-light" href="<?= e($item["url"]) ?>"><?= e($item["label"]) ?></a>
            <?php endforeach; ?>
            <button type="button" class="btn btn-light" id="openStudents">View Accounts</button>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </nav>
    </header>

    <main class="admin-container">
        <h2 class="page-title">Practical 14: Admin Dashboard</h2>
        <p class="page-description">Role-Based Access Control, Dynamic Menu and Audit Log</p>

        <div class="stats">
            <section class="stat-card">
                <p>Total User Accounts</p>
                <h2><?= $totalAccounts ?></h2>
            </section>
            <section class="stat-card">
                <p>Student Count</p>
                <h2><?= $totalStudents ?></h2>
            </section>
            <section class="stat-card">
                <p>Admin Count</p>
                <h2><?= $totalAdmins ?></h2>
            </section>
            <section class="stat-card">
                <p>Event Count</p>
                <h2><?= $totalEvents ?></h2>
            </section>
        </div>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Latest Registrations</h2>
                    <p>Recently created user accounts</p>
                </div>
                <button type="button" class="btn btn-primary" id="openStudents2">View All Accounts</button>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Role</th>
                            <th>Registered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestRegistrations as $registration): ?>
                            <tr>
                                <td><?= e($registration["user_id"]) ?></td>
                                <td><?= e($registration["full_name"]) ?></td>
                                <td><?= e($registration["email"]) ?></td>
                                <td><?= e($registration["course"]) ?></td>
                                <td>
                                    <span class="role-badge <?= $registration["role"] === "admin" ? "role-admin" : "" ?>">
                                        <?= e(ucfirst($registration["role"])) ?>
                                    </span>
                                </td>
                                <td><?= e($registration["created_at"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($latestRegistrations) === 0): ?>
                    <p class="empty-message">No registrations found.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Audit Log</h2>
                    <p>Recent dashboard activities</p>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($auditLogs as $log): ?>
                            <tr>
                                <td><?= e($log["log_id"]) ?></td>
                                <td><?= e($log["user_name"]) ?></td>
                                <td><?= e($log["action"]) ?></td>
                                <td><?= e($log["details"]) ?></td>
                                <td><?= e($log["ip_address"]) ?></td>
                                <td><?= e($log["created_at"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($auditLogs) === 0): ?>
                    <p class="empty-message">No audit records found.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Account Overview</h2>
                    <p>Search accounts and view profile details.</p>
                </div>
                <button type="button" class="btn btn-primary" id="openStudents3">Open Records</button>
            </div>
            <?php if ($error !== ""): ?>
                <p><?= e($error) ?></p>
            <?php else: ?>
                <p>Total accounts loaded: <?= $totalAccounts ?></p>
            <?php endif; ?>
        </section>
    </main>

    <!-- Modal for viewing student accounts -->
    <div class="modal" id="studentsModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" hidden>
        <div class="modal-content">
            <div class="modal-top">
                <h2 id="modalTitle">Student &amp; User Records</h2>
                <button type="button" class="close-btn" id="closeStudents" aria-label="Close popup">&times;</button>
            </div>
            <input type="search" class="search-box" id="studentSearch" placeholder="Search name, email, course or role..." aria-label="Search accounts">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="studentRows">
                        <?php foreach ($students as $student): ?>
                            <tr data-search="<?= e(strtolower($student["full_name"] . " " . $student["email"] . " " . $student["course"] . " " . $student["role"])) ?>">
                                <td><?= e($student["full_name"]) ?></td>
                                <td><?= e($student["email"]) ?></td>
                                <td><?= e($student["course"]) ?></td>
                                <td>
                                    <span class="role-badge <?= $student["role"] === "admin" ? "role-admin" : "" ?>">
                                        <?= e(ucfirst($student["role"])) ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary view-details" data-details="<?= e(json_encode([
                                        "id" => $student["user_id"],
                                        "name" => $student["full_name"],
                                        "email" => $student["email"],
                                        "mobile" => $student["mobile"],
                                        "course" => $student["course"],
                                        "year" => $student["year"],
                                        "gender" => $student["gender"],
                                        "created" => $student["created_at"],
                                        "role" => $student["role"]
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) ?>">View Details</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="text-align:right; margin-top:18px;">
                <button type="button" class="btn btn-light" id="closeStudentsBottom">Close</button>
            </div>
        </div>
    </div>

    <!-- Modal for single account details -->
    <div class="modal" id="detailsModal" role="dialog" aria-modal="true" aria-labelledby="detailsTitle" hidden>
        <div class="modal-content">
            <div class="modal-top">
                <h2 id="detailsTitle">Account Details</h2>
                <button type="button" class="close-btn" id="closeDetails" aria-label="Close popup">&times;</button>
            </div>
            <div class="detail-grid" id="detailsGrid"></div>
            <div style="text-align:right; margin-top:18px;">
                <button type="button" class="btn btn-primary" id="backToRecords">Back to Records</button>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2026 StudentHub | Practical 14 Admin Dashboard | Student: Krupanshu Patel (25DCS088)
    </footer>

    <script>
        "use strict";
        const studentsModal = document.getElementById("studentsModal");
        const detailsModal = document.getElementById("detailsModal");

        function showModal(modal) {
            modal.hidden = false;
            document.body.classList.add("modal-open");
        }

        function hideModal(modal) {
            modal.hidden = true;
            if (studentsModal.hidden && detailsModal.hidden) {
                document.body.classList.remove("modal-open");
            }
        }

        function openRecords() {
            showModal(studentsModal);
        }

        ["openStudents", "openStudents2", "openStudents3"].forEach(id => {
            const btn = document.getElementById(id);
            if (btn) btn.addEventListener("click", openRecords);
        });

        document.getElementById("closeStudents").addEventListener("click", () => hideModal(studentsModal));
        document.getElementById("closeStudentsBottom").addEventListener("click", () => hideModal(studentsModal));

        function returnToRecords() {
            hideModal(detailsModal);
            showModal(studentsModal);
        }

        document.getElementById("closeDetails").addEventListener("click", returnToRecords);
        document.getElementById("backToRecords").addEventListener("click", returnToRecords);

        document.getElementById("studentSearch").addEventListener("input", function () {
            const query = this.value.toLowerCase().trim();
            document.querySelectorAll("#studentRows tr").forEach(row => {
                row.hidden = !row.dataset.search.includes(query);
            });
        });

        document.querySelectorAll(".view-details").forEach(button => {
            button.addEventListener("click", () => {
                let data;
                try {
                    data = JSON.parse(button.getAttribute("data-details"));
                } catch (error) {
                    alert("Unable to read this account's details.");
                    return;
                }
                const fields = [
                    ["User ID", data.id],
                    ["Full Name", data.name],
                    ["Email", data.email],
                    ["Mobile", data.mobile],
                    ["Course", data.course],
                    ["Year", data.year],
                    ["Gender", data.gender],
                    ["Account Created", data.created],
                    ["Role", data.role]
                ];
                const grid = document.getElementById("detailsGrid");
                grid.replaceChildren();
                fields.forEach(([label, value]) => {
                    const item = document.createElement("div");
                    item.className = "detail-item";
                    const small = document.createElement("small");
                    small.textContent = label;
                    const strong = document.createElement("strong");
                    strong.textContent = (value === null || value === "") ? "Not provided" : String(value);
                    item.append(small, strong);
                    grid.appendChild(item);
                });
                hideModal(studentsModal);
                showModal(detailsModal);
            });
        });

        document.addEventListener("keydown", event => {
            if (event.key === "Escape") {
                if (!detailsModal.hidden) {
                    returnToRecords();
                } else if (!studentsModal.hidden) {
                    hideModal(studentsModal);
                }
            }
        });
    </script>
</body>
</html>
<?php if ($conn) $conn->close(); ?>
