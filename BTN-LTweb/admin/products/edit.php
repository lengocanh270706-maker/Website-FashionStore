<?php
require_once '../../includes/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$categories = $conn->query("SELECT * FROM categories");

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    $main_image = $product['main_image'];
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == 0) {
        $main_image = time() . '_' . $_FILES['main_image']['name'];
        move_uploaded_file($_FILES['main_image']['tmp_name'], '../../uploads/' . $main_image);
    }

    $stmt_update = $conn->prepare("UPDATE products SET category_id = ?, name = ?, price = ?, description = ?, quantity = ?, main_image = ?, status = ? WHERE id = ?");
    $stmt_update->bind_param("isdsisii", $category_id, $name, $price, $description, $quantity, $main_image, $status, $id);
    
    if ($stmt_update->execute()) {
        if (isset($_FILES['sub_images'])) {
            foreach ($_FILES['sub_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['sub_images']['error'][$key] == 0) {
                    $sub_name = time() . '_' . $_FILES['sub_images']['name'][$key];
                    move_uploaded_file($tmp_name, '../../uploads/' . $sub_name);
                    $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, image) VALUES (?, ?)");
                    $stmt_img->bind_param("is", $id, $sub_name);
                    $stmt_img->execute();
                }
            }
        }
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Sản phẩm - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 800px;">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h4 class="fw-bold mb-4">Chỉnh sửa sản phẩm: #SP<?= $product['id'] ?></h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên sản phẩm *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Danh mục *</label>
                        <select name="category_id" class="form-select">
                            <?php while($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Giá bán (VNĐ) *</label>
                        <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Tồn kho *</label>
                        <input type="number" name="quantity" class="form-control" value="<?= $product['quantity'] ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Ảnh chính hiện tại</label>
                        <div class="mb-2">
                            <img src="../../uploads/<?= htmlspecialchars($product['main_image']) ?>" style="width: 70px; height: 70px; object-fit: cover; border-radius: 6px;" alt="">
                        </div>
                        <input type="file" name="main_image" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Thêm ảnh phụ mới</label>
                        <input type="file" name="sub_images[]" class="form-control" accept="image/*" multiple>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select w-50">
                        <option value="1" <?= $product['status'] == 1 ? 'selected' : '' ?>>Còn hàng</option>
                        <option value="0" <?= $product['status'] == 0 ? 'selected' : '' ?>>Hết hàng</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Mô tả sản phẩm</label>
                    <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
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