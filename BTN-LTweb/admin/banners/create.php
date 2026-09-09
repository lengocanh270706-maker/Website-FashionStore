<?php
require_once '../../includes/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $start_at = !empty($_POST['start_at']) ? $_POST['start_at'] : null;
    $end_at = !empty($_POST['end_at']) ? $_POST['end_at'] : null;
    $status = isset($_POST['status']) ? 1 : 0;
    $image = '';

    if (!empty($_FILES['image']['name'])) {
        $upload_dir = '../../uploads/banners/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            die('Định dạng ảnh không hợp lệ.');
        }

        $image = time() . '_' . uniqid() . '.' . $ext;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image)) {
            die('Không thể tải ảnh lên.');
        }
    }

    if ($title === '') {
        die('Vui lòng nhập tiêu đề banner.');
    }

    $stmt = $conn->prepare("
        INSERT INTO banners (title, image, link, start_at, end_at, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssi", $title, $image, $link, $start_at, $end_at, $status);
    $stmt->execute();

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm banner - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar">
            <div class="sidebar-logo d-flex align-items-center gap-2">
                <img src="../../uploads/images/logomay.jpg"
                     style="width:40px;height:40px;object-fit:cover;border-radius:50%;border:2px solid #d63384">
                <span style="color:#333;font-size:19px;">Mây Admin</span>
            </div>

            <nav>
                <a href="../dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
                <a href="../products/index.php"><i class="bi bi-box"></i> Quản lý sản phẩm</a>
                <a href="../categories/index.php"><i class="bi bi-tags"></i> Quản lý danh mục</a>
                <a href="../orders/index.php"><i class="bi bi-receipt"></i> Quản lý đơn hàng</a>
                <a href="../users/index.php"><i class="bi bi-person"></i> Quản lý người dùng</a>
                <a href="../posts/index.php"><i class="bi bi-journal-text"></i> Quản lý bài viết</a>
                <a href="index.php" class="active"><i class="bi bi-image"></i> Quản lý banner</a>
                <a href="../statistics.php"><i class="bi bi-bar-chart"></i> Thống kê doanh thu</a>
                <hr class="my-3 text-muted">
                <a href="../../logout.php" class="text-danger">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
            </nav>
        </div>

        <div class="col-md-10 main-content">
            <h4 class="fw-bold mb-4">Thêm banner</h4>

            <div class="card border-0 shadow-sm p-4 rounded-4">
                <form method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tiêu đề banner</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hình ảnh</label>
                        <input type="file" name="image" class="form-control"
                               accept=".jpg,.jpeg,.png,.webp" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Ngày bắt đầu</label>
                            <input type="datetime-local" name="start_at" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Ngày kết thúc</label>
                            <input type="datetime-local" name="end_at" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Liên kết</label>

                        <select id="link_type" class="form-select mb-2">
                            <option value="">Không liên kết</option>
                            <option value="home">Trang chủ</option>
                            <option value="products">Trang sản phẩm</option>
                            <option value="product">Chi tiết sản phẩm</option>
                        </select>

                        <input type="text"
                               id="product_id"
                               class="form-control mb-2"
                               placeholder="Nhập ID sản phẩm"
                               style="display:none">

                        <input type="text"
                               name="link"
                               id="link"
                               class="form-control"
                               placeholder="Link liên kết">
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox"
                               name="status"
                               class="form-check-input"
                               id="status"
                               checked>
                        <label class="form-check-label" for="status">
                            Hiển thị banner
                        </label>
                    </div>

                    <a href="index.php" class="btn btn-secondary">
                        Quay lại
                    </a>

                    <button type="submit" class="btn btn-dark">
                        Thêm banner
                    </button>

                </form>
            </div>
        </div>

    </div>
</div>

<script src="../assets/js/script.js?v=5"></script>

</body>
</html>