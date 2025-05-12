<?php
/**
 * Get Classes API
 * 
 * Returns unique class names for student filtering
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
    'classes' => []
];

try {
    // Query to get unique class names from the users table
    $query = "SELECT DISTINCT class FROM users WHERE role = 'student' AND class IS NOT NULL AND class != '' ORDER BY class ASC";
    $classes = fetchAll($query);
    
    // Extract just the class names
    $classNames = [];
    foreach ($classes as $class) {
        $classNames[] = $class['class'];
    }
    
    $response['classes'] = $classNames;
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = 'Error fetching classes: ' . $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 