<?php
/**
 * Export Students API
 * 
 * Exports student data to CSV with filtering options
 */

// Include required files
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../config/functions.php';

// Start session if not already started
startSessionIfNotStarted();

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$classFilter = isset($_GET['class']) ? trim($_GET['class']) : '';
$departmentFilter = isset($_GET['department']) ? trim($_GET['department']) : '';
$genderFilter = isset($_GET['gender']) ? trim($_GET['gender']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$birthYearFilter = isset($_GET['birth_year']) ? (int)$_GET['birth_year'] : 0;
$sortField = isset($_GET['sort_field']) ? trim($_GET['sort_field']) : 'student_id';
$sortOrder = isset($_GET['sort_order']) ? trim($_GET['sort_order']) : 'asc';

// Validate sort parameters
$allowedSortFields = ['student_id', 'full_name', 'date_of_birth', 'class', 'department', 'status'];
if (!in_array($sortField, $allowedSortFields)) {
    $sortField = 'student_id'; // Default sort
}
$sortOrder = strtolower($sortOrder) === 'desc' ? 'DESC' : 'ASC';

try {
    // Build the base query with role = 'student'
    $baseQuery = "FROM users WHERE role = 'student'";
    
    // Add filter conditions
    $conditions = [];
    $params = ["student"]; // First parameter is for role = 'student'
    
    if (!empty($search)) {
        $conditions[] = "(student_id LIKE ? OR full_name LIKE ? OR class LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (!empty($classFilter)) {
        $conditions[] = "class = ?";
        $params[] = $classFilter;
    }
    
    if (!empty($departmentFilter)) {
        $conditions[] = "department = ?";
        $params[] = $departmentFilter;
    }
    
    if (!empty($genderFilter)) {
        $conditions[] = "gender = ?";
        $params[] = $genderFilter;
    }
    
    if (!empty($statusFilter)) {
        $conditions[] = "status = ?";
        $params[] = $statusFilter;
    }
    
    if ($birthYearFilter > 0) {
        $conditions[] = "YEAR(date_of_birth) = ?";
        $params[] = $birthYearFilter;
    }
    
    // Update base query with filters
    if (!empty($conditions)) {
        $baseQuery .= " AND " . implode(" AND ", $conditions);
    }
    
    // Get all student data for export
    $query = "SELECT student_id, full_name, date_of_birth, gender, class, email, phone, 
              address, department, entry_year, status
              " . $baseQuery . " 
              ORDER BY " . $sortField . " " . $sortOrder;
    
    $students = fetchAll($query, $params);
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=students_export_' . date('Y-m-d') . '.csv');
    
    // Create file pointer connected to PHP output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 encoding in Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Set column headers
    fputcsv($output, [
        'Mã SV', 
        'Họ và tên', 
        'Ngày sinh', 
        'Giới tính', 
        'Lớp', 
        'Email', 
        'Số điện thoại', 
        'Địa chỉ', 
        'Khoa', 
        'Năm nhập học', 
        'Trạng thái'
    ]);
    
    // Add data rows
    foreach ($students as $student) {
        fputcsv($output, [
            $student['student_id'],
            $student['full_name'],
            $student['date_of_birth'],
            $student['gender'],
            $student['class'],
            $student['email'],
            $student['phone'],
            $student['address'],
            $student['department'],
            $student['entry_year'],
            $student['status']
        ]);
    }
    
    // Close the file pointer
    fclose($output);
    exit;
    
} catch (Exception $e) {
    // If error occurs, return JSON error response
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Error exporting students data: ' . $e->getMessage()
    ]);
    exit;
} 