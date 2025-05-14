<?php
$target_dir = "uploads/"; // Thư mục lưu ảnh
$target_file = $target_dir . basename($_FILES["image"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Kiểm tra có phải file ảnh không
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        echo "Tập tin không phải ảnh.";
        $uploadOk = 0;
    }
}

// Kiểm tra tồn tại
if (file_exists($target_file)) {
    echo "Ảnh đã tồn tại.";
    $uploadOk = 0;
}

// Giới hạn kích thước (ví dụ 5MB)
if ($_FILES["image"]["size"] > 5000000) {
    echo "Ảnh quá lớn.";
    $uploadOk = 0;
}

// Cho phép các định dạng ảnh
$allowed = ["jpg", "jpeg", "png", "gif"];
if(!in_array($imageFileType, $allowed)) {
    echo "Chỉ cho phép JPG, JPEG, PNG & GIF.";
    $uploadOk = 0;
}

// Nếu không có lỗi thì upload
if ($uploadOk == 1) {
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "Ảnh ". htmlspecialchars(basename($_FILES["image"]["name"])). " đã được tải lên.";
    } else {
        echo "Có lỗi khi tải ảnh.";
    }
}
?>
