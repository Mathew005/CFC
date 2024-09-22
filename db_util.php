<?php
// db_util.php

require_once 'db_settings.php';

function db_connect() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo; // Return the PDO object
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
?>
