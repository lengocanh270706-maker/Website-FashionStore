<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: products.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT p.id,p.category_id,p.name,p.price,p.description,p.quantity,p.main_image,p.status,p.created_at,c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.status = 1
    LIMIT 1
");
$stmt->bind_param("i",$id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: products.php');
    exit;
}

$imageStmt = $conn->prepare("
    SELECT id,image
    FROM product_images
    WHERE product_id = ?
    ORDER BY id ASC
");
$imageStmt->bind_param("i",$id);
$imageStmt->execute();
$imageResult = $imageStmt->get_result();
$subImages = [];

while ($image = $imageResult->fetch_assoc()) {
    $subImages[] = $image;
}

$imageStmt->close();

$variantStmt = $conn->prepare("
    SELECT id,size,color,quantity
    FROM product_variants
    WHERE product_id = ?
    ORDER BY id ASC
");
$variantStmt->bind_param("i",$id);
$variantStmt->execute();
$variantResult = $variantStmt->get_result();
$variants = [];

while ($variant = $variantResult->fetch_assoc()) {
    $variants[] = $variant;
}

$variantStmt->close();

$sizes = [];
$colors = [];

foreach ($variants as $variant) {
    if (!empty($variant['size']) && !in_array($variant['size'],$sizes)) {
        $sizes[] = $variant['size'];
    }

    if (!empty($variant['color']) && !in_array($variant['color'],$colors)) {
        $colors[] = $variant['color'];
    }
}

include '../includes/header.php';
include '../includes/menu.php';
?>

<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="products.php" class="text-decoration-none text-dark small">
            <i class="bi bi-arrow-left me-2"></i>Quay lại sản phẩm
        </a>
    </div>


<div class="row g-5">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <?php if (!empty($product['main_image'])): ?>
                <img id="mainProductImage"
                     src="../uploads/products/<?= htmlspecialchars($product['main_image']) ?>"
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     class="w-100"
                     style="height:520px;object-fit:contain;">
            <?php else: ?>
                <div id="mainProductImage"
                     class="bg-light d-flex align-items-center justify-content-center"
                     style="height:520px;">
                    <div class="text-center text-muted">
                        <i class="bi bi-image display-3"></i>
                        <p class="mt-2 mb-0">Không có ảnh</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($product['main_image']) || count($subImages) > 0): ?>
            <div class="d-flex gap-2 mt-3 flex-wrap">
                <?php if (!empty($product['main_image'])): ?>
                    <img src="../uploads/products/<?= htmlspecialchars($product['main_image']) ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         class="product-thumbnail border border-dark rounded-3 p-1"
                         style="width:75px;height:90px;object-fit:cover;cursor:pointer;"
                         onclick="changeMainImage(this,'../uploads/products/<?= htmlspecialchars($product['main_image']) ?>')">
                <?php endif; ?>

                <?php foreach ($subImages as $image): ?>
                    <img src="../uploads/products/<?= htmlspecialchars($image['image']) ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         class="product-thumbnail border rounded-3 p-1"
                         style="width:75px;height:90px;object-fit:cover;cursor:pointer;"
                         onclick="changeMainImage(this,'../uploads/<?= htmlspecialchars($image['image']) ?>')">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-6">
        <div class="mb-3">
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                <i class="bi bi-tag me-1"></i><?= htmlspecialchars($product['category_name'] ?? 'Chưa phân loại') ?>
            </span>
        </div>

        <h2 class="fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h2>

        <div class="fs-3 fw-bold mb-3">
            <?= number_format($product['price'],0,',','.') ?>đ
        </div>

        <hr>

        <?php if (!empty($variants)): ?>

            <?php if (!empty($colors)): ?>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Màu sắc</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php foreach ($colors as $color): ?>
                            <button type="button"
                                    class="btn btn-outline-dark color-option"
                                    data-color="<?= htmlspecialchars($color) ?>">
                                <?= htmlspecialchars($color) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($sizes)): ?>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Size</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php foreach ($sizes as $size): ?>
                            <button type="button"
                                    class="btn btn-outline-dark size-option"
                                    data-size="<?= htmlspecialchars($size) ?>">
                                <?= htmlspecialchars($size) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div id="variantMessage" class="small text-danger mb-3"></div>

        <?php endif; ?>

        <div class="mb-4">
            <?php if (!empty($variants)): ?>
                <span id="stockText" class="text-muted">
                    Vui lòng chọn màu và size
                </span>
            <?php elseif ((int)$product['quantity'] > 0): ?>
                <span class="text-success fw-semibold">
                    <i class="bi bi-check-circle-fill me-1"></i>Còn hàng
                </span>
                <span class="text-muted ms-2">
                    (<?= (int)$product['quantity'] ?> sản phẩm)
                </span>
            <?php else: ?>
                <span class="text-danger fw-semibold">
                    <i class="bi bi-x-circle-fill me-1"></i>Hết hàng
                </span>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <h5 class="fw-bold mb-3">Mô tả sản phẩm</h5>
            <div class="text-muted" style="line-height:1.8;">
                <?php if (!empty($product['description'])): ?>
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                <?php else: ?>
                    <span>Chưa có mô tả cho sản phẩm này.</span>
                <?php endif; ?>
            </div>
        </div>

        <hr>

        <?php if ((int)$product['quantity'] > 0 || !empty($variants)): ?>
            <form action="cart.php" method="POST" class="mt-4" id="cartForm">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                <input type="hidden" name="variant_id" id="variant_id" value="0">
                <input type="hidden" name="add_to_cart" value="1">

                <div class="mb-3">
                    <label for="quantity" class="form-label fw-semibold">
                        Số lượng
                    </label>

                    <input type="number"
                           id="quantity"
                           name="quantity"
                           class="form-control"
                           style="width:100px;"
                           value="1"
                           min="1"
                           max="<?= !empty($variants) ? 1 : (int)$product['quantity'] ?>"
                           required>
                </div>

                <button type="submit"
                        id="addCartBtn"
                        class="btn btn-dark rounded-pill px-4 py-2"
                        <?= !empty($variants) ? 'disabled' : '' ?>>
                    <i class="bi bi-bag-plus me-2"></i>Thêm vào giỏ hàng
                </button>
            </form>
        <?php else: ?>
            <button type="button"
                    class="btn btn-secondary rounded-pill px-4 py-2"
                    disabled>
                <i class="bi bi-bag-x me-2"></i>Sản phẩm hết hàng
            </button>
        <?php endif; ?>
    </div>
</div>


</div>

<script>
window.productVariants = <?= json_encode($variants, JSON_UNESCAPED_UNICODE) ?>;
window.productName = <?= json_encode($product['name'], JSON_UNESCAPED_UNICODE) ?>;
window.hasSizes = <?= !empty($sizes) ? 'true' : 'false' ?>;
window.hasColors = <?= !empty($colors) ? 'true' : 'false' ?>;
</script>


<?php include '../includes/footer.php'; ?>
