<?php
require_once '../includes/database.php';
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
$recent_orders = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Mây Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background-color: #ffffff; min-height: 100vh; border-right: 1px solid #f0f0f0; padding-top: 20px; position: fixed; width: 16.666667%; }
        .sidebar-logo { font-size: 20px; font-weight: 700; margin-bottom: 30px; padding-left: 20px; color: #000; }
        .sidebar a { display: block; padding: 12px 20px; color: #6c757d; text-decoration: none; font-weight: 500; font-size: 15px; margin-bottom: 5px; border-radius: 0 30px 30px 0; }
        .sidebar a:hover, .sidebar a.active { background-color: #fff0f5; color: #d63384; font-weight: 600; }
        .sidebar a i { margin-right: 12px; font-size: 18px; }
        .main-content { margin-left: 16.666667%; padding: 30px 40px; }
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); display: flex; align-items: center; justify-content: space-between; }
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    </style>
</head>
<body>
    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <div class="sidebar-logo d-flex align-items-center gap-2">
                <img src="../uploads/logomay.jpg" alt="Mây Admin" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                <span style="color: #333; font-size: 19px;">Mây Admin</span>
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
        <div class="col-md-10 main-content">
            <h4 class="fw-bold mb-4">Dashboard - Tổng quan hệ thống</h4>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div><p class="text-muted mb-1">Người dùng</p><h4 class="fw-bold mb-0"><?= number_format($total_users) ?></h4></div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-people"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div><p class="text-muted mb-1">Sản phẩm</p><h4 class="fw-bold mb-0"><?= number_format($total_products) ?></h4></div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-box-seam"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div><p class="text-muted mb-1">Đơn hàng</p><h4 class="fw-bold mb-0"><?= number_format($total_orders) ?></h4></div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-receipt"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div><p class="text-muted mb-1">Doanh thu</p><h5 class="fw-bold mb-0 text-danger"><?= number_format($total_revenue) ?>đ</h5></div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-currency-dollar"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
