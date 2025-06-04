<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Big Home - Hệ thống nhà cho thuê</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php
    // Kết nối CSDL
    require_once '../config/database.php';
    
    // Start session
    session_start();
    
    // Check if user is logged in
    $isLoggedIn = isset($_SESSION['user_id']);
    
    // Xử lý các tham số
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 6; // Số item mỗi trang
    $offset = ($page - 1) * $limit;
    
    // Xử lý bộ lọc
    $districtFilter = isset($_GET['districts']) ? $_GET['districts'] : [];
    $sizeFilter = isset($_GET['sizes']) ? $_GET['sizes'] : [];
    $minPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : null;
    $maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
    
    // Xây dựng câu truy vấn
    $sql = "SELECT m.*, d.name as district_name, c.name as category_name 
            FROM motels m
            JOIN districts d ON m.district_id = d.id
            JOIN categories c ON m.category_id = c.id
            WHERE m.approve = 1";
    
    // Thêm điều kiện lọc
    if (!empty($districtFilter)) {
        $districtIds = array_map('intval', $districtFilter);
        $sql .= " AND m.district_id IN (" . implode(',', $districtIds) . ")";
    }
    
    if (!empty($sizeFilter)) {
        $sizeConditions = [];
        foreach ($sizeFilter as $size) {
            list($min, $max) = explode('-', $size);
            $sizeConditions[] = "(m.area BETWEEN $min AND $max)";
        }
        $sql .= " AND (" . implode(' OR ', $sizeConditions) . ")";
    }
    
    if ($minPrice !== null) {
        $sql .= " AND m.price >= $minPrice";
    }
    
    if ($maxPrice !== null) {
        $sql .= " AND m.price <= $maxPrice";
    }
    
    // Thêm sắp xếp
    switch ($sort) {
        case 'price_asc':
            $sql .= " ORDER BY m.price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY m.price DESC";
            break;
        case 'most_viewed':
            $sql .= " ORDER BY m.count_view DESC";
            break;
        default:
            $sql .= " ORDER BY m.created_at DESC";
    }
    
    // Truy vấn tổng số bản ghi
    $countSql = str_replace("SELECT m.*, d.name as district_name, c.name as category_name", "SELECT COUNT(*) as total", $sql);
    $countResult = $conn->query($countSql);
    $totalMotels = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalMotels / $limit);
    
    // Thêm phân trang
    $sql .= " LIMIT $limit OFFSET $offset";
    
    // Thực hiện truy vấn
    $result = $conn->query($sql);
    $motels = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $motels[] = $row;
        }
    }
    ?>

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
                <!-- Nút Đăng tin phòng trọ -->
                <?php if ($isLoggedIn): ?>
                    <a href="post.php" class="post-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Đăng tin phòng trọ</span>
                    </a>
                <?php else: ?>
                    <a href="login.php?redirect=post.php" class="post-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Đăng tin phòng trọ</span>
                    </a>
                <?php endif; ?>
                
                <!-- Dropdown Tài khoản -->
                <div class="account-dropdown">
                    <button class="account-btn">
                        <i class="fas fa-user"></i>
                        <span><?php echo $isLoggedIn ? htmlspecialchars($_SESSION['username']) : 'Tài khoản'; ?></span>
                    </button>
                    <div class="dropdown-content">
                        
                            <div class="user-view">
                                <a href="profile.php" class="profile-link">
                                    <i class="fas fa-user-circle"></i> Thông tin tài khoản
                                </a>

                                <a href="logout.php" class="logout-link">
                                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                </a>
                            </div>
        
                            <div class="guest-view">
                                <a href="login.php" class="login-link">
                                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                                </a>
                                <a href="register.php" class="register-link">
                                    <i class="fas fa-user-plus"></i> Đăng ký
                                </a>
                            </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Banner -->
    <section class="banner">
        <img src="../images/banner1.png" alt="Big Home Banner">
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Sidebar -->
            <aside class="sidebar">
                <form id="filter-form" method="get" action="index.php">
                    <!-- Bộ lọc sắp xếp -->
                    <div class="filter-section">
                        <h3>Sắp xếp theo</h3>
                        <div class="sort-options">
                            <label class="radio-item">
                                <input type="radio" name="sort" value="newest" <?php echo (!isset($_GET['sort']) || $_GET['sort'] === 'newest') ? 'checked' : ''; ?>>
                                <span class="radiomark"></span>
                                <span>Tin đăng mới nhất</span>
                            </label>
                            <label class="radio-item">
                                <input type="radio" name="sort" value="most_viewed" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'most_viewed') ? 'checked' : ''; ?>>
                                <span class="radiomark"></span>
                                <span>Tin đăng xem nhiều nhất</span>
                            </label>
                            <label class="radio-item">
                                <input type="radio" name="sort" value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'price_asc') ? 'checked' : ''; ?>>
                                <span class="radiomark"></span>
                                <span>Giá thấp đến cao</span>
                            </label>
                            <label class="radio-item">
                                <input type="radio" name="sort" value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'price_desc') ? 'checked' : ''; ?>>
                                <span class="radiomark"></span>
                                <span>Giá cao đến thấp</span>
                            </label>
                        </div>
                    </div>

                    <!-- Bộ lọc khu vực -->
                    <div class="filter-section">
                        <h3>Khu vực</h3>
                        <div class="multi-select">
                            <?php
                            $selectedDistricts = isset($_GET['districts']) ? $_GET['districts'] : [];
                            $districtSql = "SELECT * FROM districts";
                            $districtResult = $conn->query($districtSql);
                            
                            if ($districtResult->num_rows > 0) {
                                while($district = $districtResult->fetch_assoc()) {
                                    $checked = in_array($district['id'], $selectedDistricts) ? 'checked' : '';
                                    echo '<label class="checkbox-item">
                                        <input type="checkbox" name="districts[]" value="'.$district['id'].'" '.$checked.'>
                                        <span class="checkmark"></span>
                                        <span>'.htmlspecialchars($district['name']).'</span>
                                    </label>';
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Bộ lọc diện tích -->
                    <div class="filter-section">
                        <h3>Diện tích (m²)</h3>
                        <div class="multi-select">
                            <?php
                            $selectedSizes = isset($_GET['sizes']) ? $_GET['sizes'] : [];
                            $sizeOptions = [
                                '15-18' => '15-18 m²',
                                '18-21' => '18-21 m²',
                                '21-24' => '21-24 m²',
                                '24-27' => '24-27 m²',
                                '27-30' => '27-30 m²'
                            ];
                            
                            foreach ($sizeOptions as $value => $label) {
                                $checked = in_array($value, $selectedSizes) ? 'checked' : '';
                                echo '<label class="checkbox-item">
                                    <input type="checkbox" name="sizes[]" value="'.$value.'" '.$checked.'>
                                    <span class="checkmark"></span>
                                    <span>'.htmlspecialchars($label).'</span>
                                </label>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="filter-btn">
                        <span class="btn-text">Áp dụng bộ lọc</span>
                        <span class="btn-icon">
                            <i class="fas fa-filter"></i>
                        </span>
                    </button>
                    <a href="index.php" class="reset-filter">Xóa bộ lọc</a>
                </form>
            </aside>
            
            <!-- Room Listing -->
            <section class="room-listing">
                <h2 class="section-title">Phòng trọ nổi bật</h2>
                
                <?php if (!$isLoggedIn): ?>
                    <div class="guest-notice">
                        <p>Bạn đang xem với tư cách khách. <a href="login.php">Đăng nhập</a> để sử dụng đầy đủ tính năng.</p>
                    </div>
                <?php endif; ?>
                
                <div class="room-grid">
                    <?php if (!empty($motels)): ?>
                        <?php foreach ($motels as $motel): 
                            // Xử lý đường dẫn ảnh
                            $images = explode(',', $motel['images']);
                            $firstImage = !empty($images[0]) ? '../' . trim($images[0]) : '../images/default-room.jpg';
                        ?>
                            <div class="room-item">
                                <div class="room-image">
                                    <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="<?php echo htmlspecialchars($motel['title']); ?>">
                                    <span class="price"><?php echo number_format($motel['price']); ?>đ/tháng</span>
                                    <span class="area-tag"><?php echo htmlspecialchars($motel['district_name']); ?></span>
                                </div>
                                <div class="room-info">
                                    <h3><?php echo htmlspecialchars($motel['title']); ?></h3>
                                    <p class="address"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($motel['address']); ?></p>
                                    <p class="views"><i class="fas fa-eye"></i> <?php echo $motel['count_view']; ?> lượt xem</p>
                                    <a href="detail.php?id=<?php echo $motel['id']; ?>" class="detail-btn">Xem chi tiết</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-result">Không tìm thấy phòng trọ phù hợp với bộ lọc.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Phần phân trang -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination-buttons">
                    <!-- Nút Previous -->
                    <?php if ($page > 1): ?>
                        <a href="<?php echo buildPaginationUrl($page - 1); ?>" class="pagination-button prev-next">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-button prev-next disabled">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Các nút trang -->
                    <?php 
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    
                    if ($start > 1) echo '<span class="pagination-dots">...</span>';
                    
                    for ($i = $start; $i <= $end; $i++): ?>
                        <a href="<?php echo buildPaginationUrl($i); ?>" class="pagination-button <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; 
                    
                    if ($end < $totalPages) echo '<span class="pagination-dots">...</span>';
                    ?>
                    
                    <!-- Nút Next -->
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo buildPaginationUrl($page + 1); ?>" class="pagination-button prev-next">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-button prev-next disabled">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-column">
                <h3>Thông tin liên hệ</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Đường ABC, TP. Vinh, Nghệ An</p>
                <p><i class="fas fa-phone"></i> 0987 654 321</p>
                <p><i class="fas fa-envelope"></i> info@bighome.vn</p>
            </div>
            
            <div class="footer-column">
                <h3>Chính sách</h3>
                <ul>
                    <li><a href="#">Quyền riêng tư</a></li>
                    <li><a href="#">Điều khoản sử dụng</a></li>
                    <li><a href="#">Chính sách bảo mật</a></li>
                    <li><a href="#">Bản đồ trang</a></li>
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
        </div>
        
        <div class="copyright">
            <p>&copy; 2023 Big Home. All rights reserved.</p>
        </div>
    </footer>

    <?php 
    // Hàm xây dựng URL phân trang
    function buildPaginationUrl($page) {
        $params = $_GET;
        $params['page'] = $page;
        return 'index.php?' . http_build_query($params);
    }
    
    $conn->close(); 
    ?>

    <script>
    // Xử lý tìm kiếm trong header
    document.querySelector('.header .search-bar button').addEventListener('click', function() {
        const searchTerm = document.querySelector('.header .search-bar input').value.trim();
        if (searchTerm) {
            window.location.href = 'search.php?q=' + encodeURIComponent(searchTerm);
        }
    });

    // Xử lý submit form bộ lọc
    document.getElementById('filter-form').addEventListener('submit', function(e) {
        // Reset page về 1 khi áp dụng bộ lọc mới
        const pageInput = document.createElement('input');
        pageInput.type = 'hidden';
        pageInput.name = 'page';
        pageInput.value = '1';
        this.appendChild(pageInput);
    });

    // Xử lý khi nhấn Enter trong ô tìm kiếm
    document.querySelector('.header .search-bar input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.querySelector('.header .search-bar button').click();
        }
    });

    // Toggle dropdown tài khoản
    document.querySelector('.account-btn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.querySelector('.dropdown-content').classList.toggle('show');
    });

    // Đóng dropdown khi click ra ngoài
    window.addEventListener('click', function() {
        const dropdowns = document.querySelectorAll('.dropdown-content');
        dropdowns.forEach(dropdown => {
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        });
    });
    </script>
</body>
</html>