<?php
require_once '../includes/database.php';

$categories = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY id ASC");
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

if ($category_id > 0) {
    $stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 1 AND p.category_id = ? ORDER BY p.id DESC");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 1 ORDER BY p.id DESC");
}

require_once '../includes/header.php';
require_once '../includes/menu.php';
?>

<main class="collection-page">
    <div class="container">
        <div class="collection-heading">
            <h1>Bộ sưu tập</h1>
            <p>Khám phá những thiết kế thời trang mới nhất từ Mây Store</p>
        </div>

        <div class="collection-categories">
            <a href="collections.php" class="collection-category <?= $category_id == 0 ? 'active' : '' ?>">Tất cả</a>
            <?php if ($categories && $categories->num_rows > 0): ?>
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <a href="collections.php?category=<?= $category['id'] ?>" class="collection-category <?= $category_id == $category['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php if ($products && $products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="col-lg-3 col-md-4 col-6">
                        <a href="product-detail.php?id=<?= $product['id'] ?>" class="collection-detail">
                            <div class="collection-product">
                                <div class="collection-image-wrapper">
                                    <?php if (!empty($product['main_image'])): ?>
                                        <img src="../uploads/products/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="collection-image">
                                    <?php else: ?>
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                                            <span class="text-muted">Không có hình ảnh</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="collection-info">
                                    <?php if (!empty($product['category_name'])): ?>
                                        <div class="collection-category-name"><?= htmlspecialchars($product['category_name']) ?></div>
                                    <?php endif; ?>

                                    <h2 class="collection-name"><?= htmlspecialchars($product['name']) ?></h2>
                                    <div class="collection-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="collection-empty">
                        <i class="bi bi-bag-x d-block"></i>
                        <h5>Chưa có sản phẩm</h5>
                        <p>Hiện tại chưa có sản phẩm trong bộ sưu tập này.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>