<?php
/**
 * Utility Functions
 * PTIT Student Management System
 */

// Include database configuration if not already included
    require_once __DIR__ . '/db_config.php';

// Make sure we have a database connection
global $conn;
if (!isset($conn) || $conn === null) {
    // Use getDbConnection from db_config.php
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        // Handle connection error
        error_log("Database connection error: " . $e->getMessage());
    }
}

/**
 * Helper functions for Student Management System
 */

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/**
 * Format date to readable format
 * 
 * @param string $date Date in Y-m-d format
 * @param string $format Format to convert to
 * @return string Formatted date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) {
        return '';
    }
    
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Format a number to Vietnamese currency
 * 
 * @param float $amount The amount to format
 * @return string Formatted amount
 */
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @param array $params Additional query parameters
 * @return void
 */
function redirect($url, $params = []) {
    $query = '';
    
    if (!empty($params)) {
        $query = '?' . http_build_query($params);
    }
    
    header("Location: $url$query");
    exit;
}

/**
 * Generate pagination links
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $url Base URL
 * @param array $params Additional query parameters
 * @return string HTML for pagination
 */
function generatePagination($currentPage, $totalPages, $url, $params = []) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $pagination = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    $prevParams = array_merge($params, ['page' => $currentPage - 1]);
    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
    $prevUrl = $currentPage <= 1 ? '#' : $url . '?' . http_build_query($prevParams);
    
    $pagination .= "<li class='page-item $prevDisabled'>";
    $pagination .= "<a class='page-link' href='$prevUrl' aria-label='Previous'>";
    $pagination .= "<span aria-hidden='true'>&laquo;</span>";
    $pagination .= "</a></li>";
    
    // Page numbers
    $range = 2; // Show 2 pages before and after current page
    
    for ($i = max(1, $currentPage - $range); $i <= min($totalPages, $currentPage + $range); $i++) {
        $pageParams = array_merge($params, ['page' => $i]);
        $active = $i == $currentPage ? 'active' : '';
        
        $pagination .= "<li class='page-item $active'>";
        $pagination .= "<a class='page-link' href='" . $url . "?" . http_build_query($pageParams) . "'>";
        $pagination .= $i;
        $pagination .= "</a></li>";
    }
    
    // Next button
    $nextParams = array_merge($params, ['page' => $currentPage + 1]);
    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
    $nextUrl = $currentPage >= $totalPages ? '#' : $url . '?' . http_build_query($nextParams);
    
    $pagination .= "<li class='page-item $nextDisabled'>";
    $pagination .= "<a class='page-link' href='$nextUrl' aria-label='Next'>";
    $pagination .= "<span aria-hidden='true'>&raquo;</span>";
    $pagination .= "</a></li>";
    
    $pagination .= '</ul></nav>';
    
    return $pagination;
}

/**
 * Calculate GPA for a student
 * 
 * @param int $userId User ID
 * @param int|null $semesterId Semester ID or null for overall GPA
 * @return float GPA
 */
function calculateGPA($userId, $semesterId = null) {
    $params = [$userId];
    $semesterCondition = '';
    
    if ($semesterId) {
        $semesterCondition = 'AND g.semester_id = ?';
        $params[] = $semesterId;
    }
    
    $grades = fetchAll(
        "SELECT g.total_score, c.credits 
         FROM grades g
         JOIN courses c ON g.course_id = c.id
         WHERE g.user_id = ? $semesterCondition AND g.grade_status = 'Đạt'",
        $params
    );
    
    if (empty($grades)) {
        return 0;
    }
    
    $totalScore = 0;
    $totalCredits = 0;
    
    foreach ($grades as $grade) {
        $totalScore += $grade['total_score'] * $grade['credits'];
        $totalCredits += $grade['credits'];
    }
    
    if ($totalCredits == 0) {
        return 0;
    }
    
    $gpa = $totalScore / $totalCredits;
    
    // Round to 2 decimal places
    return round($gpa, 2);
}

/**
 * Get academic standing based on GPA
 * 
 * @param float $gpa GPA
 * @return string Academic standing (Xuất sắc, Giỏi, Khá, Trung bình, Yếu)
 */
