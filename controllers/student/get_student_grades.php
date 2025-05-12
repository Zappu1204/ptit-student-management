<?php
/**
 * Get Student Grades API
 * 
 * Returns grades data for a student
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
    'student_info' => [],
    'summary' => [
        'gpa' => 0,
        'total_credits' => 0,
        'completed_subjects' => 0
    ],
    'semesters' => [],
    'grades_by_semester' => []
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
    
    $response['student_info'] = [
        'student_id' => $student['student_id'],
        'full_name' => $student['full_name'],
        'class' => $student['class'] ?? 'N/A',
        'department' => $student['department'] ?? 'N/A'
    ];
    
    // Get semesters
    $query = "SELECT * FROM semesters ORDER BY start_date DESC";
    $semesters = fetchAll($query);
    $response['semesters'] = $semesters;
    
    // Get grades summary
    $query = "SELECT 
                SUM(c.credits) as total_credits,
                COUNT(DISTINCT g.course_id) as completed_subjects,
                AVG(g.final_grade) as gpa
              FROM grades g
              JOIN courses c ON g.course_id = c.id
              JOIN students s ON g.student_id = s.id
              WHERE s.user_id = ?";
    $summary = fetchOne($query, [$userId]);
    
    if ($summary) {
        $response['summary'] = [
            'gpa' => round($summary['gpa'] ?? 0, 2),
            'total_credits' => (int)($summary['total_credits'] ?? 0),
            'completed_subjects' => (int)($summary['completed_subjects'] ?? 0)
        ];
    }
    
    // Get grades by semester
    $query = "SELECT 
                g.id, g.midterm_grade, g.final_grade, g.letter_grade,
                c.id as course_id, c.code as course_code, c.name as course_name, c.credits,
                sm.id as semester_id, sm.name as semester_name, sm.year as semester_year
              FROM grades g
              JOIN courses c ON g.course_id = c.id
              JOIN students s ON g.student_id = s.id
              JOIN semesters sm ON g.semester_id = sm.id
              WHERE s.user_id = ?
              ORDER BY sm.start_date DESC, c.name ASC";
    $grades = fetchAll($query, [$userId]);
    
    // Organize grades by semester
    $gradesBySemester = [];
    $semesterGpaData = [];
    
    foreach ($grades as $grade) {
        $semesterId = $grade['semester_id'];
        
        // Initialize semester if not exists
        if (!isset($gradesBySemester[$semesterId])) {
            $gradesBySemester[$semesterId] = [
                'semester_id' => $semesterId,
                'semester_name' => $grade['semester_name'] . ' ' . $grade['semester_year'],
                'semester_gpa' => 0,
                'courses' => []
            ];
            
            $semesterGpaData[$semesterId] = [
                'total_points' => 0,
                'total_credits' => 0
            ];
        }
        
        // Calculate grade points for this course
        $coursePoints = $grade['final_grade'] * $grade['credits'];
        $semesterGpaData[$semesterId]['total_points'] += $coursePoints;
        $semesterGpaData[$semesterId]['total_credits'] += $grade['credits'];
        
        // Add course to semester
        $gradesBySemester[$semesterId]['courses'][] = [
            'course_id' => $grade['course_id'],
            'course_code' => $grade['course_code'],
            'course_name' => $grade['course_name'],
            'credits' => $grade['credits'],
            'midterm_score' => $grade['midterm_grade'],
            'final_score' => $grade['final_grade'],
            'average_score' => $grade['final_grade'], // Using final grade as average for now
            'grade_letter' => $grade['letter_grade']
        ];
    }
    
    // Calculate GPA for each semester
    foreach ($semesterGpaData as $semesterId => $data) {
        if ($data['total_credits'] > 0) {
            $semesterGpa = $data['total_points'] / $data['total_credits'];
            $gradesBySemester[$semesterId]['semester_gpa'] = round($semesterGpa, 2);
        }
    }
    
    $response['grades_by_semester'] = $gradesBySemester;
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = 'Error fetching grades data: ' . $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 