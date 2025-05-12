<?php
/**
 * Get Student Profile API
 * 
 * Returns profile data for a student
 */

// Include required files
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../config/functions.php';

// Start session if not already started
startSessionIfNotStarted();

// Check if user is logged in and is student
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get student ID from session
$userId = $_SESSION['user_id'];

// Initialize response
$response = [
    'error' => false,
    'student_id' => '',
    'full_name' => '',
    'dob' => '',
    'gender' => '',
    'class' => '',
    'email' => '',
    'phone' => '',
    'academic' => [
        'gpa' => 0,
        'total_credits' => 0,
        'passed_credits' => 0
    ]
];

try {
    // Get student info
    $query = "SELECT s.*, u.full_name FROM students s 
              JOIN users u ON s.user_id = u.id 
              WHERE s.user_id = ?";
    $student = fetchOne($query, [$userId]);
    
    if (!$student) {
        throw new Exception('Student record not found');
    }
    
    $response['student_id'] = $student['student_id'];
    $response['full_name'] = $student['full_name'];
    $response['dob'] = $student['dob'] ?? '';
    $response['gender'] = $student['gender'] ?? '';
    $response['class'] = $student['class'] ?? '';
    $response['email'] = $student['email'] ?? '';
    $response['phone'] = $student['phone'] ?? '';
    
    // Get academic summary
    $query = "SELECT 
                SUM(c.credits) as total_credits,
                SUM(CASE WHEN g.final_grade >= 5 THEN c.credits ELSE 0 END) as passed_credits,
                AVG(g.final_grade) as gpa
              FROM grades g
              JOIN courses c ON g.course_id = c.id
              JOIN students s ON g.student_id = s.id
              WHERE s.user_id = ?";
    $academic = fetchOne($query, [$userId]);
    
    if ($academic) {
        $response['academic'] = [
            'gpa' => round($academic['gpa'] ?? 0, 2),
            'total_credits' => (int)($academic['total_credits'] ?? 0),
            'passed_credits' => (int)($academic['passed_credits'] ?? 0)
        ];
    }
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = 'Error fetching profile data: ' . $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 