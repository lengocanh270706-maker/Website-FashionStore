<?php
require_once '../includes/database.php';

// Lấy tổng doanh thu từ các đơn hàng đã hoàn thành
$total_revenue = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;

// Lấy tổng số lượng đơn hàng
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];

// Lấy tổng số khách hàng 
$total_customers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];

// Lấy tổng số sản phẩm
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];

// Lấy số lượng đơn hàng theo từng trạng thái để làm biểu đồ tròn
$status_counts = [
    'completed' => 0,
    'shipping' => 0,
    'confirmed' => 0,
    'cancelled' => 0,
    'pending' => 0
];

$order_status_query = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
while ($row = $order_status_query->fetch_assoc()) {
    if (array_key_exists($row['status'], $status_counts)) {
        $status_counts[$row['status']] = $row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê doanh thu - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Thư viện biểu đồ Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fc; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        .sidebar { background-color: #ffffff; min-height: 100vh; border-right: 1px solid #f0f0f0; padding-top: 20px; position: fixed; width: 16.666667%; top: 0; left: 0; }
        .sidebar-logo { font-size: 20px; font-weight: 700; margin-bottom: 30px; padding-left: 20px; color: #000; }
        .sidebar a { display: block; padding: 12px 20px; color: #6c757d; text-decoration: none; font-weight: 500; font-size: 15px; margin-bottom: 5px; border-radius: 0 30px 30px 0; }
        .sidebar a:hover, .sidebar a.active { background-color: #fff0f5; color: #d63384; font-weight: 600; }
        .sidebar a i { margin-right: 12px; font-size: 18px; }
        .main-content { margin-left: 16.666667%; padding: 30px 40px; width: 83.333333%; }
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); display: flex; align-items: center; justify-content: space-between; }
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="sidebar-logo d-flex align-items-center gap-2">
                    <img src="../uploads/logomay.jpg" alt="Mây Store" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                    <span style="color: #333; font-size: 19px;">Mây Store</span>
                </div>
                <nav>
                    <a href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
                    <a href="products/index.php"><i class="bi bi-box"></i> Quản lý sản phẩm</a>
                    <a href="categories/index.php"><i class="bi bi-tags"></i> Quản lý danh mục</a>
                    <a href="orders/index.php"><i class="bi bi-receipt"></i> Quản lý đơn hàng</a>
                    <a href="users/index.php"><i class="bi bi-person"></i> Quản lý người dùng</a>
                    <a href="posts/index.php"><i class="bi bi-journal-text"></i> Quản lý bài viết</a>
                    <a href="banners/index.php"><i class="bi bi-image"></i> Quản lý banner</a>
                    <a href="statistics.php" class="active"><i class="bi bi-bar-chart"></i> Thống kê doanh thu</a>
                    <hr class="my-3 text-muted">
                    <a href="../index.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <!-- Topbar góc phải chứa thông tin Admin -->
                <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                    <h4 class="fw-bold mb-0 text-dark">Thống kê doanh thu và báo cáo hệ thống</h4>
                    <div class="d-flex align-items-center gap-3">
                        <img src="../uploads/logomay.jpg" alt="Admin" style="width: 42px; height: 42px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Mây Admin</h6>
                            <small class="text-muted">Quản trị viên hệ thống</small>
                        </div>
                    </div>
                </div>
                
                <!-- Các thẻ thống kê tổng quan -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div>
                                <p class="text-muted mb-1 small">Tổng doanh thu</p>
                                <h5 class="fw-bold text-danger mb-0"><?= number_format($total_revenue) ?> đ</h5>
                            </div>
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-currency-dollar"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div>
                                <p class="text-muted mb-1 small">Tổng đơn hàng</p>
                                <h5 class="fw-bold mb-0"><?= number_format($total_orders) ?></h5>
                            </div>
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-receipt"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div>
                                <p class="text-muted mb-1 small">Khách hàng</p>
                                <h5 class="fw-bold mb-0"><?= number_format($total_customers) ?></h5>
                            </div>
                            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-people"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div>
                                <p class="text-muted mb-1 small">Sản phẩm</p>
                                <h5 class="fw-bold mb-0"><?= number_format($total_products) ?></h5>
                            </div>
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-box-seam"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Khu vực hiển thị 2 Biểu đồ (Đường và Tròn) -->
                <div class="row g-4">
                    <!-- Biểu đồ doanh thu (Bên trái) -->
                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                            <h5 class="fw-bold mb-3">Biểu đồ tăng trưởng doanh thu theo tháng</h5>
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <!-- Biểu đồ tròn Tỷ lệ đơn hàng (Bên phải) -->
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                            <h5 class="fw-bold mb-3">Tỷ lệ đơn hàng</h5>
                            <div class="d-flex justify-content-center align-items-center" style="height: 280px;">
                                <canvas id="orderStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script cấu hình biểu đồ Chart.js -->
    <script>
        // 1. Biểu đồ đường Doanh thu
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                datasets: [{
                    label: 'Doanh thu thực tế (VNĐ)',
                    data: [12000000, 19000000, 30000000, 25000000, 42000000, 58000000],
                    borderColor: '#d63384',
                    backgroundColor: 'rgba(214, 51, 132, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } }
            }
        });

        // 2. Biểu đồ tròn Tỷ lệ đơn hàng 
        const ctxOrder = document.getElementById('orderStatusChart').getContext('2d');
        new Chart(ctxOrder, {
            type: 'doughnut',
            data: {
                labels: ['Hoàn thành', 'Đang giao', 'Chờ xác nhận', 'Đã hủy'],
                datasets: [{
                    data: [
                        <?= $status_counts['completed'] ?>, 
                        <?= $status_counts['shipping'] ?>, 
                        <?= $status_counts['confirmed'] + $status_counts['pending'] ?>, 
                        <?= $status_counts['cancelled'] ?>
                    ],
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12, font: { size: 13 } }
                    }
                }
            }
        });
    </script>
</body>
</html>
