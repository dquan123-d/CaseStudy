<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Ẩn tin</title>
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
    <h2>Ẩn tin</h2>
    <form method="post" action="capnhattin.php">
        <input type="hidden" name="id" value="<?= $tin['id'] ?>">

        <label>Tiêu đề:</label>
        <input type="text" name="title" required>

        <label>Trạng thái:</label>
        <input type="radio" id="hien" name="approve" value="0" required>
        <label for="hien">Hiện</label>
        <input type="radio" id="an" name="approve" value="2">
        <label for="an">Ẩn</label>
        
        <button type="submit">Cập nhật</button>
    </form>
</div>

</body>
</html>
