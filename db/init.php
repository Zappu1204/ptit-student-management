<?php
/**
 * Database Initialization Script
 * PTIT Student Management System
 * 
 * This script checks if the database and required tables exist.
 * If not, it creates them and imports sample data.
 */
// Start a session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_config.php';

global $vars, $conn;
// Load environment variables
try {
    $vars = loadEnv(__DIR__ . '/../.env');
} catch (Exception $e) {
    echo $e->getMessage();
}

// Connect to the database
try {
    $conn = databaseConnect();
} catch (Exception $e) {
    echo $e->getMessage();
}

// Define constants for environment variables
define('PASSWORD_BASE', $vars['PASSWORD_BASE']);

// Function to check if table exists
function tableExists($conn, $tableName) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$tableName'");
    return mysqli_num_rows($result) > 0;
}

// Function to log initialization messages
function logInitMessage($message, $type = 'info') {
    if (!isset($_SESSION['init_messages'])) {
        $_SESSION['init_messages'] = [];
    }
    $_SESSION['init_messages'][] = ['type' => $type, 'message' => $message];
}

// Define SQL statements to create tables
$sql_create_tables = [
    // Students table
    "CREATE TABLE IF NOT EXISTS students (
        student_id VARCHAR(10) PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        dob DATE NOT NULL,
        gender ENUM('Nam', 'Nữ') NOT NULL,
        class VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        password_hash VARCHAR(255) NOT NULL
    )",
    
    // Subjects table
    "CREATE TABLE IF NOT EXISTS subjects (
        subject_id VARCHAR(10) PRIMARY KEY,
        subject_name VARCHAR(100) NOT NULL,
        credit INT NOT NULL
    )",
    
    // Semesters table
    "CREATE TABLE IF NOT EXISTS semesters (
        semester_id INT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        year INT NOT NULL
    )",
    
    // Grades table
    "CREATE TABLE IF NOT EXISTS grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(10) NOT NULL,
        subject_id VARCHAR(10) NOT NULL,
        semester_id INT NOT NULL,
        score FLOAT NOT NULL CHECK (score >= 0 AND score <= 10),
        FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
        FOREIGN KEY (semester_id) REFERENCES semesters(semester_id) ON DELETE CASCADE
    )",
    
    // Admins table
    "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) NOT NULL,
        password_hash VARCHAR(255) NOT NULL
    )"
];

// Create tables
$success = true;
$tables_created = 0;

foreach ($sql_create_tables as $sql) {
    if (!mysqli_query($conn, $sql)) {
        $success = false;
        logInitMessage("Error creating table: " . mysqli_error($conn), 'error');
    } else {
        $tables_created++;
    }
}

if ($tables_created > 0) {
    logInitMessage("Created $tables_created database tables", 'success');
}

// Add admin user if table is empty
if (tableExists($conn, 'admins')) {
    $admin_sql = "SELECT * FROM admins LIMIT 1";
    $admin_result = mysqli_query($conn, $admin_sql);

    if (mysqli_num_rows($admin_result) == 0) {
        $admin_username = 'admin';
        $admin_email = 'admin@ptit.edu.vn';
        $admin_password = PASSWORD_BASE;
        $admin_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO admins (username, email, password_hash) VALUES ('$admin_username', '$admin_email', '$admin_password_hash')";
        
        if (!mysqli_query($conn, $sql)) {
            $success = false;
            logInitMessage("Error creating admin user: " . mysqli_error($conn), 'error');
        } else {
            logInitMessage("Admin account created with username: admin and password: admin123", 'success');
        }
    }
}

