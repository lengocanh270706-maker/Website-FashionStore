<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../includes/database.php";
require_once "../includes/auth.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

//1. LẤY / TẠO GIỎ HÀNG
$stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $cart = $result->fetch_assoc();
    $cart_id = $cart['id'];
} else {
    $stmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_id = $conn->insert_id;
}

//2. THÊM SẢN PHẨM VÀO GIỎ
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $variant_id = (int)($_POST['variant_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    if ($product_id <= 0) {
        header("Location: products.php");
        exit;
    }
    if ($quantity <= 0) {
        $quantity = 1;
    }

    //Kiểm tra sản phẩm
    $stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id = ? AND status = 1 LIMIT 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    if ($product_result->num_rows === 0) {
        $_SESSION['cart_error'] = "Sản phẩm không tồn tại.";
        header("Location: products.php");
        exit;
    }
    $product = $product_result->fetch_assoc();
    $price = (float)$product['price'];

    //TRƯỜNG HỢP CÓ VARIANT
    if ($variant_id > 0) {
        // Kiểm tra variant có thuộc sản phẩm không
        $stmt = $conn->prepare("
            SELECT id, quantity
            FROM product_variants
            WHERE id = ? AND product_id = ?
            LIMIT 1
        ");

        $stmt->bind_param("ii", $variant_id, $product_id);
        $stmt->execute();
        $variant_result = $stmt->get_result();

        if ($variant_result->num_rows === 0) {
            $_SESSION['cart_error'] = "Phân loại sản phẩm không hợp lệ.";
            header("Location: product-detail.php?id=" . $product_id);
            exit;
        }

        $variant = $variant_result->fetch_assoc();
        $stock = (int)$variant['quantity'];

        if ($stock <= 0) {
            $_SESSION['cart_error'] = "Sản phẩm đã hết hàng.";
            header("Location: product-detail.php?id=" . $product_id);
            exit;
        }

        if ($quantity > $stock) {
            $quantity = $stock;
        }

        // Kiểm tra sản phẩm + variant đã có trong giỏ chưa
        $stmt = $conn->prepare("
            SELECT id, quantity
            FROM cart_items
            WHERE cart_id = ?
            AND product_id = ?
            AND variant_id = ?
            LIMIT 1
        ");

        $stmt->bind_param("iii",$cart_id,$product_id,$variant_id);
        $stmt->execute();
        $item_result = $stmt->get_result();

        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            $new_quantity = (int)$item['quantity'] + $quantity;
            if ($new_quantity > $stock) {
                $new_quantity = $stock;
            }
            $stmt = $conn->prepare("
                UPDATE cart_items
                SET quantity = ?
                WHERE id = ?
            ");
            $stmt->bind_param(
                "ii",
                $new_quantity,
                $item['id']
            );
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("
                INSERT INTO cart_items
                (cart_id, product_id, variant_id, quantity, price)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "iiiid",
                $cart_id,
                $product_id,
                $variant_id,
                $quantity,
                $price
            );
            $stmt->execute();
        }

    // TRƯỜNG HỢP KHÔNG CÓ VARIANT
    } else {
        // Tìm item không có variant
        $stmt = $conn->prepare("
            SELECT id, quantity
            FROM cart_items
            WHERE cart_id = ?
            AND product_id = ?
            AND variant_id IS NULL
            LIMIT 1
        ");
        $stmt->bind_param("ii",$cart_id,$product_id);
        $stmt->execute();
        $item_result = $stmt->get_result();
        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            $new_quantity = (int)$item['quantity'] + $quantity;
            $stmt = $conn->prepare("
                UPDATE cart_items
                SET quantity = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ii", $new_quantity,$item['id']);
            $stmt->execute();
        } else {
            // QUAN TRỌNG:
            // variant_id phải là NULL, KHÔNG ĐƯỢC là 0
            $stmt = $conn->prepare("
                INSERT INTO cart_items
                (cart_id, product_id, variant_id, quantity, price)
                VALUES (?, ?, NULL, ?, ?)
            ");
            $stmt->bind_param("iiid",$cart_id,$product_id,$quantity,$price);
            $stmt->execute();
        }
    }

    $_SESSION['cart_success'] = "Đã thêm sản phẩm vào giỏ hàng.";
    header("Location: cart.php");
    exit;
}

// 3. CẬP NHẬT SỐ LƯỢNG
if (isset($_POST['update_cart'])) {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);

    if ($item_id > 0) {
        if ($quantity <= 0) {
            $stmt = $conn->prepare("
                DELETE FROM cart_items
                WHERE id = ? AND cart_id = ?
            ");
            $stmt->bind_param("ii",$item_id,$cart_id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("
                UPDATE cart_items
                SET quantity = ?
                WHERE id = ? AND cart_id = ?
            ");
            $stmt->bind_param("iii",$quantity,$item_id,$cart_id);
            $stmt->execute();
        }
    }
    header("Location: cart.php");
    exit;
}

// 4. XÓA SẢN PHẨM
if (isset($_GET['delete'])) {
    $item_id = (int)$_GET['delete'];
    if ($item_id > 0) {
        $stmt = $conn->prepare("
            DELETE FROM cart_items
            WHERE id = ? AND cart_id = ?
        ");
        $stmt->bind_param("ii",$item_id,$cart_id);
        $stmt->execute();
    }
    header("Location: cart.php");
    exit;
}

// 5. LẤY DANH SÁCH SẢN PHẨM TRONG GIỎ
$stmt = $conn->prepare("
    SELECT
        ci.id,
        ci.product_id,
        ci.variant_id,
        ci.quantity,
        ci.price,
        p.name,
        p.main_image,
        pv.size,
        pv.color,
        pv.quantity AS stock
    FROM cart_items ci
    INNER JOIN products p ON ci.product_id = p.id
    LEFT JOIN product_variants pv ON ci.variant_id = pv.id
    WHERE ci.cart_id = ?
    ORDER BY ci.id DESC
");

$stmt->bind_param("i", $cart_id);
$stmt->execute();
$items_result = $stmt->get_result();
$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}

// 6. TÍNH TỔNG TIỀN
$total = 0;
foreach ($items as $item) {
    $total += (float)$item['price'] * (int)$item['quantity'];
}

$shipping_fee = $total > 0 ? 30000 : 0;
$grand_total = $total + $shipping_fee;

// 7. HEADER
require_once "../includes/header.php";
require_once "../includes/menu.php";
?>

<div class="container py-5">
    <div class="mb-4">
        <h2 class="fw-semibold mb-1">Giỏ hàng</h2>
        <p class="text-muted mb-0">Kiểm tra sản phẩm trước khi thanh toán</p>
    </div>

    <?php if (isset($_SESSION['cart_success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['cart_success']) ?>
        </div>
        <?php unset($_SESSION['cart_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['cart_error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['cart_error']) ?>
        </div>
        <?php unset($_SESSION['cart_error']); ?>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="text-center py-5">
            <i class="bi bi-bag-x fs-1 text-muted"></i>
            <h4 class="mt-3">Giỏ hàng đang trống</h4>
            <p class="text-muted">Hãy thêm sản phẩm bạn yêu thích vào giỏ hàng.</p>
            <a href="products.php" class="btn btn-dark px-4">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>

        <div class="row g-4">
            <!-- DANH SÁCH SẢN PHẨM -->
            <div class="col-lg-8">
                <?php foreach ($items as $item): ?>
                    <?php
                    $item_total =
                        (float)$item['price'] *
                        (int)$item['quantity'];

                    $image = $item['main_image'] ?? '';
                    if (!empty($image)) {
                        $image_path = "../uploads/products/" . $image;
                    } else {
                        $image_path = "../assets/images/products/image1.png";
                    }
                    ?>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="row align-items-center g-3">
                                <!-- IMAGE -->
                                <div class="col-4 col-md-2">
                                    <img
                                        src="<?= htmlspecialchars($image_path) ?>"
                                        alt="<?= htmlspecialchars($item['name']) ?>"
                                        class="img-fluid rounded"
                                        style="width:100%;height:120px;object-fit:cover;"
                                    >
                                </div>

                                <!-- PRODUCT INFO -->
                                <div class="col-8 col-md-4">
                                    <h6 class="mb-2"><?= htmlspecialchars($item['name']) ?></h6>
                                    <?php if (!empty($item['size'])): ?>
                                        <div class="small text-muted">Size:
                                            <?= htmlspecialchars($item['size']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($item['color'])): ?>
                                        <div class="small text-muted">Màu:
                                            <?= htmlspecialchars($item['color']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-2 fw-semibold">
                                        <?= number_format($item['price'], 0, ',', '.') ?> đ
                                    </div>
                                </div>

                                <!-- QUANTITY -->
                                <div class="col-6 col-md-3">
                                    <form method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <input
                                            type="number"
                                            name="quantity"
                                            value="<?= $item['quantity'] ?>"
                                            min="1"
                                            <?php if ($item['variant_id'] && $item['stock'] !== null): ?>
                                                max="<?= $item['stock'] ?>"
                                            <?php endif; ?>
                                            class="form-control form-control-sm"
                                            style="width:80px;"
                                        >

                                        <button type="submit" name="update_cart" class="btn btn-sm btn-outline-dark ms-2">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- TOTAL + DELETE -->
                                <div class="col-6 col-md-3 text-md-end">
                                    <div class="fw-semibold mb-2">
                                        <?= number_format($item_total,0,',','.') ?>đ
                                    </div>

                                    <a href="cart.php?delete=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- TỔNG ĐƠN HÀNG-->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-4">Tổng đơn hàng</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Tạm tính</span>
                            <span><?= number_format($total,0,',','.') ?>đ</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Phí vận chuyển</span>
                            <span><?= number_format($shipping_fee,0,',','.') ?>đ</span>
                        </div>
                        <hr>

                        <div class="d-flex justify-content-between mb-4">
                            <strong>Tổng cộng</strong>
                            <strong class="fs-5"><?= number_format($grand_total,0,',','.') ?> đ</strong>
                        </div>
                        <a href="checkout.php" class="btn btn-dark w-100 py-2">Tiến hành thanh toán</a>
                        <a href="products.php" class="btn btn-outline-dark w-100 mt-2">Tiếp tục mua sắm</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once "../includes/footer.php";
?>