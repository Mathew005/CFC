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

    // Log the incoming type for debugging
    error_log("Received file upload request. Type: " . $type);

    // Check if the type is valid
    if (array_key_exists($type, $targetDirs)) {
        $targetDir = $targetDirs[$type];

        // Ensure the target directory exists
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                error_log("Failed to create directory: " . $targetDir);
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to create target directory"
                ]);
                exit; // Stop further execution
            }
        }

        // Generate a unique filename
        if ($type == 'docs') {
            $uniqueFileName = uniqid($type . '_', true) . ".pdf"; // Consider changing the extension based on file type
        } else {
            $uniqueFileName = uniqid($type . '_', true) . ".jpg"; // Consider changing the extension based on file type
        }
        
        $targetFile = $targetDir . $uniqueFileName;

        // Log the target file location for debugging
        error_log("Target file: " . $targetFile);

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            // Log successful upload
            error_log("File uploaded successfully: " . $targetFile);

            echo json_encode([
                "success" => true,
                "url" => $targetFile
            ]);
        } else {
            // Log the error if file move fails
            error_log("Failed to move uploaded file. Temp name: " . $_FILES['file']['tmp_name'] . " Target file: " . $targetFile);

            echo json_encode([
                "success" => false,
                "message" => "Failed to save file"
            ]);
        }
    } else {
        // Handle invalid type
        error_log("Invalid file type received: " . $type);

        echo json_encode([
            "success" => false,
            "message" => "Invalid file type"
        ]);
    }
} else {
    // Handle missing fields or invalid request
    error_log("Invalid request. Missing fields or invalid method.");

    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
}
?>
