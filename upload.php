<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
// Allow specific HTTP methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$targetAvatarDir = "files/imgs/avatars/";

// Check if the request is a POST request and contains the 'file' and 'type' fields
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type']) && isset($_FILES['file'])) {
    $type = $_POST['type'];
    
    // Check if the type is 'avatar'
    if ($type === 'avatar') {
        // Ensure the directory exists
        if (!is_dir($targetAvatarDir)) {
            mkdir($targetAvatarDir, 0755, true);
        }

        // Generate a unique filename to avoid conflicts
        $uniqueFileName = uniqid('avatar_', true) . ".jpg";
        $targetFile = $targetAvatarDir . $uniqueFileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            // Return success response with the file path
            echo json_encode([
                "success" => true,
                "url" => $targetFile
            ]);
        } else {
            // Handle failure to move the file
            echo json_encode([
                "success" => false,
                "message" => "Failed to save file"
            ]);
        }
    } else {
        // Handle invalid type
        echo json_encode([
            "success" => false,
            "message" => "Invalid file type"
        ]);
    }
} else {
    // Handle missing fields or invalid request
    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
}
?>
