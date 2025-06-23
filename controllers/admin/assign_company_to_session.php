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

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = array(
        'status' => 'error',
        'message' => 'Invalid request method'
    );
    echo json_encode($response);
    exit;
}

// Include database configuration
require_once '../../config/db_config.php';

try {
    // Get form data
    $companyId = isset($_POST['company_id']) ? intval($_POST['company_id']) : 0;
    $sessionId = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $availablePositions = isset($_POST['available_positions']) ? intval($_POST['available_positions']) : 5;
    $positions = isset($_POST['positions']) ? trim($_POST['positions']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    // Validate input
    if ($companyId <= 0 || $sessionId <= 0 || $availablePositions <= 0) {
        $response = array(
            'status' => 'error',
            'message' => 'Thông tin không hợp lệ'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if company exists
    $queryCheckCompany = "SELECT * FROM companies WHERE id = ?";
    
    $company = fetchOne($queryCheckCompany, [$companyId]);
    
    if (!$company) {
        $response = array(
            'status' => 'error',
            'message' => 'Công ty không tồn tại'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if session exists and is valid
    $queryCheckSession = "SELECT * FROM internship_sessions WHERE id = ? AND status IN ('Draft', 'Open')";
    
    $session = fetchOne($queryCheckSession, [$sessionId]);
    
    if (!$session) {
        $response = array(
            'status' => 'error',
            'message' => 'Đợt thực tập không tồn tại hoặc không thể gán công ty'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if company is already assigned to this session
    $queryCheckAssignment = "SELECT * FROM company_sessions WHERE company_id = ? AND session_id = ?";
    
    $existingAssignment = fetchOne($queryCheckAssignment, [$companyId, $sessionId]);
    
    // Add position information to notes if provided
    $positionsNote = $notes;
    if (!empty($positions)) {
        if (!empty($positionsNote)) {
            $positionsNote .= "\n";
        }
        $positionsNote .= "Vị trí: " . $positions;
    }
    
    if ($existingAssignment) {
        // Update existing assignment
        $queryUpdate = "UPDATE company_sessions 
                       SET available_positions = ?, notes = ? 
                       WHERE company_id = ? AND session_id = ?";
        
        $result = execute($queryUpdate, [$availablePositions, $positionsNote, $companyId, $sessionId]);
        
        if ($result) {
            $response = array(
                'status' => 'success',
                'message' => 'Cập nhật thông tin công ty trong đợt thực tập thành công'
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi khi cập nhật'
            );
        }
    } else {
        // Create new assignment
        $queryInsert = "INSERT INTO company_sessions (company_id, session_id, available_positions, positions_filled, notes) 
                       VALUES (?, ?, ?, 0, ?)";
        
        $result = execute($queryInsert, [$companyId, $sessionId, $availablePositions, $positionsNote]);
        
        if ($result) {
            $response = array(
                'status' => 'success',
                'message' => 'Gán công ty vào đợt thực tập thành công'
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi khi gán công ty'
            );
        }
    }
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