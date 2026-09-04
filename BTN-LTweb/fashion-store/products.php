<?php
require_once 'includes/database.php';

/* lấy ds sản phẩm*/

$sql = "SELECT 
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
        WHERE p.status = 1
        ORDER BY p.created_at DESC, p.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sản phẩm - Mây</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="assets/css/style.css">

    <style>

        .product-page {
            background-color: #fff;
            min-height: 70vh;
        }

        .product-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            background-color: #fff;
            transition: all 0.25s ease;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.10);
        }

        .product-image-wrapper {
            position: relative;
            width: 100%;
            height: 320px;
            overflow: hidden;
            background-color: #f8f8f8;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.04);
        }

        .product-info {
            padding: 18px;
        }

        .product-category {
            color: #888;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .product-name {
            color: #222;
            font-size: 17px;
            font-weight: 600;
            min-height: 48px;
            margin-bottom: 10px;
        }

        .product-price {
            color: #d63384;
            font-size: 18px;
            font-weight: 700;
        }

        .product-stock {
            font-size: 13px;
            color: #777;
        }

        .btn-detail {
            border-radius: 8px;
            width: 100%;
        }

        .empty-product {
            padding: 70px 20px;
            text-align: center;
            color: #888;
        }

    </style>

</head>

<body>

<?php include 'includes/header.php'; ?>

<?php include 'includes/menu.php'; ?>

<main class="product-page">

    <div class="container py-5">

        <div class="text-center mb-5">

            <h1 class="fw-bold">
                Sản phẩm
            </h1>

            <p class="text-muted mb-0">
                Khám phá các sản phẩm thời trang của Mây
            </p>

        </div>

        <div class="row g-4">

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($product = $result->fetch_assoc()): ?>

                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">

                        <div class="product-card shadow-sm">

                            <div class="product-image-wrapper">

                                <?php if (!empty($product['main_image'])): ?>

                                    <img
                                        src="uploads/<?= htmlspecialchars($product['main_image']) ?>"
                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                        class="product-image">

                                <?php else: ?>

                                    <div
                                        class="w-100 h-100 d-flex justify-content-center align-items-center text-muted">

                                        <div class="text-center">

                                            <i
                                                class="bi bi-image"
                                                style="font-size: 40px;">
                                            </i>

                                            <div>
                                                Không có ảnh
                                            </div>

                                        </div>

                                    </div>

                                <?php endif; ?>

                            </div>

                            <div class="product-info">

                                <div class="product-category">

                                    <?= htmlspecialchars(
                                        $product['category_name']
                                        ?? 'Chưa phân loại'
                                    ) ?>

                                </div>

                                <div class="product-name">

                                    <?= htmlspecialchars(
                                        $product['name']
                                    ) ?>

                                </div>

                                <div class="product-price mb-2">

                                    <?= number_format(
                                        $product['price'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                    đ

                                </div>

                                <div class="product-stock mb-3">

                                    <?php if ((int)$product['quantity'] > 0): ?>

                                        <span class="text-success">

                                            <i class="bi bi-check-circle"></i>

                                            Còn hàng

                                        </span>

                                    <?php else: ?>

                                        <span class="text-danger">

                                            <i class="bi bi-x-circle"></i>

                                            Hết hàng

                                        </span>

                                    <?php endif; ?>

                                </div>

                                <a
                                    href="detail.php?id=<?= (int)$product['id'] ?>"
                                    class="btn btn-dark btn-detail">

                                    <i class="bi bi-eye"></i>

                                    Xem chi tiết

                                </a>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="col-12">

                    <div class="empty-product">

                        <i
                            class="bi bi-box-seam"
                            style="font-size: 50px;">
                        </i>

                        <h5 class="mt-3">
                            Chưa có sản phẩm
                        </h5>

                        <p>
                            Hiện tại cửa hàng chưa có sản phẩm nào.
                        </p>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>

</main>

<?php include 'includes/footer.php'; ?>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>
