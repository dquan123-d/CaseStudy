<?php
// Bật báo lỗi để debug (nhớ tắt khi triển khai production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Kiểm tra headers đã được gửi chưa
if (headers_sent($filename, $linenum)) {
    die("Lỗi: Headers đã được gửi từ file $filename tại dòng $linenum. Không thể thực hiện chuyển hướng.");
}

// 2. Kiểm tra và quản lý session
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Cấu hình session bảo mật
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    session_start();
}

// 3. Tạo CSRF token nếu chưa có (phòng trường hợp trang logout truy cập trực tiếp)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 4. Kiểm tra CSRF token nếu logout từ form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Lỗi bảo mật: Token không hợp lệ');
    }
}

// 5. Xóa tất cả biến session
$_SESSION = [];

// 6. Regenerate session ID để phòng chống session fixation
if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
}

// 7. Xóa cookie session nếu có
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 8. Hủy hoàn toàn session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// 9. Ngăn chặn caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// 10. Chuyển hướng về trang login với JavaScript fallback
echo '<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="refresh" content="0;url=login.php?logout=1">
    <title>Đăng xuất</title>
    <script>
        window.location.href = "login.php?logout=1";
    </script>
</head>
<body>
    <p>Nếu không tự động chuyển hướng, <a href="login.php?logout=1">nhấn vào đây</a>.</p>
</body>
</html>';
exit;
?>