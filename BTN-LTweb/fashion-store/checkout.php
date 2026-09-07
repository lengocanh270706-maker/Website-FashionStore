<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$error = '';

$stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cart) {
    header('Location: cart.php');
    exit;
}

$cart_id = (int)$cart['id'];

$stmt = $conn->prepare("
    SELECT ci.id,ci.product_id,ci.variant_id,ci.quantity,ci.price,
           p.name,p.main_image,pv.size,pv.color,pv.quantity AS stock
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    LEFT JOIN product_variants pv ON ci.variant_id = pv.id
    WHERE ci.cart_id = ?
    ORDER BY ci.id DESC
");
$stmt->bind_param("i",$cart_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += (float)$row['price'] * (int)$row['quantity'];
}

$stmt->close();

if (!$items) {
    header('Location: cart.php');
    exit;
}

$stmt = $conn->prepare("SELECT name,phone,address FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

$saved_name = $user_info['name'] ?? $_SESSION['user']['name'] ?? '';
$saved_phone = $user_info['phone'] ?? $_SESSION['user']['phone'] ?? '';
$saved_address = trim($user_info['address'] ?? '');

$has_detail_address = preg_match('/\d+/',$saved_address) && strlen($saved_address) >= 10;

$shipping_fee = 30000;
$grand_total = $total + $shipping_fee;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customer_name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cod';

    if (!in_array($payment_method,['cod','bank'],true)) {
        $payment_method = 'cod';
    }

    if (!$customer_name || !$phone || !$address) {
        $error = 'Vui lòng nhập đầy đủ thông tin giao hàng.';
    } else {

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare("
                SELECT ci.id,ci.product_id,ci.variant_id,ci.quantity,ci.price,
                       pv.quantity AS stock
                FROM cart_items ci
                LEFT JOIN product_variants pv ON ci.variant_id = pv.id
                WHERE ci.cart_id = ?
                FOR UPDATE
            ");
            $stmt->bind_param("i",$cart_id);
            $stmt->execute();
            $checkResult = $stmt->get_result();

            $checkoutItems = [];

            while ($row = $checkResult->fetch_assoc()) {
                $checkoutItems[] = $row;
            }

            $stmt->close();

            if (!$checkoutItems) {
                throw new Exception('Giỏ hàng trống');
            }

            foreach ($checkoutItems as $item) {

                $quantity = (int)$item['quantity'];

                if (!empty($item['variant_id'])) {

                    if ($item['stock'] === null || (int)$item['stock'] < $quantity) {
                        throw new Exception('Sản phẩm không đủ số lượng');
                    }

                } else {

                    $stmt = $conn->prepare("
                        SELECT quantity
                        FROM products
                        WHERE id = ?
                        FOR UPDATE
                    ");
                    $stmt->bind_param("i",$item['product_id']);
                    $stmt->execute();
                    $productStock = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$productStock || (int)$productStock['quantity'] < $quantity) {
                        throw new Exception('Sản phẩm không đủ số lượng');
                    }
                }
            }

            $order_code = 'DH' . date('YmdHis') . rand(100,999);

            $stmt = $conn->prepare("
                INSERT INTO orders
                (user_id,order_code,total_price,customer_name,phone,address,note,
                 status,payment_method,created_at,updated_at)
                VALUES (?,?,?,?,?,?,?,'pending',?,NOW(),NOW())
            ");

            $stmt->bind_param(
                "isdsssss",
                $user_id,
                $order_code,
                $grand_total,
                $customer_name,
                $phone,
                $address,
                $note,
                $payment_method
            );

            if (!$stmt->execute()) {
                throw new Exception('Không thể tạo đơn hàng');
            }

            $order_id = $conn->insert_id;
            $stmt->close();

            foreach ($checkoutItems as $item) {

                $product_id = (int)$item['product_id'];
                $quantity = (int)$item['quantity'];
                $price = (float)$item['price'];

                if (!empty($item['variant_id'])) {

                    $variant_id = (int)$item['variant_id'];

                    $stmt = $conn->prepare("
                        UPDATE product_variants
                        SET quantity = quantity - ?
                        WHERE id = ? AND quantity >= ?
                    ");
                    $stmt->bind_param("iii",$quantity,$variant_id,$quantity);
                    $stmt->execute();

                    if ($stmt->affected_rows === 0) {
                        $stmt->close();
                        throw new Exception('Sản phẩm vừa hết hàng');
                    }

                    $stmt->close();

                    $stmt = $conn->prepare("
                        INSERT INTO order_items
                        (order_id,product_id,variant_id,quantity,price)
                        VALUES (?,?,?,?,?)
                    ");

                    $stmt->bind_param(
                        "iiiid",
                        $order_id,
                        $product_id,
                        $variant_id,
                        $quantity,
                        $price
                    );

                } else {

                    $stmt = $conn->prepare("
                        UPDATE products
                        SET quantity = quantity - ?
                        WHERE id = ? AND quantity >= ?
                    ");
                    $stmt->bind_param("iii",$quantity,$product_id,$quantity);
                    $stmt->execute();

                    if ($stmt->affected_rows === 0) {
                        $stmt->close();
                        throw new Exception('Sản phẩm vừa hết hàng');
                    }

                    $stmt->close();

                    $stmt = $conn->prepare("
                        INSERT INTO order_items
                        (order_id,product_id,variant_id,quantity,price)
                        VALUES (?, ?, NULL, ?, ?)
                    ");

                    $stmt->bind_param(
                        "iiid",
                        $order_id,
                        $product_id,
                        $quantity,
                        $price
                    );
                }

                if (!$stmt->execute()) {
                    throw new Exception('Không thể tạo sản phẩm trong đơn');
                }

                $stmt->close();
            }

            $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
            $stmt->bind_param("i",$cart_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("
                UPDATE cart
                SET updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("i",$cart_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("
                UPDATE users
                SET address = ?
                WHERE id = ?
            ");
            $stmt->bind_param("si",$address,$user_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            $_SESSION['user']['address'] = $address;

            header(
                'Location: order-success.php?order_code=' .
                urlencode($order_code)
            );
            exit;

        } catch (Exception $e) {

            $conn->rollback();

            $error = $e->getMessage() ?: 'Đặt hàng thất bại. Vui lòng thử lại.';
        }
    }
}

$show_saved_address = $has_detail_address && !isset($_POST['change_address']);
$current_address = $_POST['address'] ??
    ($show_saved_address ? $saved_address : '');
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/menu.php'; ?>

<div class="container-fluid py-4">

```
<?php if ($error): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="mb-4">
    <a href="cart.php" class="text-decoration-none text-dark">
        <i class="bi bi-arrow-left me-2"></i>Quay lại giỏ hàng
    </a>
</div>

<form method="POST" id="checkoutForm">

    <div class="row g-4">

        <div class="col-lg-7">

            <div class="card border-0 shadow-sm rounded-4 p-4">

                <h4 class="fw-bold mb-4">
                    Thông tin giao hàng
                </h4>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Họ và tên
                    </label>
                    <input type="text"
                           name="customer_name"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['customer_name'] ?? $saved_name) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Số điện thoại
                    </label>
                    <input type="text"
                           name="phone"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['phone'] ?? $saved_phone) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Địa chỉ
                    </label>
                    <textarea name="address"
                              class="form-control"
                              rows="3"
                              required><?= htmlspecialchars($current_address) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Ghi chú
                    </label>
                    <textarea name="note"
                              class="form-control"
                              rows="3"
                              placeholder="Ghi chú cho người bán..."><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
                </div>

                <h5 class="fw-bold mt-4 mb-3">
                    Phương thức thanh toán
                </h5>

                <div class="form-check border rounded-3 p-3 mb-2">
                    <input class="form-check-input"
                           type="radio"
                           name="payment_method"
                           value="cod"
                           id="cod"
                           <?= ($_POST['payment_method'] ?? 'cod') === 'cod' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="cod">
                        <i class="bi bi-cash me-2"></i>
                        Thanh toán khi nhận hàng
                    </label>
                </div>

                <div class="form-check border rounded-3 p-3">
                    <input class="form-check-input"
                           type="radio"
                           name="payment_method"
                           value="bank"
                           id="bank"
                           <?= ($_POST['payment_method'] ?? '') === 'bank' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="bank">
                        <i class="bi bi-bank me-2"></i>
                        Chuyển khoản ngân hàng
                    </label>
                </div>

            </div>

        </div>

        <div class="col-lg-5">

            <div class="card border-0 shadow-sm rounded-4 p-4">

                <h4 class="fw-bold mb-4">
                    Đơn hàng
                </h4>

                <?php foreach ($items as $item): ?>

                    <div class="d-flex gap-3 mb-3">

                        <?php if (!empty($item['main_image'])): ?>
                            <img src="../uploads/products/<?= htmlspecialchars($item['main_image']) ?>"
                                 style="width:70px;height:80px;object-fit:cover;"
                                 class="rounded-3 border">
                        <?php endif; ?>

                        <div class="flex-grow-1">

                            <div class="fw-semibold">
                                <?= htmlspecialchars($item['name']) ?>
                            </div>

                            <?php if ($item['size'] || $item['color']): ?>
                                <small class="text-muted">
                                    <?= $item['color'] ? 'Màu: '.htmlspecialchars($item['color']) : '' ?>
                                    <?= $item['size'] ? ' | Size: '.htmlspecialchars($item['size']) : '' ?>
                                </small>
                            <?php endif; ?>

                            <div class="small text-muted">
                                SL: <?= (int)$item['quantity'] ?>
                            </div>

                        </div>

                        <div class="fw-semibold">
                            <?= number_format(
                                $item['price'] * $item['quantity'],
                                0,',','.'
                            ) ?>đ
                        </div>

                    </div>

                <?php endforeach; ?>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span>Tạm tính</span>
                    <span><?= number_format($total,0,',','.') ?>đ</span>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span>Phí vận chuyển</span>
                    <span><?= number_format($shipping_fee,0,',','.') ?>đ</span>
                </div>

                <hr>

                <div class="d-flex justify-content-between fs-5 fw-bold mb-4">
                    <span>Tổng cộng</span>
                    <span><?= number_format($grand_total,0,',','.') ?>đ</span>
                </div>

                <button type="submit"
                        class="btn btn-dark w-100 rounded-pill py-3">
                    <i class="bi bi-bag-check me-2"></i>
                    Đặt hàng
                </button>

            </div>

        </div>

    </div>

</form>

</div>

<script src="../assets/js/script.js?v=4"></script>

<?php include '../includes/footer.php'; ?>
