-- Database schema for Student Management System
-- Student Management System Database Schema

-- Drop tables if they exist to avoid conflicts
DROP TABLE IF EXISTS internships;
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS semesters;
DROP TABLE IF EXISTS users;

-- Create users table (for both students and admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('Nam', 'Nữ', 'Khác') DEFAULT 'Nam',
    address TEXT,
    phone VARCHAR(20),
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    student_id VARCHAR(20) UNIQUE, -- Mã sinh viên (null for admins)
    class VARCHAR(50), -- Lớp học (null for admins)
    department VARCHAR(100), -- Khoa (null for admins)
    entry_year INT, -- Năm nhập học (null for admins)
    avatar VARCHAR(255), -- Path to avatar image
    status ENUM('Đang học', 'Đã tốt nghiệp', 'Bảo lưu', 'Đình chỉ') DEFAULT 'Đang học',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create semesters table
CREATE TABLE semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL, -- Học kỳ I, II, ...
    academic_year VARCHAR(20) NOT NULL, -- Năm học (2023-2024)
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (name, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE, -- Mã môn học
    name VARCHAR(100) NOT NULL, -- Tên môn học
    description TEXT,
    credits INT NOT NULL, -- Số tín chỉ
    semester_id INT,
    department VARCHAR(100), -- Khoa phụ trách
    lecturer VARCHAR(100), -- Giảng viên
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create grades table
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    semester_id INT NOT NULL,
    attendance_score FLOAT DEFAULT 0, -- Điểm chuyên cần (0-10)
    midterm_score FLOAT DEFAULT 0, -- Điểm giữa kỳ (0-10)
    final_score FLOAT DEFAULT 0, -- Điểm cuối kỳ (0-10)
    total_score FLOAT DEFAULT 0, -- Điểm tổng kết (0-10)
    grade_letter VARCHAR(2), -- A+, A, B+, B, C+, C, D+, D, F
    grade_status ENUM('Đạt', 'Không đạt', 'Đang học') DEFAULT 'Đang học',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, course_id, semester_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create internships table
CREATE TABLE internships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    preference_order INT DEFAULT 1, -- Thứ tự nguyện vọng
    status ENUM('Nguyện vọng', 'Chờ xét duyệt', 'Đã xếp', 'Đang thực tập', 'Hoàn thành', 'Không hoàn thành') DEFAULT 'Nguyện vọng',
    supervisor_name VARCHAR(100),
    supervisor_contact VARCHAR(100),
    feedback TEXT,
    evaluation_score FLOAT, -- Điểm đánh giá (0-10)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user with hashed password (password: "password")
INSERT INTO users (username, password, email, full_name, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ptit.edu.vn', 'Quản trị viên', 'admin');

-- Insert sample test student (password: "password")
INSERT INTO users (username, password, email, full_name, role, student_id, class, department, entry_year)
VALUES ('B22CN123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student@ptit.edu.vn', 'Nguyễn Văn A', 'student', 'B22CN123', 'D22CN01', 'Công nghệ thông tin', 2022);

-- Insert sample semesters
INSERT INTO semesters (name, academic_year, start_date, end_date, is_current)
VALUES 
('Học kỳ I', '2023-2024', '2023-09-01', '2024-01-15', TRUE),
('Học kỳ II', '2023-2024', '2024-02-01', '2024-06-15', FALSE);

-- Insert sample courses
INSERT INTO courses (course_code, name, credits, semester_id, department, lecturer)
VALUES 
('COMP101', 'Lập trình căn bản', 3, 1, 'Công nghệ thông tin', 'TS. Nguyễn Văn A'),
('COMP102', 'Cấu trúc dữ liệu và giải thuật', 4, 1, 'Công nghệ thông tin', 'PGS.TS. Trần Thị B'),
('MATH101', 'Toán rời rạc', 3, 1, 'Toán tin', 'TS. Lê Văn C'),
('COMP201', 'Cơ sở dữ liệu', 4, 2, 'Công nghệ thông tin', 'TS. Phạm Thị D'),
('COMP202', 'Lập trình hướng đối tượng', 4, 2, 'Công nghệ thông tin', 'TS. Võ Văn E');

-- Insert sample grades for test student
INSERT INTO grades (user_id, course_id, semester_id, attendance_score, midterm_score, final_score, total_score, grade_letter, grade_status)
VALUES 
(2, 1, 1, 9, 8.5, 9, 8.85, 'A', 'Đạt'), -- COMP101
(2, 2, 1, 8, 7.5, 8, 7.85, 'B+', 'Đạt'), -- COMP102
(2, 3, 1, 7, 8, 7.5, 7.55, 'B', 'Đạt'); -- MATH101 