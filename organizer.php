
<?php
// organizer.php

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
    $organizerId = $_GET['id'];
    $data = get_data('Organizers', 'OrganizerID', $organizerId);
    
    if ($data) {
        echo json_encode([
            'OrganizerID' => $data['OrganizerID'],
            'OrganizerName' => $data['OrganizerName'],
            'OrganizerEmail' => $data['OrganizerEmail'],
            'OrganizerPhone' => $data['OrganizerPhone'],
            'OrganizerImage' => $data['OrganizerImage'],
            'OrganizerWebsite' => $data['OrganizerWebsite'],
            'OrganizerAddress' => $data['OrganizerAddress'],
            'OrganizerInstitute' => $data['OrganizerInstitute'],
            'OrganizerGPS' => $data['OrganizerGPS'],
        ]);
    } else {
        echo json_encode(['error' => 'Organizer not found']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['OrganizerID'])) {
        $query = "UPDATE Organizers SET 
            OrganizerName = ?, 
            OrganizerEmail = ?, 
            OrganizerPhone = ?, 
            OrganizerPassword = ?, 
            OrganizerWebsite = ?, 
            OrganizerAddress = ?, 
            OrganizerInstitute = ?, 
            OrganizerGPS = ? 
            WHERE OrganizerID = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssssssssi', 
            $data['OrganizerName'], 
            $data['OrganizerEmail'], 
            $data['OrganizerPhone'], 
            $data['OrganizerPassword'], 
            $data['OrganizerWebsite'], 
            $data['OrganizerAddress'], 
            $data['OrganizerInstitute'], 
            $data['OrganizerGPS'], 
            $data['OrganizerID']
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update organizer data']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
