<?php
/**
 * Database Backup Script
 * 
 * This script creates a backup of the database
 * IMPORTANT: Restricted to admin users only
 */

// Include required files
require_once 'config/db_config.php';
require_once 'config/auth.php';
require_once 'config/functions.php';

// Start session
startSessionIfNotStarted();

// Check if user is admin
if (!isAdmin()) {
    die("Access denied. Only administrators can access this script.");
}

// Set the backup file name
$backupFileName = 'ptit_db_backup_' . date('Y-m-d_H-i-s') . '.sql';

// Function to generate a backup
function backupDatabase($tables = '*') {
    // Get database credentials
    $host = DB_HOST;
    $user = DB_USER;
    $pass = DB_PASSWORD;
    $dbname = DB_NAME;
    $charset = DB_CHARSET;
    
    // Connect to database
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset($charset);
    
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }
    
    // Get all tables if not specified
    if ($tables == '*') {
        $tables = array();
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',', $tables);
    }
    
    // Start the output buffer
    ob_start();
    
    // Write header comments
    echo "-- PTIT Student Management System Database Backup\n";
    echo "-- Generation Time: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Server version: " . $conn->server_info . "\n";
    echo "-- PHP Version: " . phpversion() . "\n\n";
    
    echo "SET FOREIGN_KEY_CHECKS=0;\n";
    echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    echo "SET time_zone = \"+00:00\";\n\n";
    
    echo "-- Database: `$dbname`\n";
    echo "-- ------------------------------------------------------\n";
    
    // Iterate through tables
    foreach ($tables as $table) {
        // Get table creation SQL
        $result = $conn->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch_row();
        
        echo "\n-- Table structure for table `$table`\n\n";
        echo "DROP TABLE IF EXISTS `$table`;\n";
        echo $row[1] . ";\n\n";
        
        // Get table data
        $result = $conn->query("SELECT * FROM `$table`");
        $numFields = $result->field_count;
        
        echo "-- Dumping data for table `$table`\n";
        
        if ($result->num_rows > 0) {
            // Build insert statements
            $insertStmt = "";
            while ($row = $result->fetch_row()) {
                $insertStmt .= "INSERT INTO `$table` VALUES(";
                for ($i = 0; $i < $numFields; $i++) {
                    if (isset($row[$i])) {
                        // Escape special characters
                        $row[$i] = addslashes($row[$i]);
                        // Replace newlines
                        $row[$i] = str_replace("\n", "\\n", $row[$i]);
                        $insertStmt .= '"' . $row[$i] . '"';
                    } else {
                        $insertStmt .= 'NULL';
                    }
                    
                    if ($i < ($numFields - 1)) {
                        $insertStmt .= ',';
                    }
                }
                $insertStmt .= ");\n";
            }
            
            // Output the insert statements
            echo $insertStmt;
        }
        
        echo "\n";
    }
    
    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    
    $backup = ob_get_clean();
    
    return $backup;
}

// Generate the backup
$backup = backupDatabase();

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $backupFileName);
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($backup));

// Output the backup
echo $backup;
exit; 