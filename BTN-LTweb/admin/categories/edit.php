<?php
require_once '../../includes/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    $stmt_update = $conn->prepare("UPDATE categories SET name = ?, description = ?, status = ? WHERE id = ?");
    $stmt_update->bind_param("ssii", $name, $description, $status, $id);
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
    <title>Sửa Danh mục - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 700px;">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h4 class="fw-bold mb-4">Chỉnh sửa danh mục: #DM<?= $category['id'] ?></h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên danh mục *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả danh mục</label>
                    <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($category['description']) ?></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Trạng thái hiển thị</label>
                    <select name="status" class="form-select w-50">
                        <option value="1" <?= $category['status'] == 1 ? 'selected' : '' ?>>Hiển thị</option>
                        <option value="0" <?= $category['status'] == 0 ? 'selected' : '' ?>>Ẩn</option>
                    </select>
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