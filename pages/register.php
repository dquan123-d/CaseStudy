<?php
session_start();
require_once '../config/database.php';

// Khởi tạo biến
$error = '';
$success = '';

// Xử lý đăng ký khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree_terms = isset($_POST['agree_terms']) ? true : false;

    // Validate dữ liệu
    if (empty($name)) {
        $error = "Vui lòng nhập họ và tên";
    } elseif (empty($username)) {
        $error = "Vui lòng nhập tên đăng nhập";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới";
    } elseif (empty($email)) {
        $error = "Vui lòng nhập địa chỉ email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Địa chỉ email không hợp lệ";
    } elseif (empty($phone)) {
        $error = "Vui lòng nhập số điện thoại";
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $error = "Số điện thoại phải có 10-11 chữ số";
    } elseif (empty($password)) {
        $error = "Vui lòng nhập mật khẩu";
    } elseif (strlen($password) < 8) {
        $error = "Mật khẩu phải có ít nhất 8 ký tự";
    } elseif (empty($confirm_password)) {
        $error = "Vui lòng xác nhận mật khẩu";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp";
    } elseif (!$agree_terms) {
        $error = "Bạn phải đồng ý với điều khoản dịch vụ";
    } else {
        // Kiểm tra username, email và số điện thoại đã tồn tại chưa
        $check_user = "SELECT id FROM users WHERE username = ? OR email = ? OR phone = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $check_user);
        mysqli_stmt_bind_param($stmt, "sss", $username, $email, $phone);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            // Kiểm tra cụ thể cái nào bị trùng
            $check_username = "SELECT id FROM users WHERE username = ? LIMIT 1";
            $stmt_username = mysqli_prepare($conn, $check_username);
            mysqli_stmt_bind_param($stmt_username, "s", $username);
            mysqli_stmt_execute($stmt_username);
            mysqli_stmt_store_result($stmt_username);
            
            $check_email = "SELECT id FROM users WHERE email = ? LIMIT 1";
            $stmt_email = mysqli_prepare($conn, $check_email);
            mysqli_stmt_bind_param($stmt_email, "s", $email);
            mysqli_stmt_execute($stmt_email);
            mysqli_stmt_store_result($stmt_email);
            
            $check_phone = "SELECT id FROM users WHERE phone = ? LIMIT 1";
            $stmt_phone = mysqli_prepare($conn, $check_phone);
            mysqli_stmt_bind_param($stmt_phone, "s", $phone);
            mysqli_stmt_execute($stmt_phone);
            mysqli_stmt_store_result($stmt_phone);
            
            if (mysqli_stmt_num_rows($stmt_username) > 0) {
                $error = "Tên đăng nhập này đã được sử dụng";
            } elseif (mysqli_stmt_num_rows($stmt_email) > 0) {
                $error = "Email này đã được đăng ký";
            } else {
                $error = "Số điện thoại này đã được đăng ký";
            }
            
            mysqli_stmt_close($stmt_username);
            mysqli_stmt_close($stmt_email);
            mysqli_stmt_close($stmt_phone);
        } else {
            // Thêm người dùng vào database (LƯU Ý: Lưu mật khẩu plain text - KHÔNG AN TOÀN)
            $query = "INSERT INTO users (name, username, email, phone, password, role, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, ?, 0, NOW(), NOW())";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sssss", $name, $username, $email, $phone, $password);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Đăng ký thành công! Bạn sẽ được chuyển đến trang đăng nhập";
                
                // Lưu thông báo thành công vào session
                $_SESSION['register_success'] = $success;
                
                // Chuyển hướng đến trang đăng nhập sau 2 giây
                header("Refresh: 2; url=login.php");
            } else {
                $error = "Đăng ký không thành công. Vui lòng thử lại sau";
            }
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
    <title>Đăng Ký | Phòng Trọ</title>
    <link rel="stylesheet" href="../css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .alert-danger, .alert-success {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="fas fa-user-plus"></i> Tạo Tài Khoản</h1>
                <p>Đăng ký để bắt đầu sử dụng dịch vụ</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form id="registerForm" class="auth-form" method="POST" action="">
                <div class="form-group">
                    <label for="register-name">Họ và tên</label>
                    <div class="input-with-icon">
                        <input type="text" id="register-name" name="name" class="input-field" 
                               placeholder="Nhập họ và tên" required
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="register-username">Tên đăng nhập</label>
                    <div class="input-with-icon">
                        <input type="text" id="register-username" name="username" class="input-field" 
                               placeholder="Nhập tên đăng nhập" required
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <p class="form-hint">
                        <i class="fas fa-info-circle"></i> Chỉ chứa chữ cái, số và dấu gạch dưới
                    </p>
                </div>
                
                <div class="form-group">
                    <label for="register-email">Địa chỉ Email</label>
                    <div class="input-with-icon">
                        <input type="email" id="register-email" name="email" class="input-field" 
                               placeholder="Nhập email của bạn" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="register-phone">Số điện thoại</label>
                    <div class="input-with-icon">
                        <input type="tel" id="register-phone" name="phone" class="input-field" 
                               placeholder="Nhập số điện thoại" required
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                               pattern="[0-9]{10,11}">
                        <i class="fas fa-phone"></i>
                    </div>
                    <p class="form-hint">
                        <i class="fas fa-info-circle"></i> Số điện thoại phải có 10-11 chữ số
                    </p>
                </div>
                
                <div class="form-group">
                    <label for="register-password">Mật khẩu</label>
                    <div class="input-with-icon password-container">
                        <input type="password" id="register-password" name="password" class="input-field" 
                               placeholder="Tạo mật khẩu" required>
                        <i class="fas fa-lock"></i>
                        <button type="button" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="form-hint">
                        <i class="fas fa-info-circle"></i> Ít nhất 8 ký tự, bao gồm chữ và số
                    </p>
                </div>
                
                <div class="form-group">
                    <label for="register-confirm-password">Xác nhận mật khẩu</label>
                    <div class="input-with-icon password-container">
                        <input type="password" id="register-confirm-password" name="confirm_password" 
                               class="input-field" placeholder="Nhập lại mật khẩu" required>
                        <i class="fas fa-lock"></i>
                        <button type="button" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="checkbox-container">
                    <input type="checkbox" id="agree-terms" name="agree_terms" required
                        <?php echo isset($_POST['agree_terms']) ? 'checked' : ''; ?>>
                    <label for="agree-terms">Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a></label>
                </div>
                
                <button type="submit" class="auth-btn">
                    <i class="fas fa-user-plus"></i> Đăng Ký
                </button>
                
                <div class="auth-footer">
                    Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Validate username format on input
        document.getElementById('register-username').addEventListener('input', function(e) {
            const username = e.target.value;
            const pattern = /^[a-zA-Z0-9_]+$/;
            
            if (!pattern.test(username)) {
                e.target.setCustomValidity('Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới');
            } else {
                e.target.setCustomValidity('');
            }
        });

        // Validate phone number format on input
        document.getElementById('register-phone').addEventListener('input', function(e) {
            const phone = e.target.value;
            const pattern = /^[0-9]{10,11}$/;
            
            if (!pattern.test(phone)) {
                e.target.setCustomValidity('Số điện thoại phải có 10-11 chữ số');
            } else {
                e.target.setCustomValidity('');
            }
        });
    </script>
</body>
</html>

<?php
// Đóng kết nối database
mysqli_close($conn);
?>