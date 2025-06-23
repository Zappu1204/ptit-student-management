<?php
/**
 * Authentication Functions
 * PTIT Student Management System
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include utility functions if not already included
if (!function_exists('sanitize')) {
    require_once __DIR__ . '/functions.php';
}

// Ensure database connection is established
global $conn;
if (!isset($conn) || $conn === null) {
    // Use getDbConnection from db_config.php
    require_once __DIR__ . '/db_config.php';
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        // Handle connection error
        error_log("Database connection error in auth.php: " . $e->getMessage());
    }
}

/**
 * Attempt to log in a user
 * @param string $username - Username or student ID
 * @param string $password - Password
 * @param string $user_type - Type of user (admin or student)
 * @return bool - True if login successful, false otherwise
 */
function login($username, $password, $user_type = 'student') {
    // Sanitize input
    $username = sanitize($username);
    
    if ($user_type === 'admin') {
        // Get admin user
        $admin = getAdmin($username);
        
        if ($admin && verifyPassword($password, $admin['password_hash'])) {
            // Login successful, set session variables
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['user_type'] = 'admin';
            
            // Set last login time
            $_SESSION['last_activity'] = time();
            
            return true;
        }
    } else {
        // Get student user
        $student = getStudent($username);
        
        if ($student && verifyPassword($password, $student['password_hash'])) {
            // Login successful, set session variables
            $_SESSION['user_id'] = $student['student_id'];
            $_SESSION['username'] = $student['full_name'];
            $_SESSION['user_type'] = 'student';
            
            // Set last login time
            $_SESSION['last_activity'] = time();
            
            return true;
        }
    }
    
    return false;
}

/**
 * Log out the current user
 */
function logout() {
    // Unset all session variables
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if user is logged in
 * @return bool - True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

/**
 * Check if current user is an admin
 * @return bool - True if admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_type'] === 'admin';
}

/**
 * Check if current user is a student
 * @return bool - True if student, false otherwise
 */
function isStudent() {
    return isLoggedIn() && $_SESSION['user_type'] === 'student';
}

/**
 * Require login to access a page, redirect if not logged in
 * @param string $redirect_url - URL to redirect to if not logged in
 */
function requireLogin($redirect_url = 'login.php') {
    if (!isLoggedIn()) {
        setFlashMessage('Bạn cần đăng nhập để truy cập trang này.', 'warning');
        header('Location: ' . $redirect_url);
        exit;
    }
    
    // Check session timeout (30 minutes)
    $timeout = 30 * 60; // 30 minutes in seconds
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        logout();
        setFlashMessage('Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.', 'warning');
        header('Location: ' . $redirect_url);
        exit;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
}

/**
 * Require admin privileges to access a page, redirect if not admin
 * @param string $redirect_url - URL to redirect to if not admin
 */
function requireAdmin($redirect_url = 'login.php') {
    requireLogin($redirect_url);
    
    if (!isAdmin()) {
        setFlashMessage('Bạn không có quyền truy cập trang này.', 'danger');
        header('Location: index.php');
        exit;
    }
}

/**
 * Require student privileges to access a page, redirect if not student
 * @param string $redirect_url - URL to redirect to if not student
 */
function requireStudent($redirect_url = 'login.php') {
    requireLogin($redirect_url);
    
    if (!isStudent()) {
        setFlashMessage('Bạn không có quyền truy cập trang này.', 'danger');
        header('Location: index.php');
        exit;
    }
}

/**
 * Get current user ID
 * @return string|int|null - User ID or null if not logged in
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get current username
 * @return string|null - Username or null if not logged in
 */
function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

/**
 * Get current user type
 * @return string|null - User type or null if not logged in
 */
function getCurrentUserType() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

// Login user 
function loginUser($username, $password, $remember = false) {
    // Sanitize inputs
    $username = trim($username);
    
    // Get user from database
    $user = fetchOne(
        "SELECT * FROM users WHERE username = ?",
        [$username]
    );
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'Tên đăng nhập không tồn tại'
        ];
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        return [
            'success' => false,
            'message' => 'Mật khẩu không chính xác'
        ];
    }
    
    // Start session
    startSessionIfNotStarted();
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['logged_in'] = true;
    
    // Set remember me cookie
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
    
    return [
        'success' => true,
        'user' => $user,
        'message' => 'Đăng nhập thành công'
    ];
}

