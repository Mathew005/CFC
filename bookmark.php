<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set headers for the response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection
include_once 'db_util.php';

// Get request parameters
$userId = $_GET['userId'] ?? null;
$type = $_GET['type'] ?? null; // "event" or "program"
$action = $_GET['action'] ?? null; // "add", "remove", or "check"
$bookMarkId = $_GET['bookMarkId'] ?? null;

// Validate inputs
if (!$userId || !$type || !$action || !$bookMarkId) {
    echo json_encode(["success" => false, "message" => "Missing required parameters."]);
    exit;
}

if (!in_array($type, ['event', 'program']) || !in_array($action, ['add', 'remove', 'check'])) {
    echo json_encode(["success" => false, "message" => "Invalid type or action."]);
    exit;
}

try {
    $column = $type === "event" ? "PBookMarkEvent" : "PBookMarkProgram";

    // Fetch the current bookmarks for the user
    $participant = get_data("Participants", "PID", $userId);

    if (!$participant) {
        echo json_encode(["success" => false, "message" => "User not found."]);
        exit;
    }

    $currentBookmarks = json_decode($participant[$column], true) ?? [];

    // Handle "check" action
    if ($action === "check") {
        $isBookmarked = in_array($bookMarkId, $currentBookmarks);
        echo json_encode([
            "success" => true,
            "isBookmarked" => $isBookmarked
        ]);
        exit;
    }

    // Add or remove logic (if applicable)
    if ($action === "add") {
        if (!in_array($bookMarkId, $currentBookmarks)) {
            $currentBookmarks[] = $bookMarkId;
        }
    } elseif ($action === "remove") {
        $currentBookmarks = array_filter($currentBookmarks, function ($id) use ($bookMarkId) {
            return $id !== $bookMarkId;
        });
    }

    // Update the database
    $updatedBookmarks = json_encode(array_values($currentBookmarks));
    $pdo = db_connect();

    $stmt = $pdo->prepare("UPDATE Participants SET $column = :bookmarks WHERE PID = :userId");
    $stmt->bindParam(":bookmarks", $updatedBookmarks);
    $stmt->bindParam(":userId", $userId);
    $stmt->execute();

    // Respond with success
    echo json_encode([
        "success" => true,
        "message" => "Bookmark updated successfully.",
        "bookmarks" => $currentBookmarks
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
