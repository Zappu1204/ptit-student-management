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
    // Get student ID
    $userId = $_SESSION['user_id'];
    
    // Get internship ID from POST
    $internshipId = isset($_POST['internship_id']) ? intval($_POST['internship_id']) : 0;
    
    if ($internshipId <= 0) {
        $response = array(
            'status' => 'error',
            'message' => 'Mã đăng ký không hợp lệ'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if the internship belongs to this student
    $queryCheck = "SELECT i.*, s.status as session_status 
                  FROM internships i
                  JOIN internship_sessions s ON i.session_id = s.id
                  WHERE i.id = ? AND i.user_id = ?";
    
    $internship = fetchOne($queryCheck, [$internshipId, $userId]);
    
    if (!$internship) {
        $response = array(
            'status' => 'error',
            'message' => 'Đăng ký thực tập không tồn tại hoặc không thuộc về bạn'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if the internship is in a status that can be canceled
    if ($internship['status'] !== 'Nguyện vọng') {
        $response = array(
            'status' => 'error',
            'message' => 'Không thể hủy đăng ký thực tập ở trạng thái này'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if the session is still open
    if ($internship['session_status'] !== 'Open') {
        $response = array(
            'status' => 'error',
            'message' => 'Đợt đăng ký thực tập đã kết thúc, không thể hủy'
        );
        echo json_encode($response);
        exit;
    }
    
    // Delete the registration
    $queryDelete = "DELETE FROM internships WHERE id = ? AND user_id = ?";
    
    $result = execute($queryDelete, [$internshipId, $userId]);
    
    if ($result) {
        $response = array(
            'status' => 'success',
            'message' => 'Hủy đăng ký thực tập thành công'
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi khi hủy đăng ký'
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