// Add default semesters if table is empty
if (tableExists($conn, 'semesters')) {
    $semester_sql = "SELECT * FROM semesters LIMIT 1";
    $semester_result = mysqli_query($conn, $semester_sql);

    if (mysqli_num_rows($semester_result) == 0) {
        $default_semesters = [
            [20231, "Học kỳ 1 - 2023", 2023],
            [20232, "Học kỳ 2 - 2023", 2023],
            [20241, "Học kỳ 1 - 2024", 2024],
            [20242, "Học kỳ 2 - 2024", 2024]
        ];
        
        foreach ($default_semesters as $semester) {
            $semester_id = $semester[0];
            $name = $semester[1];
            $year = $semester[2];
            
            $sql = "INSERT INTO semesters (semester_id, name, year) VALUES ($semester_id, '$name', $year)";
            
            if (!mysqli_query($conn, $sql)) {
                $success = false;
                logInitMessage("Error adding semester: " . mysqli_error($conn), 'error');
            }
        }
        logInitMessage("Default semesters added", 'success');
    }
}

// Add default subjects if table is empty
if (tableExists($conn, 'subjects')) {
    $subject_sql = "SELECT * FROM subjects LIMIT 1";
    $subject_result = mysqli_query($conn, $subject_sql);

    if (mysqli_num_rows($subject_result) == 0) {
        $default_subjects = [
            ["CS101", "Nhập môn CNTT", 3],
            ["CS102", "Lập trình C", 3],
            ["CS103", "Toán rời rạc", 3],
            ["CS104", "Hệ điều hành", 3],
            ["CS105", "Cấu trúc dữ liệu", 3],
            ["CS106", "Mạng máy tính", 3],
            ["CS107", "Cơ sở dữ liệu", 3],
            ["CS108", "An toàn thông tin", 3]
        ];
        
        foreach ($default_subjects as $subject) {
            $subject_id = $subject[0];
            $subject_name = $subject[1];
            $credit = $subject[2];
            
            $sql = "INSERT INTO subjects (subject_id, subject_name, credit) VALUES ('$subject_id', '$subject_name', $credit)";
            
            if (!mysqli_query($conn, $sql)) {
                $success = false;
                logInitMessage("Error adding subject: " . mysqli_error($conn), 'error');
            }
        }
        logInitMessage("Default subjects added", 'success');
    }
}

// Import student data if student table is empty
if (tableExists($conn, 'students')) {
    $student_sql = "SELECT * FROM students LIMIT 1";
    $student_result = mysqli_query($conn, $student_sql);

    if (mysqli_num_rows($student_result) == 0) {
        // First check if the SQL file exists in the current directory
        $sql_file = __DIR__ . '/student_data1.sql';
        
        // If not found, try the attached_assets directory
        if (!file_exists($sql_file)) {
            $sql_file = __DIR__ . '/../attached_assets/student_data1.sql';
        }
        
        // Check if the file exists in either location
        if (file_exists($sql_file)) {
            $sql_content = file_get_contents($sql_file);
            
            // Split the SQL content into individual queries
            $sql_queries = explode(';', $sql_content);
            
            $students_added = 0;
            $grades_added = 0;
            
            // Process each query
            foreach ($sql_queries as $sql_query) {
                $sql_query = trim($sql_query);
                
                if (empty($sql_query)) {
                    continue;
                }
                
                // Check if it's a student or grade insert
                if (strpos($sql_query, 'INSERT INTO students') === 0) {
                    // Add password_hash to student insert
                    $default_password = PASSWORD_BASE;
                    $password_hash = password_hash($default_password, PASSWORD_DEFAULT);
                    
                    // Add password_hash to the end of the VALUES section
                    $sql_query = str_replace("phone)", "phone, password_hash)", $sql_query);
                    $sql_query = mb_substr($sql_query, 0, -1);
                    $sql_query .= ", '" . $password_hash . "')";
                    if (mysqli_query($conn, $sql_query)) {
                        $students_added++;
                    } else {
                        logInitMessage("Error importing student data: " . mysqli_error($conn), 'error');
                    }
                } else if (strpos($sql_query, 'INSERT INTO subjects') === 0 || 
                           strpos($sql_query, 'INSERT INTO semesters') === 0 || 
                           strpos($sql_query, 'INSERT INTO grades') === 0) {
                    // Execute other insert queries
                    if (mysqli_query($conn, $sql_query)) {
                        if (strpos($sql_query, 'INSERT INTO grades') === 0) {
                            $grades_added++;
                        }
                    } else {
                        logInitMessage("Error importing data: " . mysqli_error($conn), 'error');
                    }
                }
            }
            
            logInitMessage("Imported $students_added students with default password: 'Meobeo123@'", 'success');
            if ($grades_added > 0) {
                logInitMessage("Imported $grades_added grade records", 'success');
            }
        } else {
            logInitMessage("Student data file not found: $sql_file", 'warning');
            
            // Add a test student if data file not found
            $student_id = 'B20DCCN001';
            $full_name = 'Nguyễn Văn A';
            $dob = '2002-01-01';
            $gender = 'Nam';
            $class = 'D20CQCN01-B';
            $email = 'student@ptit.edu.vn';
            $phone = '0123456789';
            $password = PASSWORD_BASE;
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO students (student_id, full_name, dob, gender, class, email, phone, password_hash) 
                    VALUES ('$student_id', '$full_name', '$dob', '$gender', '$class', '$email', '$phone', '$password_hash')";
            
            if (!mysqli_query($conn, $sql)) {
                $success = false;
                logInitMessage("Error adding test student: " . mysqli_error($conn), 'error');
            } else {
                logInitMessage("Test student account created with ID: B20DCCN001 and password: student123", 'success');
            }
        }
    }
}

