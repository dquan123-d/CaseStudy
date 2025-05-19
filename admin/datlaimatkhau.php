<?php
include 'connect.php';

$message = '';

if (!isset($_GET['id'])) {
    header("Location: danhsachtaikhoan.php");
    exit;
}

$id = intval($_GET['id']);

// Lấy thông tin user
$stmt = $conn->prepare("SELECT id, name, username FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: danhsachtaikhoan.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = 'Mật khẩu xác nhận không khớp!';
    } elseif (strlen($new_password) < 6) {
        $message = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } else {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hash, $id);
        if ($stmt->execute()) {
            header("Location: danhsachtaikhoan.php");
            exit;
        } else {
            $message = 'Lỗi khi cập nhật mật khẩu!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar p-3">
        <h5 class="mb-4">QUẢN LÝ TÀI KHOẢN</h5>
        <ul class="nav flex-column mt-4">
            <li class="nav-item"><a href="danhsachtaikhoan.php" class="nav-link">Danh sách tài khoản</a></li>
            <li class="nav-item"><a href="themtaikhoan.php" class="nav-link">Thêm tài khoản</a></li>
            <li class="nav-item"><a href="suataikhoan.php" class="nav-link">Sửa tài khoản</a></li>
            <li class="nav-item"><a href="datlaimatkhau.php" class="nav-link active">Đặt lại mật khẩu</a></li>
        </ul>
    </div>

    <div class="form-box">
        <h2>Đặt lại mật khẩu cho: <?= htmlspecialchars($user['username']) ?></h2>
        <?php if ($message): ?>
            <div class="alert alert-danger"><?= $message ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label>Mật khẩu mới</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="form-group">
                <label>Xác nhận mật khẩu mới</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-warning">Cập nhật mật khẩu</button>
        </form>
    </div>
</div>
</body>
</html>
