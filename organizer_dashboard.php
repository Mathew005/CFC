<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set HTTP headers for the response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include required files for database initialization and utilities
include_once 'db_init.php';
include_once 'db_util.php';

// Define the image host
$img_host = DB_PROTOCOL . DB_HOST . "/" . DB_NAME . "/";

// Function to determine the event status
function determine_event_status($startDate, $endDate, $cancelled) {
    $currentDate = new DateTime();
    $startDate = new DateTime($startDate);
    $endDate = new DateTime($endDate);

    if ($cancelled) {
        return "cancelled";
    } elseif ($currentDate < $startDate) {
        return "scheduled";
    } elseif ($currentDate >= $startDate && $currentDate <= $endDate) {
        return "commencing";
    } else {
        return "concluded";
    }
}

// Fetch events from the database
$sql = "SELECT 
            EID as id,
            EName as name,
            EStartDate as date,
            ELocation as location,
            Published as published,
            Cancelled as cancelled,
            EImage as image,
            EDecription as description,
            EndDate
        FROM Events";
$events = db_query($sql);

// Process and format the events data
$processed_events = [];
foreach ($events as $event) {
    $status = determine_event_status($event['date'], $event['EndDate'], $event['cancelled']);
    $processed_events[] = [
        "id" => $event['id'],
        "name" => $event['name'],
        "date" => $event['date'],
        "location" => $event['location'],
        "status" => $status,
        "view" => $event['published'] ? "published" : "staged",
        "image" => $img_host . $event['image'] . "?height=200&width=200",
        "description" => $event['description']
    ];
}

// Output the events data as JSON
echo json_encode($processed_events, JSON_PRETTY_PRINT);
?>
