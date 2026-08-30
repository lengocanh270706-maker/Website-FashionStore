<?php
require_once '../../includes/database.php';
$result = $conn->query("SELECT products.*, categories.name as cat_name FROM products LEFT JOIN categories ON products.category_id = categories.id ORDER BY products.id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fc; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        .sidebar { background-color: #ffffff; min-height: 100vh; border-right: 1px solid #f0f0f0; padding-top: 20px; position: fixed; width: 16.666667%; top: 0; left: 0; }
        .sidebar-logo { font-size: 20px; font-weight: 700; margin-bottom: 30px; padding-left: 20px; color: #000; }
        .sidebar a { display: block; padding: 12px 20px; color: #6c757d; text-decoration: none; font-weight: 500; font-size: 15px; margin-bottom: 5px; border-radius: 0 30px 30px 0; }
        .sidebar a:hover, .sidebar a.active { background-color: #fff0f5; color: #d63384; font-weight: 600; }
        .sidebar a i { margin-right: 12px; font-size: 18px; }
        .main-content { margin-left: 16.666667%; padding: 30px 40px; width: 83.333333%; }
        .prod-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="sidebar-logo d-flex align-items-center gap-2">
                    <img src="../../uploads/logomay.jpg" alt="Mây Admin" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                    <span style="color: #333; font-size: 19px;">Mây Admin</span>
                </div>
                <nav>
                    <a href="../dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
                    <a href="index.php" class="active"><i class="bi bi-box"></i> Quản lý sản phẩm</a>
                    <a href="../categories/index.php"><i class="bi bi-tags"></i> Quản lý danh mục</a>
                    <a href="../orders/index.php"><i class="bi bi-receipt"></i> Quản lý đơn hàng</a>
                    <a href="../users/index.php"><i class="bi bi-person"></i> Quản lý người dùng</a>
                    <a href="../posts/index.php"><i class="bi bi-journal-text"></i> Quản lý bài viết</a>
                    <a href="../banners/index.php"><i class="bi bi-image"></i> Quản lý banner</a>
                    <a href="../statistics.php"><i class="bi bi-bar-chart"></i> Thống kê doanh thu</a>
                    <hr class="my-3 text-muted">
                    <a href="../../index.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold">Quản lý sản phẩm</h4>
                    <a href="create.php" class="btn btn-dark">+ Thêm sản phẩm</a>
                </div>
                <div class="card border-0 shadow-sm p-3 rounded-4">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá bán</th>
                                <th>Tồn kho</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-muted">#SP<?= $row['id'] ?></td>
                                    <td>
                                        <?php if(!empty($row['main_image'])): ?>
                                            <img src="../../uploads/<?= htmlspecialchars($row['main_image']) ?>" class="prod-img shadow-sm" alt="">
                                        <?php else: ?>
                                            <span class="text-muted small">Không ảnh</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-medium"><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['cat_name'] ?? 'Chưa phân loại') ?></td>
                                    <td class="text-danger fw-bold"><?= number_format($row['price']) ?> đ</td>
                                    <td><span class="badge bg-secondary"><?= $row['quantity'] ?></span></td>
                                    <td>
                                        <?= ($row['status'] == 1) ? '<span class="badge bg-success bg-opacity-25 text-success">Còn hàng</span>' : '<span class="badge bg-danger bg-opacity-25 text-danger">Hết hàng</span>' ?>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="text-primary fs-5 me-2" title="Sửa"><i class="bi bi-pencil-square"></i></a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" class="text-danger fs-5" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">Chưa có sản phẩm nào trong hệ thống.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>