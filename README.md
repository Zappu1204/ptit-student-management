# PTIT Student Management System

Hệ thống Quản lý Sinh viên cho Học viện Công nghệ Bưu chính Viễn thông (PTIT).

## Tính năng

- Quản lý thông tin sinh viên
- Quản lý môn học và điểm số
- Tính toán GPA và xếp loại học lực
- Quản lý thực tập sinh viên
- Hệ thống thông báo qua email
- Giao diện thân thiện với người dùng

## Công nghệ sử dụng

- PHP (vanilla - không framework)
- MySQL
- HTML5
- CSS (Bootstrap 5)
- JavaScript (vanilla + jQuery)
- PHPMailer

## Cấu trúc cơ sở dữ liệu

Hệ thống sử dụng 5 bảng cơ sở dữ liệu:
1. **users** - Quản lý tài khoản người dùng (sinh viên và quản trị viên)
2. **courses** - Thông tin về các khóa học
3. **grades** - Điểm số của sinh viên
4. **semesters** - Thông tin về học kỳ
5. **internships** - Thông tin thực tập của sinh viên

## Cài đặt

### Yêu cầu hệ thống

- PHP 7.4 hoặc cao hơn
- MySQL 5.7 hoặc cao hơn
- Web server (Apache/Nginx)

### Các bước cài đặt

1. Clone repository này về máy của bạn:
```bash
git clone https://github.com/yourusername/ptit-student-management.git
cd ptit-student-management
```

2. Tạo database MySQL:
```sql
CREATE DATABASE ptit_student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Import cấu trúc database từ file `db/schema.sql`:
```bash
mysql -u username -p ptit_student_management < db/schema.sql
```

4. Tạo file `.env` từ file mẫu `.env.example`:
```bash
cp .env.example .env
```

5. Cập nhật thông tin cấu hình trong file `.env`:
```
DB_HOST=localhost
DB_PORT=3306
DB_USER=your_db_username
DB_PASSWORD=your_db_password
DB_NAME=ptit_student_management
DB_CHARSET=utf8mb4

# Email settings
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_email_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="PTIT Student Management"
```

6. Cấu hình web server trỏ đến thư mục gốc của dự án

7. Đảm bảo thư mục `uploads` có quyền ghi

### Tài khoản mặc định

- **Admin**: admin / password
- **Test Student**: B22CN123 / password

## Cấu trúc thư mục

```
StudentManagementSystem/
├── assets/                # Static assets (CSS, JS, images)
├── config/                # Configuration files
│   ├── auth.php           # Authentication functions
│   ├── db_config.php      # Database configuration
│   ├── functions.php      # Helper functions
│   └── view_helper.php    # View rendering functions
├── controllers/           # PHP processing scripts
│   ├── admin/             # Admin controllers
│   ├── public/            # Public controllers (login, logout, etc.)
│   └── student/           # Student controllers
├── db/                    # Database schema
├── includes/              # Legacy includes
├── PHPMailer/             # PHPMailer library
├── uploads/               # Uploaded files
├── views/                 # HTML view files
│   ├── admin/             # Admin view files
│   ├── includes/          # Shared view components
│   ├── layouts/           # Layout templates
│   ├── public/            # Public view files
│   └── student/           # Student view files
├── .env                   # Environment variables
├── .gitignore             # Git ignore file
└── index.php              # Entry point
```

## Bảo mật

- Mật khẩu được mã hóa bằng bcrypt
- Bảo vệ khỏi SQL injection và XSS
- Phân quyền rõ ràng giữa admin và sinh viên

## Liên hệ

Nếu bạn có bất kỳ câu hỏi hoặc góp ý nào, vui lòng liên hệ với tôi qua email: [your_email@example.com](mailto:your_email@example.com) 