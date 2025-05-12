<?php
/**
 * Delete Student API
 * 
 * Deletes a student and related data
 */

// Set content type to JSON from the start to ensure any error output is JSON
header('Content-Type: application/json');

// Initialize response
$response = [
    'error' => false,
    'message' => 'Student deleted successfully'
];

try {
    // Include required files
    require_once __DIR__ . '/../../config/db_config.php';
    require_once __DIR__ . '/../../config/functions.php';
    
    // Start session if not already started
    startSessionIfNotStarted();
    
    // Check if user is logged in and is admin
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $response['error'] = true;
        $response['message'] = 'Unauthorized access';
        echo json_encode($response);
        exit;
    }
    
    // Get JSON data from request
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    // Check if JSON is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    // Validate student ID
    if (empty($input['id'])) {
        throw new Exception('Student ID is required');
    }
    
    // Check if student exists
    $query = "SELECT id, student_id, full_name FROM users WHERE id = ? AND role = 'student'";
    $student = fetchOne($query, [$input['id']]);
    
    if (!$student) {
        throw new Exception('Student not found');
    }
    
    // Delete related records from grades table if it exists
    if (function_exists('tableExists') && tableExists('grades')) {
        $query = "DELETE FROM grades WHERE user_id = ?";
        execute($query, [$input['id']]);
    }
    
    // Delete related records from internships table if it exists
    if (function_exists('tableExists') && tableExists('internships')) {
        $query = "DELETE FROM internships WHERE user_id = ?";
        execute($query, [$input['id']]);
    }
    
    // Delete related records from attendance table if it exists
    if (function_exists('tableExists') && tableExists('attendance')) {
        $query = "DELETE FROM attendance WHERE user_id = ?";
        execute($query, [$input['id']]);
    }
    
    // Finally, delete the student from users table
    $query = "DELETE FROM users WHERE id = ?";
    $result = execute($query, [$input['id']]);
    
    if (!$result) {
        throw new Exception('Failed to delete student');
    }
    
    // Add deleted student info to response
    $response['student_id'] = $student['student_id'];
    $response['full_name'] = $student['full_name'];
    
} catch (Exception $e) {
    // Set error response
    $response['error'] = true;
    $response['message'] = 'Error deleting student: ' . $e->getMessage();
}

// Output JSON response
echo json_encode($response);
exit; 