<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
// Allow specific HTTP methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$targetDirs = [
    'avatar' => "files/imgs/avatars/",
    'event' => "files/imgs/events/",
    'program' => "files/imgs/programs/",
    'docs' => "files/docs/" // Added the docs directory
];

// Check if the request is a POST request and contains the 'file' and 'type' fields
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type']) && isset($_FILES['file'])) {
    $type = $_POST['type'];

    // Check if the type is valid
    if (array_key_exists($type, $targetDirs)) {
        $targetDir = $targetDirs[$type];

        // Ensure the target directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Generate a unique filename
        $uniqueFileName = uniqid($type . '_', true) . ".jpg"; // Consider changing the extension based on file type
        $targetFile = $targetDir . $uniqueFileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            echo json_encode([
                "success" => true,
                "url" => $targetFile
            ]);
        } else {
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
