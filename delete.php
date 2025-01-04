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

// Include database utilities
include_once 'db_util.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    // Check if all required fields are provided
    if (empty($input['table']) || empty($input['id']) || empty($input['column'])) {
        throw new Exception("Missing required parameters: table, id, or column.");
    }

    // Extract parameters
    $table = htmlspecialchars($input['table']);
    $id = $input['id'];
    $column = htmlspecialchars($input['column']);

    // Validate table and column names (basic validation to prevent SQL injection)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
        throw new Exception("Invalid table or column name.");
    }

    // Delete query
    $sql = "DELETE FROM $table WHERE $column = :id";
    $params = [':id' => $id];
    
    // Execute query
    $deletedRows = db_execute($sql, $params);

    if ($deletedRows > 0) {
        echo json_encode(["success" => true, "message" => "Row deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "No rows deleted. Check if the ID exists."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
