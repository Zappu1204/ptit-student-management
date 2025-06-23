<?php
// Start the session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $response = array(
        'status' => 'error',
        'message' => 'Unauthorized access'
    );
    echo json_encode($response);
    exit;
}

// Include database configuration
require_once '../../config/db_config.php';

try {
    // Query to get all internship sessions with semester information
    $query = "SELECT s.*, sem.name as semester_name
              FROM internship_sessions s
              LEFT JOIN semesters sem ON s.semester_id = sem.id
              ORDER BY s.id DESC";
    
    $sessions = fetchAll($query);
    
    $response = array(
        'status' => 'success',
        'sessions' => $sessions
    );
} catch (Exception $e) {
    $response = array(
        'status' => 'error',
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    );
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 