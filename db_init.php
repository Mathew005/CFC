<?php
// db_init.php

require_once 'db_settings.php';
require_once 'db_util.php';

function db_create_database() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create the database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_general_ci";
        $pdo->exec($sql);
        // echo "Database created or already exists.<br>";

    } catch (PDOException $e) {
        die("Database creation failed: " . $e->getMessage());
    } finally {
        $pdo = null;
    }
}

function db_create_tables() {
    $pdo = db_connect(); // Use the db_connect function to establish a connection

    // Create users table
    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            user_type ENUM('participant', 'organizer') NOT NULL,
            full_name VARCHAR(255),
            organization_name VARCHAR(255),
            address TEXT,
            contact_number VARCHAR(20),
            country_code VARCHAR(5),
            interests TEXT,
            website VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    try {
        $pdo->exec($sql);
        // echo "Table `users` created or already exists.<br>";
    } catch (PDOException $e) {
        die("Table creation failed: " . $e->getMessage());
    } finally {
        db_disconnect($pdo); // Close the database connection
    }
}

// Initialize the database and tables
db_create_database();
db_create_tables();
?>
