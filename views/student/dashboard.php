<?php
/**
 * Student Dashboard
 */

// Include required files
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/view_helper.php';

// Set page title
$page_title = 'Tổng quan';

// Get current user
$user_id = $_SESSION['user_id'];

// Get student grades
$studentGrades = fetchAll(
    "SELECT g.*, c.name as course_name, c.course_code, c.credits, s.name as semester_name, s.academic_year 
     FROM grades g
     JOIN courses c ON g.course_id = c.id
     JOIN semesters s ON g.semester_id = s.id
     WHERE g.user_id = ?
     ORDER BY s.academic_year DESC, s.name DESC, c.name ASC",
    [$user_id]
);

// Get upcoming courses
$upcomingCourses = fetchAll(
    "SELECT c.*, s.name as semester_name, s.academic_year 
     FROM courses c
     JOIN semesters s ON c.semester_id = s.id
     WHERE s.is_current = 1
     ORDER BY c.name ASC
     LIMIT 5"
);

// Get student's internship status
$internships = fetchAll(
    "SELECT i.*, c.name as company_name 
     FROM internships i
     LEFT JOIN (
         SELECT id, name as company_name FROM companies
     ) c ON i.company_id = c.id
     WHERE i.user_id = ?
     ORDER BY i.start_date DESC",
    [$user_id]
);

// Calculate GPA for different semesters
$semesterGPAs = [];
$semesters = fetchAll("SELECT id, name, academic_year FROM semesters ORDER BY academic_year DESC, name DESC");

foreach ($semesters as $semester) {
    $gpa = calculateGPA($user_id, $semester['id']);
    if ($gpa > 0) { // Only include semesters with grades
        $semesterGPAs[$semester['name'] . ' ' . $semester['academic_year']] = $gpa;
    }
}

// Content to include in the layout
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Tổng quan học tập</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print"></i> In báo cáo
            </button>
        </div>
    </div>
</div>

