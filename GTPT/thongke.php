<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thống kê</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f7f7f7;
      padding: 30px;
    }

    .stat-box {
      background-color: white;
      border-radius: 8px;
      padding: 20px;
      max-width: 400px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin: auto;
    }

    .stat-box h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .stat-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #ddd;
    }

    .stat-item:last-child {
      border-bottom: none;
    }

    .stat-label {
      font-weight: bolder;
    }

    .stat-count {
      color: #007bff;
    }
  </style>
</head>
<body>
  <div class="stat-box">
    <h2>Thống kê</h2>

    <div class="stat-item">
      <div class="stat-label">Chờ duyệt:</div>
      <div class="stat-count">12</div>
    </div>

    <div class="stat-item">
      <div class="stat-label">Đã duyệt:</div>
      <div class="stat-count">45</div>
    </div>

    <div class="stat-item">
      <div class="stat-label">Đã ẩn:</div>
      <div class="stat-count">8</div>
    </div>

    <div class="stat-item">
      <div class="stat-label">Tổng số:</div>
      <div class="stat-count">65</div>
    </div>
  </div>
</body>
</html>
