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

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = array(
        'status' => 'error',
        'message' => 'Invalid request method'
    );
    echo json_encode($response);
    exit;
}

// Include database configuration
require_once '../../config/db_config.php';

try {
    // Get session ID from POST
    $sessionId = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    
    if ($sessionId <= 0) {
        $response = array(
            'status' => 'error',
            'message' => 'Mã đợt thực tập không hợp lệ'
        );
        echo json_encode($response);
        exit;
    }
    
    // Check if the session exists and can be finalized
    $queryCheck = "SELECT * FROM internship_sessions WHERE id = ? AND status IN ('Open', 'Closed')";
    
    $session = fetchOne($queryCheck, [$sessionId]);
    
    if (!$session) {
        $response = array(
            'status' => 'error',
            'message' => 'Đợt thực tập không tồn tại hoặc không thể chốt danh sách'
        );
        echo json_encode($response);
        exit;
    }
    
    // Start a transaction
    beginTransaction();
    
    // 1. Update session status to "Finalized"
    $queryUpdateSession = "UPDATE internship_sessions SET status = 'Finalized' WHERE id = ?";
    execute($queryUpdateSession, [$sessionId]);
    
    // 2. Get all companies for this session and their available positions
    $queryCompanies = "SELECT cs.company_id, cs.available_positions, cs.positions_filled 
                      FROM company_sessions cs
                      WHERE cs.session_id = ?";
    
    $companies = fetchAll($queryCompanies, [$sessionId]);
    
    $companyCapacity = array();
    foreach ($companies as $company) {
        $companyId = $company['company_id'];
        $availablePositions = $company['available_positions'];
        $positionsFilled = $company['positions_filled'];
        $companyCapacity[$companyId] = array(
            'available' => $availablePositions - $positionsFilled,
            'filled' => $positionsFilled
        );
    }
    
    // 3. Get all internship registrations for this session, ordered by preference
    $queryRegistrations = "SELECT * FROM internships 
                          WHERE session_id = ? AND status = 'Nguyện vọng' 
                          ORDER BY preference_order ASC";
    
    $internships = fetchAll($queryRegistrations, [$sessionId]);
    
    // 4. Process internship assignments
    $assigned = array(); // Track which students have been assigned
    $assignedCount = 0;
    
    // First pass: try to assign students to their first preferences
    foreach ($internships as $internship) {
        $userId = $internship['user_id'];
        $companyId = $internship['company_id'];
        $preferenceOrder = $internship['preference_order'];
        
        // Skip if student is already assigned or company has no capacity
        if (isset($assigned[$userId]) || !isset($companyCapacity[$companyId]) || $companyCapacity[$companyId]['available'] <= 0) {
            continue;
        }
        
        // If this is preference 1 and there's capacity, assign student
        if ($preferenceOrder === 1) {
            // Update internship status
            $queryUpdate = "UPDATE internships SET status = 'Đã xếp' WHERE id = ?";
            execute($queryUpdate, [$internship['id']]);
            
            // Decrement available positions
            $companyCapacity[$companyId]['available']--;
            $companyCapacity[$companyId]['filled']++;
            
            // Mark student as assigned
            $assigned[$userId] = true;
            $assignedCount++;
        }
    }
    
    // Second pass: try to assign remaining students to their second preferences
    foreach ($internships as $internship) {
        $userId = $internship['user_id'];
        $companyId = $internship['company_id'];
        $preferenceOrder = $internship['preference_order'];
        
        // Skip if student is already assigned or company has no capacity
        if (isset($assigned[$userId]) || !isset($companyCapacity[$companyId]) || $companyCapacity[$companyId]['available'] <= 0) {
            continue;
        }
        
        // If this is preference 2 and there's capacity, assign student
        if ($preferenceOrder === 2) {
            // Update internship status
            $queryUpdate = "UPDATE internships SET status = 'Đã xếp' WHERE id = ?";
            execute($queryUpdate, [$internship['id']]);
            
            // Decrement available positions
            $companyCapacity[$companyId]['available']--;
            $companyCapacity[$companyId]['filled']++;
            
            // Mark student as assigned
            $assigned[$userId] = true;
            $assignedCount++;
        }
    }
    
    // Third pass: try to assign remaining students to their third preferences
    foreach ($internships as $internship) {
        $userId = $internship['user_id'];
        $companyId = $internship['company_id'];
        $preferenceOrder = $internship['preference_order'];
        
        // Skip if student is already assigned or company has no capacity
        if (isset($assigned[$userId]) || !isset($companyCapacity[$companyId]) || $companyCapacity[$companyId]['available'] <= 0) {
            continue;
        }
        
        // If this is preference 3 and there's capacity, assign student
        if ($preferenceOrder === 3) {
            // Update internship status
            $queryUpdate = "UPDATE internships SET status = 'Đã xếp' WHERE id = ?";
            execute($queryUpdate, [$internship['id']]);
            
            // Decrement available positions
            $companyCapacity[$companyId]['available']--;
            $companyCapacity[$companyId]['filled']++;
            
            // Mark student as assigned
            $assigned[$userId] = true;
            $assignedCount++;
        }
    }
    
    // 5. Update company_sessions with new positions_filled counts
    foreach ($companyCapacity as $companyId => $capacity) {
        $queryUpdateCompany = "UPDATE company_sessions 
                              SET positions_filled = ? 
                              WHERE company_id = ? AND session_id = ?";
        
        execute($queryUpdateCompany, [$capacity['filled'], $companyId, $sessionId]);
    }
    
    // Commit the transaction
    commitTransaction();
    
    $response = array(
        'status' => 'success',
        'message' => 'Đã chốt danh sách thực tập. Phân bổ thành công ' . $assignedCount . ' sinh viên',
        'assigned_count' => $assignedCount
    );
} catch (Exception $e) {
    // Rollback the transaction in case of error
    rollbackTransaction();
    
    $response = array(
        'status' => 'error',
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    );
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 