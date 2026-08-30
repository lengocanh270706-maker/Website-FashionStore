<?php
require_once '../../includes/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $status = $_POST['status'];

    $image = $post['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], '../../uploads/' . $image);
    }

    $stmt_update = $conn->prepare("UPDATE posts SET title = ?, content = ?, image = ?, status = ? WHERE id = ?");
    $stmt_update->bind_param("sssii", $title, $content, $image, $status, $id);
    
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
    <title>Sửa Bài viết - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Sử dụng CDN CKEditor Full để hiển thị đầy đủ thanh công cụ -->
    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 950px;">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h4 class="fw-bold mb-4">Chỉnh sửa bài viết: #BV<?= $post['id'] ?></h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề bài viết *</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Ảnh bìa hiện tại</label>
                    <div class="mb-2">
                        <?php if(!empty($post['image'])): ?>
                            <img src="../../uploads/<?= htmlspecialchars($post['image']) ?>" style="width: 100px; height: 60px; object-fit: cover; border-radius: 6px;" alt="">
                        <?php else: ?>
                            <span class="text-muted small">Không có ảnh bìa</span>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nội dung chi tiết</label>
                    <textarea name="content" id="editor" rows="10" class="form-control"><?= htmlspecialchars($post['content']) ?></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select w-50">
                        <option value="1" <?= ($post['status'] == 1) ? 'selected' : '' ?>>Đã đăng (Published)</option>
                        <option value="0" <?= ($post['status'] == 0) ? 'selected' : '' ?>>Bản nháp (Draft)</option>
                    </select>
                </div>
                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
                    <button type="submit" class="btn btn-dark px-4">Cập nhật thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kích hoạt CKEditor cho textarea -->
    <script>
        CKEDITOR.replace('editor');
    </script>
</body>
</html>