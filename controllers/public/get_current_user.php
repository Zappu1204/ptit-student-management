<?php
/**
 * Get Current User API
 * 
 * Returns the current logged-in user's data as JSON
 */

// Include required files
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../config/functions.php';

// Start session if not already started
startSessionIfNotStarted();

// Default response
$response = [
    'logged_in' => false,
    'username' => null,
    'full_name' => null,
    'role' => null,
    'user_id' => null
];

// Check if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $response['logged_in'] = true;
    $response['username'] = $_SESSION['username'] ?? null;
    $response['full_name'] = $_SESSION['full_name'] ?? null;
    $response['role'] = $_SESSION['role'] ?? null;
    $response['user_id'] = $_SESSION['user_id'] ?? null;
    
    // If it's a student, fetch additional details if needed
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'student' && isset($_SESSION['user_id'])) {
        $query = "SELECT * FROM students WHERE user_id = ?";
        $student = fetchOne($query, [$_SESSION['user_id']]);
        
        if ($student) {
            $response['student_id'] = $student['student_id'] ?? null;
            $response['email'] = $student['email'] ?? null;
            $response['class'] = $student['class'] ?? null;
            $response['department'] = $student['department'] ?? null;
        }
    }
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 