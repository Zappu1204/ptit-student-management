<?php
/**
 * Add Student API
 * 
 * Adds a new student to the database
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

// Get JSON data from request
$input = json_decode(file_get_contents('php://input'), true);

// Initialize response
$response = [
    'error' => false,
    'message' => 'Student added successfully'
];

try {
    // Validate required fields
    $requiredFields = ['student_id', 'username', 'password', 'full_name', 'email'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }
    
    // Check if username already exists
    $query = "SELECT id FROM users WHERE username = ?";
    $existingUser = fetchOne($query, [$input['username']]);
    
    if ($existingUser) {
        throw new Exception('Username already exists');
    }
    
    // Check if student_id already exists
    $query = "SELECT id FROM users WHERE student_id = ?";
    $existingStudent = fetchOne($query, [$input['student_id']]);
    
    if ($existingStudent) {
        throw new Exception('Student ID already exists');
    }
    
    // Hash password
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    
    // Prepare data for insert
    $data = [
        'username' => $input['username'],
        'password' => $hashedPassword,
        'full_name' => $input['full_name'],
        'email' => $input['email'],
        'student_id' => $input['student_id'],
        'role' => 'student',
        'date_of_birth' => !empty($input['date_of_birth']) ? $input['date_of_birth'] : null,
        'gender' => !empty($input['gender']) ? $input['gender'] : 'Nam',
        'class' => $input['class'] ?? null,
        'department' => $input['department'] ?? null,
        'entry_year' => !empty($input['entry_year']) ? (int)$input['entry_year'] : null,
        'phone' => $input['phone'] ?? null,
        'address' => $input['address'] ?? null,
        'status' => !empty($input['status']) ? $input['status'] : 'Đang học',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Build query
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $query = "INSERT INTO users ({$columns}) VALUES ({$placeholders})";
    
    // Execute query
    $result = execute($query, array_values($data));
    
    if (!$result) {
        throw new Exception('Failed to add student');
    }
    
    // Get new student ID
    $studentId = getLastInsertId();
    $response['id'] = $studentId;
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 