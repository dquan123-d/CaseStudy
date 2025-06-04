
    // Khởi tạo lightbox với các tùy chọn
    lightbox.option({
        'resizeDuration': 200,
        'wrapAround': true,
        'showImageNumberLabel': true,
        'positionFromTop': 100,
        'disableScrolling': true
    });
    
    // Thêm hiệu ứng khi đóng/mở lightbox
    document.addEventListener('lightbox:show', function() {
        document.body.style.overflow = 'hidden';
    });
    
    document.addEventListener('lightbox:close', function() {
        document.body.style.overflow = '';
    });