function getAcademicStanding($gpa) {
    if ($gpa >= 9.0) {
        return 'Xuất sắc';
    } elseif ($gpa >= 8.0) {
        return 'Giỏi';
    } elseif ($gpa >= 7.0) {
        return 'Khá';
    } elseif ($gpa >= 5.0) {
        return 'Trung bình';
    } else {
        return 'Yếu';
    }
}

/**
 * Convert numeric grade to letter grade
 * 
 * @param float $score Score (0-10)
 * @return string Letter grade (A+, A, B+, B, C+, C, D+, D, F)
 */
function convertToLetterGrade($score) {
    if ($score >= 9.0) {
        return 'A+';
    } elseif ($score >= 8.5) {
        return 'A';
    } elseif ($score >= 8.0) {
        return 'B+';
    } elseif ($score >= 7.0) {
        return 'B';
    } elseif ($score >= 6.5) {
        return 'C+';
    } elseif ($score >= 5.5) {
        return 'C';
    } elseif ($score >= 5.0) {
        return 'D+';
    } elseif ($score >= 4.0) {
        return 'D';
    } else {
        return 'F';
    }
}

/**
 * Calculate total credits completed by a student
 * 
 * @param int $userId User ID
 * @return int Total credits
 */
function calculateTotalCredits($userId) {
    $result = fetchOne(
        "SELECT SUM(c.credits) as total_credits
         FROM grades g
         JOIN courses c ON g.course_id = c.id
         WHERE g.user_id = ? AND g.grade_status = 'Đạt'",
        [$userId]
    );
    
    return $result['total_credits'] ?? 0;
}

/**
 * Get top students based on GPA
 * 
 * @param int $limit Number of students to return
 * @param int|null $semesterId Semester ID or null for overall GPA
 * @return array Array of top students
 */
function getTopStudents($limit = 3, $semesterId = null) {
    $students = fetchAll(
        "SELECT * FROM users WHERE role = 'student' AND status = 'Đang học'",
        []
    );
    
    if (empty($students)) {
        return [];
    }
    
    $studentsWithGpa = [];
    
    foreach ($students as $student) {
        $gpa = calculateGPA($student['id'], $semesterId);
        $student['gpa'] = $gpa;
        $student['academic_standing'] = getAcademicStanding($gpa);
        $studentsWithGpa[] = $student;
    }
    
    // Sort by GPA (descending)
    usort($studentsWithGpa, function($a, $b) {
        return $b['gpa'] <=> $a['gpa'];
    });
    
    // Return top N students
    return array_slice($studentsWithGpa, 0, $limit);
}

/**
 * Generate random password
 * 
 * @param int $length Password length
 * @return string Random password
 */
function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $password = '';
    
    $charLength = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[mt_rand(0, $charLength)];
    }
    
    return $password;
}

/**
 * Email class for sending notifications
 */
class EmailSender {
    private $mailer;
    private $config;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Load PHPMailer
        require_once __DIR__ . '/../PHPMailer/src/Exception.php';
        require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
        
        // Load environment configuration
        $this->loadConfig();
        
