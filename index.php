<?php
/**
 * Main entry point for Student Management System
 */

// Include required files
require_once 'config/db_config.php';
require_once 'config/auth.php';
require_once 'config/functions.php';
require_once 'config/view_helper.php';

// Start session
startSessionIfNotStarted();

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect based on user role
    if (isAdmin()) {
        // Redirect to admin dashboard
        header('Location: views/admin/dashboard.php');
        exit;
    } else if (isStudent()) {
        // Redirect to student dashboard
        header('Location: views/student/dashboard.php');
        exit;
    }
} else {
    // Redirect to public index page (HTML file)
    header('Location: views/public/index.html');
    exit;
}