<?php
include 'connect.php';
$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách tài khoản</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar p-3">
        <h5 class="mb-4">QUẢN LÝ TÀI KHOẢN</h5>
        <ul class="nav flex-column mt-4">
            <li class="nav-item"><a href="danhsachtaikhoan.php" class="nav-link">Danh sách tài khoản</a></li>
            <li class="nav-item"><a href="themtaikhoan.php" class="nav-link">Thêm tài khoản</a></li>
            <li class="nav-item"><a href="suataikhoan.php" class="nav-link">Sửa tài khoản</a></li>
            <li class="nav-item"><a href="datlaimatkhau.php" class="nav-link">Đặt lại mật khẩu</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="form-box">
        <h2>Danh sách tài khoản</h2>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Quyền</th>
                    <th>Số điện thoại</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['username'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['role'] == 1 ? 'Admin' : 'User' ?></td>
                    <td><?= $row['phone'] ?></td>
                    <td>
                        <a href="suataikhoan.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Sửa</a>
                        <a href="datlaimatkhau.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"
                           onclick="return confirm('Bạn có chắc muốn đặt lại mật khẩu?')">Đặt lại mật khẩu</a>
                    </td>
                </tr>
                <?php endwhile ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
