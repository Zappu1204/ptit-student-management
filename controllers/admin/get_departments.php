<?php
/**
 * Get Departments API
 * 
 * Returns list of unique department names from student records
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
    'departments' => []
];

try {
    // Get unique department names where not null and not empty
    $query = "SELECT DISTINCT department FROM users 
              WHERE role = 'student' AND department IS NOT NULL AND department != '' 
              ORDER BY department";
    
    $result = fetchAll($query);
    
    if ($result) {
        $departments = array_column($result, 'department');
        $response['departments'] = $departments;
    }
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = 'Error fetching departments: ' . $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 