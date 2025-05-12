<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="admin-sidebar position-sticky pt-3">
        <div class="list-group list-group-flush">
            <a href="../admin/dashboard.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a href="../admin/students.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate me-2"></i> Quản lý Sinh viên
            </a>
            <a href="../admin/subjects.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'subjects.php' ? 'active' : ''; ?>">
                <i class="fas fa-book me-2"></i> Quản lý Môn học
            </a>
            <a href="../admin/grades.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'grades.php' ? 'active' : ''; ?>">
                <i class="fas fa-graduation-cap me-2"></i> Quản lý Điểm số
            </a>
            <a href="../admin/semesters.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'semesters.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt me-2"></i> Quản lý Học kỳ
            </a>
            <a href="../admin/reports.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar me-2"></i> Báo cáo & Thống kê
            </a>
            <a href="../logout.php" class="list-group-item list-group-item-action text-primary">
                <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
            </a>
        </div>
    </div>
</div>