<?php
/**
 * Update Student API
 * 
 * Updates an existing student's information
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
    'message' => 'Student updated successfully'
];

try {
    // Validate required fields
    if (empty($input['id'])) {
        throw new Exception('Student ID is required');
    }
    
    if (empty($input['full_name'])) {
        throw new Exception('Full name is required');
    }
    
    if (empty($input['email'])) {
        throw new Exception('Email is required');
    }
    
    // Check if student exists
    $query = "SELECT * FROM users WHERE id = ? AND role = 'student'";
    $student = fetchOne($query, [$input['id']]);
    
    if (!$student) {
        throw new Exception('Student not found');
    }
    
    // Prepare data for update
    $data = [
        'full_name' => $input['full_name'],
        'email' => $input['email'],
        'date_of_birth' => !empty($input['date_of_birth']) ? $input['date_of_birth'] : null,
        'gender' => !empty($input['gender']) ? $input['gender'] : $student['gender'],
        'class' => $input['class'] ?? $student['class'],
        'department' => $input['department'] ?? $student['department'],
        'entry_year' => !empty($input['entry_year']) ? (int)$input['entry_year'] : $student['entry_year'],
        'phone' => $input['phone'] ?? $student['phone'],
        'address' => $input['address'] ?? $student['address'],
        'status' => !empty($input['status']) ? $input['status'] : $student['status'],
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Handle password reset if requested
    if (!empty($input['reset_password']) && $input['reset_password'] === true) {
        if (empty($input['password'])) {
            throw new Exception('New password is required for password reset');
        }
        
        $data['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
    }
    
    // Build update query
    $setClause = '';
    $params = [];
    
    foreach ($data as $key => $value) {
        if (!empty($setClause)) {
            $setClause .= ', ';
        }
        $setClause .= "{$key} = ?";
        $params[] = $value;
    }
    
    // Add student ID to params
    $params[] = $input['id'];
    
    $query = "UPDATE users SET {$setClause} WHERE id = ?";
    
    // Execute query
    $result = execute($query, $params);
    
    if (!$result) {
        throw new Exception('Failed to update student');
    }
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 