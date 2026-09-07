<?php
require_once '../../includes/database.php';
$result = $conn->query("SELECT posts.*, users.name as author_name FROM posts LEFT JOIN users ON posts.author_id = users.id ORDER BY posts.id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Bài viết - Mây Admin</title>
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
                    <img src="../../uploads/images/logomay.jpg" alt="Mây Admin" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                    <span style="color: #333; font-size: 19px;">Mây Admin</span>
                </div>
                <nav>
                    <a href="../dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
                    <a href="../products/index.php"><i class="bi bi-box"></i> Quản lý sản phẩm</a>
                    <a href="../categories/index.php"><i class="bi bi-tags"></i> Quản lý danh mục</a>
                    <a href="../orders/index.php"><i class="bi bi-receipt"></i> Quản lý đơn hàng</a>
                    <a href="../users/index.php"><i class="bi bi-person"></i> Quản lý người dùng</a>
                    <a href="index.php" class="active"><i class="bi bi-journal-text"></i> Quản lý bài viết</a>
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
                    <h4 class="fw-bold mb-0 text-dark">Quản lý bài viết</h4>
                    <div class="d-flex align-items-center gap-3">
                        <img src="../../uploads/images/logomay.jpg" alt="Admin" style="width: 42px; height: 42px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Mây Admin</h6>
                            <small class="text-muted">Quản trị viên hệ thống</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <a href="create.php" class="btn btn-dark">+ Thêm bài viết</a>
                </div>

                <div class="card border-0 shadow-sm p-3 rounded-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ảnh bìa</th>
                                    <th>Tiêu đề</th>
                                    <th>Tác giả</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đăng</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-muted">#BV<?= $row['id'] ?></td>
                                        <td>
                                            <?php if(!empty($row['image'])): ?>
                                                <img src="../../uploads/posts/<?= htmlspecialchars($row['image']) ?>" class="post-img shadow-sm" alt="">
                                            <?php else: ?>
                                                <span class="text-muted small">Không ảnh</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-medium" style="max-width: 300px;"><?= htmlspecialchars($row['title']) ?></td>
                                        <td><?= htmlspecialchars($row['author_name'] ?? 'Admin') ?></td>
                                        <td>
                                            <?= ($row['status'] == 1) ? '<span class="badge bg-success bg-opacity-25 text-success">Đã đăng</span>' : '<span class="badge bg-secondary bg-opacity-25 text-secondary">Bản nháp</span>' ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                                        <td>
                                            <a href="edit.php?id=<?= $row['id'] ?>" class="text-primary fs-5 me-2" title="Sửa bài viết"><i class="bi bi-pencil-square"></i></a>
                                            <a href="delete.php?id=<?= $row['id'] ?>" class="text-danger fs-5" title="Xóa bài viết" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center py-4 text-muted">Chưa có bài viết nào trong hệ thống.</td></tr>
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
