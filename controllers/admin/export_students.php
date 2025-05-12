<?php
/**
 * Export Students to Excel
 * 
 * This file exports student data to Excel format using PHPSpreadsheet
 */

// Include required files
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/functions.php';

// Check if user is admin
requireAdmin();

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="ptit_students_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

// Get filter parameters
$department = $_GET['department'] ?? '';
$class = $_GET['class'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$query = "SELECT * FROM users WHERE role = 'student'";
$params = [];

if (!empty($department)) {
    $query .= " AND department = ?";
    $params[] = $department;
}

if (!empty($class)) {
    $query .= " AND class = ?";
    $params[] = $class;
}

if (!empty($status)) {
    $query .= " AND status = ?";
    $params[] = $status;
}

// Order by
$query .= " ORDER BY full_name ASC";

// Get students
$students = fetchAll($query, $params);

// Create Excel content using HTML table
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" 
    xmlns:x="urn:schemas-microsoft-com:office:excel" 
    xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Danh sách sinh viên</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000000;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Danh sách sinh viên PTIT</h1>
    <p>Ngày xuất: <?php echo date('d/m/Y H:i:s'); ?></p>
    
    <?php if (!empty($department) || !empty($class) || !empty($status)): ?>
    <p>
        Bộ lọc: 
        <?php if (!empty($department)): ?>Khoa: <?php echo htmlspecialchars($department); ?><?php endif; ?>
        <?php if (!empty($class)): ?><?php echo !empty($department) ? ' | ' : ''; ?>Lớp: <?php echo htmlspecialchars($class); ?><?php endif; ?>
        <?php if (!empty($status)): ?><?php echo (!empty($department) || !empty($class)) ? ' | ' : ''; ?>Trạng thái: <?php echo htmlspecialchars($status); ?><?php endif; ?>
    </p>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Mã SV</th>
                <th>Họ và tên</th>
                <th>Email</th>
                <th>Lớp</th>
                <th>Khoa</th>
                <th>Năm nhập học</th>
                <th>Ngày sinh</th>
                <th>Giới tính</th>
                <th>Số điện thoại</th>
                <th>Địa chỉ</th>
                <th>Trạng thái</th>
                <th>GPA</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="13" style="text-align:center;">Không có dữ liệu</td>
                </tr>
            <?php else: ?>
                <?php foreach ($students as $index => $student): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($student['student_id'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['class'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['department'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['entry_year'] ?? ''); ?></td>
                        <td><?php echo !empty($student['date_of_birth']) ? date('d/m/Y', strtotime($student['date_of_birth'])) : ''; ?></td>
                        <td><?php echo htmlspecialchars($student['gender'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['phone'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['address'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['status'] ?? ''); ?></td>
                        <td><?php echo number_format(calculateGPA($student['id']), 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html> 