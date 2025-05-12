<div class="col-md-3 col-lg-2">
    <div class="card border-0 shadow-sm mb-4">
        <div class="list-group list-group-flush">
            <a href="<?php echo $base_path; ?>views/student/dashboard.html" class="list-group-item list-group-item-action <?php echo basename($_SERVER['SCRIPT_NAME']) === 'dashboard.html' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Trang chủ
            </a>
            <a href="<?php echo $base_path; ?>views/student/profile.html" class="list-group-item list-group-item-action <?php echo basename($_SERVER['SCRIPT_NAME']) === 'profile.html' ? 'active' : ''; ?>">
                <i class="fas fa-user me-2"></i> Thông tin cá nhân
            </a>
            <a href="<?php echo $base_path; ?>views/student/grades.html" class="list-group-item list-group-item-action <?php echo basename($_SERVER['SCRIPT_NAME']) === 'grades.html' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar me-2"></i> Bảng điểm
            </a>
            <a href="<?php echo $base_path; ?>views/student/subjects.html" class="list-group-item list-group-item-action <?php echo basename($_SERVER['SCRIPT_NAME']) === 'subjects.html' ? 'active' : ''; ?>">
                <i class="fas fa-book me-2"></i> Môn học
            </a>
        </div>
    </div>
</div>
