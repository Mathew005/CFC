<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set HTTP headers for the response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include required files for database initialization and utilities
include_once 'db_init.php';
include_once 'db_util.php';

// Check if event ID is provided in URL parameters
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(array("message" => "Event ID is required."));
    exit();
}

$eventId = $_GET['id'];

try {
    $pdo = db_connect();
    
    // Prepare the main query for registrations, joining with Programs and Participants
    $sql = "SELECT 
                r.RID as id,
                r.ParticipantName as name,
                r.ParticipantPhone as contact,
                r.ParticipantEmail as email,
                r.PID as eventId,
                r.RTime as registrationTime,
                r.AdditionParticipantNames as additionalNames,
                r.AdditionParticipantEmail as additionalEmails,
                r.AdditionParticipantPhone as additionalPhones,
                p.PName as eventName,
                p.Min as minMembers,
                p.Max as maxMembers,
                part.PInstitute as collegeName,
                part.PCourse as course,
                part.PDepartment as department
            FROM Registrations r
            LEFT JOIN Programs p ON r.PID = p.PID
            LEFT JOIN Participants part ON part.PID = r.ParticipantID
            WHERE p.EID = :eid";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':eid', $eventId, PDO::PARAM_INT);
    $stmt->execute();
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formattedData = array(
        'events' => array(),
        'registrationData' => array(),
        'groupMembers' => array()
    );
    
    // Get programs (events) details for this main event
    $eventSql = "SELECT 
                    PID as id, 
                    PName as name,
                    CASE 
                        WHEN Min <= 1 AND Max <= 1 THEN 'solo'
                        ELSE 'group'
                    END as type
                 FROM Programs 
                 WHERE EID = :eid";
    $eventStmt = $pdo->prepare($eventSql);
    $eventStmt->bindParam(':eid', $eventId, PDO::PARAM_INT);
    $eventStmt->execute();
    $formattedData['events'] = $eventStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format registration data
    foreach ($registrations as $reg) {
        // Determine event type based on min/max members
        $eventType = ($reg['minMembers'] <= 1 && $reg['maxMembers'] <= 1) ? 'solo' : 'group';
        
        $registration = array(
            'id' => intval($reg['id']),
            'name' => $reg['name'],
            'contact' => $reg['contact'],
            'email' => $reg['email'],
            'eventId' => intval($reg['eventId']),
            'collegeName' => $reg['collegeName'],
            'eventType' => $eventType,
            'participants' => (!empty($reg['additionalNames'])) ? 
                            count(explode(',', $reg['additionalNames'])) + 1 : 1,
            'department' => $reg['department'],
            'course' => $reg['course'],
            'registrationTime' => $reg['registrationTime']
        );
        
        $formattedData['registrationData'][] = $registration;
        
        // Process additional participants if they exist
        if (!empty($reg['additionalNames'])) {
            $names = explode(',', $reg['additionalNames']);
            $emails = explode(',', $reg['additionalEmails']);
            $phones = explode(',', $reg['additionalPhones']);
            
            $groupMembers = array();
            for ($i = 0; $i < count($names); $i++) {
                $groupMembers[] = array(
                    'name' => trim($names[$i]),
                    'contact' => isset($phones[$i]) ? trim($phones[$i]) : '',
                    'email' => isset($emails[$i]) ? trim($emails[$i]) : ''
                );
            }
            
            if (!empty($groupMembers)) {
                $formattedData['groupMembers'][$reg['id']] = $groupMembers;
            }
        }
    }
    
    // Send the response
    http_response_code(200);
    echo json_encode($formattedData);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "message" => "An error occurred while retrieving the data.",
        "error" => $e->getMessage()
    ));
}