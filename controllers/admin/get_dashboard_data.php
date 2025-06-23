<?php
/**
 * Get Admin Dashboard Data API
 * 
 * Returns stats and counts for the admin dashboard
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
    'studentCount' => 0,
    'subjectCount' => 0,
    'gradeCount' => 0,
    'semesterCount' => 0
];

try {
    // Get student count
    $query = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
    $result = fetchOne($query);
    $response['studentCount'] = $result['count'] ?? 0;
    
    // Get subject count
    $query = "SELECT COUNT(*) as count FROM courses";
    $result = fetchOne($query);
    $response['subjectCount'] = $result['count'] ?? 0;
    
    // Get grade count
    $query = "SELECT COUNT(*) as count FROM grades";
    $result = fetchOne($query);
    $response['gradeCount'] = $result['count'] ?? 0;
    
    // Get semester count
    $query = "SELECT COUNT(*) as count FROM semesters";
    $result = fetchOne($query);
    $response['semesterCount'] = $result['count'] ?? 0;
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = 'Error fetching dashboard data: ' . $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 