<!-- Academic Stats -->
<div class="row">
    <!-- GPA Card -->
    <div class="col-md-3 mb-4">
        <div class="card student-card bg-primary text-white h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-award card-icon"></i>
                <h2 class="display-4"><?php echo number_format(calculateGPA($user_id), 2); ?></h2>
                <h5>Điểm trung bình tích lũy</h5>
            </div>
            <div class="card-footer bg-white text-primary py-2">
                <a href="gpa.php" class="text-decoration-none d-flex justify-content-between align-items-center">
                    <span>Xem chi tiết</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Credits Card -->
    <div class="col-md-3 mb-4">
        <div class="card student-card bg-success text-white h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-graduation-cap card-icon"></i>
                <h2 class="display-4"><?php echo (int)calculateTotalCredits($user_id); ?></h2>
                <h5>Tín chỉ đã tích lũy</h5>
            </div>
            <div class="card-footer bg-white text-success py-2">
                <a href="courses.php" class="text-decoration-none d-flex justify-content-between align-items-center">
                    <span>Xem chi tiết</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Courses Card -->
    <div class="col-md-3 mb-4">
        <div class="card student-card bg-info text-white h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-book card-icon"></i>
                <?php 
                $totalCourses = fetchOne(
                    "SELECT COUNT(DISTINCT course_id) as count FROM grades WHERE user_id = ? AND grade_status = 'Đạt'", 
                    [$user_id]
                )['count'] ?? 0;
                ?>
                <h2 class="display-4"><?php echo $totalCourses; ?></h2>
                <h5>Môn học đã hoàn thành</h5>
            </div>
            <div class="card-footer bg-white text-info py-2">
                <a href="courses.php" class="text-decoration-none d-flex justify-content-between align-items-center">
                    <span>Xem chi tiết</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Academic Standing Card -->
    <div class="col-md-3 mb-4">
        <div class="card student-card bg-warning text-white h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-star card-icon"></i>
                <?php $academicStanding = getAcademicStanding(calculateGPA($user_id)); ?>
                <h2 class="h1"><?php echo htmlspecialchars($academicStanding); ?></h2>
                <h5>Xếp loại học lực</h5>
            </div>
            <div class="card-footer bg-white text-warning py-2">
                <a href="gpa.php" class="text-decoration-none d-flex justify-content-between align-items-center">
                    <span>Xem chi tiết</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- GPA Chart -->
    <div class="col-md-7 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Biểu đồ điểm trung bình theo học kỳ</h5>
                <a href="gpa.php" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($semesterGPAs)): ?>
                    <div class="text-center py-5">
                        <p class="text-muted">Chưa có dữ liệu điểm trung bình</p>
                    </div>
                <?php else: ?>
                    <canvas id="gpaChart" height="250"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Grades -->
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Điểm số gần đây</h5>
                <a href="grades.php" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-list"></i> Xem tất cả
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($studentGrades)): ?>
                    <div class="text-center py-5">
                        <p class="text-muted">Chưa có dữ liệu điểm số</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Mã môn</th>
                                    <th>Tên môn học</th>
                                    <th>Học kỳ</th>
                                    <th>Điểm</th>
                                    <th>Xếp loại</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Display just the 5 most recent grades
                                $recentGrades = array_slice($studentGrades, 0, 5); 
                                foreach ($recentGrades as $grade): 
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['course_code']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['semester_name'] . ' ' . $grade['academic_year']); ?></td>
                                        <td><strong><?php echo number_format($grade['total_score'], 1); ?></strong></td>
                                        <td>
                                            <?php 
                                            $letterGrade = convertToLetterGrade($grade['total_score']);
                                            $badgeClass = 'bg-secondary';
                                            
                                            if (in_array($letterGrade, ['A+', 'A'])) {
                                                $badgeClass = 'bg-danger';
                                            } elseif (in_array($letterGrade, ['B+', 'B'])) {
                                                $badgeClass = 'bg-warning';
                                            } elseif (in_array($letterGrade, ['C+', 'C'])) {
                                                $badgeClass = 'bg-info';
                                            } elseif (in_array($letterGrade, ['D+', 'D'])) {
                                                $badgeClass = 'bg-primary';
                                            } elseif ($letterGrade === 'F') {
                                                $badgeClass = 'bg-dark';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $letterGrade; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Upcoming Courses -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Môn học học kỳ hiện tại</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcomingCourses)): ?>
                    <div class="text-center py-5">
                        <p class="text-muted">Không có môn học trong học kỳ hiện tại</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcomingCourses as $course): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($course['name']); ?></h6>
                                    <small><?php echo $course['credits']; ?> tín chỉ</small>
                                </div>
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="text-muted">Mã: <?php echo htmlspecialchars($course['course_code']); ?></small>
                                    <small class="text-muted"><?php echo htmlspecialchars($course['semester_name'] . ' ' . $course['academic_year']); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Internships -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Thực tập</h5>
                <a href="internships.php" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-briefcase"></i> Quản lý
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($internships)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                        <p>Bạn chưa đăng ký thực tập</p>
                        <a href="internships.php" class="btn btn-primary mt-2">Đăng ký ngay</a>
                    </div>
                <?php else: ?>
                    <?php 
                    $currentInternship = null;
                    foreach ($internships as $internship) {
                        if (in_array($internship['status'], ['Đang thực tập', 'Đã xếp'])) {
                            $currentInternship = $internship;
                            break;
                        }
                    }
                    
                    if ($currentInternship): 
                    ?>
                        <div class="card border-primary mb-3">
                            <div class="card-header bg-primary text-white">
                                <strong>Thực tập hiện tại</strong>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($currentInternship['company_name'] ?? $currentInternship['company_id']); ?></h5>
                                <p class="card-text">
                                    <strong>Vị trí:</strong> <?php echo htmlspecialchars($currentInternship['position']); ?><br>
                                    <strong>Thời gian:</strong> <?php echo formatDate($currentInternship['start_date']); ?> - 
                                    <?php echo !empty($currentInternship['end_date']) ? formatDate($currentInternship['end_date']) : 'Hiện tại'; ?><br>
                                    <strong>Trạng thái:</strong> 
                                    <span class="badge bg-success"><?php echo htmlspecialchars($currentInternship['status']); ?></span>
                                </p>
                                <?php if (!empty($currentInternship['supervisor_name'])): ?>
                                    <p class="card-text">
                                        <strong>Người hướng dẫn:</strong> <?php echo htmlspecialchars($currentInternship['supervisor_name']); ?><br>
                                        <strong>Liên hệ:</strong> <?php echo htmlspecialchars($currentInternship['supervisor_contact'] ?? 'Chưa có thông tin'); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Bạn đã đăng ký thực tập nhưng chưa được xếp vào công ty. Vui lòng chờ thông báo từ nhà trường.
                        </div>
                        
                        <h6 class="mt-3">Nguyện vọng đã đăng ký:</h6>
                        <ol class="list-group list-group-numbered">
                            <?php foreach ($internships as $index => $internship): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold"><?php echo htmlspecialchars($internship['company_name'] ?? $internship['company_id']); ?></div>
                                        Vị trí: <?php echo htmlspecialchars($internship['position']); ?>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">NV<?php echo $internship['preference_order']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($semesterGPAs)): ?>
<script>
// GPA Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('gpaChart').getContext('2d');
    
    const semesters = [
        <?php 
        foreach (array_keys($semesterGPAs) as $semester) {
            echo "'" . addslashes($semester) . "', ";
        }
        ?>
    ];
    
    const gpas = [
        <?php 
        foreach ($semesterGPAs as $gpa) {
            echo number_format($gpa, 2) . ", ";
        }
        ?>
    ];
    
    const gpaChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: semesters,
            datasets: [{
                label: 'GPA',
                data: gpas,
                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(13, 110, 253, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    min: Math.max(0, Math.min(...gpas) - 0.5),
                    max: Math.min(10, Math.max(...gpas) + 0.5),
                    ticks: {
                        stepSize: 0.5
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'GPA: ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();

// Include the student layout with our content
include '../layouts/student.php'; 