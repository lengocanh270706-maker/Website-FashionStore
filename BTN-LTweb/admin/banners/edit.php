<?php
require_once '../../includes/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM banners WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$banner = $stmt->get_result()->fetch_assoc();

if (!$banner) {
    header('Location: index.php');
    exit;
}

$products = $conn->query("SELECT id, name FROM products WHERE status = 1 ORDER BY name");

$link = $banner['link'] ?? '';
$link_type = '';
$product_id = '';

if (strpos($link, 'product-detail.php?id=') !== false) {
    $link_type = 'product';
    $product_id = (int)substr($link, strpos($link, 'id=') + 3);
} elseif (strpos($link, 'products.php') !== false) {
    $link_type = 'products';
} elseif (strpos($link, 'index.php') !== false) {
    $link_type = 'home';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $link_type = $_POST['link_type'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);
    $status = isset($_POST['status']) ? 1 : 0;
    $image = $banner['image'];

    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../../uploads/banners/' . $image);

        if (!empty($banner['image'])) {
            $old = '../../uploads/banners/' . $banner['image'];
            if (file_exists($old)) unlink($old);
        }
    }

    if ($link_type === 'home') {
        $link = '/Website-FashionStore/BTN-LTweb/fashion-store/index.php';
    } elseif ($link_type === 'products') {
        $link = '/Website-FashionStore/BTN-LTweb/fashion-store/products.php';
    } elseif ($link_type === 'product' && $product_id > 0) {
        $link = '/Website-FashionStore/BTN-LTweb/fashion-store/product-detail.php?id=' . $product_id;
    } else {
        $link = '';
    }

    $stmt = $conn->prepare("
        UPDATE banners
        SET title = ?, image = ?, link = ?, status = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("sssii", $title, $image, $link, $status, $id);
    $stmt->execute();

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sửa Banner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Sửa banner</h3>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Tiêu đề</label>
            <input type="text" name="title" class="form-control"
                   value="<?= htmlspecialchars($banner['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Hình ảnh</label><br>
            <img src="../../uploads/banners/<?= htmlspecialchars($banner['image']) ?>"
                 width="300" class="mb-2 rounded">
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <div class="mb-3">
            <label class="form-label">Liên kết</label>
            <select name="link_type" id="link_type" class="form-select">
                <option value="">Không liên kết</option>
                <option value="home" <?= $link_type === 'home' ? 'selected' : '' ?>>Trang chủ</option>
                <option value="products" <?= $link_type === 'products' ? 'selected' : '' ?>>Trang sản phẩm</option>
                <option value="product" <?= $link_type === 'product' ? 'selected' : '' ?>>Chi tiết sản phẩm</option>
            </select>
        </div>

        <div class="mb-3" id="product_box" style="<?= $link_type === 'product' ? '' : 'display:none' ?>">
            <label class="form-label">Chọn sản phẩm</label>
            <select name="product_id" id="product_id" class="form-select">
                <option value="">-- Chọn sản phẩm --</option>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <option value="<?= $product['id'] ?>"
                        <?= $product_id == $product['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($product['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="status" class="form-check-input" id="status"
                   <?= $banner['status'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="status">Hiển thị</label>
        </div>

        <button class="btn btn-dark">
            <i class="bi bi-save"></i> Lưu thay đổi
        </button>
    </form>
</div>

<script>
const type = document.getElementById('link_type');
const box = document.getElementById('product_box');
const product = document.getElementById('product_id');

type.addEventListener('change', function() {
    const show = this.value === 'product';
    box.style.display = show ? 'block' : 'none';
    product.required = show;
    if (!show) product.value = '';
});
</script>
<script src="../assets/js/script.js?v=1"></script>
</body>
</html>