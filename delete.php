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

// Include database utilities
include_once 'db_util.php';

try {
    // Get parameters from the query string (GET request)
    $table = isset($_GET['table']) ? htmlspecialchars($_GET['table']) : null;
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $column = isset($_GET['column']) ? htmlspecialchars($_GET['column']) : null;

    // Log the received parameters
    error_log("Received parameters - Table: $table, ID: $id, Column: $column");

    // Check if all required fields are provided
    if (empty($table) || empty($id) || empty($column)) {
        throw new Exception("Missing required parameters: table, id, or column.");
    }

    // Validate table and column names (basic validation to prevent SQL injection)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
        throw new Exception("Invalid table or column name.");
    }

    // Log validation success
    error_log("Table and column names validated successfully.");

    // Delete query
    $sql = "DELETE FROM $table WHERE $column = :id";
    $params = [':id' => $id];
    
    // Log SQL query and parameters
    error_log("SQL Query: $sql, Params: " . print_r($params, true));

    // Execute query
    $deletedRows = db_execute($sql, $params);

    // Log the result of the delete operation
    error_log("Deleted rows: $deletedRows");

    if ($deletedRows > 0) {
        echo json_encode(["success" => true, "message" => "Row deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "No rows deleted. Check if the ID exists."]);
    }
} catch (Exception $e) {
    // Log the exception error
    error_log("Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
