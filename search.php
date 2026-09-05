<?php
require_once 'includes/database.php';
require_once 'includes/filter.php';


// =========================
// LẤY DANH MỤC
// =========================

$category_result = $conn->query("
    SELECT id, name
    FROM categories
    WHERE status = 1
    ORDER BY name ASC
");


// =========================
// LẤY SIZE
// =========================

$size_result = $conn->query("
    SELECT DISTINCT size
    FROM product_variants
    WHERE size IS NOT NULL AND size != ''
    ORDER BY size ASC
");


// =========================
// LẤY MÀU
// =========================

$color_result = $conn->query("
    SELECT DISTINCT color
    FROM product_variants
    WHERE color IS NOT NULL AND color != ''
    ORDER BY color ASC
");


// =========================
// ĐẾM TỔNG SẢN PHẨM
// =========================

$count_sql = "
    SELECT COUNT(DISTINCT p.id) AS total
    FROM products p
    LEFT JOIN product_variants pv ON p.id = pv.product_id
    $where
";

$count_stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();

$count_result = $count_stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];


// =========================
// PHÂN TRANG
// =========================

require_once 'includes/pagination.php';


// =========================
// LẤY DANH SÁCH SẢN PHẨM
// =========================

$product_sql = "
    SELECT 
        p.id,
        p.name,
        p.price,
        p.main_image,
        p.description,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c 
        ON p.category_id = c.id
    LEFT JOIN product_variants pv 
        ON p.id = pv.product_id
    $where
    GROUP BY p.id
    ORDER BY p.id DESC
    LIMIT ? OFFSET ?
";

$product_stmt = $conn->prepare($product_sql);


// Thêm LIMIT và OFFSET
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


// =========================
// TẠO URL PHÂN TRANG
// =========================

function buildPageUrl($page)
{
    $params = $_GET;
    $params['page'] = $page;

    return '?' . http_build_query($params);
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Tìm kiếm sản phẩm - Mây Store</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
        rel="stylesheet">


    <style>

        body {
            background-color: #f8f9fc;
            font-family: 'Segoe UI', sans-serif;
        }

        .topbar {
            background-color: white;
            padding: 15px 30px;
            border-bottom: 1px solid #eee;
        }

        .logo {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #d63384;
        }

        .search-box {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .product-card {
            background-color: white;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            height: 100%;
            transition: 0.2s;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .product-image {
            width: 100%;
            height: 230px;
            object-fit: cover;
        }

        .product-name {
            font-weight: 600;
            min-height: 48px;
        }

        .product-price {
            color: #d63384;
            font-size: 18px;
            font-weight: 700;
        }

        .btn-pink {
            background-color: #d63384;
            color: white;
            border: none;
        }

        .btn-pink:hover {
            background-color: #b02a6f;
            color: white;
        }

        .pagination .page-link {
            color: #d63384;
        }

        .pagination .active .page-link {
            background-color: #d63384;
            border-color: #d63384;
            color: white;
        }

    </style>

</head>


<body>


<!-- =========================
     HEADER
========================= -->

<div class="topbar">

    <div class="container">

        <div class="d-flex justify-content-between align-items-center">

            <a href="index.php"
               class="text-decoration-none text-dark">

                <div class="d-flex align-items-center gap-2">

                    <img
                        src="uploads/logomay.jpg"
                        class="logo"
                        alt="Mây Store">

                    <span class="fw-bold fs-5">
                        Mây Store
                    </span>

                </div>

            </a>


            <div>

                <a href="index.php"
                   class="text-decoration-none text-secondary me-3">

                    <i class="bi bi-house"></i>
                    Trang chủ

                </a>

                <a href="search.php"
                   class="text-decoration-none"
                   style="color:#d63384;">

                    <i class="bi bi-search"></i>
                    Tìm kiếm

                </a>

            </div>

        </div>

    </div>

</div>


<!-- =========================
     MAIN
========================= -->

<div class="container py-4">


    <h3 class="fw-bold mb-4">

        <i class="bi bi-search"></i>

        Tìm kiếm sản phẩm

    </h3>


    <!-- =========================
         FORM FILTER
    ========================= -->

    <div class="search-box mb-4">

        <form method="GET"
              action="search.php">

            <div class="row g-3">


                <!-- Tìm kiếm -->

                <div class="col-md-4">

                    <label class="form-label fw-semibold">

                        Tên sản phẩm

                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Nhập tên sản phẩm..."
                        value="<?= htmlspecialchars($search) ?>">

                </div>


                <!-- Danh mục -->

                <div class="col-md-4">

                    <label class="form-label fw-semibold">

                        Danh mục

                    </label>

                    <select name="category"
                            class="form-select">

                        <option value="0">
                            Tất cả danh mục
                        </option>

                        <?php while ($category = $category_result->fetch_assoc()): ?>

                            <option
                                value="<?= $category['id'] ?>"
                                <?= ($category_id == $category['id']) ? 'selected' : '' ?>>

                                <?= htmlspecialchars($category['name']) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <!-- Size -->

                <div class="col-md-4">

                    <label class="form-label fw-semibold">

                        Size

                    </label>

                    <select name="size"
                            class="form-select">

                        <option value="">
                            Tất cả size
                        </option>

                        <?php while ($size_row = $size_result->fetch_assoc()): ?>

                            <option
                                value="<?= htmlspecialchars($size_row['size']) ?>"
                                <?= ($size == $size_row['size']) ? 'selected' : '' ?>>

                                <?= htmlspecialchars($size_row['size']) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <!-- Giá thấp nhất -->

                <div class="col-md-3">

                    <label class="form-label fw-semibold">

                        Giá từ

                    </label>

                    <input
                        type="number"
                        name="min_price"
                        class="form-control"
                        placeholder="0"
                        value="<?= $min_price > 0 ? $min_price : '' ?>">

                </div>


                <!-- Giá cao nhất -->

                <div class="col-md-3">

                    <label class="form-label fw-semibold">

                        Giá đến

                    </label>

                    <input
                        type="number"
                        name="max_price"
                        class="form-control"
                        placeholder="5000000"
                        value="<?= $max_price > 0 ? $max_price : '' ?>">

                </div>


                <!-- Màu -->

                <div class="col-md-3">

                    <label class="form-label fw-semibold">

                        Màu sắc

                    </label>

                    <select name="color"
                            class="form-select">

                        <option value="">
                            Tất cả màu
                        </option>

                        <?php while ($color_row = $color_result->fetch_assoc()): ?>

                            <option
                                value="<?= htmlspecialchars($color_row['color']) ?>"
                                <?= ($color == $color_row['color']) ? 'selected' : '' ?>>

                                <?= htmlspecialchars($color_row['color']) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <!-- Buttons -->

                <div class="col-md-3 d-flex align-items-end gap-2">

                    <button
                        type="submit"
                        class="btn btn-pink flex-grow-1">

                        <i class="bi bi-search"></i>
                        Tìm kiếm

                    </button>

                    <a
                        href="search.php"
                        class="btn btn-outline-secondary">

                        <i class="bi bi-arrow-counterclockwise"></i>

                    </a>

                </div>

            </div>

        </form>

    </div>


    <!-- =========================
         KẾT QUẢ
    ========================= -->

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <strong>
                Kết quả tìm kiếm:
            </strong>

            <?= $total_products ?> sản phẩm

        </div>

    </div>


    <!-- =========================
         PRODUCT LIST
    ========================= -->

    <div class="row g-4">

        <?php if ($products->num_rows > 0): ?>

            <?php while ($product = $products->fetch_assoc()): ?>

                <div class="col-12 col-sm-6 col-md-4 col-lg-3">

                    <div class="product-card">

                        <?php if (!empty($product['main_image'])): ?>

                            <img
                                src="uploads/products/<?= htmlspecialchars($product['main_image']) ?>"
                                class="product-image"
                                alt="<?= htmlspecialchars($product['name']) ?>">

                        <?php else: ?>

                            <div
                                class="product-image d-flex align-items-center justify-content-center bg-light">

                                <i class="bi bi-image fs-1 text-muted"></i>

                            </div>

                        <?php endif; ?>


                        <div class="p-3">

                            <div class="text-muted small mb-1">

                                <?= htmlspecialchars(
                                    $product['category_name'] ?? 'Chưa phân loại'
                                ) ?>

                            </div>


                            <div class="product-name mb-2">

                                <?= htmlspecialchars($product['name']) ?>

                            </div>


                            <div class="product-price mb-3">

                                <?= number_format(
                                    $product['price'],
                                    0,
                                    ',',
                                    '.'
                                ) ?> ₫

                            </div>


                            <a
                                href="detail.php?id=<?= $product['id'] ?>"
                                class="btn btn-dark w-100">

                                Xem chi tiết

                            </a>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="text-center bg-white rounded-4 p-5">

                    <i class="bi bi-search fs-1 text-muted"></i>

                    <h5 class="mt-3">
                        Không tìm thấy sản phẩm
                    </h5>

                    <p class="text-muted">

                        Vui lòng thử lại với điều kiện tìm kiếm khác.

                    </p>

                </div>

            </div>

        <?php endif; ?>

    </div>


    <!-- =========================
         PAGINATION
    ========================= -->

    <?php if ($total_pages > 1): ?>

        <nav class="mt-5">

            <ul class="pagination justify-content-center">


                <!-- Previous -->

                <li
                    class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">

                    <a
                        class="page-link"
                        href="<?= buildPageUrl($page - 1) ?>">

                        <i class="bi bi-chevron-left"></i>

                    </a>

                </li>


                <!-- Number -->

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                    <li
                        class="page-item <?= ($i == $page) ? 'active' : '' ?>">

                        <a
                            class="page-link"
                            href="<?= buildPageUrl($i) ?>">

                            <?= $i ?>

                        </a>

                    </li>

                <?php endfor; ?>


                <!-- Next -->

                <li
                    class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">

                    <a
                        class="page-link"
                        href="<?= buildPageUrl($page + 1) ?>">

                        <i class="bi bi-chevron-right"></i>

                    </a>

                </li>

            </ul>

        </nav>

    <?php endif; ?>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>