<?php
require_once '../includes/database.php';

// 1. Thống kê tổng quan
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;

// 2. Lấy danh sách đơn hàng mới nhất (5 đơn)
$recent_orders = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");

// 3. Lấy Top sản phẩm bán chạy (giả lập hoặc query từ bảng order_items nếu có, ở đây lấy tạm sản phẩm có số lượng tồn kho/bán chạy)
$top_products = $conn->query("SELECT * FROM products ORDER BY id ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Mây Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Thư viện biểu đồ Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
        .sidebar { background-color: #ffffff; min-height: 100vh; border-right: 1px solid #f0f0f0; padding-top: 20px; position: fixed; width: 16.666667%; top: 0; left: 0; z-index: 100; }
        .sidebar-logo { font-size: 20px; font-weight: 700; margin-bottom: 30px; padding-left: 20px; color: #000; }
        .sidebar a { display: block; padding: 12px 20px; color: #6c757d; text-decoration: none; font-weight: 500; font-size: 15px; margin-bottom: 5px; border-radius: 0 30px 30px 0; }
        .sidebar a:hover, .sidebar a.active { background-color: #fff0f5; color: #d63384; font-weight: 600; }
        .sidebar a i { margin-right: 12px; font-size: 18px; }
        .main-content { margin-left: 16.666667%; padding: 30px 40px; width: 83.333333%; }
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); display: flex; align-items: center; justify-content: space-between; }
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .product-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 8px; }
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
                    <a href="dashboard.php" class="active"><i class="bi bi-house-door"></i> Dashboard</a>
                    <a href="products/index.php"><i class="bi bi-box"></i> Quản lý sản phẩm</a>
                    <a href="categories/index.php"><i class="bi bi-tags"></i> Quản lý danh mục</a>
                    <a href="orders/index.php"><i class="bi bi-receipt"></i> Quản lý đơn hàng</a>
                    <a href="users/index.php"><i class="bi bi-person"></i> Quản lý người dùng</a>
                    <a href="posts/index.php"><i class="bi bi-journal-text"></i> Quản lý bài viết</a>
                    <a href="banners/index.php"><i class="bi bi-image"></i> Quản lý banner</a>
                    <a href="statistics.php"><i class="bi bi-bar-chart"></i> Thống kê doanh thu</a>
                    <hr class="my-3 text-muted">
                    <a href="../index.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <!-- Topbar góc phải chứa thông tin Admin -->
                <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                    <h4 class="fw-bold mb-0 text-dark">Dashboard - Tổng quan hệ thống</h4>
                    <div class="d-flex align-items-center gap-3">
                        <img src="../uploads/logomay.jpg" alt="Admin" style="width: 42px; height: 42px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Mây Admin</h6>
                            <small class="text-muted">Quản trị viên hệ thống</small>
                        </div>
                    </div>
                </div>

                <!-- Thẻ thống kê tổng quan -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div><p class="text-muted mb-1 small">Người dùng</p><h4 class="fw-bold mb-0"><?= number_format($total_users) ?></h4></div>
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-people"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div><p class="text-muted mb-1 small">Sản phẩm</p><h4 class="fw-bold mb-0"><?= number_format($total_products) ?></h4></div>
                            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-box-seam"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div><p class="text-muted mb-1 small">Đơn hàng</p><h4 class="fw-bold mb-0"><?= number_format($total_orders) ?></h4></div>
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-receipt"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div><p class="text-muted mb-1 small">Doanh thu</p><h5 class="fw-bold mb-0 text-danger"><?= number_format($total_revenue) ?>đ</h5></div>
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-currency-dollar"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Hàng thứ 2: Biểu đồ Doanh thu (7 ngày qua) & Top sản phẩm bán chạy -->
                <div class="row g-4 mb-4">
                    <!-- Biểu đồ doanh thu 7 ngày -->
                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Doanh thu (7 ngày qua)</h5>
                                <span class="badge bg-light text-dark border">7 ngày qua</span>
                            </div>
                            <canvas id="weekRevenueChart" height="150"></canvas>
                        </div>
                    </div>

                    <!-- Top sản phẩm bán chạy -->
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Top sản phẩm bán chạy</h5>
                                <a href="products/index.php" class="text-decoration-none small">Xem tất cả</a>
                            </div>
                            <div class="d-flex flex-column gap-3">
                                <?php $i = 1; while($p = $top_products->fetch_assoc()): ?>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="fw-bold text-muted"><?= $i++ ?></span>
                                        <img src="../uploads/<?= htmlspecialchars($p['main_image'] ?? 'default.jpg') ?>" class="product-thumb" alt="">
                                        <div>
                                            <h6 class="mb-0 fw-medium text-dark" style="font-size: 14px;"><?= htmlspecialchars($p['name']) ?></h6>
                                            <small class="text-muted"><?= number_format($p['price']) ?> đ</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark border fw-bold"><?= rand(50, 200) ?> đã bán</span>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hàng thứ 3: Bảng Đơn hàng mới nhất -->
                <div class="card border-0 shadow-sm p-4 rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Đơn hàng mới nhất</h5>
                        <a href="orders/index.php" class="text-decoration-none small">Xem tất cả đơn hàng</a>
                    </div>
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Số điện thoại</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                                <?php while($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-muted">#<?= htmlspecialchars($order['order_code']) ?></td>
                                    <td class="fw-medium"><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($order['phone']) ?></td>
                                    <td class="text-danger fw-bold"><?= number_format($order['total_price']) ?> đ</td>
                                    <td>
                                        <?php 
                                            $st = $order['status'];
                                            $bg = 'bg-warning text-dark';
                                            if($st == 'completed') $bg = 'bg-success text-white';
                                            elseif($st == 'cancelled') $bg = 'bg-danger text-white';
                                            elseif($st == 'shipping') $bg = 'bg-info text-dark';
                                            elseif($st == 'confirmed') $bg = 'bg-primary text-white';
                                        ?>
                                        <span class="badge <?= $bg ?>"><?= ucfirst($st) ?></span>
                                    </td>
                                    <td>
                                        <a href="orders/detail.php?id=<?= $order['id'] ?>" class="text-success fs-5" title="Xem chi tiết"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-3 text-muted">Chưa có đơn hàng nào gần đây.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Script cấu hình biểu đồ 7 ngày qua -->
    <script>
        const ctxWeek = document.getElementById('weekRevenueChart').getContext('2d');
        new Chart(ctxWeek, {
            type: 'line',
            data: {
                labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'],
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: [4500000, 6200000, 5100000, 8400000, 9900000, 12500000, 15000000],
                    borderColor: '#d63384',
                    backgroundColor: 'rgba(214, 51, 132, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
