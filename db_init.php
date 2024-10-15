<?php
session_start();
$_SESSION['display'] = '';

// db_init.php
require_once 'db_util.php';
require_once 'db_settings.php';

function db_create_database() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create the database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_general_ci";
        $pdo->exec($sql);
        $_SESSION['display'] .= "Database " . DB_NAME . " created or already exists.<br>";

    } catch (PDOException $e) {
        die("Database creation failed: " . $e->getMessage());
    } finally {
        $pdo = null;
    }
}

function db_create_tables_from_schemas($schema_dir) {
    $pdo = db_connect();
    
    // Get schema files sorted in order
    $schema_files = scandir($schema_dir);
    sort($schema_files); // Sort files to ensure the correct order

    foreach ($schema_files as $schema_file) {
        if ($schema_file === '.' || $schema_file === '..') {
            continue;
        }

        $schema_path = $schema_dir . '/' . $schema_file;
        $schema_sql = file_get_contents($schema_path);

        try {
            // Attempt to execute the schema creation
            $pdo->exec($schema_sql);
            $_SESSION['display'] .= "Successfully created table from $schema_file.<br>";
        } catch (PDOException $e) {
            // Handle other errors, such as already existing tables
            if (strpos($e->getMessage(), 'table already exists') !== false) {
                $_SESSION['display'] .= "Table from $schema_file already exists.<br>";
            } else {
                $_SESSION['display'] .= "Error creating table from $schema_file: " . $e->getMessage() . "<br>";
            }
        }
    }
}

// Initialize the database and tables
db_create_database();
db_create_tables_from_schemas(__DIR__ . '/schemas');
?>
