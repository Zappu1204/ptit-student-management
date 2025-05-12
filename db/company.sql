-- Company and Internship Registration System 

-- Create companies table
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    description TEXT,
    industry VARCHAR(100),
    website VARCHAR(255),
    contact_person VARCHAR(100),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    max_interns INT DEFAULT 5,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create internship sessions table
CREATE TABLE IF NOT EXISTS internship_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    registration_start_date DATE NOT NULL,
    registration_end_date DATE NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    semester_id INT,
    status ENUM('Draft', 'Open', 'Closed', 'Finalized') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create company_sessions junction table (which companies are in which sessions)
CREATE TABLE IF NOT EXISTS company_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    session_id INT NOT NULL,
    available_positions INT DEFAULT 5,
    positions_filled INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES internship_sessions(id) ON DELETE CASCADE,
    UNIQUE KEY (company_id, session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modify internships table to link with sessions and companies
ALTER TABLE internships 
ADD COLUMN company_id INT AFTER user_id,
ADD COLUMN session_id INT AFTER company_id,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
ADD FOREIGN KEY (session_id) REFERENCES internship_sessions(id) ON DELETE CASCADE;

-- Insert sample companies
INSERT INTO companies (name, address, description, industry, website, contact_person, contact_email, contact_phone, max_interns) 
VALUES 
('FPT Software', 'Hà Nội, Việt Nam', 'Công ty phần mềm hàng đầu Việt Nam với nhiều dự án lớn từ các khách hàng quốc tế', 'Phần mềm', 'https://fptsoftware.com', 'Nguyễn Văn A', 'recruiter@fpt.com.vn', '0123456789', 10),
('Viettel Solutions', 'Hà Nội, Việt Nam', 'Công ty công nghệ thuộc Tập đoàn Viettel, chuyên về các giải pháp CNTT và viễn thông', 'Viễn thông & CNTT', 'https://viettelsolutions.vn', 'Trần Thị B', 'hr@viettelsolutions.vn', '0987654321', 8),
('VNG Corporation', 'TP. Hồ Chí Minh, Việt Nam', 'Công ty công nghệ hàng đầu Việt Nam với các sản phẩm như Zalo, ZaloPay', 'Internet & Game', 'https://vng.com.vn', 'Lê Văn C', 'careers@vng.com.vn', '0369852147', 15),
('MISA JSC', 'Hà Nội, Việt Nam', 'Công ty phát triển phần mềm kế toán, ERP cho doanh nghiệp và cơ quan nhà nước', 'Phần mềm doanh nghiệp', 'https://misa.com.vn', 'Phạm Thị D', 'tuyendung@misa.com.vn', '0951623847', 7),
('CMC Technology Corporation', 'Hà Nội, Việt Nam', 'Tập đoàn công nghệ với nhiều mảng: phần mềm, hạ tầng CNTT, viễn thông', 'CNTT & Viễn thông', 'https://cmc.com.vn', 'Hoàng Văn E', 'recruitment@cmc.com.vn', '0753951824', 12);

-- Insert sample internship session
INSERT INTO internship_sessions (name, description, start_date, end_date, registration_start_date, registration_end_date, academic_year, semester_id, status, created_by)
VALUES 
('Thực tập Xuân 2024', 'Đợt thực tập học kỳ II năm học 2023-2024 cho sinh viên năm 3 và 4', '2024-05-15', '2024-07-15', '2024-04-01', '2024-04-30', '2023-2024', 2, 'Open', 1);

-- Link companies to the internship session
INSERT INTO company_sessions (company_id, session_id, available_positions, notes)
VALUES 
(1, 1, 10, 'Vị trí: Java Developer, Frontend Developer, QA Engineer'),
(2, 1, 8, 'Vị trí: Network Engineer, Software Developer'),
(3, 1, 15, 'Vị trí: Game Developer, Mobile Developer, Backend Developer'),
(4, 1, 7, 'Vị trí: Software Developer, Business Analyst'),
(5, 1, 12, 'Vị trí: Software Developer, System Engineer, Data Analyst');

-- Insert sample internship registrations
INSERT INTO internships (user_id, company_id, session_id, company_name, position, start_date, end_date, preference_order, status)
VALUES 
(2, 1, 1, 'FPT Software', 'Java Developer', '2024-05-15', '2024-07-15', 1, 'Nguyện vọng'),
(2, 3, 1, 'VNG Corporation', 'Backend Developer', '2024-05-15', '2024-07-15', 2, 'Nguyện vọng'); 