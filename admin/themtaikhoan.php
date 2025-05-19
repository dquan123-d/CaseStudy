<?php
include 'connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Kiểm tra username hoặc email đã tồn tại chưa
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = 'Username hoặc Email đã tồn tại!';
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, role, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiis", $name, $username, $email, $role, $phone, $password);
        if ($stmt->execute()) {
            header("Location: danhsachtaikhoan.php");
            exit;
        } else {
            $message = 'Lỗi khi thêm tài khoản!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm tài khoản</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar p-3">
        <h5 class="mb-4">QUẢN LÝ TÀI KHOẢN</h5>
        <ul class="nav flex-column mt-4">
            <li class="nav-item"><a href="danhsachtaikhoan.php" class="nav-link">Danh sách tài khoản</a></li>
            <li class="nav-item"><a href="themtaikhoan.php" class="nav-link active">Thêm tài khoản</a></li>
            <li class="nav-item"><a href="suataikhoan.php" class="nav-link">Sửa tài khoản</a></li>
            <li class="nav-item"><a href="datlaimatkhau.php" class="nav-link">Đặt lại mật khẩu</a></li>
        </ul>
    </div>

    <div class="form-box">
        <h2>Thêm tài khoản mới</h2>
        <?php if ($message): ?>
            <div class="alert alert-danger"><?= $message ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label>Họ tên</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Quyền</label>
                <select name="role" class="form-control" required>
                    <option value="0">User</option>
                    <option value="1">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">Thêm tài khoản</button>
        </form>
    </div>
</div>
</body>
</html>
