CREATE TABLE IF NOT EXISTS students (
    student_id VARCHAR(10) PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    gender ENUM('Nam', 'Nữ') NOT NULL,
    class VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL
)

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    subject_id VARCHAR(10) PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    credit INT NOT NULL
)

-- Semesters table
CREATE TABLE IF NOT EXISTS semesters (
    semester_id INT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    year INT NOT NULL
)
    
-- Grades table
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(10) NOT NULL,
    subject_id VARCHAR(10) NOT NULL,
    semester_id INT NOT NULL,
    score FLOAT NOT NULL CHECK (score >= 0 AND score <= 10),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(semester_id) ON DELETE CASCADE
)

-- (Tùy chọn) Bảng intership
CREATE TABLE internships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(10),
    company_name VARCHAR(100),
    position VARCHAR(100),
    start_date DATE,
    end_date DATE