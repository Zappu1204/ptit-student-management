<?php
/**
 * Get Birth Years API
 * 
 * Returns unique birth years for student filtering
 */

// Include required files
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../config/functions.php';

// Start session if not already started
startSessionIfNotStarted();

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Initialize response
$response = [
    'error' => false,
    'birth_years' => []
];

try {
    // Query to get unique birth years from the users table
    $query = "SELECT DISTINCT YEAR(date_of_birth) as birth_year 
              FROM users 
              WHERE role = 'student' 
              AND date_of_birth IS NOT NULL 
              ORDER BY birth_year DESC";
    $years = fetchAll($query);
    
    // Extract just the years
    $birthYears = [];
    foreach ($years as $year) {
        if (!empty($year['birth_year'])) {
            $birthYears[] = $year['birth_year'];
        }
    }
    
    $response['birth_years'] = $birthYears;
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = 'Error fetching birth years: ' . $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 