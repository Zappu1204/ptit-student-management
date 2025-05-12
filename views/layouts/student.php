<?php
/**
 * Student Layout
 */

// Include required files
require_once __DIR__ . '/../../config/auth.php';

// Check if user is student
requireStudent();

// Get current user
$currentUser = getCurrentUser();

// Calculate student's GPA
require_once __DIR__ . '/../../config/functions.php';
$gpa = calculateGPA($currentUser['id']);
$academicStanding = getAcademicStanding($gpa);
$totalCredits = calculateTotalCredits($currentUser['id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Sinh viên - PTIT</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/student.css">
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content {
            flex: 1;
        }
        
        .sidebar {
            background-color: #343a40;
            min-height: calc(100vh - 56px);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
            margin-bottom: 0.25rem;
            border-radius: 0.25rem;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #0d6efd;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 0.5rem;
        }
        
        .student-card {
            transition: all 0.3s ease;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .card-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .student-profile-header {
            background-color: #0d6efd;
            color: white;
            padding: 2rem 0;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .student-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../student/dashboard.php">
                <img src="../../assets/images/ptit-logo.png" alt="PTIT Logo" height="30" class="d-inline-block align-text-top me-2" onerror="this.src='https://upload.wikimedia.org/wikipedia/vi/c/c6/Logo_PTIT.png'; this.onerror=null;">
                PTIT Student
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($currentUser['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="../student/profile.php"><i class="fas fa-user me-2"></i> Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="../student/change_password.php"><i class="fas fa-key me-2"></i> Đổi mật khẩu</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../../controllers/public/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid main-content">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse" id="sidebarMenu">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? ' active' : ''; ?>" href="../student/dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Tổng quan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? ' active' : ''; ?>" href="../student/profile.php">
                                <i class="fas fa-user"></i> Thông tin cá nhân
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? ' active' : ''; ?>" href="../student/courses.php">
                                <i class="fas fa-book"></i> Danh sách môn học
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'grades.php' ? ' active' : ''; ?>" href="../student/grades.php">
                                <i class="fas fa-graduation-cap"></i> Bảng điểm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'gpa.php' ? ' active' : ''; ?>" href="../student/gpa.php">
                                <i class="fas fa-chart-line"></i> Điểm trung bình
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? ' active' : ''; ?>" href="../student/schedule.php">
                                <i class="fas fa-calendar-alt"></i> Lịch học
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'internships.php' ? ' active' : ''; ?>" href="../student/internships.php">
                                <i class="fas fa-briefcase"></i> Thực tập
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Student Info Summary -->
                <div class="student-info-summary mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <div class="col-md-2 text-center bg-light p-3">
                                    <?php if (!empty($currentUser['avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($currentUser['avatar']); ?>" alt="<?php echo htmlspecialchars($currentUser['full_name']); ?>" class="rounded-circle mb-2" style="width: 70px; height: 70px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 70px; height: 70px; font-size: 32px;">
                                            <?php echo strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($currentUser['student_id']); ?></h6>
                                </div>
                                <div class="col-md-5 p-3">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($currentUser['full_name']); ?></h5>
                                    <p class="mb-1"><i class="fas fa-graduation-cap me-2"></i> <?php echo htmlspecialchars($currentUser['department'] ?? 'Chưa có khoa'); ?></p>
                                    <p class="mb-1"><i class="fas fa-users me-2"></i> <?php echo htmlspecialchars($currentUser['class'] ?? 'Chưa có lớp'); ?></p>
                                    <p class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Năm nhập học: <?php echo htmlspecialchars($currentUser['entry_year'] ?? '---'); ?></p>
                                </div>
                                <div class="col-md-5 p-3 bg-light">
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <h6 class="text-muted mb-1">GPA</h6>
                                            <h4 class="mb-0"><?php echo number_format($gpa, 2); ?></h4>
                                            <small class="text-muted"><?php echo htmlspecialchars($academicStanding); ?></small>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <h6 class="text-muted mb-1">Tín chỉ</h6>
                                            <h4 class="mb-0"><?php echo (int)$totalCredits; ?></h4>
                                            <small class="text-muted">Đã hoàn thành</small>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-muted mb-1">Trạng thái</h6>
                                            <h5 class="mb-0">
                                                <?php if ($currentUser['status'] == 'Đang học'): ?>
                                                    <span class="badge bg-success">Đang học</span>
                                                <?php elseif ($currentUser['status'] == 'Đã tốt nghiệp'): ?>
                                                    <span class="badge bg-primary">Đã tốt nghiệp</span>
                                                <?php elseif ($currentUser['status'] == 'Bảo lưu'): ?>
                                                    <span class="badge bg-warning">Bảo lưu</span>
                                                <?php elseif ($currentUser['status'] == 'Đình chỉ'): ?>
                                                    <span class="badge bg-danger">Đình chỉ</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($currentUser['status']); ?></span>
                                                <?php endif; ?>
                                            </h5>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-muted mb-1">Email</h6>
                                            <p class="mb-0 small"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Page Content -->
                <?php 
                // Display flash message if exists
                if (function_exists('displayFlashMessage')) {
                    echo displayFlashMessage();
                }
                
                // Include the content template
                if (isset($content_template)) {
                    include $content_template;
                }
                ?>
            </main>
        </div>
    </div>
    
    <footer class="bg-dark text-white py-3 mt-auto">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> - Hệ thống Quản lý Sinh viên PTIT</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Phiên bản 1.0.0</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Scripts -->
    <script src="../../assets/js/student.js"></script>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
                }
            });
        });
    </script>
</body>
</html> 