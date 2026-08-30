<?php
require_once '../../includes/database.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name, description, status) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $status);
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Lỗi cơ sở dữ liệu: " . $conn->error;
        }
    } else {
        $error = "Vui lòng nhập tên danh mục!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Danh mục - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 700px;">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h4 class="fw-bold mb-4">Thêm danh mục mới</h4>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên danh mục *</label>
                    <input type="text" name="name" class="form-control" placeholder="Ví dụ: Áo sơ mi, Quần jean..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả danh mục</label>
                    <textarea name="description" rows="4" class="form-control" placeholder="Nhập mô tả chi tiết cho danh mục..."></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Trạng thái hiển thị</label>
                    <select name="status" class="form-select w-50">
                        <option value="1">Hiển thị</option>
                        <option value="0">Ẩn</option>
                    </select>
                </div>
                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
                    <button type="submit" class="btn btn-dark px-4">Lưu danh mục</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>