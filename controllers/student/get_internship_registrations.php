<?php
// Start the session
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
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
    // Get student ID
    $userId = $_SESSION['user_id'];
    
    // Get current active session if any
    $currentDate = date('Y-m-d');
    $querySession = "SELECT id FROM internship_sessions 
                     WHERE status IN ('Open', 'Closed', 'Finalized') 
                     AND registration_start_date <= ? 
                     ORDER BY id DESC LIMIT 1";
    
    $session = fetchOne($querySession, [$currentDate]);
    
    if ($session) {
        $sessionId = $session['id'];
        
        // Get internship registrations for this student and session
        $query = "SELECT i.*, c.name as company_name 
                  FROM internships i
                  LEFT JOIN companies c ON i.company_id = c.id
                  WHERE i.user_id = ? AND i.session_id = ?
                  ORDER BY i.preference_order ASC";
        
        $registrations = fetchAll($query, [$userId, $sessionId]);
    } else {
        // Get all internships if no active session
        $query = "SELECT i.*, c.name as company_name, s.name as session_name
                  FROM internships i
                  LEFT JOIN companies c ON i.company_id = c.id
                  LEFT JOIN internship_sessions s ON i.session_id = s.id
                  WHERE i.user_id = ?
                  ORDER BY i.created_at DESC, i.preference_order ASC";
        
        $registrations = fetchAll($query, [$userId]);
    }
    
    $response = array(
        'status' => 'success',
        'registrations' => $registrations
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