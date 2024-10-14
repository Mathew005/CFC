<?php
// index.php

// Ensure the database and tables are created when the server starts
require_once 'db_init.php'; // This will create/check the database and tables

// Check if session is already started to avoid the warning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_util.php'; // Includes the database utility functions

// Fetch the list of tables from the database
function get_table_list() {
    $pdo = db_connect();
    try {
        $sql = "SHOW TABLES";
        $tables = db_query($sql);
        return $tables;
    } catch (PDOException $e) {
        die("Error fetching table list: " . $e->getMessage());
    } finally {
        db_disconnect($pdo);
    }
}

// Fetch the data of a specific table
function get_table_data($table) {
    $pdo = db_connect();
    try {
        $sql = "SELECT * FROM $table";
        $data = db_query($sql);
        return $data;
    } catch (PDOException $e) {
        die("Error fetching data from table: " . $e->getMessage());
    } finally {
        db_disconnect($pdo);
    }
}

// Get the list of tables to display
$tables = get_table_list();

// Handle if a table is selected
$selected_table = isset($_GET['table']) ? $_GET['table'] : null;
$contents = null;
if ($selected_table) {
    $contents = get_table_data($selected_table);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }
        .sidebar {
            width: 200px;
            border-right: 2px solid #000;
            padding-right: 20px;
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px;
            cursor: pointer;
        }
        .sidebar ul li:hover {
            background-color: #f0f0f0;
        }
        .table-container {
            flex: 1;
        }
        table {
            width: 100%;
            border: 2px solid black;
            border-collapse: collapse;
        }
        th, td {
            border: 2px solid black;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        td {
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Sidebar: List of Tables -->
    <div class="sidebar">
        <h3>Tables</h3>
        <ul>
            <?php foreach ($tables as $table): ?>
                <li><a href="?table=<?php echo $table['Tables_in_' . DB_NAME]; ?>"><?php echo $table['Tables_in_' . DB_NAME]; ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Main Content: Table Data -->
    <div class="table-container">
        <?php if ($selected_table && $contents): ?>
            <h3>Contents of Table: <?php echo htmlspecialchars($selected_table); ?></h3>
            <table>
                <thead>
                    <tr>
                        <?php if (count($contents) > 0): ?>
                            <?php foreach (array_keys($contents[0]) as $column): ?>
                                <th><?php echo htmlspecialchars($column); ?></th>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <th>No data available</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contents as $row): ?>
                        <tr>
                            <?php foreach ($row as $column => $value): ?>
                                <td><?php echo htmlspecialchars($value); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($selected_table): ?>
            <p>No data available in the selected table.</p>
        <?php else: ?>
            <p>Please select a table to view its contents.</p>
        <?php endif; ?>
    </div>

</body>
</html>
