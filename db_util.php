<?php
// db_util.php

require_once 'db_settings.php';
include_once 'db_init.php';

function db_connect() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

function db_disconnect(&$pdo) {
    $pdo = null;
}

function db_query($sql, $params = []) {
    $pdo = db_connect();
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    } finally {
        db_disconnect($pdo);
    }
}

function db_execute($sql, $params = []) {
    $pdo = db_connect();
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        die("Execution failed: " . $e->getMessage());
    } finally {
        db_disconnect($pdo);
    }
}

function db_insert($sql, $params = []) {
    $pdo = db_connect();
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        die("Insertion failed: " . $e->getMessage());
    } finally {
        db_disconnect($pdo);
    }
}

function db_insert_2($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $pdo->lastInsertId(); // Get the last inserted ID
    } catch (PDOException $e) {
        error_log($e->getMessage());
        die("Insertion failed: " . $e->getMessage());
    }
}


// Utility to fetch single row data
function get_data($table, $column, $key) {
    $pdo = db_connect();

    try {
        $sql = "SELECT * FROM $table WHERE $column = :key";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':key', $key);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;

    } catch (PDOException $e) {
        die("Data retrieval failed: " . $e->getMessage());
    } finally {
        db_disconnect($pdo);
    }
}

// Utility to fetch multiple rows based on multiple columns and keys
function get_datas($table, $columns, $keys) {
    $pdo = db_connect();

    // Build WHERE clause dynamically based on the number of columns/keys
    $where_clause = [];
    foreach ($columns as $index => $column) {
        $where_clause[] = "$column = :key$index";
    }
    $where_clause = implode(' AND ', $where_clause); // Joins conditions with 'AND'

    try {
        $sql = "SELECT * FROM $table WHERE $where_clause";
        $stmt = $pdo->prepare($sql);

        // Bind each key to its corresponding placeholder
        foreach ($keys as $index => $key) {
            $stmt->bindParam(":key$index", $key);
        }

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $results;

    } catch (PDOException $e) {
        die("Data retrieval failed: " . $e->getMessage());
    } finally {
        db_disconnect($pdo);
    }
}

function db_last_insert_id() {
    $pdo = db_connect();
    return $pdo->lastInsertId();
}

?>