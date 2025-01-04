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

// Function to fetch and structure the data
try {
    // Check if EID parameter is set
    if (!isset($_GET['id'])) {
        throw new Exception("Event ID (EID) parameter is missing.");
    }

    // Get EID parameter
    $eid = $_GET['id'];

    // Fetch the event based on EID
    $event = db_query("SELECT * FROM Events WHERE EID = ?", [$eid]);

    if (empty($event)) {
        throw new Exception("Event not found for the provided EID.");
    }

    $event = $event[0]; // Single event data

    // Fetch programs for the event
    $programs = db_query("SELECT * FROM Programs WHERE EID = ?", [$event['EID']]);

    // Structure programs data
    $programsData = [];
    foreach ($programs as $program) {
        // Fetch coordinators for the program
        $coordinators = db_query("SELECT * FROM Coordinators WHERE CID = ?", [$program['CID']]);
        $coordinatorsData = [];
        
        // Add up to 4 coordinators, as per the schema
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($coordinators[0]["Name$i"])) {
                $coordinatorsData[] = [
                    "name" => $coordinators[0]["Name$i"],
                    "role" => "Program Coordinator", // Assuming a default role, can be modified
                    "email" => $coordinators[0]["Email$i"],
                    "phone" => $coordinators[0]["Phone$i"],
                ];
            }
        }

        $programsData[] = [
            "id" => (string)$program['PID'],
            "name" => $program['PName'],
            "type" => ucwords($program['PType']),
            "date" => date('Y-m-d', strtotime($program['PStartDate'])),
            "venue" => $program['PLocation'],
            "time" => date('H:i', strtotime($program['PTime'])),
            "regFee" => (float)$program['Fee'],
            "image" => $img_host . $program['PImage'] . "?height=200&width=200",
            "rulesRegulations" => $program['PDecription'],
            "rulesRegulationsFile" => $program['PDF'],
            "isTeamEvent" => $program['Min'] > 1,
            "minParticipants" => (int)$program['Min'],
            "maxParticipants" => (int)$program['Max'],
            "coordinators" => $coordinatorsData,
            "status" => $program['Open'] ? 'open' : 'closed',
        ];
    }

    // Fetch the event coordinator
    $eventCoordinator = db_query("SELECT * FROM Coordinators WHERE CID = ?", [$event['CID']]);
    $eventCoordinatorsData = [];
    
    // Add up to 4 coordinators for the event
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($eventCoordinator[0]["Name$i"])) {
            $eventCoordinatorsData[] = [
                "name" => $eventCoordinator[0]["Name$i"],
                "role" => "Event Coordinator", // Assuming a default role, can be modified
                "email" => $eventCoordinator[0]["Email$i"],
                "phone" => $eventCoordinator[0]["Phone$i"],
            ];
        }
    }

    // Structure event data
    $response = [
        "id" => (string)$event['EID'],
        "title" => $event['EName'],
        "description" => $event['EDecription'],
        "image" => $img_host . $event['EImage'] . "?height=400&width=800",
        "eventType" => ucwords($event['EType']),
        "date" => date('Y-m-d\TH:i:s', strtotime($event['EStartDate'])),
        "location" => $event['ELocation'],
        "coordinators" => $eventCoordinatorsData,
        "programs" => $programsData,
        "status" => 'scheduled', // Assuming the event status is always scheduled
        "view" => 'staged', // Assuming the view status
    ];

    // Send response
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
