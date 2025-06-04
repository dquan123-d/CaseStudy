<?php
session_start();
require_once '../config/database.php';

// Khởi tạo biến lỗi
$error = '';

// Xử lý đăng nhập khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra các trường bắt buộc
    if (empty($_POST['username'])) {
        $error = "Vui lòng nhập tên đăng nhập";
    } elseif (empty($_POST['password'])) {
        $error = "Vui lòng nhập mật khẩu";
    } else {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;

        // Truy vấn kiểm tra thông tin đăng nhập với prepared statement
        $query = "SELECT id, name, username, email, password, role, phone, avatar FROM users WHERE username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                
                // Kiểm tra mật khẩu (vì đang lưu plain text nên so sánh trực tiếp)
                if ($password === $user['password']) {
                    // Lưu thông tin user vào session
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['phone'] = $user['phone'];
                    $_SESSION['avatar'] = $user['avatar'];
                    $_SESSION['logged_in'] = true;

                    // Ghi nhớ đăng nhập nếu người dùng chọn
                    if ($remember) {
                        setcookie('remember_username', $username, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                    }

                    // Chuyển hướng đến trang index.php
                    header('Location: ../pages/index.php');
                    exit();
                } else {
                    $error = "Tên đăng nhập hoặc mật khẩu không chính xác";
                }
            } else {
                $error = "Tên đăng nhập hoặc mật khẩu không chính xác";
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $error = "Lỗi hệ thống, vui lòng thử lại sau";
        }
    }
}

// Kiểm tra cookie ghi nhớ đăng nhập
if (empty($_SESSION['logged_in']) && isset($_COOKIE['remember_username'])) {
    $username = mysqli_real_escape_string($conn, $_COOKIE['remember_username']);
    $query = "SELECT id, name, username, email, role, phone, avatar FROM users WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['avatar'] = $user['avatar'];
            $_SESSION['logged_in'] = true;
            
            header('Location: ../pages/index.php');
            exit();
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập | Big Home</title>
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Đăng Nhập</h1>
            <p>Nhập thông tin để truy cập tài khoản của bạn</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" class="form-control" 
                       placeholder="Nhập tên đăng nhập" required
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : (isset($_COOKIE['remember_username']) ? htmlspecialchars($_COOKIE['remember_username']) : ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Nhập mật khẩu" required>
            </div>
            
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" <?php echo isset($_COOKIE['remember_username']) ? 'checked' : ''; ?>> 
                    Ghi nhớ đăng nhập
                </label>
                <a href="forgot_password.php" class="forgot-password">Quên mật khẩu?</a>
            </div>
            
            <button type="submit" class="login-btn">Đăng Nhập</button>
            
            <div class="register-link">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </div>
        </form>
    </div>
</body>
</html>

<?php
// Đóng kết nối database
mysqli_close($conn);
?>