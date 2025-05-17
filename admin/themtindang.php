<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách phòng trọ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar p-3">
            <h5 class="mb-4">QUẢN LÝ TIN ĐĂNG</h5>
            <ul class="nav flex-column mt-4">
                <li class="nav-item"><a href="themtindang.php" class="nav-link">Thêm tin đăng </a></li>
                <li class="nav-item"><a href="suatin.php" class="nav-link">Sửa tin đăng</a></li>
                <li class="nav-item"><a href="antin.php" class="nav-link">Ẩn tin đăng</a></li>
                <li class="nav-item"><a href="capnhattin.php" class="nav-link">Cập nhật trạng thái tin đăng</a></li>
                <li class="nav-item"><a href="thongke.php" class="nav-link">Thống kê</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="form-box">
            <h2>Thêm tin đăng</h2>
            <form method="post" action="themtindang.php">
                <input type="hidden" name="id" value="<?= $tin['id'] ?>">

            <label>Tiêu đề:</label>
            <input type="text" name="tieude" placeholder="Tiêu đề" required>

            <label>Nội dung:</label>
            <textarea name="text" placeholder="Nội dung" rows="5" required></textarea>

            <label>Địa chỉ:</label>
            <input type="text" name="diachi" placeholder="Địa chỉ" required>

            <label>Giá thuê:</label>
            <input type="number" name="gia" placeholder="Giá (VND)" required>

            <form action="upload.php" method="post" enctype="multipart/form-data">
            <label for="imageUpload">Chọn ảnh để tải lên:</label><br>
            <input type="file" name="image" id="imageUpload"><br><br>
            </form>


            <button type="submit">Thêm tin</button>
            </form>
        </div>
    </div>

</body>
</html>