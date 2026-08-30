<?php
require_once '../../includes/database.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $link = trim($_POST['link']);
    $status = $_POST['status'];

    if (!empty($title)) {
        $image = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = time() . '_' . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], '../../uploads/' . $image);
        }

        $stmt = $conn->prepare("INSERT INTO banners (title, description, image, link, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $title, $description, $image, $link, $status);
        
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Lỗi cơ sở dữ liệu: " . $conn->error;
        }
    } else {
        $error = "Vui lòng nhập tiêu đề banner!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Banner - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 700px;">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h4 class="fw-bold mb-4">Thêm banner mới</h4>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề banner *</label>
                    <input type="text" name="title" class="form-control" placeholder="Nhập tiêu đề banner..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Hình ảnh banner *</label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Đường dẫn liên kết (Link khi click vào banner)</label>
                    <input type="text" name="link" class="form-control" placeholder="Ví dụ: products/detail.php?id=1">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái hiển thị</label>
                    <select name="status" class="form-select w-50">
                        <option value="1">Bật (Hiển thị ngay)</option>
                        <option value="0">Tắt (Ẩn)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Mô tả ngắn</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="Nhập mô tả hoặc khẩu hiệu trên banner..."></textarea>
                </div>
                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
                    <button type="submit" class="btn btn-dark px-4">Lưu banner</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>