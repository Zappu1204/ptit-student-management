<?php
/**
 * Admin Dashboard
 */

// Include required files
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/view_helper.php';

// Set page title
$page_title = 'Tổng quan';

// Get stats
$totalStudents = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'student'")['count'] ?? 0;
$activeStudents = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'student' AND status = 'Đang học'")['count'] ?? 0;
$totalCourses = fetchOne("SELECT COUNT(*) as count FROM courses")['count'] ?? 0;
$recentStudents = fetchAll("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC LIMIT 5");

// Get departments for chart
$departmentData = fetchAll("
    SELECT department, COUNT(*) as count 
    FROM users 
    WHERE role = 'student' AND department IS NOT NULL AND department != '' 
    GROUP BY department 
    ORDER BY count DESC 
    LIMIT 5
");

// Get top 5 students
$topStudents = getTopStudents(5);

// Content to include in the layout
ob_start();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Tổng quan hệ thống</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="window.print()">
                <i class="fas fa-print"></i> In báo cáo
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-calendar-alt"></i> Học kỳ hiện tại
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Học kỳ I, 2023-2024</a></li>
            <li><a class="dropdown-item" href="#">Học kỳ II, 2023-2024</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Tất cả các học kỳ</a></li>
        </ul>
    </div>
</div>

<!-- Stats Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card admin-card bg-primary text-white h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-user-graduate card-icon"></i>
                <h2 class="display-4"><?php echo $totalStudents; ?></h2>
                <h5>Tổng số sinh viên</h5>
            </div>
            <div class="card-footer bg-white text-primary py-2">
                <a href="students.php" class="text-decoration-none d-flex justify-content-between align-items-center">
                    <span>Xem chi tiết</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card admin-card bg-success text-white h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-user-check card-icon"></i>
                <h2 class="display-4"><?php echo $activeStudents; ?></h2>
                <h5>Sinh viên đang học</h5>
            </div>
            <div class="card-footer bg-white text-success py-2">
                <a href="students.php?status=active" class="text-decoration-none d-flex justify-content-between align-items-center">
                    <span>Xem chi tiết</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card admin-card bg-info text-white h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-book card-icon"></i>
                <h2 class="display-4"><?php echo $totalCourses; ?></h2>
                <h5>Môn học</h5>
            </div>
            <div class="card-footer bg-white text-info py-2">
                <a href="courses.php" class="text-decoration-none d-flex justify-content-between align-items-center">
                    <span>Xem chi tiết</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card admin-card bg-warning text-white h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-briefcase card-icon"></i>
                <h2 class="display-4">
                    <?php 
                    $internshipCount = fetchOne("SELECT COUNT(*) as count FROM internships WHERE status = 'Đang thực tập'")['count'] ?? 0;
                    echo $internshipCount;
                    ?>
                </h2>
                <h5>Sinh viên đang thực tập</h5>
            </div>
            <div class="card-footer bg-white text-warning py-2">
                <a href="internships.php" class="text-decoration-none d-flex justify-content-between align-items-center">
                    <span>Xem chi tiết</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Department Distribution Chart -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Phân bố sinh viên theo khoa</h5>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i> Tải xuống</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i> In</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="reports.php"><i class="fas fa-chart-bar me-2"></i> Xem tất cả báo cáo</a></li>
                </ul>
            </div>
            <div class="card-body">
                <canvas id="departmentChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Top 5 Students -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Top 5 sinh viên xuất sắc nhất</h5>
                <a href="reports.php?report=top_students" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Mã SV</th>
                                <th scope="col">Họ và tên</th>
                                <th scope="col">Lớp</th>
                                <th scope="col">GPA</th>
                                <th scope="col">Xếp loại</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topStudents)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Chưa có dữ liệu</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($topStudents as $index => $student): ?>
                                    <tr>
                                        <th scope="row"><?php echo $index + 1; ?></th>
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td>
                                            <a href="student_detail.php?id=<?php echo $student['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($student['full_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['class'] ?? '---'); ?></td>
                                        <td><strong><?php echo number_format($student['gpa'], 2); ?></strong></td>
                                        <td>
                                            <?php if ($student['academic_standing'] == 'Xuất sắc'): ?>
                                                <span class="badge bg-danger"><?php echo $student['academic_standing']; ?></span>
                                            <?php elseif ($student['academic_standing'] == 'Giỏi'): ?>
                                                <span class="badge bg-warning"><?php echo $student['academic_standing']; ?></span>
                                            <?php elseif ($student['academic_standing'] == 'Khá'): ?>
                                                <span class="badge bg-info"><?php echo $student['academic_standing']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo $student['academic_standing']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Students -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Sinh viên mới đăng ký</h5>
                <a href="students.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-list"></i> Xem tất cả
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Mã SV</th>
                                <th scope="col">Họ và tên</th>
                                <th scope="col">Email</th>
                                <th scope="col">Lớp</th>
                                <th scope="col">Ngày tạo</th>
                                <th scope="col">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentStudents)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Chưa có dữ liệu</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentStudents as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['student_id'] ?? '---'); ?></td>
                                        <td>
                                            <a href="student_detail.php?id=<?php echo $student['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($student['full_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class'] ?? '---'); ?></td>
                                        <td><?php echo formatDate($student['created_at'], 'd/m/Y H:i'); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="student_detail.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="student_edit.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thao tác nhanh</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="student_add.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-user-plus text-primary me-2"></i>
                            Thêm sinh viên mới
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="course_add.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-book-medical text-success me-2"></i>
                            Thêm môn học mới
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="semester_add.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-calendar-plus text-info me-2"></i>
                            Thêm học kỳ mới
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="grades.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-graduation-cap text-warning me-2"></i>
                            Nhập điểm sinh viên
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="notifications.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-envelope text-danger me-2"></i>
                            Gửi thông báo
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Trạng thái hệ thống</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Database
                        <span class="badge bg-success">Hoạt động</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Email Service
                        <span class="badge bg-success">Hoạt động</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Backup
                        <span class="badge bg-warning">4 giờ trước</span>
                    </li>
                </ul>
                <div class="d-grid mt-3">
                    <a href="settings.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-cog me-2"></i> Quản lý hệ thống
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Department Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('departmentChart').getContext('2d');
    
    const departmentNames = [
        <?php 
        foreach ($departmentData as $dept) {
            echo "'" . addslashes($dept['department']) . "', ";
        }
        ?>
    ];
    
    const departmentCounts = [
        <?php 
        foreach ($departmentData as $dept) {
            echo $dept['count'] . ", ";
        }
        ?>
    ];
    
    const departmentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: departmentNames,
            datasets: [{
                label: 'Số lượng sinh viên',
                data: departmentCounts,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();

// Include the admin layout with our content
include '../layouts/admin.php'; 