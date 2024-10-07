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

    } catch (PDOException $e) {
        die("Database creation failed: " . $e->getMessage());
    } finally {
        $pdo = null;
    }
}

function db_create_tables_from_schemas($schema_dir) {
    $pdo = db_connect();
    $schema_files = scandir($schema_dir);
    $pending_schemas = [];

    // Loop to process schemas until no pending foreign key errors remain
    $max_attempts = 5; // Limit retries to avoid infinite loops
    $attempt = 0;

    while ($attempt < $max_attempts) {
        $retry = false;

        foreach ($schema_files as $schema_file) {
            if ($schema_file === '.' || $schema_file === '..') {
                continue;
            }

            $schema_path = $schema_dir . '/' . $schema_file;
            $schema_sql = file_get_contents($schema_path);

            try {
                // Attempt to execute the schema creation
                $pdo->exec($schema_sql);
                $_SESSION['display'] .= "Successfully created table from $schema_file<br>";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                    // Foreign key constraint issue, retry later
                    $_SESSION['display'] .= "Foreign key constraint issue: $schema_file. Will retry later.<br>";
                    $retry = true;
                    $pending_schemas[] = $schema_file;
                } else {
                    // Handle other errors
                    $_SESSION['display'] .= "Error creating table from $schema_file: " . $e->getMessage() . "<br>";
                }
            }
        }

        if (!$retry) {
            // If there are no pending schemas, exit the loop
            break;
        }

        // Retry only the pending schemas in the next round
        $schema_files = $pending_schemas;
        $pending_schemas = [];  // Reset for next iteration
        $attempt++;
    }

    if ($retry) {
        $_SESSION['display'] .= "Failed to create some tables due to unresolved foreign key constraints.<br>";
    }
}

// Initialize the database and tables
db_create_database();
db_create_tables_from_schemas(__DIR__ . '/schemas');
?>
