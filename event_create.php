<?php
// event_create.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit();
}

require_once 'db_util.php';

// Function to validate and sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the raw POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate and sanitize input data
    $eventName = sanitize_input($data['eventName']);
    $festTypes = implode(',', array_map('sanitize_input', $data['festTypes']));
    $description = sanitize_input($data['description']);
    $isMultiDay = $data['isMultiDay'] ? 1 : 0;
    $startDate = $isMultiDay ? $data['dateRange']['from'] : $data['date'];
    $endDate = $isMultiDay ? $data['dateRange']['to'] : $data['date'];
    $location = sanitize_input($data['location']);
    $googleMapsLink = sanitize_input($data['googleMapsLink']);
    $eventImage = $data['croppedImage']; // Assuming this is a base64 encoded string

    // Prepare the SQL statement
    $sql = "INSERT INTO Events (EventName, EventType, EventDec, EventStartDate, EventEndDate, EventLocation, EventGPS, EventImage) 
            VALUES (:eventName, :festTypes, :description, :startDate, :endDate, :location, :googleMapsLink, :eventImage)";

    // Execute the query
    try {
        $eventId = db_insert($sql, [
            ':eventName' => $eventName,
            ':festTypes' => $festTypes,
            ':description' => $description,
            ':startDate' => $startDate,
            ':endDate' => $endDate,
            ':location' => $location,
            ':googleMapsLink' => $googleMapsLink,
            ':eventImage' => $eventImage
        ]);

        // Insert coordinators
        foreach ($data['coordinators'] as $coordinator) {
            $coordinatorSql = "INSERT INTO Contacts (Name, Phone, Email, IsFaculty) VALUES (:name, :phone, :email, :isFaculty)";
            $coordinatorId = db_insert($coordinatorSql, [
                ':name' => sanitize_input($coordinator['name']),
                ':phone' => sanitize_input($coordinator['phone']),
                ':email' => sanitize_input($coordinator['email']),
                ':isFaculty' => $coordinator['isFaculty'] ? 1 : 0
            ]);

            // Link coordinator to event
            $linkSql = "INSERT INTO EventCoordinators (EventID, ContactID) VALUES (:eventId, :coordinatorId)";
            db_execute($linkSql, [':eventId' => $eventId, ':coordinatorId' => $coordinatorId]);
        }

        // Send success response
        http_response_code(201);
        echo json_encode(["message" => "Event created successfully", "eventId" => $eventId]);
    } catch (Exception $e) {
        // Send error response
        http_response_code(500);
        echo json_encode(["error" => "Failed to create event: " . $e->getMessage()]);
    }
} else {
    // If not a POST request, return method not allowed
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>