<?php
// Bật báo lỗi để debug (nhớ tắt khi triển khai production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Đảm bảo session được bắt đầu đúng cách
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

// Kiểm tra đăng nhập kỹ hơn
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// Lấy thông tin người dùng từ database với xử lý lỗi
try {
    $userId = $_SESSION['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị câu lệnh SQL");
    }
    
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi thực thi câu lệnh SQL");
    }
    
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        // Nếu không tìm thấy user trong DB nhưng có session -> xóa session và chuyển hướng
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit;
    }
} catch (Exception $e) {
    error_log("Lỗi: " . $e->getMessage());
    die("Đã xảy ra lỗi khi tải thông tin người dùng");
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_info'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    try {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $address, $userId);
        
        if ($stmt->execute()) {
            $success = "Cập nhật thông tin thành công!";
            // Cập nhật lại thông tin user
            $user['name'] = $name;
            $user['phone'] = $phone;
            $user['address'] = $address;
        } else {
            $error = "Có lỗi xảy ra khi cập nhật thông tin!";
        }
    } catch (Exception $e) {
        $error = "Lỗi hệ thống: " . $e->getMessage();
    }
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    // Lấy dữ liệu từ form
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Kiểm tra các trường không được trống
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_pw = "Vui lòng điền đầy đủ các trường!";
    } 
    // Kiểm tra mật khẩu mới có khớp không
    elseif ($new_password !== $confirm_password) {
        $error_pw = "Mật khẩu mới không khớp!";
    }
    // Kiểm tra độ dài mật khẩu mới
    elseif (strlen($new_password) < 6) {
        $error_pw = "Mật khẩu mới phải có ít nhất 6 ký tự!";
    }
    // Kiểm tra mật khẩu hiện tại có đúng không
    elseif ($current_password !== $user['password']) {
        $error_pw = "Mật khẩu hiện tại không đúng!";
    }
    // Nếu tất cả điều kiện đều hợp lệ
    else {
        // Cập nhật mật khẩu mới vào CSDL (lưu dưới dạng plain text theo yêu cầu bài tập)
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $userId);
        
        if ($stmt->execute()) {
            $success_pw = "Đổi mật khẩu thành công!";
            // Cập nhật lại thông tin user trong biến $user
            $user['password'] = $new_password;
        } else {
            $error_pw = "Có lỗi xảy ra khi đổi mật khẩu!";
        }
    }
}
// Xử lý upload avatar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['avatar']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../uploads/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $new_filename = 'avatar_' . $userId . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                // Xóa avatar cũ nếu có
                if (!empty($user['avatar']) && file_exists('../' . $user['avatar'])) {
                    unlink('../' . $user['avatar']);
                }
                
                // Cập nhật database
                $avatar_path = 'uploads/avatars/' . $new_filename;
                $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->bind_param("si", $avatar_path, $userId);
                
                if ($stmt->execute()) {
                    $success_avatar = "Cập nhật ảnh đại diện thành công!";
                    $user['avatar'] = $avatar_path;
                } else {
                    $error_avatar = "Có lỗi khi cập nhật database!";
                }
            } else {
                $error_avatar = "Có lỗi khi tải lên ảnh!";
            }
        } else {
            $error_avatar = "Chỉ chấp nhận file ảnh (JPEG, PNG, GIF)!";
        }
    } else {
        $error_avatar = "Vui lòng chọn ảnh đại diện!";
    }
}

