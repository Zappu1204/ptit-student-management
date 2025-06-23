<?php
/**
 * Get Top Students API
 * 
 * This file returns top students data in JSON format
 */

// Include required files
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../config/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Get top 3 students
    $topStudents = getTopStudents(3);
    
    // Convert to a simpler array structure for JSON
    $result = array_map(function($student) {
        return [
            'id' => $student['id'],
            'full_name' => $student['full_name'],
            'student_id' => $student['student_id'] ?? '',
            'email' => $student['email'],
            'department' => $student['department'] ?? '',
            'class' => $student['class'] ?? '',
            'avatar' => $student['avatar'] ?? '',
            'gpa' => (float)$student['gpa'],
            'academic_standing' => $student['academic_standing']
        ];
    }, $topStudents);
    
    // Output JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Return error
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
} 