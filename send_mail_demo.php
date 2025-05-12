<?php
// Bao gồm các tệp cần thiết của PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Điều chỉnh đường dẫn này cho phù hợp với cấu trúc thư mục của bạn
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

/**
 * Get the variable environment from the .env file
 * @param string $filePath - Path to the .env file
 * @return array - Array of environment variables
 * @throws Exception
 */
function loadEnv($filePath) {
    $env = [];

    if (!file_exists($filePath)) {
        throw new Exception("Tệp .env không tồn tại: $filePath");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Bỏ qua dòng trống hoặc dòng bắt đầu bằng '#'
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        // Tách tên và giá trị biến
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Loại bỏ dấu ngoặc kép nếu có
            $value = trim($value, "\"'");

            $env[$name] = $value;
        }
    }
    return $env;
}

// --- Tải biến môi trường ---
$envConfig = [];
try {
    // Đường dẫn đến file .env, __DIR__ là thư mục chứa file PHP này (scripts/)
    // nên '../.env' trỏ ra thư mục gốc (your_project_root)
    $envPath = __DIR__ . '/../.env';
    $envConfig = loadEnv($envPath);
} catch (Exception $e) {
    die("Lỗi khi tải tệp .env: " . $e->getMessage());
}

// --- Lấy các giá trị cấu hình mail từ $envConfig ---
// Sử dụng toán tử ?? để cung cấp giá trị mặc định nếu biến không tồn tại trong .env
$mailHost       = $envConfig['MAIL_HOST'] ?? 'smtp.gmail.com';
$mailPort       = $envConfig['MAIL_PORT'] ?? 587;
$mailUsername   = $envConfig['MAIL_USERNAME'] ?? '';
$mailPassword   = $envConfig['MAIL_PASSWORD'] ?? '';
$mailEncryption = $envConfig['MAIL_ENCRYPTION'] ?? 'tls';
$mailFromAddr   = $envConfig['MAIL_FROM_ADDRESS'] ?? 'no-reply@example.com';
$mailFromName   = $envConfig['MAIL_FROM_NAME'] ?? 'My Application';

// Thông tin người nhận (có thể lấy từ form, database, hoặc .env như ví dụ này)
$recipientEmail = $envConfig['MAIL_TO_ADDRESS'] ?? 'test@example.com';
$recipientName  = $envConfig['MAIL_TO_NAME'] ?? 'Test User';


// --- Khởi tạo và cấu hình PHPMailer ---
$mail = new PHPMailer(true); // true để bật exceptions

try {
    // Cấu hình Server
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Bật để xem log debug chi tiết
    $mail->isSMTP();
    $mail->Host       = $mailHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailUsername;
    $mail->Password   = $mailPassword;

    if (strtolower($mailEncryption) == 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Mặc định là tls
    }

    $mail->Port       = (int)$mailPort; // Chuyển port sang kiểu int
    $mail->CharSet    = 'UTF-8';

    // Người gửi và người nhận
    $mail->setFrom($mailFromAddr, $mailFromName);
    $mail->addAddress($recipientEmail, $recipientName); // Thêm người nhận
    // $mail->addReplyTo('info@example.com', 'Information');
    // $mail->addCC('cc@example.com');
    // $mail->addBCC('bcc@example.com');

    // Nội dung Email
    $mail->isHTML(true); // Đặt định dạng email là HTML
    $mail->Subject = 'Đây là email thử nghiệm từ PHP với .env';
    $mail->Body    = "<h1>Xin chào " . htmlspecialchars($recipientName) . "!</h1>
                      <p>Đây là một email thử nghiệm được gửi bằng PHPMailer.</p>
                      <p>Cấu hình được tải từ tệp <code>.env</code>.</p>
                      <p>Trân trọng,<br>Hệ thống của bạn</p>";
    $mail->AltBody = "Xin chào " . $recipientName . "!\n" .
                     "Đây là một email thử nghiệm được gửi bằng PHPMailer.\n" .
                     "Cấu hình được tải từ tệp .env.\n\n" .
                     "Trân trọng,\nHệ thống của bạn";

    $mail->send();
    echo 'Email đã được gửi thành công đến ' . htmlspecialchars($recipientEmail) . '!';

} catch (Exception $e) {
    echo "Không thể gửi email. Lỗi Mailer: {$mail->ErrorInfo}. Chi tiết: {$e->getMessage()}";
}

?>