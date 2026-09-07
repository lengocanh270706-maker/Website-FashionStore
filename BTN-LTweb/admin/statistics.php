<?php
require_once '../includes/database.php';

$total_revenue = $conn->query("SELECT COALESCE(SUM(total_price),0) total FROM orders WHERE status='completed'")->fetch_assoc()['total'];
$total_orders = $conn->query("SELECT COUNT(*) count FROM orders")->fetch_assoc()['count'];
$total_customers = $conn->query("SELECT COUNT(*) count FROM users WHERE role='user'")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) count FROM products")->fetch_assoc()['count'];

$revenue = [];
$q = $conn->query("
    SELECT DATE_FORMAT(created_at,'%Y-%m') month,SUM(total_price) total
    FROM orders
    WHERE status='completed' AND created_at>=DATE_SUB(CURDATE(),INTERVAL 11 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m')
    ORDER BY month
");
while ($r = $q->fetch_assoc()) $revenue[$r['month']] = $r['total'];

$months = $revenue_data = [];
for ($i=11; $i>=0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $months[] = date('m/Y', strtotime("-$i months"));
    $revenue_data[] = $revenue[$date] ?? 0;
}

$status_counts = ['completed'=>0,'shipping'=>0,'confirmed'=>0,'cancelled'=>0,'pending'=>0];
$q = $conn->query("SELECT status,COUNT(*) count FROM orders GROUP BY status");
while ($r = $q->fetch_assoc()) $status_counts[$r['status']] = $r['count'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<div class="container-fluid">
<div class="row">

<div class="col-md-2 sidebar">
    <div class="sidebar-logo d-flex align-items-center gap-2">
        <img src="../uploads/images/logomay.jpg" style="width:40px;height:40px;object-fit:cover;border-radius:50%;border:2px solid #d63384">
        <span style="color:#333;font-size:19px">Mây Store</span>
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
        <hr>
        <a href="../fashion-store/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
    </nav>
</div>

<div class="col-md-10 main-content">

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
    <h4 class="fw-bold mb-0">Thống kê doanh thu và báo cáo hệ thống</h4>
    <div class="d-flex align-items-center gap-3">
        <img src="../uploads/images/logomay.jpg" style="width:42px;height:42px;object-fit:cover;border-radius:50%;border:2px solid #d63384">
        <div>
            <h6 class="mb-0 fw-bold">Mây Admin</h6>
            <small class="text-muted">Quản trị viên hệ thống</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">

    <div class="col-md-3">
        <div class="stat-card">
            <div><p class="text-muted mb-1 small">Tổng doanh thu</p><h5 class="fw-bold text-danger"><?= number_format($total_revenue) ?> đ</h5></div>
            <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-currency-dollar"></i></div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div><p class="text-muted mb-1 small">Tổng đơn hàng</p><h5 class="fw-bold"><?= number_format($total_orders) ?></h5></div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-receipt"></i></div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div><p class="text-muted mb-1 small">Khách hàng</p><h5 class="fw-bold"><?= number_format($total_customers) ?></h5></div>
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-people"></i></div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div><p class="text-muted mb-1 small">Sản phẩm</p><h5 class="fw-bold"><?= number_format($total_products) ?></h5></div>
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-box-seam"></i></div>
        </div>
    </div>

</div>

<div class="row g-4">

    <div class="col-md-7">
        <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
            <h5 class="fw-bold mb-3">Biểu đồ tăng trưởng doanh thu theo tháng</h5>
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
            <h5 class="fw-bold mb-3">Tỷ lệ đơn hàng</h5>
            <div style="height:280px"><canvas id="orderStatusChart"></canvas></div>
        </div>
    </div>

</div>

</div>
</div>
</div>

<script>
new Chart(document.getElementById('revenueChart'),{
    type:'line',
    data:{
        labels:<?= json_encode($months) ?>,
        datasets:[{
            label:'Doanh thu (VNĐ)',
            data:<?= json_encode($revenue_data) ?>,
            borderColor:'#d63384',
            backgroundColor:'rgba(214,51,132,.05)',
            borderWidth:3,
            fill:true,
            tension:.3
        }]
    },
    options:{
        responsive:true,
        scales:{y:{beginAtZero:true,ticks:{callback:v=>new Intl.NumberFormat('vi-VN').format(v)+' đ'}}},
        plugins:{legend:{position:'top'}}
    }
});

new Chart(document.getElementById('orderStatusChart'),{
    type:'doughnut',
    data:{
        labels:['Hoàn thành','Đang giao','Chờ xác nhận','Đã hủy'],
        datasets:[{
            data:[
                <?= $status_counts['completed'] ?>,
                <?= $status_counts['shipping'] ?>,
                <?= $status_counts['confirmed'] + $status_counts['pending'] ?>,
                <?= $status_counts['cancelled'] ?>
            ],
            backgroundColor:['#0d6efd','#198754','#ffc107','#dc3545'],
            borderWidth:2
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{legend:{position:'right',labels:{boxWidth:12}}}
    }
});
</script>

</body>
</html>