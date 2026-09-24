<?php
/**
 * Practical 13: RESTful PHP JSON API for Event Management
 * Student: Krupanshu Patel (25DCS088)
 */
header("Content-Type: application/json; charset=UTF-8");
require_once "db.php";

function sendResponse($statusCode, $response)
{
    http_response_code($statusCode);
    echo json_encode($response);
    exit;
}

function getInputData()
{
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    if (!is_array($data)) {
        sendResponse(400, [
            "success" => false,
            "message" => "Invalid JSON data."
        ]);
    }
    return $data;
}

function validateEvent($data)
{
    $eventName = trim($data["event_name"] ?? "");
    $eventDate = trim($data["event_date"] ?? "");
    $eventVenue = trim($data["event_venue"] ?? $data["venue"] ?? "");

    if ($eventName === "" || $eventDate === "" || $eventVenue === "") {
        sendResponse(400, [
            "success" => false,
            "message" => "Event name, date and venue are required."
        ]);
    }

    $dateObject = DateTime::createFromFormat("!Y-m-d", $eventDate);
    if (!$dateObject || $dateObject->format("Y-m-d") !== $eventDate) {
        sendResponse(400, [
            "success" => false,
            "message" => "Invalid event date. Use YYYY-MM-DD format."
        ]);
    }

    return [
        "event_name" => $eventName,
        "event_date" => $eventDate,
        "event_time" => trim($data["event_time"] ?? ""),
        "event_venue" => $eventVenue,
        "event_description" => trim($data["event_description"] ?? "")
    ];
}

$method = $_SERVER["REQUEST_METHOD"];

// GET - List all events
if ($method === "GET") {
    $sql = "SELECT * FROM events ORDER BY event_id DESC";
    $result = $conn->query($sql);
    if (!$result) {
        sendResponse(500, [
            "success" => false,
            "message" => "Unable to fetch events."
        ]);
    }

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    sendResponse(200, [
        "success" => true,
        "data" => $events
    ]);
}

// POST - Add new event
if ($method === "POST") {
    $data = validateEvent(getInputData());
    $eventName = $data["event_name"];
    $eventDate = $data["event_date"];
    $eventTime = $data["event_time"] !== "" ? $data["event_time"] : null;
    $eventVenue = $data["event_venue"];
    $eventDescription = $data["event_description"];

    $sql = "INSERT INTO events (event_name, event_date, event_time, event_venue, event_description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendResponse(500, [
            "success" => false,
            "message" => "Unable to prepare event insertion."
        ]);
    }

    $stmt->bind_param("sssss", $eventName, $eventDate, $eventTime, $eventVenue, $eventDescription);
    if (!$stmt->execute()) {
        $stmt->close();
        sendResponse(500, [
            "success" => false,
            "message" => "Unable to add event. Check the database columns."
        ]);
    }

    $eventId = $conn->insert_id;
    $stmt->close();

    sendResponse(201, [
        "success" => true,
        "message" => "Event added successfully.",
        "event_id" => $eventId
    ]);
}

// PUT - Update event
if ($method === "PUT") {
    $data = getInputData();
    $eventId = filter_var($data["event_id"] ?? null, FILTER_VALIDATE_INT);
    if (!$eventId || $eventId < 1) {
        sendResponse(400, [
            "success" => false,
            "message" => "A valid event_id is required."
        ]);
    }

    $validData = validateEvent($data);
    $eventName = $validData["event_name"];
    $eventDate = $validData["event_date"];
    $eventTime = $validData["event_time"] !== "" ? $validData["event_time"] : null;
    $eventVenue = $validData["event_venue"];
    $eventDescription = $validData["event_description"];

    $sql = "UPDATE events SET event_name = ?, event_date = ?, event_time = ?, event_venue = ?, event_description = ? WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendResponse(500, [
            "success" => false,
            "message" => "Unable to prepare event update."
        ]);
    }

    $stmt->bind_param("sssssi", $eventName, $eventDate, $eventTime, $eventVenue, $eventDescription, $eventId);
    if (!$stmt->execute()) {
        $stmt->close();
        sendResponse(500, [
            "success" => false,
            "message" => "Unable to update event."
        ]);
    }

    $affectedRows = $stmt->affected_rows;
    $stmt->close();

    if ($affectedRows === 0) {
        $check = $conn->prepare("SELECT event_id FROM events WHERE event_id = ?");
        $check->bind_param("i", $eventId);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();
        if (!$exists) {
            sendResponse(404, [
                "success" => false,
                "message" => "Event not found."
            ]);
        }
    }

    sendResponse(200, [
        "success" => true,
        "message" => "Event updated successfully."
    ]);
}

// DELETE - Remove event
if ($method === "DELETE") {
    $eventId = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT);
    if (!$eventId || $eventId < 1) {
        sendResponse(400, [
            "success" => false,
            "message" => "A valid event ID is required in ?id=."
        ]);
    }

    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    if (!$stmt) {
        sendResponse(500, [
            "success" => false,
            "message" => "Unable to prepare event deletion."
        ]);
    }

    $stmt->bind_param("i", $eventId);
    if (!$stmt->execute()) {
        $stmt->close();
        sendResponse(500, [
            "success" => false,
            "message" => "Unable to delete event."
        ]);
    }

    $deleted = $stmt->affected_rows;
    $stmt->close();

    if ($deleted === 0) {
        sendResponse(404, [
            "success" => false,
            "message" => "Event not found."
        ]);
    }

    sendResponse(200, [
        "success" => true,
        "message" => "Event deleted successfully."
    ]);
}

header("Allow: GET, POST, PUT, DELETE");
sendResponse(405, [
    "success" => false,
    "message" => "Method not allowed."
]);
?>
