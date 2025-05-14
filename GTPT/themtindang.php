<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập nhật tin đăng</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f9f9f9;
    }

    .form-box {
        width: 90%;
        max-width: 700px;
        margin: 50px auto;
        padding: 20px;
        background: white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
    }

    h2 {
        text-align: left;
        margin-bottom: 20px;
        font-size: 24px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    input[type="text"],
    input[type="number"],
    select,
    textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 3px;
        box-sizing: border-box;
    }

    button {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px;
        width: 100%;
        font-size: 16px;
        border-radius: 3px;
        cursor: pointer;
    }

    button:hover {
        background-color: #218838;
    }
</style>
</head>
<body>

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

</body>
</html>
