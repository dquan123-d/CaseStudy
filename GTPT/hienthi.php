<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang quản trị Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #2c3e50;
            color: white;
            padding: 30px 0;
            text-align: center;
        }

        h1 {
            margin: 0;
            font-size: 28px;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            border-left: 5px solid #3498db;
            padding-left: 10px;
            color: #2c3e50;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        li {
            padding: 10px 0;
        }

        a {
            text-decoration: none;
            color: #2980b9;
            font-weight: bold;
            transition: 0.2s;
        }

        a:hover {
            color: #e74c3c;
        }

        .section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<header>
    <h1>Quản Lý Thông Tin Phòng Trọ</h1>
</header>

<div class="container">

    <div class="section">
        <h2>Quản lý tài khoản</h2>
        <ul>
            <li><a href="themtaikhoan.php">Thêm tài khoản</a></li>
            <li><a href="admin_users.php">Sửa / Danh sách tài khoản</a></li>
            <li><a href="reset_password.php">Reset mật khẩu</a></li>
            
        </ul>
    </div>

    <div class="section">
        <h2>Quản lý tin đăng</h2>
        <ul>
            <li><a href="themtindang.php">Thêm tin</a></li>
            <li><a href="suatin.php">Sửa / Danh sách tin</a></li>
            <li><a href="antin.php">Ẩn tin</a></li>
            <li><a href="capnhattin.php">Cập nhật trạng thái</a></li>
            <li><a href="thongke.php">Thống kê</a></li>
        </ul>
    </div>

</div>

</body>
</html>
