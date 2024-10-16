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
function get_table_list()
{
    $pdo = db_connect();
    try {
        $sql = 'SHOW TABLES';
        $tables = db_query($sql);
        return $tables;
    } catch (PDOException $e) {
        die('Error fetching table list: ' . $e->getMessage());
    } finally {
        db_disconnect($pdo);
    }
}

// Fetch the data of a specific table
function get_table_data($table)
{
    $pdo = db_connect();
    try {
        $sql = "SELECT * FROM $table";
        $data = db_query($sql);
        return $data;
    } catch (PDOException $e) {
        die('Error fetching data from table: ' . $e->getMessage());
    } finally {
        db_disconnect($pdo);
    }
}

// Identify if a column is a password column
function is_password_column($column_name)
{
    return stripos($column_name, 'password') !== false;
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
            margin-bottom: 10px;
        }
        .sidebar a {
            display: inline-block;
            width: 80%;
            padding: 12px 20px;
            text-align: center;
            text-decoration: none;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s, box-shadow 0.3s;
            font-weight: bold;
            font-size: 16px;
        }
        .sidebar a:hover {
            background-color: #0056b3;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }
        .sidebar a:focus, .sidebar a:active {
            outline: none;
            box-shadow: 0px 0px 8px #007BFF;
        }

        .table-container {
            flex: 1;
            overflow-x: auto; /* Allow horizontal scrolling */
        }
        table {
            width: 100%;
            border: 2px solid black;
            border-collapse: collapse;
            min-width: 800px; /* Minimum width of the table */
        }
        th, td {
            border: 2px solid black;
            padding: 10px;
            text-align: left;
            word-wrap: break-word; /* Ensure long words break to the next line */
        }
        th {
            background-color: #f2f2f2;
        }
        td {
            text-align: center;
        }
        .empty-row {
            font-weight: bold;
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
                <li>
                    <a href="?table=<?php echo $table['Tables_in_' . DB_NAME]; ?>">
                        <?php echo htmlspecialchars($table['Tables_in_' . DB_NAME]); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Main Content: Table Data -->
    <div class="table-container">
        <?php if ($selected_table): ?>
            <h3>Contents of Table: <?php echo htmlspecialchars($selected_table); ?></h3>
            <table>
                <thead>
                    <tr>
                        <?php if ($contents && count($contents) > 0): ?>
                            <!-- Display headers based on the first row of data -->
                            <?php foreach (array_keys($contents[0]) as $column): ?>
                                <th><?php echo htmlspecialchars($column); ?></th>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- If no data, show headers by querying table structure -->
                            <?php
                            $pdo = db_connect();
                            $sql = "DESCRIBE $selected_table";
                            $columns = db_query($sql);
                            foreach ($columns as $column): ?>
                                <th><?php echo htmlspecialchars($column['Field']); ?></th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($contents && count($contents) > 0): ?>
                        <!-- Display rows if there is data -->
                        <?php foreach ($contents as $row): ?>
                            <tr>
                                <?php foreach ($row as $column => $value): ?>
                                    <td>
                                        <?php 
                                        // Check if the current column is a password column
                                        if (is_password_column($column)) {
                                            echo '******'; // Mask the password
                                        } else {
                                            echo htmlspecialchars($value); // Display the value normally
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Display "Empty" row if no data -->
                        <tr>
                            <td colspan="<?php echo count($columns); ?>" class="empty-row">Empty</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Please select a table to view its contents.</p>
        <?php endif; ?>
    </div>

</body>
</html>
