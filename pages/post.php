<?php
// Bật báo lỗi để debug (nhớ tắt khi triển khai production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Đảm bảo session được bắt đầu
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// Lấy danh sách quận/huyện từ bảng districts
$districts = [];
$result = $conn->query("SELECT id, name FROM districts ORDER BY name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $districts[] = $row;
    }
}

// Xử lý form khi submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $address = $_POST['address'] ?? '';
    $district_id = $_POST['district_id'] ?? '';
    $price = $_POST['price'] ?? 0;
    $area = $_POST['area'] ?? 0;
    $description = $_POST['description'] ?? '';
    
    $utilities = $_POST['utilities'] ?? [];
    $utilities_str = implode(',', $utilities);
    
    $errors = [];

    if (empty($title)) $errors[] = "Vui lòng nhập tiêu đề phòng trọ";
    if (empty($address)) $errors[] = "Vui lòng nhập địa chỉ";
    if (empty($district_id)) $errors[] = "Vui lòng chọn quận/huyện";
    if ($price <= 0) $errors[] = "Giá phòng phải lớn hơn 0";
    if ($area <= 0) $errors[] = "Diện tích phải lớn hơn 0";

    // Xử lý upload ảnh
    $image_paths = [];
    if (!empty($_FILES['images']['name'][0])) {
        $upload_dir = '../uploads/motels/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['images']['name'][$key];
            $file_size = $_FILES['images']['size'][$key];
            $file_tmp = $_FILES['images']['tmp_name'][$key];
            $file_type = $_FILES['images']['type'][$key];

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($file_type, $allowed_types)) {
                if ($file_size <= 2097152) {
                    $new_file_name = time() . '_' . uniqid() . '_' . $file_name;
                    $upload_path = $upload_dir . $new_file_name;

                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $image_paths[] = 'uploads/motels/' . $new_file_name;
                    } else {
                        $errors[] = "Lỗi khi tải lên ảnh: $file_name";
                    }
                } else {
                    $errors[] = "Ảnh quá lớn (tối đa 2MB): $file_name";
                }
            } else {
                $errors[] = "Chỉ chấp nhận file ảnh (JPEG, PNG, GIF): $file_name";
            }
        }
    } else {
        $errors[] = "Vui lòng chọn ít nhất 1 ảnh";
    }

    // Thêm vào CSDL nếu không có lỗi
    if (empty($errors)) {
        try {
            $images_json = json_encode($image_paths);
            $stmt = $conn->prepare("INSERT INTO motels (user_id, title, address, district_id, price, area, utilities, description, images, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issiiisss", $_SESSION['id'], $title, $address, $district_id, $price, $area, $utilities_str, $description, $images_json);

            if ($stmt->execute()) {
                $success = "Đăng tin thành công! Tin đăng đang chờ được duyệt.";
                $title = $address = $description = '';
                $price = $area = 0;
                $utilities = [];
                $district_id = '';
            } else {
                $errors[] = "Lỗi khi thêm bài đăng vào CSDL.";
            }
        } catch (Exception $e) {
            $errors[] = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng tin mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2 class="text-center mb-4">Đăng tin cho thuê phòng trọ</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $error) echo "<li>$error</li>"; ?></ul></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Tên phòng trọ</label>
            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($title ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Địa chỉ chi tiết</label>
            <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($address ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Chọn quận/huyện</label>
            <select class="form-select" name="district_id" required>
                <option value="">-- Chọn quận/huyện --</option>
                <?php foreach ($districts as $d): ?>
                    <option value="<?php echo $d['id']; ?>" <?php echo (isset($district_id) && $district_id == $d['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($d['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Giá phòng (VNĐ)</label>
            <input type="number" class="form-control" name="price" min="1" value="<?php echo $price ?? '' ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Diện tích (m²)</label>
            <input type="number" class="form-control" name="area" min="1" value="<?php echo $area ?? '' ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Hình ảnh</label>
            <input type="file" class="form-control" name="images[]" multiple accept="image/*" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tiện ích</label><br>
            <?php
            $utility_options = [
                'toilet' => 'Nhà vệ sinh riêng',
                'kitchen' => 'Bếp riêng',
                'no-owner' => 'Không chung chủ',
                'security' => 'An ninh tốt',
                'aircon' => 'Điều hòa',
                'balcony' => 'Ban công',
                'window' => 'Cửa sổ thoáng'
            ];
            foreach ($utility_options as $val => $label):
            ?>
                <div class="form-check form-check-inline">
                    <input type="checkbox" name="utilities[]" value="<?php echo $val; ?>"
                           class="form-check-input" id="ut_<?php echo $val; ?>"
                        <?php echo (isset($utilities) && in_array($val, $utilities)) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="ut_<?php echo $val; ?>"><?php echo $label; ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả chi tiết</label>
            <textarea class="form-control" name="description" rows="5"><?php echo htmlspecialchars($description ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Đăng tin</button>
        <a href="profile.php?tab=posts" class="btn btn-secondary">Quay về</a>
    </form>
</div>
</body>
</html>
