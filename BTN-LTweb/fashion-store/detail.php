<?php

require_once 'includes/database.php';

$id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

if ($id <= 0) {

    header('Location: products.php');
    exit;
}
$stmt = $conn->prepare(
    "SELECT
        p.id,
        p.category_id,
        p.name,
        p.price,
        p.description,
        p.quantity,
        p.main_image,
        p.status,
        p.created_at,
        c.name AS category_name

     FROM products p

     LEFT JOIN categories c
        ON p.category_id = c.id

     WHERE p.id = ?
       AND p.status = 1

     LIMIT 1"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

$product = $result->fetch_assoc();


if (!$product) {

    header('Location: products.php');

    exit;

}

$imageStmt = $conn->prepare(
    "SELECT
        id,
        image

     FROM product_images

     WHERE product_id = ?

     ORDER BY id ASC"
);

$imageStmt->bind_param("i", $id);

$imageStmt->execute();

$imageResult = $imageStmt->get_result();

$subImages = [];

while ($image = $imageResult->fetch_assoc()) {

    $subImages[] = $image;

}

?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">


    <title>
        <?= htmlspecialchars($product['name']) ?> - Mây
    </title>

    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
        rel="stylesheet">

    <!-- CSS website -->

    <link
        rel="stylesheet"
        href="assets/css/style.css">

    <style>

        .detail-page {

            background-color: #fff;

            min-height: 70vh;
        }

        .main-product-image {

            width: 100%;

            height: 550px;

            object-fit: cover;

            border-radius: 16px;

            background-color: #f8f8f8;
        }

        .thumbnail-list {

            display: flex;

            gap: 12px;

            margin-top: 15px;

            overflow-x: auto;

            padding-bottom: 5px;
        }

        .thumbnail {

            width: 85px;

            height: 85px;

            object-fit: cover;

            border-radius: 10px;

            border: 2px solid transparent;

            cursor: pointer;

            flex-shrink: 0;

            transition: 0.2s;
        }

        .thumbnail:hover {

            border-color: #d63384;
        }

        .thumbnail.active {

            border-color: #d63384;
        }


        .detail-category {

            color: #888;

            font-size: 14px;

            margin-bottom: 10px;
        }

        .detail-title {

            font-size: 32px;

            font-weight: 700;

            color: #222;

            margin-bottom: 20px;
        }

        .detail-price {

            font-size: 28px;

            font-weight: 700;

            color: #d63384;
        }

        .detail-description {

            color: #555;

            line-height: 1.8;

            white-space: pre-line;
        }

        .quantity-input {

            width: 130px;
        }

        .detail-stock {

            font-size: 14px;
        }

        .back-products {

            text-decoration: none;

            color: #555;
        }

        .back-products:hover {

            color: #d63384;
        }

    </style>

</head>

<body>

<?php include 'includes/header.php'; ?>

<?php include 'includes/menu.php'; ?>


<main class="detail-page">

    <div class="container py-5">

        <div class="mb-4">

            <a
                href="products.php"
                class="back-products">

                <i class="bi bi-arrow-left"></i>

                Quay lại sản phẩm

            </a>

        </div>

        <div class="row g-5">

            <div class="col-lg-6">

                <!-- MAIN IMAGE -->

                <?php if (!empty($product['main_image'])): ?>

                    <img
                        id="mainProductImage"
                        src="uploads/<?= htmlspecialchars($product['main_image']) ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        class="main-product-image">

                <?php else: ?>

                    <div
                        id="mainProductImage"
                        class="main-product-image d-flex justify-content-center align-items-center">

                        <div class="text-center text-muted">

                            <i
                                class="bi bi-image"
                                style="font-size: 60px;">
                            </i>

                            <p class="mt-2">

                                Không có ảnh

                            </p>

                        </div>

                    </div>

                <?php endif; ?>


                    !empty($product['main_image']) ||
                    count($subImages) > 0
                ): ?>

                    <div class="thumbnail-list">

                        <!-- MAIN IMAGE THUMBNAIL -->

                        <?php if (!empty($product['main_image'])): ?>

                            <img
                                src="uploads/<?= htmlspecialchars($product['main_image']) ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>"
                                class="thumbnail active"
                                onclick="changeMainImage(
                                    this,
                                    'uploads/<?= htmlspecialchars($product['main_image']) ?>'
                                )">

                        <?php endif; ?>

                        <!-- SUB IMAGES -->

                        <?php foreach ($subImages as $image): ?>

                            <img
                                src="uploads/<?= htmlspecialchars($image['image']) ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>"
                                class="thumbnail"
                                onclick="changeMainImage(
                                    this,
                                    'uploads/<?= htmlspecialchars($image['image']) ?>'
                                )">

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

            <div class="col-lg-6">

                <!-- CATEGORY -->

                <div class="detail-category">

                    <i class="bi bi-tag"></i>

                    <?= htmlspecialchars(
                        $product['category_name']
                        ?? 'Chưa phân loại'
                    ) ?>

                </div>

                <!-- PRODUCT NAME -->

                <h1 class="detail-title">

                    <?= htmlspecialchars(
                        $product['name']
                    ) ?>

                </h1>
                <!-- PRICE -->

                <div class="detail-price mb-3">

                    <?= number_format(
                        $product['price'],
                        0,
                        ',',
                        '.'
                    ) ?>

                </div>
                <hr>

                <div class="detail-stock mb-4">

                    <?php if ((int)$product['quantity'] > 0): ?>

                        <span class="text-success">

                            <i class="bi bi-check-circle-fill"></i>

                            Còn hàng

                        </span>

                        <span class="text-muted ms-2">

                            (<?= (int)$product['quantity'] ?> sản phẩm)

                        </span>

                    <?php else: ?>

                        <span class="text-danger">

                            <i class="bi bi-x-circle-fill"></i>

                            Hết hàng

                        </span>

                    <?php endif; ?>

                </div>

                <div class="mb-4">

                    <h5 class="fw-bold mb-3">

                        Mô tả sản phẩm

                    </h5>

                    <div class="detail-description">

                        <?php if (!empty($product['description'])): ?>

                            <?= nl2br(
                                htmlspecialchars(
                                    $product['description']
                                )
                            ) ?>

                        <?php else: ?>

                            <span class="text-muted">

                                Chưa có mô tả cho sản phẩm này.

                            </span>

                        <?php endif; ?>

                    </div>

                </div>

                <hr>

                <?php if ((int)$product['quantity'] > 0): ?>

                    <form
                        action="cart.php"
                        method="POST"
                        class="mt-4">

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int)$product['id'] ?>">

                        <div class="mb-3">

                            <label
                                for="quantity"
                                class="form-label fw-semibold">

                                Số lượng

                            </label>

                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                class="form-control quantity-input"
                                value="1"
                                min="1"
                                max="<?= (int)$product['quantity'] ?>"
                                required>

                        </div>

                        <button
                            type="submit"
                            name="add_to_cart"
                            class="btn btn-dark btn-lg px-4">

                            <i class="bi bi-cart-plus"></i>

                            Thêm vào giỏ hàng

                        </button>

                    </form>

                <?php else: ?>

                    <button
                        type="button"
                        class="btn btn-secondary btn-lg"
                        disabled>

                        <i class="bi bi-cart-x"></i>

                        Sản phẩm hết hàng

                    </button>

                <?php endif; ?>

            </div>

        </div>

    </div>

</main>

<?php include 'includes/footer.php'; ?>
rap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>

<script>

function changeMainImage(element, imageUrl) {

    const mainImage =
        document.getElementById('mainProductImage');

    if (mainImage && mainImage.tagName === 'IMG') {

        mainImage.src = imageUrl;
    }

    document
        .querySelectorAll('.thumbnail')
        .forEach(function(thumbnail) {

            thumbnail.classList.remove('active');

        });

    element.classList.add('active');
}

</script>
</body>
</html>
