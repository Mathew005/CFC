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

// Check if participant ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Participant ID is required']);
    exit;
}

$participantId = $_GET['id'];

try {
    // Get participant's registrations
    $sql = "SELECT DISTINCT 
                r.RID,
                p.PID as programId,
                p.PName as programName,
                p.PType as category,
                p.PImage as image,
                p.PTime as time,
                p.PLocation as venue,
                p.PDecription as rules,
                p.Fee as amountPaid,
                p.PStartDate as date,
                e.EID as eventId,
                e.EName as eventName,
                e.ELocation as location,
                o.OInstitute as institute,
                o.OGPS as gps,
                p.PDF as pdf,
                r.ParticipantName,
                r.AdditionParticipantNames
            FROM Registrations r
            JOIN Programs p ON r.PID = p.PID
            JOIN Events e ON p.EID = e.EID
            JOIN Organizers o ON e.OID = o.OID
            WHERE r.ParticipantID = :participantId";
    
    $registrations = db_query($sql, [':participantId' => $participantId]);

    // Get participant's bookmarked programs
    $participant = get_data('Participants', 'PID', $participantId);
    $bookmarkedProgramIds = json_decode($participant['PBookMarkProgram'], true) ?? [];
    $bookmarkedEventIds = json_decode($participant['PBookMarkEvent'], true) ?? [];

    // Get bookmarked programs data
    $sql = "SELECT 
                p.PID as programId,
                p.PName as title,
                p.PImage as image,
                p.PStartDate as date,
                e.ELocation as location
            FROM Programs p
            JOIN Events e ON p.EID = e.EID
            WHERE p.PID IN (" . implode(',', array_map('intval', $bookmarkedProgramIds)) . ")";
    
    $bookmarkedPrograms = !empty($bookmarkedProgramIds) ? db_query($sql, []) : [];

    // Get bookmarked events data
    $sql = "SELECT 
                e.EID as eventId,
                e.EName as title,
                e.EImage as image,
                e.EStartDate as date,
                e.ELocation as location
            FROM Events e
            WHERE e.EID IN (" . implode(',', array_map('intval', $bookmarkedEventIds)) . ")";
    
    $bookmarkedEvents = !empty($bookmarkedEventIds) ? db_query($sql, []) : [];

    // Process registrations data
    $processedRegistrations = [];
    foreach ($registrations as $index => $reg) {
        // Process members array
        $members = [$reg['ParticipantName']];
        if ($reg['AdditionParticipantNames']) {
            $additionalMembers = array_map('trim', explode(',', $reg['AdditionParticipantNames']));
            $members = array_merge($members, $additionalMembers);
        }

        $processedRegistrations[] = [
            'id' => $index,
            // 'programId' => 'P' . str_pad($reg['programId'], 3, '0', STR_PAD_LEFT),
            'programId' => $reg['programId'],
            'image' => $img_host . $reg['image'],
            'programName' => $reg['programName'],
            'eventId' => $reg['eventId'],
            'eventName' => $reg['eventName'],
            'institute' => $reg['institute'],
            'gpsLink' => $reg['gps'],
            'pdf' => $img_host . $reg['pdf'],
            'date' => date('Y-m-d', strtotime($reg['date'])),
            // 'registrationId' => substr($reg['eventName'], 0, 2) . date('Y', strtotime($reg['date'])) . '-' . 
            //                    str_pad($reg['programId'], 3, '0', STR_PAD_LEFT),
            'registrationId' => $reg['RID'],
            'category' => ucwords($reg['category']),
            'rules' => $reg['rules'],
            'location' => $reg['location'],
            'venue' => $reg['venue'],
            'time' => date('h:i A', strtotime($reg['time'])),
            'members' => $members,
            'amountPaid' => floatval($reg['amountPaid'])
        ];
    }

    // Process bookmarked programs
    $processedBookmarkedPrograms = [];
    foreach ($bookmarkedPrograms as $index => $prog) {
        $processedBookmarkedPrograms[] = [
            'id' => $index,
            'programId' => ['programId'],
            'image' => $img_host . $prog['image'],
            'title' => $prog['title'],
            'location' => $prog['location'],
            'date' => date('Y-m-d', strtotime($prog['date']))
        ];
    }

    // Process bookmarked events
    $processedBookmarkedEvents = [];
    foreach ($bookmarkedEvents as $index => $event) {
        $processedBookmarkedEvents[] = [
            'id' => $index,
            'eventId' => $event['eventId'],
            'image' => $img_host . $event['image'],
            'title' => $event['title'],
            'date' => date('Y-m-d', strtotime($event['date'])),
            'location' => $event['location']
        ];
    }

    // Prepare final response
    $response = [
        'registrations' => $processedRegistrations,
        'bookmarkedPrograms' => $processedBookmarkedPrograms,
        'bookmarkedEvents' => $processedBookmarkedEvents
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>