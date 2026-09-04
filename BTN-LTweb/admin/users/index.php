<?php
require_once '../../includes/database.php';

// Xử lý Khóa / Mở khóa tài khoản (Lock / Unlock)
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $current_status = $_GET['status'];
    $new_status = ($current_status == 1) ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Người dùng - Mây Admin</title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="sidebar-logo d-flex align-items-center gap-2">
                    <img src="../../uploads/logomay.jpg" alt="Mây Admin" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                    <span style="color: #333; font-size: 19px;">Mây Store</span>
                </div>
                <nav>
                    <a href="../dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
                    <a href="../products/index.php"><i class="bi bi-box"></i> Quản lý sản phẩm</a>
                    <a href="../categories/index.php"><i class="bi bi-tags"></i> Quản lý danh mục</a>
                    <a href="../orders/index.php"><i class="bi bi-receipt"></i> Quản lý đơn hàng</a>
                    <a href="index.php" class="active"><i class="bi bi-person"></i> Quản lý người dùng</a>
                    <a href="../posts/index.php"><i class="bi bi-journal-text"></i> Quản lý bài viết</a>
                    <a href="../banners/index.php"><i class="bi bi-image"></i> Quản lý banner</a>
                    <a href="../statistics.php"><i class="bi bi-bar-chart"></i> Thống kê doanh thu</a>
                    <hr class="my-3 text-muted">
                    <a href="../../index.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <!-- Topbar góc phải chứa thông tin Admin -->
                <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                    <h4 class="fw-bold mb-0 text-dark">Quản lý người dùng</h4>
                    <div class="d-flex align-items-center gap-3">
                        <img src="../../uploads/logomay.jpg" alt="Admin" style="width: 42px; height: 42px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Mây Admin</h6>
                            <small class="text-muted">Quản trị viên hệ thống</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <a href="create.php" class="btn btn-dark">+ Thêm người dùng mới</a>
                </div>

                <div class="card border-0 shadow-sm p-3 rounded-4">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ và tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-muted">#NC<?= $row['id'] ?></td>
                                    <td class="fw-medium"><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone'] ?? 'Chưa cập nhật') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($row['role'] == 'admin') ? 'dark' : 'secondary' ?>">
                                            <?= ucfirst($row['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= ($row['status'] == 1) ? '<span class="badge bg-success bg-opacity-25 text-success">Hoạt động</span>' : '<span class="badge bg-danger bg-opacity-25 text-danger">Bị khóa</span>' ?>
                                    </td>
                                    <td>
                                        <!-- Nút Sửa / Phân quyền -->
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="text-primary fs-5 me-2" title="Chỉnh sửa / Phân quyền"><i class="bi bi-pencil-square"></i></a>
                                        
                                        <!-- Nút Khóa / Mở khóa tài khoản -->
                                        <a href="index.php?action=toggle&id=<?= $row['id'] ?>&status=<?= $row['status'] ?>" 
                                           class="btn btn-sm btn-outline-<?= ($row['status'] == 1) ? 'danger' : 'success' ?> me-1"
                                           onclick="return confirm('Bạn có chắc muốn thay đổi trạng thái tài khoản này?');">
                                            <?= ($row['status'] == 1) ? 'Khóa' : 'Mở khóa' ?>
                                        </a>

                                        <!-- Nút Xóa -->
                                        <a href="delete.php?id=<?= $row['id'] ?>" class="text-danger fs-5" title="Xóa tài khoản" onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản này?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">Chưa có người dùng nào trong hệ thống.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
