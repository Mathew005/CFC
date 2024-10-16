<?php
// participant.php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
// Allow specific HTTP methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Preflight request, just respond with 200
    http_response_code(200);
    exit();
}

include_once 'db_init.php';
include_once 'db_util.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $participantId = $_GET['id'];
    $data = get_data('Participants', 'ParticipantID', $participantId);
    
    if ($data) {
        echo json_encode([
            'ParticipantID' => $data['ParticipantID'],
            'ParticipantName' => $data['ParticipantName'],
            'ParticipantEmail' => $data['ParticipantEmail'],
            'ParticipantPhone' => $data['ParticipantPhone'],
            'ParticipantImage' => $data['ParticipantImage'],
            'ParticipantCourse' => $data['ParticipantCourse'],
            'ParticipantDepartment' => $data['ParticipantDepartment'],
            'ParticipantInstitute' => $data['ParticipantInstitute'],
            'ParticipantLocation' => $data['ParticipantLocation'],
            'ParticipantInterests' => $data['ParticipantInterests'],
        ]);
    } else {
        echo json_encode(['error' => 'Participant not found']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['ParticipantID'])) {
        $query = "UPDATE Participants SET 
            ParticipantName = ?, 
            ParticipantEmail = ?, 
            ParticipantPhone = ?, 
            ParticipantPassword = ?, 
            ParticipantWebsite = ?, 
            ParticipantAddress = ?, 
            ParticipantCourse = ?, 
            ParticipantDepartment = ?, 
            ParticipantInstitute = ?, 
            ParticipantLocation = ?, 
            ParticipantInterests = ? 
            WHERE ParticipantID = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssssssssssi', 
            $data['ParticipantName'], 
            $data['ParticipantEmail'], 
            $data['ParticipantPhone'], 
            $data['ParticipantPassword'], 
            $data['ParticipantWebsite'], 
            $data['ParticipantAddress'], 
            $data['ParticipantCourse'], 
            $data['ParticipantDepartment'], 
            $data['ParticipantInstitute'], 
            $data['ParticipantLocation'], 
            $data['ParticipantInterests'], 
            $data['ParticipantID']
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update participant data']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
