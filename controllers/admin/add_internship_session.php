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
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $academicYear = isset($_POST['academic_year']) ? trim($_POST['academic_year']) : '';
    $semesterId = isset($_POST['semester_id']) ? intval($_POST['semester_id']) : 0;
    $registrationStartDate = isset($_POST['registration_start_date']) ? trim($_POST['registration_start_date']) : '';
    $registrationEndDate = isset($_POST['registration_end_date']) ? trim($_POST['registration_end_date']) : '';
    $startDate = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $endDate = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Draft';
    
    // Validate input
    if (empty($name) || empty($academicYear) || $semesterId <= 0 || 
        empty($registrationStartDate) || empty($registrationEndDate) || 
        empty($startDate) || empty($endDate)) {
        
        $response = array(
            'status' => 'error',
            'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc'
        );
        echo json_encode($response);
        exit;
    }
    
    // Validate dates
    $regStartDate = new DateTime($registrationStartDate);
    $regEndDate = new DateTime($registrationEndDate);
    $sessStartDate = new DateTime($startDate);
    $sessEndDate = new DateTime($endDate);
    
    if ($regStartDate > $regEndDate) {
        $response = array(
            'status' => 'error',
            'message' => 'Ngày bắt đầu đăng ký phải trước ngày kết thúc đăng ký'
        );
        echo json_encode($response);
        exit;
    }
    
    if ($sessStartDate > $sessEndDate) {
        $response = array(
            'status' => 'error',
            'message' => 'Ngày bắt đầu thực tập phải trước ngày kết thúc thực tập'
        );
        echo json_encode($response);
        exit;
    }
    
    if ($regEndDate > $sessStartDate) {
        $response = array(
            'status' => 'error',
            'message' => 'Ngày kết thúc đăng ký phải trước ngày bắt đầu thực tập'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if a session with the same name and academic year already exists
    $queryCheck = "SELECT id FROM internship_sessions WHERE name = ? AND academic_year = ?";
    
    $existingSession = fetchOne($queryCheck, [$name, $academicYear]);
    
    if ($existingSession) {
        $response = array(
            'status' => 'error',
            'message' => 'Đợt thực tập với tên và năm học này đã tồn tại'
        );
        echo json_encode($response);
        exit;
    }
    
    // Insert the new internship session
    $createdBy = $_SESSION['user_id'];
    
    $queryInsert = "INSERT INTO internship_sessions (name, description, academic_year, semester_id, 
                                                    registration_start_date, registration_end_date, 
                                                    start_date, end_date, status, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [$name, $description, $academicYear, $semesterId, 
              $registrationStartDate, $registrationEndDate, $startDate, $endDate, 
              $status, $createdBy];
    
    $result = execute($queryInsert, $params);
    
    if ($result) {
        $newSessionId = getLastInsertId();
        
        $response = array(
            'status' => 'success',
            'message' => 'Thêm đợt thực tập mới thành công',
            'session_id' => $newSessionId
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi khi thêm đợt thực tập'
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