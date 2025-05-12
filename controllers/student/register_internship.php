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
    
    // Get form data
    $companyId = isset($_POST['company_id']) ? intval($_POST['company_id']) : 0;
    $sessionId = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $companyName = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
    $position = isset($_POST['position']) ? trim($_POST['position']) : '';
    $preferenceOrder = isset($_POST['preference_order']) ? intval($_POST['preference_order']) : 0;
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    
    // Validate input
    if ($companyId <= 0 || $sessionId <= 0 || empty($companyName) || empty($position) || $preferenceOrder <= 0) {
        $response = array(
            'status' => 'error',
            'message' => 'Thông tin đăng ký không hợp lệ'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if the session is open for registration
    $querySession = "SELECT * FROM internship_sessions 
                     WHERE id = ? AND status = 'Open'
                     AND registration_start_date <= CURDATE() 
                     AND registration_end_date >= CURDATE()";
    
    $session = fetchOne($querySession, [$sessionId]);
    
    if (!$session) {
        $response = array(
            'status' => 'error',
            'message' => 'Đợt đăng ký thực tập đã kết thúc hoặc không tồn tại'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if the company is valid and has open positions
    $queryCompany = "SELECT cs.* FROM company_sessions cs
                     JOIN companies c ON cs.company_id = c.id
                     WHERE cs.company_id = ? AND cs.session_id = ? AND c.status = 'Active'";
    
    $companySession = fetchOne($queryCompany, [$companyId, $sessionId]);
    
    if (!$companySession) {
        $response = array(
            'status' => 'error',
            'message' => 'Công ty không tồn tại hoặc không còn nhận thực tập sinh'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if positions are still available
    if ($companySession['positions_filled'] >= $companySession['available_positions']) {
        $response = array(
            'status' => 'error',
            'message' => 'Công ty đã đủ số lượng thực tập sinh'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if student already has this preference order for this session
    $queryCheck = "SELECT * FROM internships 
                  WHERE user_id = ? AND session_id = ? AND preference_order = ?";
    
    $existingPreference = fetchOne($queryCheck, [$userId, $sessionId, $preferenceOrder]);
    
    if ($existingPreference) {
        $response = array(
            'status' => 'error',
            'message' => 'Bạn đã đăng ký nguyện vọng ' . $preferenceOrder . ' cho đợt thực tập này'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if student already has 3 preferences for this session
    $queryCount = "SELECT COUNT(*) as preference_count FROM internships 
                  WHERE user_id = ? AND session_id = ?";
    
    $countResult = fetchOne($queryCount, [$userId, $sessionId]);
    
    if ($countResult && $countResult['preference_count'] >= 3) {
        $response = array(
            'status' => 'error',
            'message' => 'Bạn đã đăng ký đủ 3 nguyện vọng cho đợt thực tập này'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if student already applied to this company for this session
    $queryCheckCompany = "SELECT * FROM internships 
                         WHERE user_id = ? AND session_id = ? AND company_id = ?";
    
    $existingCompany = fetchOne($queryCheckCompany, [$userId, $sessionId, $companyId]);
    
    if ($existingCompany) {
        $response = array(
            'status' => 'error',
            'message' => 'Bạn đã đăng ký thực tập tại công ty này'
        );
        echo json_encode($response);
        exit;
    }
    
    // All checks passed, register the internship
    $startDate = $session['start_date'];
    $endDate = $session['end_date'];
    $status = 'Nguyện vọng';
    
    $queryInsert = "INSERT INTO internships (user_id, company_id, session_id, company_name, position, 
                                            start_date, end_date, preference_order, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [$userId, $companyId, $sessionId, $companyName, $position, 
              $startDate, $endDate, $preferenceOrder, $status];
    
    $result = execute($queryInsert, $params);
    
    if ($result) {
        $response = array(
            'status' => 'success',
            'message' => 'Đăng ký nguyện vọng thực tập thành công'
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi khi đăng ký'
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