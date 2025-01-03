<?php

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'db_util.php';

// Log the incoming request method
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

// Handle POST method
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    // Log the raw input data
    $input = json_decode(file_get_contents("php://input"), true);
    error_log("Raw POST Data: " . json_encode($input));

    if (isset($input['table'], $input['data'])) {
        $table = $input['table'];
        $data = $input['data'];

        // Prepare the SQL statement for insertion
        $columns = implode(", ", array_keys($data));
        $placeholders = ':' . implode(", :", array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $params = [];
        
        foreach ($data as $key => $value) {
            $params[":$key"] = $value;
        }

        // Log the action details
        error_log("INSERT Action: Table: $table, Data: " . json_encode($data));

        // Create the PDO connection
        $pdo = db_connect(); 

        try {
            // Execute the insert
            $result = db_insert_2($pdo, $sql, $params);
            
            // Get the last inserted ID
            $lastId = $pdo->lastInsertId();

            if ($result > 0) {
                echo json_encode(['success' => true, 'insertId' => $lastId]);
                error_log("Insert successful into table: $table, New ID: $lastId");
            } else {
                echo json_encode(['success' => false, 'message' => 'Insert failed']);
                error_log("Insert failed into table: $table");
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            error_log("Database error: " . $e->getMessage());
        } finally {
            db_disconnect($pdo); // Disconnect after operation
        }
    } else {
        echo json_encode(['error' => 'Missing required parameters']);
        error_log("Error: Missing required parameters in POST request");
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
    error_log("Error: Invalid request method");
}

?>
