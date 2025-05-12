<?php
/**
 * Get Student API
 * 
 * Returns details for a specific student
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

// Get student ID from request
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$studentId) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Missing student ID'
    ]);
    exit;
}

// Initialize response
$response = [
    'error' => false
];

try {
    // Get student data
    $query = "SELECT * FROM users WHERE id = ? AND role = 'student'";
    $student = fetchOne($query, [$studentId]);
    
    if (!$student) {
        throw new Exception('Student not found');
    }
    
    // Add student data to response
    $response = array_merge($response, $student);
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = 'Error fetching student: ' . $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 