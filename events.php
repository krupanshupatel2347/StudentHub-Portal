<?php
/**
 * Practical 12: Event Management CRUD with Poster Upload and File Validation
 * Student: Krupanshu Patel (25DCS088)
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once "db.php";

$message = "";
$messageType = "";
$uploadDirectory = __DIR__ . "/uploads/";

if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

function clean($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add" || $action === "update") {
        $eventId = (int)($_POST["event_id"] ?? 0);
        $eventName = trim($_POST["event_name"] ?? "");
        $eventDate = $_POST["event_date"] ?? "";
        $eventTime = $_POST["event_time"] ?? "";
        $eventVenue = trim($_POST["event_venue"] ?? "");
        $eventDescription = trim($_POST["event_description"] ?? "");
        $posterName = null;
        $oldPoster = "";

        if ($action === "update" && $eventId > 0) {
            $find = $conn->prepare("SELECT poster FROM events WHERE event_id = ?");
            $find->bind_param("i", $eventId);
            $find->execute();
            $existingEvent = $find->get_result()->fetch_assoc();
            $find->close();

            if (!$existingEvent) {
                $message = "Event not found.";
                $messageType = "error";
            } else {
                $oldPoster = $existingEvent["poster"] ?? "";
            }
        }

        if ($message === "") {
            if ($eventName === "" || $eventDate === "" || $eventTime === "" || $eventVenue === "" || $eventDescription === "") {
                $message = "Please fill in all event fields.";
                $messageType = "error";
            } else {
                $uploadError = UPLOAD_ERR_NO_FILE;
                if (isset($_FILES["poster"])) {
                    $uploadError = $_FILES["poster"]["error"];
                }

                if ($uploadError !== UPLOAD_ERR_NO_FILE && $uploadError !== UPLOAD_ERR_OK) {
                    $message = "Poster upload failed. Please try again.";
                    $messageType = "error";
                } elseif ($uploadError === UPLOAD_ERR_OK) {
                    $file = $_FILES["poster"];
                    if ($file["size"] > 2 * 1024 * 1024) {
                        $message = "Poster size must not exceed 2 MB.";
                        $messageType = "error";
                    } else {
                        $allowedTypes = [
                            "image/jpeg" => "jpg",
                            "image/png" => "png",
                            "image/webp" => "webp"
                        ];
                        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $fileInfo->file($file["tmp_name"]);

                        if (!isset($allowedTypes[$mimeType])) {
                            $message = "Only JPG, PNG and WEBP posters are allowed.";
                            $messageType = "error";
                        } else {
                            $posterName = uniqid("poster_", true) . "." . $allowedTypes[$mimeType];
                            if (!move_uploaded_file($file["tmp_name"], $uploadDirectory . $posterName)) {
                                $message = "Unable to save the poster.";
                                $messageType = "error";
                            }
                        }
                    }
                }
            }
        }

        if ($message === "") {
            if ($action === "add") {
                $stmt = $conn->prepare(
                    "INSERT INTO events (event_name, event_date, event_time, event_venue, event_description, poster) VALUES (?, ?, ?, ?, ?, ?)"
                );
                $savedPoster = $posterName;
                $stmt->bind_param("ssssss", $eventName, $eventDate, $eventTime, $eventVenue, $eventDescription, $savedPoster);
            } else {
                if ($posterName !== null) {
                    $stmt = $conn->prepare(
                        "UPDATE events SET event_name=?, event_date=?, event_time=?, event_venue=?, event_description=?, poster=? WHERE event_id=?"
                    );
                    $stmt->bind_param("ssssssi", $eventName, $eventDate, $eventTime, $eventVenue, $eventDescription, $posterName, $eventId);
                } else {
                    $stmt = $conn->prepare(
                        "UPDATE events SET event_name=?, event_date=?, event_time=?, event_venue=?, event_description=? WHERE event_id=?"
                    );
                    $stmt->bind_param("sssssi", $eventName, $eventDate, $eventTime, $eventVenue, $eventDescription, $eventId);
                }
            }

            if ($stmt && $stmt->execute()) {
                if ($action === "update" && $posterName !== null && $oldPoster !== "" && is_file($uploadDirectory . basename($oldPoster))) {
                    unlink($uploadDirectory . basename($oldPoster));
                }
                $message = $action === "add" ? "Event added successfully!" : "Event updated successfully!";
                $messageType = "success";
            } else {
                if ($posterName !== null && is_file($uploadDirectory . $posterName)) {
                    unlink($uploadDirectory . $posterName);
                }
                $message = "Unable to save event.";
                $messageType = "error";
            }
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    } elseif ($action === "delete") {
        $eventId = (int)($_POST["event_id"] ?? 0);
        $stmt = $conn->prepare("SELECT poster FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($event) {
            $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
            $stmt->bind_param("i", $eventId);
            if ($stmt->execute()) {
                if (!empty($event["poster"])) {
                    $posterPath = $uploadDirectory . basename($event["poster"]);
                    if (is_file($posterPath)) {
                        unlink($posterPath);
                    }
                }
                $message = "Event deleted successfully!";
                $messageType = "success";
            } else {
                $message = "Unable to delete event.";
                $messageType = "error";
            }
            $stmt->close();
        } else {
            $message = "Event not found.";
            $messageType = "error";
        }
    }
}

$editEvent = null;
if (isset($_GET["edit"])) {
    $editId = (int)$_GET["edit"];
    $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editEvent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$editEvent) {
        $message = "Event not found.";
        $messageType = "error";
    }
}

$search = trim($_GET["search"] ?? "");
if ($search !== "") {
    $stmt = $conn->prepare("SELECT * FROM events WHERE event_name LIKE ? OR event_venue LIKE ? ORDER BY event_id DESC");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare("SELECT * FROM events ORDER BY event_id DESC");
}
$stmt->execute();
$events = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 12 - Event Management</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f7fb; color: #1f2937; margin: 0; padding: 25px 15px; }
        .container { max-width: 1150px; margin: auto; }
        h1 { text-align: center; color: #2563eb; }
        .subtitle { text-align: center; margin-bottom: 25px; color: #64748b; }
        .top-actions { text-align: center; margin: 20px 0; }
        .card { background: white; padding: 24px; margin: 22px 0; border-radius: 12px; box-shadow: 0 3px 12px rgba(0, 0, 0, .08); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        label { display: block; font-weight: bold; margin-bottom: 7px; }
        input, textarea { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px; }
        textarea { resize: vertical; min-height: 90px; }
        button, .button { display: inline-block; padding: 10px 15px; background: #2563eb; color: white; border: none; border-radius: 6px; text-decoration: none; cursor: pointer; font-size: 14px; }
        button:hover, .button:hover { background: #1d4ed8; }
        .submit-button { margin-top: 18px; }
        .cancel { background: #64748b; }
        .delete { background: #dc2626; }
        .message { padding: 12px; border-radius: 6px; margin: 15px 0; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .search-form { display: flex; gap: 10px; align-items: end; }
        .search-form div { flex: 1; }
        .table-wrap { overflow-x: auto; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
        th { background: #2563eb; color: white; }
        .poster { width: 100px; height: 75px; object-fit: cover; border-radius: 5px; }
        .actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .actions form { margin: 0; }
        @media (max-width: 650px) {
            .search-form { display: grid; grid-template-columns: 1fr; }
            .card { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Event Management System</h1>
        <p class="subtitle">Practical 12: Event Management CRUD with Poster Upload</p>

        <div class="top-actions">
            <a href="admin_dashboard.php" class="button">Back to Admin Dashboard</a>
            <a href="students.php" class="button">Student Management</a>
        </div>

        <?php if ($message !== ""): ?>
            <div class="message <?= clean($messageType) ?>">
                <?= clean($message) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2><?= $editEvent ? "Update Event" : "Add New Event" ?></h2>
            <form method="POST" action="events.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= $editEvent ? "update" : "add" ?>">
                <?php if ($editEvent): ?>
                    <input type="hidden" name="event_id" value="<?= (int)$editEvent["event_id"] ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div>
                        <label for="event_name">Event Name</label>
                        <input type="text" id="event_name" name="event_name" maxlength="150" required value="<?= clean($editEvent["event_name"] ?? "") ?>">
                    </div>
                    <div>
                        <label for="event_date">Event Date</label>
                        <input type="date" id="event_date" name="event_date" required value="<?= clean($editEvent["event_date"] ?? "") ?>">
                    </div>
                    <div>
                        <label for="event_time">Event Time</label>
                        <input type="time" id="event_time" name="event_time" required value="<?= clean($editEvent["event_time"] ?? "") ?>">
                    </div>
                    <div>
                        <label for="event_venue">Event Venue</label>
                        <input type="text" id="event_venue" name="event_venue" maxlength="200" required value="<?= clean($editEvent["event_venue"] ?? "") ?>">
                    </div>
                </div>

                <div style="margin-top: 16px;">
                    <label for="event_description">Event Description</label>
                    <textarea id="event_description" name="event_description" required><?= clean($editEvent["event_description"] ?? "") ?></textarea>
                </div>

                <div style="margin-top: 16px;">
                    <label for="poster">Event Poster (JPG, PNG, WEBP; max 2 MB)</label>
                    <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
                    <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <?php if (!empty($editEvent["poster"])): ?>
                        <p style="margin-top: 8px;">Current poster:</p>
                        <img class="poster" src="uploads/<?= rawurlencode(basename($editEvent["poster"])) ?>" alt="Current event poster">
                        <p style="font-size: 13px; color: #64748b;">Choose a new poster only if you want to replace it.</p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="submit-button"><?= $editEvent ? "Update Event" : "Add Event" ?></button>
                <?php if ($editEvent): ?>
                    <a href="events.php" class="button cancel">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h2>Search Events</h2>
            <form method="GET" action="events.php" class="search-form">
                <div>
                    <label for="search">Search by event name or venue</label>
                    <input type="text" id="search" name="search" placeholder="Enter event name or venue" value="<?= clean($search) ?>">
                </div>
                <button type="submit">Search</button>
                <a href="events.php" class="button cancel">Reset</a>
            </form>
        </div>

        <div class="card">
            <h2>Event Records</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event Name</th>
                            <th>Date &amp; Time</th>
                            <th>Venue</th>
                            <th>Description</th>
                            <th>Poster</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($events && $events->num_rows > 0): ?>
                            <?php while ($event = $events->fetch_assoc()): ?>
                                <tr>
                                    <td><?= (int)$event["event_id"] ?></td>
                                    <td><?= clean($event["event_name"]) ?></td>
                                    <td>
                                        <?= clean($event["event_date"]) ?><br>
                                        <?= clean($event["event_time"]) ?>
                                    </td>
                                    <td><?= clean($event["event_venue"]) ?></td>
                                    <td><?= nl2br(clean($event["event_description"])) ?></td>
                                    <td>
                                        <?php if (!empty($event["poster"]) && is_file($uploadDirectory . basename($event["poster"]))): ?>
                                            <a href="uploads/<?= rawurlencode(basename($event["poster"])) ?>" target="_blank">
                                                <img class="poster" src="uploads/<?= rawurlencode(basename($event["poster"])) ?>" alt="Event poster">
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #94a3b8;">No poster</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a class="button" href="events.php?edit=<?= (int)$event["event_id"] ?>">Edit</a>
                                            <form method="POST" action="events.php" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="event_id" value="<?= (int)$event["event_id"] ?>">
                                                <button type="submit" class="delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:20px;">No events found. Add your first event above.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$stmt->close();
if ($conn) $conn->close();
?>
