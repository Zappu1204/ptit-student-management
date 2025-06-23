<?php
/**
 * Logout controller
 * 
 * This file handles the logout functionality
 */

// Include required files
require_once __DIR__ . '/../../config/auth.php';

// Logout user
logoutUser();

// Redirect to login page
header('Location: ../../views/public/login.php');
exit; 