        // Initialize PHPMailer
        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
    }
    
    /**
     * Load configuration from .env file
     */
    private function loadConfig() {
        $envPath = __DIR__ . '/../.env';
        
        if (!file_exists($envPath)) {
            throw new Exception("Environment file not found");
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->config = [];
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, "\"'");
                
                $this->config[$name] = $value;
            }
        }
    }
    
    /**
     * Setup PHPMailer with configuration
     */
    private function setupMailer() {
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['MAIL_HOST'] ?? 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['MAIL_USERNAME'] ?? '';
        $this->mailer->Password = $this->config['MAIL_PASSWORD'] ?? '';
        
        if (strtolower($this->config['MAIL_ENCRYPTION'] ?? 'tls') == 'ssl') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        $this->mailer->Port = (int)($this->config['MAIL_PORT'] ?? 587);
        $this->mailer->CharSet = 'UTF-8';
        
        // Default sender
        $this->mailer->setFrom(
            $this->config['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
            $this->config['MAIL_FROM_NAME'] ?? 'Student Management System'
        );
    }
    
    /**
     * Send an email
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $altBody Plain text alternative
     * @return bool Success status
     */
    public function send($to, $subject, $body, $altBody = '') {
        try {
            // Reset recipients
            $this->mailer->clearAddresses();
            
            // Add recipient
            $this->mailer->addAddress($to);
            
            // Set content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);
            
            // Send email
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}

/**
 * Send email notification (legacy function for backward compatibility)
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Plain text alternative
 * @return bool Success status
 */
function sendEmailNotification($to, $subject, $body, $altBody = '') {
    try {
        $emailSender = new EmailSender();
        return $emailSender->send($to, $subject, $body, $altBody);
    } catch (Exception $e) {
        error_log("Email configuration error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if a student needs academic warning
 * 
 * @param int $userId User ID
 * @return array Warning status and reason
 */
function checkAcademicWarning($userId) {
    $warnings = [];
    
    // Check overall GPA
    $gpa = calculateGPA($userId);
    
    if ($gpa < 5.0) {
        $warnings[] = [
            'type' => 'gpa',
            'message' => 'Điểm trung bình tích lũy dưới 5.0',
            'value' => $gpa
        ];
    }
    
    // Check number of failing courses
    $failingCourses = fetchAll(
        "SELECT c.name 
         FROM grades g
         JOIN courses c ON g.course_id = c.id
         WHERE g.user_id = ? AND g.total_score < 4.0",
        [$userId]
    );
    
    if (count($failingCourses) >= 3) {
        $warnings[] = [
            'type' => 'failing_courses',
            'message' => 'Có từ 3 môn học trở lên có điểm dưới 4.0',
            'courses' => $failingCourses
        ];
    }
    
    return [
        'has_warning' => !empty($warnings),
        'warnings' => $warnings
    ];
}

/**
 * Send birthday wishes to students
 * 
 * @return int Number of emails sent
 */
function sendBirthdayWishes() {
    // Get students with birthday today
    $today = date('m-d');
    
    $students = fetchAll(
        "SELECT * FROM users 
         WHERE role = 'student' 
         AND status = 'Đang học'
         AND DATE_FORMAT(date_of_birth, '%m-%d') = ?",
        [$today]
    );
    
    if (empty($students)) {
        return 0;
    }
    
    $emailsSent = 0;
    
    try {
        $emailSender = new EmailSender();
        
        foreach ($students as $student) {
            $subject = 'Chúc mừng sinh nhật!';
            $body = "
                <h1>Chúc mừng sinh nhật {$student['full_name']}!</h1>
                <p>Nhà trường xin gửi lời chúc mừng sinh nhật đến bạn.</p>
                <p>Chúc bạn một ngày sinh nhật vui vẻ và thành công trong học tập!</p>
                <p>Trân trọng,<br>Trường PTIT</p>
            ";
            
            $sent = $emailSender->send($student['email'], $subject, $body);
            
            if ($sent) {
                $emailsSent++;
            }
        }
    } catch (Exception $e) {
        error_log("Error sending birthday wishes: " . $e->getMessage());
    }
    
    return $emailsSent;
}

/**
 * Check for and send academic warnings
 * 
 * @return int Number of warnings sent
 */
function sendAcademicWarnings() {
    // Get all active students
    $students = fetchAll(
        "SELECT * FROM users WHERE role = 'student' AND status = 'Đang học'",
        []
    );
    
    if (empty($students)) {
        return 0;
    }
    
    $warningsSent = 0;
    
    try {
        $emailSender = new EmailSender();
        
        foreach ($students as $student) {
            $warningCheck = checkAcademicWarning($student['id']);
            
            if ($warningCheck['has_warning']) {
                $subject = 'Cảnh báo học vụ';
                
                $warningMessages = '';
                foreach ($warningCheck['warnings'] as $warning) {
                    $warningMessages .= "<li>{$warning['message']}</li>";
                }
                
                $body = "
                    <h1>Cảnh báo học vụ</h1>
                    <p>Xin chào {$student['full_name']},</p>
                    <p>Hệ thống đã phát hiện các vấn đề sau trong quá trình học tập của bạn:</p>
                    <ul>
                        $warningMessages
                    </ul>
                    <p>Vui lòng liên hệ với phòng Đào tạo để được tư vấn và hỗ trợ.</p>
                    <p>Trân trọng,<br>Trường PTIT</p>
                ";
                
                $sent = $emailSender->send($student['email'], $subject, $body);
                
                if ($sent) {
                    $warningsSent++;
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error sending academic warnings: " . $e->getMessage());
    }
    
    return $warningsSent;
}

/**
 * Get data for export to Excel
 * 
 * @param string $type Data type (students, courses, grades)
 * @param array $filters Filter parameters
 * @return array Data for export
 */
function getExportData($type, $filters = []) {
    $data = [];
    
    switch ($type) {
        case 'students':
            $query = "SELECT * FROM users WHERE role = 'student'";
            $params = [];
            
            if (!empty($filters['department'])) {
                $query .= " AND department = ?";
                $params[] = $filters['department'];
            }
            
            if (!empty($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['class'])) {
                $query .= " AND class = ?";
                $params[] = $filters['class'];
            }
            
            $data = fetchAll($query, $params);
            break;
            
        case 'courses':
            $query = "SELECT * FROM courses";
            $params = [];
            
            if (!empty($filters['semester_id'])) {
                $query .= " WHERE semester_id = ?";
                $params[] = $filters['semester_id'];
            }
            
            if (!empty($filters['department'])) {
                $query .= isset($params[0]) ? " AND department = ?" : " WHERE department = ?";
                $params[] = $filters['department'];
            }
            
            $data = fetchAll($query, $params);
            break;
            
        case 'grades':
            $query = "
                SELECT g.*, u.full_name, u.student_id, c.name as course_name, c.course_code, c.credits
                FROM grades g
                JOIN users u ON g.user_id = u.id
                JOIN courses c ON g.course_id = c.id
            ";
            $params = [];
            
            if (!empty($filters['user_id'])) {
                $query .= " WHERE g.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['course_id'])) {
                $query .= isset($params[0]) ? " AND g.course_id = ?" : " WHERE g.course_id = ?";
                $params[] = $filters['course_id'];
            }
            
            if (!empty($filters['semester_id'])) {
                $query .= isset($params[0]) ? " AND g.semester_id = ?" : " WHERE g.semester_id = ?";
                $params[] = $filters['semester_id'];
            }
            
            $data = fetchAll($query, $params);
            break;
    }
    
    return $data;
}

/**
 * Sanitize input to prevent XSS
 * 
 * @param mixed $input Input to sanitize
 * @return mixed Sanitized input
 */
function sanitize($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize($value);
        }
    } else {
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    return $input;
}

/**
 * Validate student ID format
 * 
 * @param string $studentId Student ID to validate
 * @return bool True if valid, false otherwise
 */
function validateStudentId($studentId) {
    // Example: B22CN585 (Batch, Year, Department, Number)
    return preg_match('/^[A-Z][0-9]{2}[A-Z]{2}[0-9]{3}$/', $studentId);
}

/**
 * Display a flash message
 * 
 * @return string|null HTML for flash message or null if no message
 */
function displayFlashMessage() {
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }
    
    $message = $_SESSION['flash_message'];
    $type = $_SESSION['flash_type'] ?? 'info';
    
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    
    return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}

/**
 * Set a flash message
 * 
 * @param string $message Message to display
 * @param string $type Message type (success, danger, warning, info)
 * @return void
 */
function setFlashMessage($message, $type = 'info') {
    startSessionIfNotStarted();
    
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Start session if not already started
 * 
 * @return void
 */
function startSessionIfNotStarted() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if a table exists in the database
 *
 * @param string $table Table name to check
 * @return bool True if table exists, false otherwise
 */
function tableExists($table) {
    global $conn;
    
    // Make sure we have a database connection
    if (!isset($conn) || $conn === null) {
        $conn = getDbConnection();
    }
    
    try {
        // Try to query the table
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        return $result && $result->num_rows > 0;
    } catch (Exception $e) {
        // Log error and return false
        error_log("Error checking if table exists: " . $e->getMessage());
        return false;
    }
}