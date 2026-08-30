<?php
require_once '../../includes/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM banners WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$banner = $stmt->get_result()->fetch_assoc();

if (!$banner) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $link = trim($_POST['link']);
    $status = $_POST['status'];

    $image = $banner['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], '../../uploads/' . $image);
    }

    $stmt_update = $conn->prepare("UPDATE banners SET title = ?, description = ?, image = ?, link = ?, status = ? WHERE id = ?");
    $stmt_update->bind_param("ssssii", $title, $description, $image, $link, $status, $id);
    
    if ($stmt_update->execute()) {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Banner - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 700px;">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h4 class="fw-bold mb-4">Chỉnh sửa banner: #BN<?= $banner['id'] ?></h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề banner *</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($banner['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Hình ảnh hiện tại</label>
                    <div class="mb-2">
                        <?php if(!empty($banner['image'])): ?>
                            <img src="../../uploads/<?= htmlspecialchars($banner['image']) ?>" style="width: 150px; height: 60px; object-fit: cover; border-radius: 6px;" alt="">
                        <?php else: ?>
                            <span class="text-muted small">Không có ảnh</span>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Đường dẫn liên kết (Link)</label>
                    <input type="text" name="link" class="form-control" value="<?= htmlspecialchars($banner['link'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái hiển thị</label>
                    <select name="status" class="form-select w-50">
                        <option value="1" <?= ($banner['status'] == 1) ? 'selected' : '' ?>>Bật (Đang hiển thị)</option>
                        <option value="0" <?= ($banner['status'] == 0) ? 'selected' : '' ?>>Tắt (Đang ẩn)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Mô tả ngắn</label>
                    <textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($banner['description'] ?? '') ?></textarea>
                </div>
                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
                    <button type="submit" class="btn btn-dark px-4">Cập nhật thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>