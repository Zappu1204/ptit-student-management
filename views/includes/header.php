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
    <?php
    // Determine correct path for assets
    $is_admin = isset($is_admin) && $is_admin;
    $is_student = isset($is_student) && $is_student;
    $base_path = ($is_admin || $is_student) ? '../../' : '../';
    ?>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <header class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top py-2">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo $is_admin ? '../../admin/dashboard.php' : ($is_student ? '../../student/dashboard.php' : '../index.php'); ?>">
                <i class="fas fa-graduation-cap me-2"></i>
                <span>PTIT Student Management</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user me-1"></i> <?php echo $_SESSION['username']; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>controllers/public/logout_process.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main>
        <!-- Flash Messages -->
        <div class="container-fluid mt-3">
            <?php echo displayFlashMessages(); ?>
        </div>
