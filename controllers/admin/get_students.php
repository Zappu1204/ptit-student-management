<?php
/**
 * Get Students API
 * 
 * Returns paginated student data with filtering options
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

// Get parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$classFilter = isset($_GET['class']) ? trim($_GET['class']) : '';
$departmentFilter = isset($_GET['department']) ? trim($_GET['department']) : '';
$genderFilter = isset($_GET['gender']) ? trim($_GET['gender']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$birthYearFilter = isset($_GET['birth_year']) ? (int)$_GET['birth_year'] : 0;
$sortField = isset($_GET['sort_field']) ? trim($_GET['sort_field']) : '';
$sortOrder = isset($_GET['sort_order']) ? trim($_GET['sort_order']) : 'asc';

// Validate sort parameters
$allowedSortFields = ['student_id', 'full_name', 'date_of_birth', 'class', 'department', 'status'];
if (!in_array($sortField, $allowedSortFields)) {
    $sortField = 'student_id'; // Default sort
}
$sortOrder = strtolower($sortOrder) === 'desc' ? 'DESC' : 'ASC';

// Set pagination parameters
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Initialize response
$response = [
    'error' => false,
    'students' => [],
    'pagination' => [
        'current_page' => $page,
        'items_per_page' => $itemsPerPage,
        'total_items' => 0,
        'total_pages' => 0
    ]
];

try {
    // Build the base query - users table with role = 'student'
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
    
    // Update base query with role = 'student' condition
    $baseQuery = "FROM users WHERE role = ?";
    
    // Add WHERE clause if there are additional conditions
    if (!empty($conditions)) {
        $baseQuery .= " AND " . implode(" AND ", $conditions);
    }
    
    // Count total items for pagination
    $countQuery = "SELECT COUNT(*) as total " . $baseQuery;
    $countResult = fetchOne($countQuery, $params);
    
    $totalItems = $countResult ? (int)$countResult['total'] : 0;
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    // Update pagination info
    $response['pagination']['total_items'] = $totalItems;
    $response['pagination']['total_pages'] = $totalPages;
    
    // If page is out of range, set to last page
    if ($page > $totalPages && $totalPages > 0) {
        $page = $totalPages;
        $response['pagination']['current_page'] = $page;
        $offset = ($page - 1) * $itemsPerPage;
    }
    
    // Get students data with pagination
    $query = "SELECT id, student_id, full_name, date_of_birth as dob, gender, class, email, phone, address,
              department, entry_year, status
              " . $baseQuery . " 
              ORDER BY " . $sortField . " " . $sortOrder . " LIMIT ?, ?";
    
    // Add pagination parameters
    $params[] = $offset;
    $params[] = $itemsPerPage;
    
    $students = fetchAll($query, $params);
    
    $response['students'] = $students;
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = 'Error fetching students data: ' . $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 