// Return initialization status
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'messages' => isset($_SESSION['init_messages']) ? $_SESSION['init_messages'] : []
    ]);
    exit;
}

// Output results in HTML
?>
<!--<!DOCTYPE html>-->
<!--<html lang="vi">-->
<!--<head>-->
<!--    <meta charset="UTF-8">-->
<!--    <meta name="viewport" content="width=device-width, initial-scale=1.0">-->
<!--    <title>Khởi tạo cơ sở dữ liệu - PTIT Student Management</title>-->
<!--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">-->
<!--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">-->
<!--</head>-->
<!--<body class="bg-light">-->
<!--    <div class="container py-5">-->
<!--        <div class="row justify-content-center">-->
<!--            <div class="col-md-8">-->
<!--                <div class="card shadow-sm border-0">-->
<!--                    <div class="card-header bg-danger text-white">-->
<!--                        <h2 class="h4 mb-0">Khởi tạo cơ sở dữ liệu</h2>-->
<!--                    </div>-->
<!--                    <div class="card-body">-->
<!--                        --><?php //if ($success): ?>
<!--                            <div class="alert alert-success">-->
<!--                                <i class="fas fa-check-circle me-2"></i> Cơ sở dữ liệu đã được khởi tạo thành công!-->
<!--                            </div>-->
<!--                        --><?php //else: ?>
<!--                            <div class="alert alert-danger">-->
<!--                                <i class="fas fa-exclamation-circle me-2"></i> Có lỗi xảy ra trong quá trình khởi tạo cơ sở dữ liệu.-->
<!--                            </div>-->
<!--                        --><?php //endif; ?>
<!--                        -->
<!--                        <h5 class="mt-4">Thông tin khởi tạo:</h5>-->
<!--                        <div class="list-group mb-4">-->
<!--                            --><?php //if (isset($_SESSION['init_messages'])): ?>
<!--                                --><?php //foreach ($_SESSION['init_messages'] as $message): ?>
<!--                                    <div class="list-group-item list-group-item---><?php //
//                                        echo ($message['type'] == 'success') ? 'success' :
//                                            (($message['type'] == 'error') ? 'danger' : 'warning');
//                                    ?><!--">-->
<!--                                        --><?php //echo htmlspecialchars($message['message']); ?>
<!--                                    </div>-->
<!--                                --><?php //endforeach; ?>
<!--                            --><?php //endif; ?>
<!--                        </div>-->
<!--                        -->
<!--                        <div class="d-flex justify-content-between">-->
<!--                            <a href="../index.php" class="btn btn-primary">-->
<!--                                <i class="fas fa-home me-2"></i> Trang chủ-->
<!--                            </a>-->
<!--                            <a href="../login.php" class="btn btn-success">-->
<!--                                <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập-->
<!--                            </a>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>-->
<!--</body>-->
<!--</html>-->
<?php
//// Clear initialization messages
//unset($_SESSION['init_messages']);
//?>