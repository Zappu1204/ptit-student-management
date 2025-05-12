<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="student-sidebar position-sticky pt-3">
        <div class="list-group list-group-flush">
            <a href="../student/dashboard.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a href="../student/profile.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user me-2"></i> Thông tin cá nhân
            </a>
            <a href="../student/grades.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'grades.php' ? 'active' : ''; ?>">
                <i class="fas fa-graduation-cap me-2"></i> Điểm số
            </a>
            <a href="../student/subjects.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'subjects.php' ? 'active' : ''; ?>">
                <i class="fas fa-book me-2"></i> Môn học
            </a>
            <a href="../student/schedule.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt me-2"></i> Lịch học
            </a>
            <a href="../logout.php" class="list-group-item list-group-item-action text-primary">
                <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
            </a>
        </div>
    </div>
</div>