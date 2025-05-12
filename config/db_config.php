<?php
/**
 * Database Configuration
 * 
 * This file handles the database connection configuration using environment variables
 */

// Load environment variables from .env file
function loadEnvConfig() {
    $envPath = __DIR__ . '/../.env';
    
    if (!file_exists($envPath)) {
        die("Error: .env file not found.");
    }
    
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove quotes if present
        if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
            $value = substr($value, 1, -1);
        } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
            $value = substr($value, 1, -1);
        }
        
        $_ENV[$name] = $value;
    }
}

// Load environment variables
loadEnvConfig();

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'ptit_student_management');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

/**
 * Get a database connection
 * 
 * @return mysqli A MySQL database connection
 */
function getDbConnection() {
    static $conn = null;
    
    if ($conn === null) {
        // Create connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset
        $conn->set_charset(DB_CHARSET);
    }
    
    return $conn;
}

/**
 * Execute a SQL query and return the result
 * 
 * @param string $sql The SQL query to execute
 * @param array $params Parameters to bind to the query
 * @param string $types Types of the parameters (i: integer, d: double, s: string, b: blob)
 * @return mysqli_result|bool Result of the query
 */
function executeQuery($sql, $params = [], $types = '') {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    if (!empty($params)) {
        if (empty($types)) {
            // Auto-detect types if not provided
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
            }
        }
        
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result === false && $stmt->errno === 0) {
        // No result set (INSERT, UPDATE, DELETE)
        $result = true;
    }
    
    $stmt->close();
    
    return $result;
}

/**
 * Fetch all rows from a query result
 * 
 * @param string $sql The SQL query to execute
 * @param array $params Parameters to bind to the query
 * @param string $types Types of the parameters
 * @return array Array of result rows
 */
function fetchAll($sql, $params = [], $types = '') {
    $result = executeQuery($sql, $params, $types);
    
    if ($result === true) {
        return [];
    }
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    $result->free();
    
    return $rows;
}

/**
 * Fetch a single row from a query result
 * 
 * @param string $sql The SQL query to execute
 * @param array $params Parameters to bind to the query
 * @param string $types Types of the parameters
 * @return array|null The result row or null if not found
 */
function fetchOne($sql, $params = [], $types = '') {
    $result = executeQuery($sql, $params, $types);
    
    if ($result === true) {
        return null;
    }
    
    $row = $result->fetch_assoc();
    
    $result->free();
    
    return $row;
}

/**
 * Execute a query that does not return a result set (INSERT, UPDATE, DELETE)
 * 
 * @param string $sql The SQL query to execute
 * @param array $params Parameters to bind to the query
 * @param string $types Types of the parameters
 * @return int|bool Number of affected rows or false on failure
 */
function execute($sql, $params = [], $types = '') {
    $conn = getDbConnection();
    
    $result = executeQuery($sql, $params, $types);
    
    if ($result === true) {
        return $conn->affected_rows;
    }
    
    $result->free();
    
    return 0;
}

/**
 * Get the ID of the last inserted row
 * 
 * @return int|string The ID of the last inserted row
 */
function getLastInsertId() {
    $conn = getDbConnection();
    return $conn->insert_id;
}

/**
 * Escape a string to prevent SQL injection
 * 
 * @param string $value The value to escape
 * @return string The escaped value
 */
function escapeString($value) {
    $conn = getDbConnection();
    return $conn->real_escape_string($value);
}