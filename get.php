<?php

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'db_util.php';

// Log the incoming request method
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

// Handle GET method
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Check if both 'table' and 'columns' parameters are provided
    if (isset($_GET['table']) && isset($_GET['columns'])) {
        $table = $_GET['table']; // Table name from GET parameter
        $columns = $_GET['columns']; // Comma-separated column names

        // Validate and sanitize columns (just a basic example, could be expanded)
        $columnsArray = explode(',', $columns); // Split columns by commas
        $columnsArray = array_map('trim', $columnsArray); // Trim any whitespace

        // Log the action details
        error_log("GET Action: Fetching data from table: $table, Columns: " . implode(', ', $columnsArray));

        // Create the PDO connection
        $pdo = db_connect();

        try {
            // Prepare the SQL query to select specific columns from the specified table
            $columnsList = implode(', ', array_map(function($col) {
                return "`$col`"; // Escape column names with backticks
            }, $columnsArray));

            // Create the SQL query
            $sql = "SELECT $columnsList FROM `$table`"; // Use backticks to escape table and column names

            // Execute the query
            $stmt = $pdo->query($sql);

            // Fetch all results
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                echo json_encode(['success' => true, 'data' => $results]);
                error_log("Data fetched successfully from table: $table");
            } else {
                echo json_encode(['success' => false, 'message' => 'No data found']);
                error_log("No data found in table: $table");
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            error_log("Database error: " . $e->getMessage());
        } finally {
            db_disconnect($pdo); // Disconnect after operation
        }
    } else {
        echo json_encode(['error' => 'Missing table or columns parameter']);
        error_log("Error: Missing 'table' or 'columns' parameter in GET request");
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
    error_log("Error: Invalid request method");
}

?>
