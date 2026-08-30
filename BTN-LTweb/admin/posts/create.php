<?php
require_once '../../includes/database.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = $_POST['content']; 
    $status = $_POST['status'];
    $author_id = 1; 

    if (!empty($title)) {
        $image = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = time() . '_' . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], '../../uploads/' . $image);
        }

        $stmt = $conn->prepare("INSERT INTO posts (title, content, image, author_id, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $title, $content, $image, $author_id, $status);
        
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Lỗi cơ sở dữ liệu: " . $conn->error;
        }
    } else {
        $error = "Vui lòng nhập tiêu đề bài viết!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Bài viết - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Dùng CDN CKEditor chuẩn để hiển thị đầy đủ thanh công cụ -->
    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 950px;">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h4 class="fw-bold mb-4">Thêm bài viết mới</h4>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề bài viết *</label>
                    <input type="text" name="title" class="form-control" placeholder="Nhập tiêu đề..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Ảnh bìa bài viết</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nội dung chi tiết</label>
                    <textarea name="content" id="editor" rows="10" class="form-control"></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select w-50">
                        <option value="1">Đã đăng (Published)</option>
                        <option value="0">Bản nháp (Draft)</option>
                    </select>
                </div>
                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
                    <button type="submit" class="btn btn-dark px-4">Lưu bài viết</button>
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