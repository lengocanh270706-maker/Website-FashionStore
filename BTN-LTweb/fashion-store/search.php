<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/filter.php';

/* LẤY DANH MỤC */
$category_result = $conn->query("
    SELECT id, name
    FROM categories
    WHERE status = 1
    ORDER BY name ASC
");

/* LẤY SIZE*/
$size_result = $conn->query("
    SELECT DISTINCT size
    FROM product_variants
    WHERE size IS NOT NULL
    AND size != ''
    ORDER BY size ASC
");

/* LẤY Màu*/
$color_result = $conn->query("
    SELECT DISTINCT color 
    FROM product_variants
    WHERE color IS NOT NULL
    AND color != ''
    ORDER BY color ASC
");


/* ĐẾM SẢN PHẨM*/
$count_sql = "
    SELECT COUNT(*) AS total
    FROM products p
    $where
";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_products = (int)$count_result->fetch_assoc()['total'];
$count_stmt->close();

/* PHÂN TRANG*/
require_once __DIR__ . '/../includes/pagination.php';
/* LẤY SẢN PHẨM*/
$product_sql = "
    SELECT
        p.id,
        p.name,
        p.price,
        p.quantity,
        p.main_image,
        p.description,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c
        ON p.category_id = c.id
    $where
    ORDER BY p.created_at DESC, p.id DESC
    LIMIT ? OFFSET ?
";
$product_stmt = $conn->prepare($product_sql);

/* THÊM LIMIT + OFFSET*/
$product_params = $params;
$product_types = $types . "ii";
$product_params[] = $limit;
$product_params[] = $offset;
$product_stmt->bind_param(
    $product_types,
    ...$product_params
);
$product_stmt->execute();
$products = $product_stmt->get_result();

/* URL PHÂN TRANG*/
function buildPageUrl($page)
{
    $query = $_GET;
    $query['page'] = $page;
    return '?' . http_build_query($query);
}


/* HEADER*/
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>
<main>
    <section class="container py-5">
        <!-- TIÊU ĐỀ -->
        <div class="text-center mb-5">
            <h1 class="fw-bold">
                <i class="bi bi-search"></i>Tìm kiếm sản phẩm
            </h1>
            <p class="text-muted mb-0">Tìm kiếm và lọc sản phẩm theo nhu cầu của bạn</p>
        </div>

        <!-- FORM TÌM KIẾM + LỌC -->
        <div class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-body p-4">
                <form method="GET" action="search.php">
                    <div class="row g-3">
                        <!-- TÊN SẢN PHẨM -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tên sản phẩm</label>
                            <input
                                type="text"
                                name="q"
                                class="form-control"
                                placeholder="Nhập tên sản phẩm..."
                                value="<?= htmlspecialchars($search) ?>"
                            >
                        </div>

                        <!-- DANH MỤC -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Danh mục</label>
                            <select name="category" class="form-select">
                                <option value="0">Tất cả danh mục</option>
                                <?php while ($category = $category_result->fetch_assoc()): ?>
                                    <option
                                        value="<?= (int)$category['id'] ?>"
                                        <?= ($category_id == $category['id']) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- SIZE -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Size</label>
                            <select name="size" class="form-select">
                                <option value="">Tất cả size</option>
                                <?php while ($size_row = $size_result->fetch_assoc()): ?>
                                    <option
                                        value="<?= htmlspecialchars($size_row['size']) ?>"
                                        <?= ($size == $size_row['size']) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($size_row['size']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- GIÁ TỪ -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Giá từ</label>
                            <input
                                type="number"
                                name="min_price"
                                class="form-control"
                                placeholder="0"
                                min="0"
                                value="<?= $min_price > 0 ? $min_price : '' ?>"
                            >
                        </div>

                        <!-- GIÁ ĐẾN -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Giá đến</label>
                            <input
                                type="number"
                                name="max_price"
                                class="form-control"
                                placeholder="5.000.000"
                                min="0"
                                value="<?= $max_price > 0 ? $max_price : '' ?>"
                            >
                        </div>

                        <!-- MÀU -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Màu sắc</label>
                            <select name="color" class="form-select">
                                <option value="">Tất cả màu</option>
                                <?php while ($color_row = $color_result->fetch_assoc()): ?>
                                    <option
                                        value="<?= htmlspecialchars($color_row['color']) ?>"
                                        <?= ($color == $color_row['color']) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($color_row['color']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- BUTTON -->
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-dark flex-grow-1">
                                <i class="bi bi-search"></i>Tìm kiếm
                            </button>

                            <a href="search.php" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <!-- KẾT QUẢ -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="text-muted">Kết quả:</span>
                <strong><?= $total_products ?> sản phẩm</strong>
            </div>
        </div>

        <!-- DANH SÁCH SẢN PHẨM -->
        <div class="row g-4">
            <?php if ($products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card product-card border-0 shadow-sm h-100">
                            <!-- ẢNH -->
                            <?php if (!empty($product['main_image'])): ?>
                                <div class="product-image-wrapper" style="height:280px;">
                                    <img
                                        src="../uploads/products/<?= htmlspecialchars($product['main_image']) ?>"
                                        class="w-100 h-100 object-fit-cover"
                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                    >
                                </div>
                            <?php else: ?>

                                <div class="bg-light d-flex align-items-center justify-content-center" style="height:280px;">
                                    <i class="bi bi-image fs-1 text-muted"></i>
                                </div>
                            <?php endif; ?>

                            <!-- THÔNG TIN -->
                            <div class="card-body d-flex flex-column">
                                <div class="text-muted small mb-1">
                                    <?= htmlspecialchars(
                                        $product['category_name'] ?? 'Chưa phân loại'
                                    ) ?>
                                </div>
                                <h5 class="product-name fw-semibold mb-2"><?= htmlspecialchars($product['name']) ?></h5>
                                <div class="fw-bold fs-5 mb-2">
                                    <?= number_format($product['price'],0,',','.') ?> ₫
                                </div>
                                <?php if ((int)$product['quantity'] > 0): ?>
                                    <div class="text-success small mb-3">
                                        <i class="bi bi-check-circle"></i>Còn hàng
                                    </div>
                                <?php else: ?>
                                    <div class="text-danger small mb-3">
                                        <i class="bi bi-x-circle"></i>Hết hàng
                                    </div>
                                <?php endif; ?>
                                <a href="detail.php?id=<?= (int)$product['id'] ?>" class="btn btn-dark w-100 mt-auto">
                                    <i class="bi bi-eye"></i>Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>

                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-search fs-1 text-muted"></i>
                        <h5 class="mt-3">Không tìm thấy sản phẩm</h5>
                        <p class="text-muted">Vui lòng thử lại với điều kiện tìm kiếm khác.</p>
                        <a href="search.php" class="btn btn-dark">Xem tất cả sản phẩm </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- PHÂN TRANG -->
        <?php if ($total_pages > 1): ?>
            <nav class="mt-5" aria-label="Phân trang sản phẩm">
                <ul class="pagination justify-content-center">
                    <!-- PREVIOUS -->
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <?php if ($page > 1): ?>
                            <a class="page-link" href="<?= htmlspecialchars(buildPageUrl($page - 1)) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        <?php else: ?>
                            <span class="page-link"> <i class="bi bi-chevron-left"></i></span>
                        <?php endif; ?>
                    </li>


                    <!-- NUMBER -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars(buildPageUrl($i)) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- NEXT -->
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <?php if ($page < $total_pages): ?>
                            <a class="page-link" href="<?= htmlspecialchars(buildPageUrl($page + 1)) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>