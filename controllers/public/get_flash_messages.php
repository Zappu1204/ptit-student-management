<?php
/**
 * Get Flash Messages API
 * 
 * Returns flash messages from session as JSON and clears them
 */

// Include required files
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../config/functions.php';

// Start session if not already started
startSessionIfNotStarted();

// Default response
$response = [
    'messages' => []
];

// Check for flash messages in session
if (isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages'])) {
    $response['messages'] = $_SESSION['flash_messages'];
    
    // Clear flash messages after retrieving them
    $_SESSION['flash_messages'] = [];
}

// Set JSON header
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
exit; 