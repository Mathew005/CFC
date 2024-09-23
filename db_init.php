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

    } catch (PDOException $e) {
        die("Database creation failed: " . $e->getMessage());
    } finally {
        $pdo = null;
    }
}

function db_create_tables() {
    $pdo = db_connect();

    // Create participants table with participant_id as primary key
    $sql_participants = "
        CREATE TABLE IF NOT EXISTS participants (
            participant_id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255),
            interests TEXT,
            contact_number VARCHAR(20),
            country_code VARCHAR(5),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    // Create organizers table with organizer_id as primary key
    $sql_organizers = "
        CREATE TABLE IF NOT EXISTS organizers (
            organizer_id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            organization_name VARCHAR(255),
            address TEXT,
            website VARCHAR(255),
            contact_number VARCHAR(20),
            country_code VARCHAR(5),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    try {
        $pdo->exec($sql_participants);
        $pdo->exec($sql_organizers);
    } catch (PDOException $e) {
        die("Table creation failed: " . $e->getMessage());
    } finally {
        db_disconnect($pdo);
    }
}

// Initialize the database and tables
db_create_database();
db_create_tables();
?>
