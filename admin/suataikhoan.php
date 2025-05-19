<?php
include 'connect.php';

$message = '';

if (!isset($_GET['id'])) {
    header("Location: danhsachtaikhoan.php");
    exit;
}

$id = intval($_GET['id']);

// Lấy dữ liệu tài khoản hiện tại
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: danhsachtaikhoan.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $phone = trim($_POST['phone']);

    // Kiểm tra username hoặc email có bị trùng với tài khoản khác không
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = 'Username hoặc Email đã tồn tại!';
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=?, role=?, phone=? WHERE id=?");
        $stmt->bind_param("sssisi", $name, $username, $email, $role, $phone, $id);
        if ($stmt->execute()) {
            header("Location: danhsachtaikhoan.php");
            exit;
        } else {
            $message = 'Lỗi khi cập nhật tài khoản!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa tài khoản</title>
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
            <li class="nav-item"><a href="suataikhoan.php" class="nav-link active">Sửa tài khoản</a></li>
            <li class="nav-item"><a href="datlaimatkhau.php" class="nav-link">Đặt lại mật khẩu</a></li>
        </ul>
    </div>

    <div class="form-box">
        <h2>Sửa thông tin tài khoản</h2>
        <?php if ($message): ?>
            <div class="alert alert-danger"><?= $message ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label>Họ tên</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($user['name']) ?>">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <div class="form-group">
                <label>Quyền</label>
                <select name="role" class="form-control" required>
                    <option value="0" <?= $user['role'] == 0 ? 'selected' : '' ?>>User</option>
                    <option value="1" <?= $user['role'] == 1 ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($user['phone']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </form>
    </div>
</div>
</body>
</html>
