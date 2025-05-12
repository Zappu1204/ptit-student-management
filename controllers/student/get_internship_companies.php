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
    // Get current date
    $currentDate = date('Y-m-d');
    
    // Get student ID
    $userId = $_SESSION['user_id'];
    
    // First get active session ID
    $query = "SELECT id FROM internship_sessions 
              WHERE status IN ('Open') 
              AND registration_start_date <= ? 
              AND registration_end_date >= ? 
              ORDER BY id DESC LIMIT 1";
    
    $session = fetchOne($query, [$currentDate, $currentDate]);
    
    if (!$session) {
        $response = array(
            'status' => 'error',
            'message' => 'Không có đợt đăng ký thực tập nào đang mở'
        );
        echo json_encode($response);
        exit;
    }
    
    $sessionId = $session['id'];
    
    // Get companies available for this session
    $query = "SELECT c.id, c.name, c.description, c.industry, c.website, 
                     cs.available_positions, cs.positions_filled, cs.notes as positions
              FROM companies c
              JOIN company_sessions cs ON c.id = cs.company_id
              WHERE cs.session_id = ? AND c.status = 'Active'
              ORDER BY c.name ASC";
    
    $companies = fetchAll($query, [$sessionId]);
    
    // Process each company to extract position information
    foreach ($companies as &$company) {
        // Extract position information from notes if available
        if (!empty($company['positions']) && strpos($company['positions'], 'Vị trí:') !== false) {
            $positionsPart = explode('Vị trí:', $company['positions'])[1];
            $company['positions'] = trim($positionsPart);
        }
    }
    
    $response = array(
        'status' => 'success',
        'session_id' => $sessionId,
        'companies' => $companies
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