// Lấy danh sách bài đăng của user
$stmt = $conn->prepare("SELECT id, title, created_at, approve FROM motels WHERE id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Xử lý xóa bài đăng
if (isset($_GET['delete_post'])) {
    $post_id = (int)$_GET['delete_post'];
    
    try {
        // Kiểm tra xem bài đăng thuộc về user này
        $stmt = $conn->prepare("SELECT id FROM motels WHERE id = ? AND id = ?");
        $stmt->bind_param("ii", $post_id, $userId);
        $stmt->execute();
        $valid_post = $stmt->get_result()->fetch_assoc();
        
        if ($valid_post) {
            // Xóa bài đăng
            $stmt = $conn->prepare("DELETE FROM motels WHERE id = ?");
            $stmt->bind_param("i", $post_id);
            
            if ($stmt->execute()) {
                $success_post = "Xóa bài đăng thành công!";
                // Làm mới danh sách bài đăng
                header("Location: profile.php?tab=posts");
                exit;
            } else {
                $error_post = "Có lỗi khi xóa bài đăng!";
            }
        } else {
            $error_post = "Bài đăng không tồn tại hoặc không thuộc quyền sở hữu của bạn!";
        }
    } catch (Exception $e) {
        $error_post = "Lỗi hệ thống: " . $e->getMessage();
    }
}

// Xác định tab hiện tại
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'info';
$allowed_tabs = ['info', 'avatar', 'password', 'posts', 'reports'];
if (!in_array($current_tab, $allowed_tabs)) {
    $current_tab = 'info';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="../css/profile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
    <!-- Main Content -->
    <div class="main-container">
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <!-- Avatar -->
            <img src="<?php echo !empty($user['avatar']) ? '../' . htmlspecialchars($user['avatar']) : '../images/default-avatar.jpg'; ?>" 
                 alt="Ảnh đại diện" class="profile-avatar-large">
            
            <!-- Nút quay về -->
            <a href="index.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Trang chủ
            </a>
            
            <ul class="sidebar-menu">
                <li><a href="?tab=info" class="<?php echo $current_tab == 'info' ? 'active' : ''; ?>"><i class="fas fa-user-edit"></i> Chỉnh sửa thông tin</a></li>
                <li><a href="?tab=avatar" class="<?php echo $current_tab == 'avatar' ? 'active' : ''; ?>"><i class="fas fa-camera"></i> Ảnh đại diện</a></li>
                <li><a href="?tab=password" class="<?php echo $current_tab == 'password' ? 'active' : ''; ?>"><i class="fas fa-key"></i> Đổi mật khẩu</a></li>
                <li><a href="?tab=posts" class="<?php echo $current_tab == 'posts' ? 'active' : ''; ?>"><i class="fas fa-newspaper"></i> Quản lý tin đăng</a></li>
                <li><a href="?tab=reports" class="<?php echo $current_tab == 'reports' ? 'active' : ''; ?>"><i class="fas fa-flag"></i> Báo cáo đã thuê</a></li>
            </ul>
        </aside>
        
        <!-- Content Area -->
        <main class="profile-content">
            <!-- Phần hiển thị thông tin người dùng - Đã được cải tiến -->
            <div class="user-info-container">
                <div class="user-avatar-wrapper">
                    <img src="<?php echo !empty($user['avatar']) ? '../' . htmlspecialchars($user['avatar']) : '../images/default-avatar.jpg'; ?>" 
                         alt="Ảnh đại diện" class="profile-avatar-large">
                    <div class="user-main-info">
                        <h1><?php echo htmlspecialchars($user['name'] ?: $user['username']); ?></h1>
                        <div class="user-meta">
                            <span class="user-meta-item">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                            </span>
                            <span class="user-meta-item">
                                <i class="fas fa-phone"></i> <?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Chưa cập nhật'; ?>
                            </span>
                            <?php if(!empty($user['address'])): ?>
                                <span class="user-meta-item">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['address']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="user-join-info">
                    <i class="fas fa-calendar-alt"></i> Thành viên từ: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                    <?php if(isset($user['last_login'])): ?>
                        | <i class="fas fa-sign-in-alt"></i> Đăng nhập lần cuối: <?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Thông tin cá nhân -->
            <div id="info" style="<?php echo $current_tab != 'info' ? 'display: none;' : ''; ?>">
                <h2 class="form-title">Chỉnh sửa thông tin tài khoản</h2>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php elseif (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="profile.php?tab=info">
                    <div class="mb-3">
                        <label for="name" class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <input type="text" class="form-control" id="address" name="address" 
                        value="<?php echo isset($user['address']) && $user['address'] !== null ? htmlspecialchars($user['address']) : ''; ?>">
                    </div>
                    <button type="submit" name="update_info" class="btn btn-primary">Cập nhật thông tin</button>
                </form>
            </div>
            
            <!-- Tab Ảnh đại diện -->
            <div id="avatar" style="<?php echo $current_tab != 'avatar' ? 'display: none;' : ''; ?>">
                <h2 class="form-title">Thay đổi ảnh đại diện</h2>
                
                <?php if (isset($success_avatar)): ?>
                    <div class="alert alert-success"><?php echo $success_avatar; ?></div>
                <?php elseif (isset($error_avatar)): ?>
                    <div class="alert alert-danger"><?php echo $error_avatar; ?></div>
                <?php endif; ?>
                
                <div class="text-center mb-4">
                    <img src="<?php echo !empty($user['avatar']) ? '../' . htmlspecialchars($user['avatar']) : '../images/default-avatar.jpg'; ?>" 
                         alt="Ảnh đại diện hiện tại" class="avatar-preview" id="avatar-preview">
                </div>
                
                <form method="POST" action="profile.php?tab=avatar" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Chọn ảnh mới (JPEG, PNG, GIF - tối đa 2MB)</label>
                        <input class="form-control" type="file" id="avatar" name="avatar" accept="image/*" required>
                    </div>
                    <button type="submit" name="update_avatar" class="btn btn-primary">Cập nhật ảnh đại diện</button>
                </form>
            </div>
            
            <div id="password" style="<?php echo $current_tab != 'password' ? 'display: none;' : ''; ?>">
    <h2 class="form-title">Đổi mật khẩu</h2>
    
    <?php if (isset($success_pw)): ?>
        <div class="alert alert-success"><?php echo $success_pw; ?></div>
    <?php elseif (isset($error_pw)): ?>
        <div class="alert alert-danger"><?php echo $error_pw; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="profile.php?tab=password">
        <div class="mb-3">
            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">Mật khẩu mới (ít nhất 6 ký tự)</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
        </div>
        <button type="submit" name="change_password" class="btn btn-primary">Đổi mật khẩu</button>
    </form>
</div>
            
            <!-- Tab Quản lý tin đăng -->
            <div id="posts" style="<?php echo $current_tab != 'posts' ? 'display: none;' : ''; ?>">
                <h2 class="form-title">Quản lý tin đăng</h2>
                
                <?php if (isset($success_post)): ?>
                    <div class="alert alert-success"><?php echo $success_post; ?></div>
                <?php elseif (isset($error_post)): ?>
                    <div class="alert alert-danger"><?php echo $error_post; ?></div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <a href="post.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm tin đăng mới</a>
                </div>
                
                <?php if (count($posts) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tiêu đề</th>
                                    <th>Ngày đăng</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td><?php echo $post['id']; ?></td>
                                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></td>
                                        <td>
                                            <?php if ($post['approve'] == 1): ?>
                                                <span class="badge bg-success">Đã duyệt</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Sửa
                                            </a>
                                            <a href="profile.php?tab=posts&delete_post=<?php echo $post['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa bài đăng này?')">
                                                <i class="fas fa-trash"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Bạn chưa có tin đăng nào. <a href="post.php" class="alert-link">Đăng tin mới ngay</a></div>
                <?php endif; ?>
            </div>
            
            <!-- Tab Báo cáo đã thuê -->
            <div id="reports" style="<?php echo $current_tab != 'reports' ? 'display: none;' : ''; ?>">
                <h2 class="form-title">Gửi báo cáo đã thuê</h2>
                
                <form method="POST" action="profile.php?tab=reports">
                    <div class="mb-3">
                        <label for="post_id" class="form-label">Chọn tin đăng đã thuê</label>
                        <select class="form-select" id="post_id" name="post_id" required>
                            <option value="">-- Chọn tin đăng --</option>
                            <?php foreach ($posts as $post): ?>
                                <?php if ($post['approve'] == 1): ?>
                                    <option value="<?php echo $post['id']; ?>">
                                        <?php echo htmlspecialchars($post['title']); ?> (ID: <?php echo $post['id']; ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="report_content" class="form-label">Nội dung báo cáo</label>
                        <textarea class="form-control" id="report_content" name="report_content" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="rating" class="form-label">Đánh giá</label>
                        <select class="form-select" id="rating" name="rating" required>
                            <option value="5">★★★★★ - Rất tốt</option>
                            <option value="4">★★★★☆ - Tốt</option>
                            <option value="3">★★★☆☆ - Bình thường</option>
                            <option value="2">★★☆☆☆ - Không hài lòng</option>
                            <option value="1">★☆☆☆☆ - Rất tệ</option>
                        </select>
                    </div>
                    <button type="submit" name="submit_report" class="btn btn-primary">Gửi báo cáo</button>
                </form>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hiển thị preview ảnh đại diện trước khi upload
        document.getElementById('avatar').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Xử lý confirm trước khi xóa bài đăng
        function confirmDelete() {
            return confirm('Bạn có chắc chắn muốn xóa bài đăng này?');
        }
    </script>
</body>
</html>