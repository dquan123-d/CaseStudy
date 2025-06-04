<?php
// Kết nối CSDL
require_once '../config/database.php';

// Truy vấn lấy thông tin phòng trọ chi tiết nếu có id
$motel = null;
if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $sql = "SELECT m.*, d.name as district_name 
            FROM motels m
            JOIN districts d ON m.district_id = d.id
            WHERE m.id = '$id' AND m.approve = 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $motel = $result->fetch_assoc();
        // Tăng lượt xem
        $updateViewSql = "UPDATE motels SET count_view = count_view + 1 WHERE id = '$id'";
        $conn->query($updateViewSql);
    }
}

// Truy vấn lấy 3 bài đăng mới nhất (trừ bài đăng hiện tại nếu đang xem chi tiết)
$latestPostsSql = "SELECT m.id, m.title, m.price, m.address, m.count_view, m.images, d.name as district_name 
                  FROM motels m
                  JOIN districts d ON m.district_id = d.id
                  WHERE m.approve = 1" . 
                  (isset($motel['id']) ? " AND m.id != '{$motel['id']}'" : "") . "
                  ORDER BY m.created_at DESC
                  LIMIT 3";

$latestPostsResult = $conn->query($latestPostsSql);
$latestPosts = [];
if ($latestPostsResult->num_rows > 0) {
    while($row = $latestPostsResult->fetch_assoc()) {
        $latestPosts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($motel['title']) ? htmlspecialchars($motel['title']) : 'Big Home - Hệ thống nhà cho thuê'; ?></title>
    <link rel="stylesheet" href="../css/detail.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
 
        
</head>
<body>
    <!-- Header và Navigation -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <h1>Big Home</h1>
                <p>Kết nối nhu cầu thực</p>
            </div>
            
            <div class="search-bar">
                <input type="text" placeholder="Tìm kiếm phòng trọ, địa điểm...">
                <button><i class="fas fa-search"></i></button>
            </div>
            
            <div class="header-actions">
                <a href="post.php" class="post-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Đăng tin phòng trọ</span>
                </a>
                
                <div class="account-dropdown">
                    <button class="account-btn">
                        <i class="fas fa-user"></i>
                        <span>Tài khoản</span>
                    </button>
                    <div class="dropdown-content">
                        <div class="guest-view">
                            <a href="login.php" class="login-link">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập
                            </a>
                            <a href="register.php" class="register-link">
                                <i class="fas fa-user-plus"></i> Đăng ký
                            </a>
                            <a href="profile.php" class="profile-link">
                                <i class="fas fa-user-circle"></i> Thông tin tài khoản
                            </a>
                            <a href="logout.php" class="logout-link">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Quay lại trang chủ
        </a>
    </div>

    <div class="container main-container">
        <div class="content-area">
            <?php if (isset($motel)): ?>
                <h1 class="room-title"><?php echo htmlspecialchars($motel['title']); ?></h1>
                
                <!-- Ảnh chính có thể click -->
                <div class="room-image-container">
                    <?php 
                    $images = explode(',', $motel['images']);
                    $mainImage = !empty($images[0]) ? '../' . trim($images[0]) : '../images/default-room.jpg';
                    ?>
                    <img src="<?php echo htmlspecialchars($mainImage); ?>" 
                         alt="<?php echo htmlspecialchars($motel['title']); ?>" 
                         class="room-image clickable-image" 
                         onclick="openImageModal(this.src, this.alt)">
                </div>
                
                <!-- Gallery ảnh phụ -->
                <?php if (count($images) > 1): ?>
                <div class="image-gallery">
                    <?php for ($i = 1; $i < count($images) && $i < 5; $i++): ?>
                        <?php if (!empty(trim($images[$i]))): ?>
                            <img src="../<?php echo trim($images[$i]); ?>" 
                                 alt="<?php echo htmlspecialchars($motel['title']); ?> - Ảnh <?php echo $i+1; ?>" 
                                 class="gallery-thumbnail clickable-image" 
                                 onclick="openImageModal('../<?php echo trim($images[$i]); ?>', '<?php echo htmlspecialchars($motel['title']); ?> - Ảnh <?php echo $i+1; ?>')">
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
                
                <!-- Modal xem ảnh -->
                <div id="imageModal" class="modal">
                    <span class="close-btn" onclick="closeImageModal()">&times;</span>
                    <img class="modal-content" id="modalImage">
                    <div class="caption" id="caption"></div>
                </div>
                
                <div class="room-description">
                    <p><?php echo nl2br(htmlspecialchars($motel['description'])); ?></p>
                </div>
                
                <div class="info-section">
                    <h2 class="section-title">Thông tin liên hệ</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Liên hệ:</span>
                            <span class="phone"><?php echo htmlspecialchars($motel['phone']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Địa chỉ:</span>
                            <span><?php echo htmlspecialchars($motel['address']); ?>, <?php echo htmlspecialchars($motel['district_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Giá phòng:</span>
                            <span><?php echo number_format($motel['price']); ?>đ/tháng</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Diện tích:</span>
                            <span><?php echo htmlspecialchars($motel['area']); ?>m²</span>
                        </div>
                    </div>
                </div>
                
                <div class="info-section">
                    <h2 class="section-title">Tiện ích</h2>
                    <div class="amenities">
                        <?php 
                        $utilities = explode(',', $motel['utilities']);
                        $utilityIcons = [
                            'wifi' => 'fa-wifi',
                            'nội thất' => 'fa-bed',
                            'tivi' => 'fa-tv',
                            'điều hòa' => 'fa-fan',
                            'nóng lạnh' => 'fa-water',
                            'vệ sinh' => 'fa-broom',
                            'để xe' => 'fa-parking',
                            'bếp' => 'fa-utensils',
                            'máy giặt' => 'fa-washing-machine',
                            'tủ lạnh' => 'fa-refrigerator'
                        ];
                        
                        foreach ($utilities as $util) {
                            $util = trim(strtolower($util));
                            $icon = 'fa-check-circle'; // Icon mặc định
                            foreach ($utilityIcons as $key => $value) {
                                if (strpos($util, $key) !== false) {
                                    $icon = $value;
                                    break;
                                }
                            }
                            echo '<div class="amenity"><i class="fas '.$icon.'"></i> '.htmlspecialchars(trim($util)).'</div>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="report-section">
                    <h2 class="section-title">Báo cáo</h2>
                    <div class="report-options">
                        <label class="report-option">
                            <input type="radio" name="report" value="rented"> Đã cho thuê
                        </label>
                        <label class="report-option">
                            <input type="radio" name="report" value="available"> Sai thông tin
                        </label>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-report">Gửi báo cáo</button>
                        <button class="btn btn-edit">Sửa bài đăng</button>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <h2>Không tìm thấy phòng trọ</h2>
                    <p>Phòng trọ bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
                    <a href="index.php" class="btn btn-primary">Quay lại trang chủ</a>
                </div>
            <?php endif; ?>
        </div>
        

</div>

<!-- Footer -->
    <footer class="footer">
    <div class="footer-container">
        <div class="footer-column">
            <h3>Thông tin liên hệ</h3>
            <p><i class="fas fa-map-marker-alt"></i> 123 Đường ABC, TP. Vinh, Nghệ An</p>
            <p><i class="fas fa-phone"></i> 0987 654 321</p>
            <p><i class="fas fa-envelope"></i> info@bighome.vn</p>
        </div>
        
        <div class="footer-column">
            <h3>Chính sách</h3>
            <ul>
                <li><a href="#"><i class="fas fa-chevron-right"></i> Quyền riêng tư</a></li>
                <li><a href="#"><i class="fas fa-chevron-right"></i> Điều khoản sử dụng</a></li>
                <li><a href="#"><i class="fas fa-chevron-right"></i> Chính sách bảo mật</a></li>
                <li><a href="#"><i class="fas fa-chevron-right"></i> Bản đồ trang</a></li>
            </ul>
        </div>
        
        <div class="footer-column">
            <h3>Kết nối với chúng tôi</h3>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        
        <div class="copyright">
            <p>&copy; 2023 Big Home. All rights reserved.</p>
        </div>
    </div>
</footer>


    <script>
        // Hàm mở modal ảnh
        function openImageModal(src, alt) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            const captionText = document.getElementById('caption');
            
            modal.style.display = "block";
            modalImg.src = src;
            captionText.innerHTML = alt;
            
            // Thêm sự kiện click ra ngoài ảnh để đóng modal
            modal.onclick = function(event) {
                if (event.target === modal) {
                    closeImageModal();
                }
            }
            
            // Ngăn chặn sự kiện click trên ảnh lan ra modal
            modalImg.onclick = function(event) {
                event.stopPropagation();
            }
            
            // Cuộn lên đầu trang để xem ảnh rõ hơn
            window.scrollTo(0, 0);
        }
        
        // Hàm đóng modal ảnh
        function closeImageModal() {
            document.getElementById('imageModal').style.display = "none";
        }
        
        // Thêm sự kiện cho phím ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeImageModal();
            }
        });
        
        // Xử lý báo cáo bài đăng
        document.querySelector('.btn-report')?.addEventListener('click', function() {
            const selectedReport = document.querySelector('input[name="report"]:checked');
            if (selectedReport) {
                alert('Cảm ơn bạn đã báo cáo. Chúng tôi sẽ xem xét và xử lý sớm nhất.');
                // Gửi AJAX request để báo cáo
                // ...
            } else {
                alert('Vui lòng chọn loại báo cáo.');
            }
        });
    </script>
</body>
</html>