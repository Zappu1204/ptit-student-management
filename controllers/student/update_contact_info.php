<?php
/**
 * Update Student Contact Info API
 * 
 * Updates email and phone for a student
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

// Check if request is POST and JSON content
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get JSON data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    // Try to get regular POST data
    $input = [
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? ''
    ];
}

// Validate input
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');

// Initialize response
$response = [
    'error' => false,
    'message' => 'Contact information updated successfully'
];

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['error'] = true;
    $response['message'] = 'Invalid email address';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Validate phone (simple validation - adjust as needed)
if (empty($phone) || !preg_match('/^[0-9]{10,11}$/', $phone)) {
    $response['error'] = true;
    $response['message'] = 'Invalid phone number';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    // Update student contact info
    $query = "UPDATE students SET 
              email = ?, 
              phone = ?,
              updated_at = NOW()
              WHERE user_id = ?";
    
    $result = execute($query, [$email, $phone, $userId]);
    
    if (!$result) {
        throw new Exception('Failed to update contact information');
    }
    
    // Set flash message
    setFlashMessage('success', 'Thông tin liên hệ đã được cập nhật');
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = 'Error updating contact info: ' . $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 