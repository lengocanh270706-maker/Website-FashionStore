<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

/* =========================
   LẤY HOẶC TẠO GIỎ HÀNG
========================= */
$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cart) {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())");
    $stmt->execute([$user_id]);

    $cart_id = $conn->lastInsertId();
} else {
    $cart_id = $cart['id'];
}

/* =========================
   THÊM SẢN PHẨM
   cart.php?action=add&product_id=1&variant_id=2
========================= */
if (isset($_GET['action']) && $_GET['action'] == 'add') {

    $product_id = (int)($_GET['product_id'] ?? 0);
    $variant_id = (int)($_GET['variant_id'] ?? 0);
    $quantity = max(1, (int)($_GET['quantity'] ?? 1));

    $stmt = $conn->prepare("
        SELECT p.*, pv.quantity AS variant_quantity
        FROM products p
        LEFT JOIN product_variants pv ON p.id = pv.product_id AND pv.id = ?
        WHERE p.id = ? AND p.status = 1
    ");
    $stmt->execute([$variant_id, $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {

        $available_quantity = $variant_id > 0
            ? (int)$product['variant_quantity']
            : (int)$product['quantity'];

        if ($available_quantity > 0) {

            $stmt = $conn->prepare("
                SELECT * FROM cart_items
                WHERE cart_id = ?
                AND product_id = ?
                AND variant_id = ?
            ");

            $stmt->execute([
                $cart_id,
                $product_id,
                $variant_id ?: null
            ]);

            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {

                $new_quantity = $item['quantity'] + $quantity;

                if ($new_quantity > $available_quantity) {
                    $new_quantity = $available_quantity;
                }

                $stmt = $conn->prepare("
                    UPDATE cart_items
                    SET quantity = ?, price = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $new_quantity,
                    $product['price'],
                    $item['id']
                ]);

            } else {

                $stmt = $conn->prepare("
                    INSERT INTO cart_items
                    (cart_id, product_id, variant_id, quantity, price)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $cart_id,
                    $product_id,
                    $variant_id ?: null,
                    min($quantity, $available_quantity),
                    $product['price']
                ]);
            }

            $stmt = $conn->prepare("
                UPDATE cart
                SET updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([$cart_id]);
        }
    }

    header("Location: cart.php");
    exit;
}

/* =========================
   CẬP NHẬT SỐ LƯỢNG
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {

    foreach ($_POST['quantity'] as $item_id => $quantity) {

        $item_id = (int)$item_id;
        $quantity = max(1, (int)$quantity);

        $stmt = $conn->prepare("
            SELECT ci.*, 
                   p.quantity AS product_quantity,
                   pv.quantity AS variant_quantity
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN product_variants pv ON ci.variant_id = pv.id
            WHERE ci.id = ? AND ci.cart_id = ?
        ");

        $stmt->execute([$item_id, $cart_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {

            $stock = $item['variant_id']
                ? $item['variant_quantity']
                : $item['product_quantity'];

            $quantity = min($quantity, (int)$stock);

            if ($quantity > 0) {

                $stmt = $conn->prepare("
                    UPDATE cart_items
                    SET quantity = ?
                    WHERE id = ?
                ");

                $stmt->execute([$quantity, $item_id]);

            } else {

                $stmt = $conn->prepare("
                    DELETE FROM cart_items
                    WHERE id = ?
                ");

                $stmt->execute([$item_id]);
            }
        }
    }

    header("Location: cart.php");
    exit;
}

/* =========================
   XÓA SẢN PHẨM
========================= */
if (isset($_GET['action']) && $_GET['action'] == 'delete') {

    $item_id = (int)($_GET['id'] ?? 0);

    $stmt = $conn->prepare("
        DELETE FROM cart_items
        WHERE id = ? AND cart_id = ?
    ");

    $stmt->execute([$item_id, $cart_id]);

    header("Location: cart.php");
    exit;
}

/* =========================
   LẤY DANH SÁCH GIỎ HÀNG
========================= */
$stmt = $conn->prepare("
    SELECT 
        ci.*,
        p.name,
        p.main_image,
        p.price AS product_price,
        pv.size,
        pv.color,
        pv.quantity AS stock
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    LEFT JOIN product_variants pv ON ci.variant_id = pv.id
    WHERE ci.cart_id = ?
    ORDER BY ci.id DESC
");

$stmt->execute([$cart_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;

foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Giỏ hàng</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body>

<div class="container py-5">

    <h2 class="mb-4">
        <i class="bi bi-cart3"></i>
        Giỏ hàng của tôi
    </h2>

    <?php if (empty($items)): ?>

        <div class="text-center py-5">

            <i class="bi bi-cart-x fs-1 text-secondary"></i>

            <h4 class="mt-3">
                Giỏ hàng đang trống
            </h4>

            <a href="products.php" class="btn btn-dark mt-3">
                Tiếp tục mua sắm
            </a>

        </div>

    <?php else: ?>

        <form method="POST">

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead class="table-dark">

                        <tr>
                            <th>Sản phẩm</th>
                            <th>Phân loại</th>
                            <th>Giá</th>
                            <th style="width:150px">Số lượng</th>
                            <th>Thành tiền</th>
                            <th></th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($items as $item): ?>

                        <tr>

                            <td>

                                <div class="d-flex align-items-center">

                                    <?php if (!empty($item['main_image'])): ?>

                                        <img
                                            src="uploads/products/<?= htmlspecialchars($item['main_image']) ?>"
                                            width="80"
                                            height="80"
                                            style="object-fit:cover"
                                            class="rounded me-3">

                                    <?php endif; ?>

                                    <strong>
                                        <?= htmlspecialchars($item['name']) ?>
                                    </strong>

                                </div>

                            </td>

                            <td>

                                <?php if ($item['size']): ?>
                                    Size: <?= htmlspecialchars($item['size']) ?><br>
                                <?php endif; ?>

                                <?php if ($item['color']): ?>
                                    Màu: <?= htmlspecialchars($item['color']) ?>
                                <?php endif; ?>

                            </td>

                            <td>
                                <?= number_format($item['price'], 0, ',', '.') ?>đ
                            </td>

                            <td>

                                <input
                                    type="number"
                                    name="quantity[<?= $item['id'] ?>]"
                                    value="<?= $item['quantity'] ?>"
                                    min="1"
                                    max="<?= $item['stock'] ?? 999 ?>"
                                    class="form-control">

                            </td>

                            <td>

                                <strong>
                                    <?= number_format(
                                        $item['price'] * $item['quantity'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>đ
                                </strong>

                            </td>

                            <td>

                                <a
                                    href="cart.php?action=delete&id=<?= $item['id'] ?>"
                                    class="btn btn-outline-danger"
                                    onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">

                                    <i class="bi bi-trash"></i>

                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">

                <a href="products.php" class="btn btn-outline-dark">
                    ← Tiếp tục mua hàng
                </a>

                <button
                    type="submit"
                    name="update_cart"
                    class="btn btn-secondary">

                    Cập nhật giỏ hàng

                </button>

            </div>

        </form>

        <div class="card mt-4 ms-auto" style="max-width:400px">

            <div class="card-body">

                <h4>Tổng đơn hàng</h4>

                <hr>

                <div class="d-flex justify-content-between">

                    <span>Tạm tính:</span>

                    <strong>
                        <?= number_format($total, 0, ',', '.') ?>đ
                    </strong>

                </div>

                <div class="d-flex justify-content-between mt-2">

                    <span>Phí vận chuyển:</span>

                    <strong>30.000đ</strong>

                </div>

                <hr>

                <div class="d-flex justify-content-between">

                    <strong>Tổng cộng:</strong>

                    <strong class="text-danger fs-5">

                        <?= number_format($total + 30000, 0, ',', '.') ?>đ

                    </strong>

                </div>

                <a
                    href="checkout.php"
                    class="btn btn-dark w-100 mt-3">

                    Tiến hành đặt hàng

                </a>

            </div>

        </div>

    <?php endif; ?>

</div>

</body>
</html>