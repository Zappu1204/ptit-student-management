<?php
/**
 * Login process controller
 * 
 * This file handles the login form submission
 */

// Include required files
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/functions.php';

// Start session
startSessionIfNotStarted();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $userType = $_POST['user_type'] ?? 'student';
    $remember = isset($_POST['remember']);
    
    // Basic validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Vui lòng nhập tên đăng nhập';
    }
    
    if (empty($password)) {
        $errors[] = 'Vui lòng nhập mật khẩu';
    }
    
    // If no validation errors, try to log in
    if (empty($errors)) {
        // Get user from database
        $query = "SELECT * FROM users WHERE username = ? AND role = ?";
        $user = fetchOne($query, [$username, $userType]);
        
        if (!$user) {
            $errors[] = 'Tên đăng nhập hoặc mật khẩu không chính xác';
        } else {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['logged_in'] = true;
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database - skipping this part since remember_token column doesn't exist
                    // execute(
                    //     "UPDATE users SET remember_token = ? WHERE id = ?",
                    //     [$token, $user['id']]
                    // );
                    
                    // Just set cookie without database storage
                    setcookie('remember_token', $token, $expires, '/', '', false, true);
                }
                
                // Redirect based on user role
                if ($user['role'] === 'admin') {
                    header('Location: ../../views/admin/dashboard.html');
                } else {
                    header('Location: ../../views/student/dashboard.html');
                }
                exit;
            } else {
                $errors[] = 'Tên đăng nhập hoặc mật khẩu không chính xác';
            }
        }
    }
    
    // If there are errors, redirect back to login with errors
    if (!empty($errors)) {
        // URL encode errors for passing through URL parameters
        $errorsJson = urlencode(json_encode($errors));
        
        // Redirect with error parameters
        header("Location: ../../views/public/login.html?errors={$errorsJson}&username=" . urlencode($username) . "&user_type=" . urlencode($userType));
        exit;
    }
} else {
    // If not a POST request, redirect to login page
    header('Location: ../../views/public/login.html');
    exit;
} 