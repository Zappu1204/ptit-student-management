<?php
/**
 * Change Password API
 * 
 * Allows student to change their password
 */

// Include required files
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../config/functions.php';

// Start session if not already started
startSessionIfNotStarted();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get user ID from session
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
        'current_password' => $_POST['current_password'] ?? '',
        'new_password' => $_POST['new_password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? ''
    ];
}

// Validate input
$currentPassword = $input['current_password'] ?? '';
$newPassword = $input['new_password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';

// Initialize response
$response = [
    'error' => false,
    'message' => 'Password changed successfully'
];

// Validate password fields
if (empty($currentPassword)) {
    $response['error'] = true;
    $response['message'] = 'Current password is required';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (empty($newPassword)) {
    $response['error'] = true;
    $response['message'] = 'New password is required';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (strlen($newPassword) < 6) {
    $response['error'] = true;
    $response['message'] = 'New password must be at least 6 characters';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if ($newPassword !== $confirmPassword) {
    $response['error'] = true;
    $response['message'] = 'New password and confirm password do not match';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    // Get user's current password from database
    $query = "SELECT password FROM users WHERE id = ?";
    $user = fetchOne($query, [$userId]);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        $response['error'] = true;
        $response['message'] = 'Current password is incorrect';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in database
    $query = "UPDATE users SET 
              password = ?, 
              updated_at = NOW()
              WHERE id = ?";
    
    $result = execute($query, [$hashedPassword, $userId]);
    
    if (!$result) {
        throw new Exception('Failed to update password');
    }
    
    // Set flash message
    setFlashMessage('success', 'Mật khẩu đã được thay đổi thành công');
    
} catch (Exception $e) {
    $response['error'] = true;
    $response['message'] = 'Error changing password: ' . $e->getMessage();
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 