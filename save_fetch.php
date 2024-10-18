<?php

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'db_util.php';

// Log the incoming request method
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

// Handle GET and POST methods
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Log incoming GET parameters
    error_log("GET Parameters: " . json_encode($_GET));

    // Retrieve data based on table, id, and columns
    if (isset($_GET['table'], $_GET['id'], $_GET['columnIdentifier'], $_GET['columnTargets'])) {
        $table = $_GET['table'];
        $id = $_GET['id'];
        $columnIdentifier = $_GET['columnIdentifier'];
        $columnTargets = explode(',', $_GET['columnTargets']); // Split comma-separated column names

        // Fetch data from the specified columns
        $data = [];
        foreach ($columnTargets as $columnTarget) {
            $result = get_data_column($table, $columnIdentifier, $id, $columnTarget);
            if ($result) {
                $data[$columnTarget] = $result[$columnTarget];
            } else {
                error_log("No data found for table: $table, id: $id, column: $columnTarget");
            }
        }

        // Return the results as JSON
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Missing required parameters']);
        error_log("Error: Missing required parameters in GET request");
    }

} elseif ($method == 'POST') {
    // Log the raw input data
    $input = json_decode(file_get_contents("php://input"), true);
    error_log("Raw POST Data: " . json_encode($input));

    if (isset($input['table'], $input['identifier'], $input['identifierColumn'], $input['target'], $input['data'])) {
        $table = $input['table'];
        $id = $input['identifier'];
        $columnIdentifier = $input['identifierColumn'];
        $columnTarget = $input['target'];
        $data = $input['data'];

        // Log the action details
        error_log("POST Action: Table: $table, ID: $id, Column: $columnTarget");

        // Save the data to the specific column
        $sql = "UPDATE $table SET $columnTarget = :data WHERE $columnIdentifier = :id";
        $params = [
            ':data' => $data,
            ':id' => $id
        ];
        $result = db_execute($sql, $params);

        if ($result > 0) {
            echo json_encode(['success' => true]);
            error_log("Update successful for ID: $id");
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed']);
            error_log("Update failed for ID: $id");
        }
    } else {
        echo json_encode(['error' => 'Missing required parameters']);
        error_log("Error: Missing required parameters in POST request");
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
    error_log("Error: Invalid request method");
}

// Utility function to fetch single column data
function get_data_column($table, $columnIdentifier, $id, $columnTarget) {
    $sql = "SELECT $columnTarget FROM $table WHERE $columnIdentifier = :id";
    $params = [':id' => $id];
    $result = db_query($sql, $params);

    if (count($result) > 0) {
        return $result[0]; // Return the first result
    } else {
        error_log("No results found for table: $table, ID: $id, column: $columnTarget");
        return null;
    }
}