// Register new student
function registerStudent($userData) {
    // Validate required fields
    $requiredFields = ['username', 'password', 'email', 'full_name', 'student_id'];
    foreach ($requiredFields as $field) {
        if (empty($userData[$field])) {
            return [
                'success' => false,
                'message' => "Vui lòng nhập $field"
            ];
        }
    }
    
    // Check if username exists
    $existingUser = fetchOne(
        "SELECT id FROM users WHERE username = ?",
        [$userData['username']]
    );
    
    if ($existingUser) {
        return [
            'success' => false,
            'message' => 'Tên đăng nhập đã tồn tại'
        ];
    }
    
    // Check if email exists
    $existingEmail = fetchOne(
        "SELECT id FROM users WHERE email = ?",
        [$userData['email']]
    );
    
    if ($existingEmail) {
        return [
            'success' => false,
            'message' => 'Email đã tồn tại'
        ];
    }
    
    // Check if student_id exists
    $existingStudentId = fetchOne(
        "SELECT id FROM users WHERE student_id = ?",
        [$userData['student_id']]
    );
    
    if ($existingStudentId) {
        return [
            'success' => false,
            'message' => 'Mã sinh viên đã tồn tại'
        ];
    }
    
    // Hash password
    $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
    
    // Prepare data for insertion
    $params = [
        $userData['username'],
        $hashedPassword,
        $userData['email'],
        $userData['full_name'],
        $userData['student_id'],
        $userData['class'] ?? null,
        $userData['department'] ?? null,
        $userData['entry_year'] ?? null,
        $userData['date_of_birth'] ?? null,
        $userData['gender'] ?? 'Nam',
        $userData['address'] ?? null,
        $userData['phone'] ?? null
    ];
    
    // Insert new user
    $result = execute(
        "INSERT INTO users (
            username, password, email, full_name, student_id,
            class, department, entry_year, date_of_birth, gender,
            address, phone, role
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'student')",
        $params
    );
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Đăng ký thành công',
            'user_id' => getLastInsertId()
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Đăng ký thất bại'
        ];
    }
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return fetchOne(
        "SELECT * FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );
}

// Function to handle password reset
function generatePasswordResetToken($email) {
    // Find user by email
    $user = fetchOne(
        "SELECT * FROM users WHERE email = ?",
        [$email]
    );
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'Email không tồn tại trong hệ thống'
        ];
    }
    
    // Generate token
    $token = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
    
    // Check if the reset_token column exists before trying to use it
    $tableInfo = fetchAll("DESCRIBE users");
    $hasResetTokenColumn = false;
    
    foreach ($tableInfo as $column) {
        if ($column['Field'] === 'reset_token') {
            $hasResetTokenColumn = true;
            break;
        }
    }
    
    if ($hasResetTokenColumn) {
        // Store token in database
        execute(
            "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?",
            [$token, $expires, $user['id']]
        );
    } else {
        // Store token in session as a fallback
        $_SESSION['password_reset_token'] = $token;
        $_SESSION['password_reset_email'] = $email;
        $_SESSION['password_reset_expires'] = $expires;
    }
    
    return [
        'success' => true,
        'token' => $token,
        'user' => $user
    ];
}

// Verify password reset token
function verifyPasswordResetToken($token) {
    // Check if reset_token column exists
    $tableInfo = fetchAll("DESCRIBE users");
    $hasResetTokenColumn = false;
    
    foreach ($tableInfo as $column) {
        if ($column['Field'] === 'reset_token') {
            $hasResetTokenColumn = true;
            break;
        }
    }
    
    if ($hasResetTokenColumn) {
        // Find user by token in database
        $user = fetchOne(
            "SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()",
            [$token]
        );
    } else {
        // Check session-based token instead
        if (isset($_SESSION['password_reset_token']) && 
            $_SESSION['password_reset_token'] === $token &&
            $_SESSION['password_reset_expires'] > date('Y-m-d H:i:s')) {
            
            $user = fetchOne(
                "SELECT * FROM users WHERE email = ?",
                [$_SESSION['password_reset_email']]
            );
        } else {
            $user = null;
        }
    }
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'Token không hợp lệ hoặc đã hết hạn'
        ];
    }
    
    return [
        'success' => true,
        'user' => $user
    ];
}

// Reset password
function resetPassword($token, $password) {
    // Verify token
    $verification = verifyPasswordResetToken($token);
    
    if (!$verification['success']) {
        return $verification;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if reset_token column exists
    $tableInfo = fetchAll("DESCRIBE users");
    $hasResetTokenColumn = false;
    
    foreach ($tableInfo as $column) {
        if ($column['Field'] === 'reset_token') {
            $hasResetTokenColumn = true;
            break;
        }
    }
    
    if ($hasResetTokenColumn) {
        // Update password and clear token in database
        execute(
            "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?",
            [$hashedPassword, $verification['user']['id']]
        );
    } else {
        // Just update password
        execute(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hashedPassword, $verification['user']['id']]
        );
        
        // Clear session tokens
        unset($_SESSION['password_reset_token']);
        unset($_SESSION['password_reset_email']);
        unset($_SESSION['password_reset_expires']);
    }
    
    return [
        'success' => true,
        'message' => 'Mật khẩu đã được đặt lại thành công'
    ];
}
?>