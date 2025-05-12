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
    // Query to get all companies
    $query = "SELECT * FROM companies ORDER BY id DESC";
    
    $companies = fetchAll($query);
    
    $response = array(
        'status' => 'success',
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