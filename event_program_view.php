<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set HTTP headers for the response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include required files for database initialization and utilities
include_once 'db_init.php';
include_once 'db_util.php';

// Define the image host
$img_host = DB_PROTOCOL . DB_HOST . "/" . DB_NAME . "/";

// Log the incoming request for debugging
error_log("Request received with id: " . $_GET['id']);

// Try-catch block for error handling
try {
    // Check if EID parameter is set
    if (!isset($_GET['id'])) {
        throw new Exception("Event ID (EID) parameter is missing.");
    }

    // Get EID parameter
    $eid = $_GET['id'];
    error_log("Fetching event data for EID: " . $eid); // Log EID

    // Fetch the event based on EID
    $event = db_query("SELECT * FROM Events WHERE EID = ?", [$eid]);

    if (empty($event)) {
        throw new Exception("Event not found for the provided EID.");
    }

    $event = $event[0]; // Single event data
    error_log("Event found: " . json_encode($event)); // Log event data for debugging

    $response = [];

    // Fetch programs for the event
    $programs = db_query("SELECT * FROM Programs WHERE EID = ?", [$event['EID']]);
    error_log("Programs found: " . json_encode($programs)); // Log programs data

    // Structure programs data
    $programsData = [];
    foreach ($programs as $program) {
        // Log each program being processed
        error_log("Processing program: " . json_encode($program));

        // Fetch coordinators for the program
        $coordinators = db_query("SELECT * FROM Coordinators WHERE CID = ?", [$program['CID']]);
        error_log("Coordinators found for program: " . json_encode($coordinators)); // Log coordinators data

        $coordinatorsData = [];
        foreach ($coordinators as $coordinator) {
            $coordinatorsData[] = [
                "name" => $coordinator['Name1'],
                "email" => $coordinator['Email1'],
                "phone" => $coordinator['Phone1'],
                "isFaculty" => (bool)$coordinator['Faculty1'],
            ];
        }

        $programsData[] = [
            "id" => (string)$program['PID'],
            "name" => $program['PName'],
            "category" => ucwords($program['PType']),
            "date" => date('F j, Y', strtotime($program['PStartDate'])),
            "time" => $program['PTime'],
            "image" => $img_host . $program['PImage'],
            "rules" => $program['PDecription'],
            "regFees" => (int)$program['Fee'],
            "isTeamEvent" => $program['Min'] > 1,
            "pdf" => $img_host . $program['PDF'],
            "minParticipants" => (int)$program['Min'],
            "maxParticipants" => (int)$program['Max'],
            "coordinators" => $coordinatorsData,
            "registrationOpen" => (bool)$program['Open'],
        ];
    }

    // Fetch the event organizer
    $organizer = db_query("SELECT * FROM Organizers WHERE OID = ?", [$event['OID']]);
    error_log("Organizer found: " . json_encode($organizer)); // Log organizer data

    $organizerData = $organizer ? [
        "id" => (string)$organizer[0]['OID'],
        "name" => $organizer[0]['OName'],
        "email" => $organizer[0]['OEmail'],
        "phone" => $organizer[0]['OPhone'],
        "image" => $img_host . $organizer[0]['OImage'],
        "website" => $organizer[0]['OWebsite'],
        "address" => $organizer[0]['OAddress'],
        "location" => $organizer[0]['OLocation'],
        "institute" => $organizer[0]['OInstitute'], // Correct field
        "gps" => $organizer[0]['OGPS'], // Correct field
    ] : null;

    // Fetch coordinators for the event
    $eventCoordinators = db_query("SELECT * FROM Coordinators WHERE CID = ?", [$event['CID']]);
    error_log("Event coordinators found: " . json_encode($eventCoordinators)); // Log event coordinators data

    $eventCoordinatorsData = [];
    foreach ($eventCoordinators as $coordinator) {
        $eventCoordinatorsData[] = [
            "name" => $coordinator['Name1'],
            "email" => $coordinator['Email1'],
            "phone" => $coordinator['Phone1'],
            "isFaculty" => (bool)$coordinator['Faculty1'],
        ];
    }

    // Structure event data
    $response = [
        "id" => (string)$event['EID'],
        "name" => $event['EName'],
        "institution" => $organizerData['institute'], // Organizer's institute
        "category" => implode(', ', array_map('ucwords', explode(',', $event['EType']))),
        "location" => $event['ELocation'],
        "gpsLink" => $organizerData['gps'], // Organizer's GPS
        "description" => $event['EDecription'],
        "duration" => date('F j, Y', strtotime($event['EStartDate'])) . " - " . date('F j, Y', strtotime($event['EndDate'])),
        "coordinators" => $eventCoordinatorsData,
        "image" => $img_host . $event['EImage'],
        "programs" => $programsData,
        "organizer" => $organizerData,
    ];

    // Send response
    echo json_encode($response, JSON_PRETTY_PRINT);
    error_log("Response sent: " . json_encode($response)); // Log the final response

} catch (Exception $e) {
    // Log error details and send response
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
