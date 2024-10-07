<?php
// index.php

// Ensure the database and tables are created when the server starts
require_once 'db_init.php'; // This will create/check the database and tables

$_SESSION['display'] .= "<br><b>Server is running, and the database is initialized.<b>";

echo $_SESSION['display'];
?>