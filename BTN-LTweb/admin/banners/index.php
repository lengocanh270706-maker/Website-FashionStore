<?php
require_once '../../includes/database.php';

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'toggle') {
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("
        UPDATE banners
        SET status = IF(status = 1, 0, 1), updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header('Location: index.php');
    exit;
}

$result = $conn->query("SELECT * FROM banners ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Banner - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
<div class="container-fluid">
<div class="row">
<div class="col-12 d-md-none">
    <nav class="navbar navbar-light bg-white border-bottom">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-2">
                <button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminMenu" aria-controls="adminMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="bi bi-list fs-2"></i>
                </button>
                <span class="navbar-brand fw-bold mb-0">Mây Store</span>
            </div>
        </div>
    </nav>
</div>

<div class="col-12 col-md-3 col-lg-2 sidebar">
    <div class="collapse d-md-block" id="adminMenu">

        <div class="sidebar-logo d-flex align-items-center gap-2">
            <img src="../../uploads/images/logomay.jpg" alt="Mây Store" style="width:40px;height:40px;object-fit:cover;border-radius:50%;border:2px solid #d63384;">
            <span style="color:#333;font-size:19px;">Mây Store</span>
        </div>

        <nav class="d-flex flex-wrap flex-md-column gap-1">
            <a href="../dashboard.php" class="active"><i class="bi bi-house-door"></i> Dashboard</a>
            <a href="../products/index.php"><i class="bi bi-box"></i> Quản lý sản phẩm</a>
            <a href="../categories/index.php"><i class="bi bi-tags"></i> Quản lý danh mục</a>
            <a href="../orders/index.php"><i class="bi bi-receipt"></i> Quản lý đơn hàng</a>
            <a href="../users/index.php"><i class="bi bi-person"></i> Quản lý người dùng</a>
            <a href="../posts/index.php"><i class="bi bi-journal-text"></i> Quản lý bài viết</a>
            <a href="../banners/index.php"><i class="bi bi-image"></i> Quản lý banner</a>
            <a href="../statistics.php"><i class="bi bi-bar-chart"></i> Thống kê doanh thu</a>
            <hr class="my-3 text-muted w-100">
            <a href="../../fashion-store/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
        </nav>

    </div>
</div>

        <div class="col-md-10 main-content">
                <!-- Topbar góc phải chứa thông tin Admin -->
                <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                    <h4 class="fw-bold mb-0 text-dark">Quản lý banners</h4>
                    <div class="d-flex align-items-center gap-3">
                        <img src="../../uploads/images/logomay.jpg" alt="Admin" style="width: 42px; height: 42px; object-fit: cover; border-radius: 50%; border: 2px solid #d63384;">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Mây Admin</h6>
                            <small class="text-muted">Quản trị viên hệ thống</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <a href="create.php" class="btn btn-dark">+ Thêm banner</a>
                </div>

            <div class="card border-0 shadow-sm p-3 rounded-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hình ảnh</th>
                                <th>Tiêu đề</th>
                                <th>Link liên kết</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-muted">#BN<?= $row['id'] ?></td>
                                    <td>
                                        <?php if (!empty($row['image'])): ?>
                                            <img src="../../uploads/banners/<?= htmlspecialchars($row['image']) ?>"
                                                class="banner-img shadow-sm"
                                                alt="Banner">
                                        <?php else: ?>
                                            <span class="text-muted small">Không ảnh</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-medium"><?= htmlspecialchars($row['title']) ?></td>
                                    <td>
                                        <?php if (!empty($row['link'])): ?>
                                            <a href="<?= htmlspecialchars($row['link']) ?>"
                                            target="_blank"
                                            class="small text-decoration-none">
                                                <?= htmlspecialchars($row['link']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Không có</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($row['status'] == 1): ?>
                                            <span class="badge bg-success bg-opacity-25 text-success">Đang hiển thị</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary">Đã ẩn</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <a href="edit.php?id=<?= $row['id'] ?>"
                                        class="text-primary fs-5 me-2"
                                        title="Sửa banner">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <a href="index.php?action=toggle&id=<?= $row['id'] ?>"
                                        class="btn btn-sm btn-outline-<?= $row['status'] == 1 ? 'secondary' : 'success' ?>"
                                        onclick="return confirm('Bạn có chắc muốn đổi trạng thái banner này?');">
                                            <?= $row['status'] == 1 ? 'Tắt' : 'Bật' ?>
                                        </a>

                                        <a href="delete.php?id=<?= $row['id'] ?>"
                                        class="text-danger fs-5 ms-2"
                                        title="Xóa"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa banner này?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Chưa có banner nào trong hệ thống.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>