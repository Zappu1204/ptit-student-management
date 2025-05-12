<?php
/**
 * Admin Layout
 */

// Include required files
require_once __DIR__ . '/../../config/auth.php';

// Check if user is admin
requireAdmin();

// Get current user
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Quản trị - PTIT</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    
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
            background-color: #dc3545;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 0.5rem;
        }
        
        .admin-card {
            transition: all 0.3s ease;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .card-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../admin/dashboard.php">
                <img src="../../assets/images/ptit-logo.png" alt="PTIT Logo" height="30" class="d-inline-block align-text-top me-2" onerror="this.src='https://upload.wikimedia.org/wikipedia/vi/c/c6/Logo_PTIT.png'; this.onerror=null;">
                PTIT Admin
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
                            <li><a class="dropdown-item" href="../admin/profile.php"><i class="fas fa-user me-2"></i> Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="../admin/change_password.php"><i class="fas fa-key me-2"></i> Đổi mật khẩu</a></li>
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
                            <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? ' active' : ''; ?>" href="../admin/dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Tổng quan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo strpos($_SERVER['PHP_SELF'], 'students') !== false ? ' active' : ''; ?>" href="../admin/students.php">
                                <i class="fas fa-user-graduate"></i> Quản lý Sinh viên
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo strpos($_SERVER['PHP_SELF'], 'courses') !== false ? ' active' : ''; ?>" href="../admin/courses.php">
                                <i class="fas fa-book"></i> Quản lý Môn học
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo strpos($_SERVER['PHP_SELF'], 'grades') !== false ? ' active' : ''; ?>" href="../admin/grades.php">
                                <i class="fas fa-graduation-cap"></i> Quản lý Điểm số
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo strpos($_SERVER['PHP_SELF'], 'semesters') !== false ? ' active' : ''; ?>" href="../admin/semesters.php">
                                <i class="fas fa-calendar-alt"></i> Quản lý Học kỳ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo strpos($_SERVER['PHP_SELF'], 'internships') !== false ? ' active' : ''; ?>" href="../admin/internships.php">
                                <i class="fas fa-briefcase"></i> Quản lý Thực tập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo strpos($_SERVER['PHP_SELF'], 'reports') !== false ? ' active' : ''; ?>" href="../admin/reports.php">
                                <i class="fas fa-chart-bar"></i> Báo cáo & Thống kê
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo strpos($_SERVER['PHP_SELF'], 'notifications') !== false ? ' active' : ''; ?>" href="../admin/notifications.php">
                                <i class="fas fa-bell"></i> Gửi thông báo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? ' active' : ''; ?>" href="../admin/settings.php">
                                <i class="fas fa-cog"></i> Cài đặt hệ thống
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
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
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Scripts -->
    <script src="../../assets/js/admin.js"></script>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
                }
            });
            
            $('.datatable-export').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
                }
            });
        });
    </script>
</body>
</html> 