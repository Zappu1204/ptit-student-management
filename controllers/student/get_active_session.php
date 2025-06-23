<?php
// Start the session
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
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
    // Get current date
    $currentDate = date('Y-m-d');
    
    // Query to get active session
    $query = "SELECT * FROM internship_sessions 
              WHERE status IN ('Open', 'Closed') 
              AND registration_start_date <= ? 
              AND registration_end_date >= ? 
              ORDER BY id DESC LIMIT 1";
    
    $session = fetchOne($query, [$currentDate, $currentDate]);
    
    if ($session) {
        // Calculate remaining days
        $registrationEndDate = new DateTime($session['registration_end_date']);
        $today = new DateTime($currentDate);
        $interval = $today->diff($registrationEndDate);
        $remainingDays = $interval->days;
        
        // If end date is in the past, set remaining days to 0
        if ($today > $registrationEndDate) {
            $remainingDays = 0;
        }
        
        $response = array(
            'status' => 'success',
            'session' => $session,
            'remaining_days' => $remainingDays
        );
    } else {
        // Check if there's any upcoming session
        $query = "SELECT * FROM internship_sessions 
                  WHERE registration_start_date > ? 
                  ORDER BY registration_start_date ASC LIMIT 1";
        
        $upcomingSession = fetchOne($query, [$currentDate]);
        
        if ($upcomingSession) {
            // Calculate days until registration starts
            $registrationStartDate = new DateTime($upcomingSession['registration_start_date']);
            $today = new DateTime($currentDate);
            $interval = $today->diff($registrationStartDate);
            $daysUntilStart = $interval->days;
            
            $response = array(
                'status' => 'info',
                'message' => 'Sắp có đợt đăng ký mới',
                'session' => $upcomingSession,
                'days_until_start' => $daysUntilStart
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Không có đợt đăng ký thực tập nào đang diễn ra'
            );
        }
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