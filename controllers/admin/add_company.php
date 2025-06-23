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
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $industry = isset($_POST['industry']) ? trim($_POST['industry']) : '';
    $website = isset($_POST['website']) ? trim($_POST['website']) : '';
    $contactPerson = isset($_POST['contact_person']) ? trim($_POST['contact_person']) : '';
    $contactEmail = isset($_POST['contact_email']) ? trim($_POST['contact_email']) : '';
    $contactPhone = isset($_POST['contact_phone']) ? trim($_POST['contact_phone']) : '';
    $maxInterns = isset($_POST['max_interns']) ? intval($_POST['max_interns']) : 5;
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Active';
    
    // Validate input
    if (empty($name) || empty($address) || empty($industry)) {
        $response = array(
            'status' => 'error',
            'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if company with same name already exists
    $queryCheck = "SELECT id FROM companies WHERE name = ?";
    
    $existingCompany = fetchOne($queryCheck, [$name]);
    
    if ($existingCompany) {
        $response = array(
            'status' => 'error',
            'message' => 'Công ty với tên này đã tồn tại'
        );
        echo json_encode($response);
        exit;
    }
    
    // Insert the new company
    $queryInsert = "INSERT INTO companies (name, address, description, industry, website, 
                                         contact_person, contact_email, contact_phone, 
                                         max_interns, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [$name, $address, $description, $industry, $website, 
              $contactPerson, $contactEmail, $contactPhone, $maxInterns, $status];
    
    $result = execute($queryInsert, $params);
    
    if ($result) {
        $newCompanyId = getLastInsertId();
        
        $response = array(
            'status' => 'success',
            'message' => 'Thêm công ty mới thành công',
            'company_id' => $newCompanyId
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi khi thêm công ty'
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