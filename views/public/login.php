<?php
/**
 * Login page
 */

// Include required files
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/auth.php';

// Start session
startSessionIfNotStarted();

// Check if user is already logged in
if (isLoggedIn()) {
    // Redirect based on user role
    if (isAdmin()) {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../student/dashboard.php');
    }
    exit;
}

// Check for errors from login attempt
$errors = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']);

// Get saved username and user type from session
$username = $_SESSION['login_username'] ?? '';
unset($_SESSION['login_username']);

$default_user_type = $_SESSION['login_user_type'] ?? ($_GET['user_type'] ?? 'student');
unset($_SESSION['login_user_type']);

// Set page title
$page_title = 'Đăng nhập';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>PTIT Student Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-card">
                    <div class="card-header text-center">
                        <img src="../../assets/images/ptit-logo.png" alt="PTIT Logo" class="auth-logo mb-2" onerror="this.src='https://upload.wikimedia.org/wikipedia/vi/c/c6/Logo_PTIT.png'; this.onerror=null;">
                        <h4 class="mb-0">Đăng nhập</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="../../controllers/public/login_process.php" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="user_type" class="form-label">Đăng nhập với tư cách</label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="student" <?php echo $default_user_type === 'student' ? 'selected' : ''; ?>>Sinh viên</option>
                                    <option value="admin" <?php echo $default_user_type === 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                                </select>
                                <div class="invalid-feedback">Vui lòng chọn loại tài khoản.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên đăng nhập</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Nhập tên đăng nhập hoặc mã sinh viên" value="<?php echo htmlspecialchars($username); ?>" required>
                                    <div class="invalid-feedback">Vui lòng nhập tên đăng nhập.</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">Vui lòng nhập mật khẩu.</div>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                            </div>
                              <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger">Đăng nhập</button>
                                <a href="../../index.php" class="btn btn-outline-secondary">Quay lại trang chủ</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="../../assets/js/script.js"></script>
    
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
        
        // Form validation
        (function() {
            'use strict';
            
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
