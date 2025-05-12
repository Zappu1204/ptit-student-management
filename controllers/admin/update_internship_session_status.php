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
    // Get data from POST
    $sessionId = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    // Validate input
    if ($sessionId <= 0 || empty($status)) {
        $response = array(
            'status' => 'error',
            'message' => 'Thông tin không hợp lệ'
        );
        echo json_encode($response);
        exit;
    }
    
    // Validate status value
    $validStatuses = array('Draft', 'Open', 'Closed', 'Finalized');
    if (!in_array($status, $validStatuses)) {
        $response = array(
            'status' => 'error',
            'message' => 'Trạng thái không hợp lệ'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if session exists
    $queryCheck = "SELECT id, status FROM internship_sessions WHERE id = ?";
    
    $session = fetchOne($queryCheck, [$sessionId]);
    
    if (!$session) {
        $response = array(
            'status' => 'error',
            'message' => 'Đợt thực tập không tồn tại'
        );
        echo json_encode($response);
        exit;
    }
    
    $currentStatus = $session['status'];
    
    // Check if status transition is valid
    $validTransitions = array(
        'Draft' => array('Open', 'Closed', 'Draft'),
        'Open' => array('Closed', 'Draft', 'Open'),
        'Closed' => array('Open', 'Finalized', 'Closed'),
        'Finalized' => array('Finalized') // Once finalized, cannot change
    );
    
    if (!in_array($status, $validTransitions[$currentStatus])) {
        $response = array(
            'status' => 'error',
            'message' => 'Không thể chuyển từ trạng thái ' . $currentStatus . ' sang ' . $status
        );
        echo json_encode($response);
        exit;
    }
    
    // Update session status
    $queryUpdate = "UPDATE internship_sessions SET status = ? WHERE id = ?";
    
    $result = execute($queryUpdate, [$status, $sessionId]);
    
    if ($result) {
        $response = array(
            'status' => 'success',
            'message' => 'Cập nhật trạng thái thành công'
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi khi cập nhật trạng thái'
        );
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