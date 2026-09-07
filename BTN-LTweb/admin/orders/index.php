<?php
require_once '../../includes/database.php';
$result = $conn->query("SELECT * FROM orders ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn hàng - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="sidebar-logo d-flex align-items-center gap-2">
                    <img src="../../uploads/images/logomay.jpg" alt="Mây Store" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                    <span style="color: #333; font-size: 19px;">Mây Store</span>
                </div>
                <nav>
                    <a href="../dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
                    <a href="../products/index.php"><i class="bi bi-box"></i> Quản lý sản phẩm</a>
                    <a href="../categories/index.php"><i class="bi bi-tags"></i> Quản lý danh mục</a>
                    <a href="index.php" class="active"><i class="bi bi-receipt"></i> Quản lý đơn hàng</a>
                    <a href="../users/index.php"><i class="bi bi-person"></i> Quản lý người dùng</a>
                    <a href="../posts/index.php"><i class="bi bi-journal-text"></i> Quản lý bài viết</a>
                    <a href="../banners/index.php"><i class="bi bi-image"></i> Quản lý banner</a>
                    <a href="../statistics.php"><i class="bi bi-bar-chart"></i> Thống kê doanh thu</a>
                    <hr class="my-3 text-muted">
                    <a href="../../fashion-store/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <!-- Topbar góc phải chứa thông tin Admin -->
                <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                    <h4 class="fw-bold mb-0 text-dark">Quản lý đơn hàng</h4>
                    <div class="d-flex align-items-center gap-3">
                        <img src="../../uploads/images/logomay.jpg" alt="Admin" style="width: 42px; height: 42px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Mây Admin</h6>
                            <small class="text-muted">Quản trị viên hệ thống</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <a href="create.php" class="btn btn-dark">+ Thêm đơn hàng</a>
                </div>

                <div class="card border-0 shadow-sm p-3 rounded-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Số điện thoại</th>
                                    <th>Tổng tiền</th>
                                    <th>Thanh toán</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-muted">#<?= htmlspecialchars($row['order_code']) ?></td>
                                        <td class="fw-medium"><?= htmlspecialchars($row['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($row['phone']) ?></td>
                                        <td class="text-danger fw-bold"><?= number_format($row['total_price']) ?> đ</td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['payment_method']) ?></span></td>
                                        <td>
                                            <?php 
                                                $status = $row['status'];
                                                $badge_bg = 'bg-warning text-dark';
                                                if($status == 'completed') $badge_bg = 'bg-success text-white';
                                                elseif($status == 'cancelled') $badge_bg = 'bg-danger text-white';
                                                elseif($status == 'shipping') $badge_bg = 'bg-info text-dark';
                                                elseif($status == 'confirmed') $badge_bg = 'bg-primary text-white';
                                            ?>
                                            <span class="badge <?= $badge_bg ?>"><?= ucfirst($status) ?></span>
                                        </td>
                                        <td>
                                            <a href="detail.php?id=<?= $row['id'] ?>" class="text-success fs-5 me-2" title="Xem chi tiết & Xác nhận"><i class="bi bi-eye"></i></a>
                                            <a href="edit.php?id=<?= $row['id'] ?>" class="text-primary fs-5 me-2" title="Sửa đơn hàng"><i class="bi bi-pencil-square"></i></a>
                                            <a href="delete.php?id=<?= $row['id'] ?>" class="text-danger fs-5" title="Xóa đơn hàng" onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này?');"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center py-4 text-muted">Chưa có đơn hàng nào trong hệ thống.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
