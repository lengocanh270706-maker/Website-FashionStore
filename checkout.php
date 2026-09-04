<?php
session_start();

require_once 'includes/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

/* =========================
   LẤY GIỎ HÀNG
========================= */

$stmt = $conn->prepare("
    SELECT *
    FROM cart
    WHERE user_id = ?
");

$stmt->execute([$user_id]);

$cart = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cart) {
    header("Location: cart.php");
    exit;
}

$cart_id = $cart['id'];

/* =========================
   LẤY SẢN PHẨM
========================= */

$stmt = $conn->prepare("
    SELECT
        ci.*,
        p.name,
        p.main_image,
        pv.size,
        pv.color,
        pv.quantity AS stock
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    LEFT JOIN product_variants pv ON ci.variant_id = pv.id
    WHERE ci.cart_id = ?
");

$stmt->execute([$cart_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    header("Location: cart.php");
    exit;
}

$total = 0;

foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$shipping_fee = 30000;

$grand_total = $total + $shipping_fee;

$error = '';

/* =========================
   ĐẶT HÀNG
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customer_name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cod';

    if ($customer_name === '') {

        $error = 'Vui lòng nhập họ tên.';

    } elseif ($phone === '') {

        $error = 'Vui lòng nhập số điện thoại.';

    } elseif ($address === '') {

        $error = 'Vui lòng nhập địa chỉ.';

    } elseif (!in_array($payment_method, ['cod', 'bank'])) {

        $error = 'Phương thức thanh toán không hợp lệ.';

    } else {

        try {

            $conn->beginTransaction();

            /* Kiểm tra tồn kho lại */

            foreach ($items as $item) {

                if ($item['variant_id']) {

                    $stmt = $conn->prepare("
                        SELECT quantity
                        FROM product_variants
                        WHERE id = ?
                        FOR UPDATE
                    ");

                    $stmt->execute([$item['variant_id']]);

                } else {

                    $stmt = $conn->prepare("
                        SELECT quantity
                        FROM products
                        WHERE id = ?
                        FOR UPDATE
                    ");

                    $stmt->execute([$item['product_id']]);
                }

                $stock = $stmt->fetchColumn();

                if ($stock < $item['quantity']) {

                    throw new Exception(
                        "Sản phẩm " . $item['name'] . " không đủ số lượng."
                    );
                }
            }

            /* Tạo mã đơn */

            $order_code = 'DH' . date('YmdHis') . rand(100, 999);

            /* Tạo đơn hàng */

            $stmt = $conn->prepare("
                INSERT INTO orders
                (
                    user_id,
                    order_code,
                    total_price,
                    customer_name,
                    phone,
                    address,
                    note,
                    status,
                    payment_method,
                    created_at,
                    updated_at
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW(), NOW())
            ");

            $stmt->execute([
                $user_id,
                $order_code,
                $grand_total,
                $customer_name,
                $phone,
                $address,
                $note,
                $payment_method
            ]);

            $order_id = $conn->lastInsertId();

            /* =========================
               LƯU ORDER ITEMS
            ========================= */

            foreach ($items as $item) {

                $stmt = $conn->prepare("
                    INSERT INTO order_items
                    (
                        order_id,
                        product_id,
                        variant_id,
                        quantity,
                        price
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['variant_id'] ?: null,
                    $item['quantity'],
                    $item['price']
                ]);

                /* =========================
                   TRỪ TỒN KHO
                ========================= */

                if ($item['variant_id']) {

                    $stmt = $conn->prepare("
                        UPDATE product_variants
                        SET quantity = quantity - ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $item['quantity'],
                        $item['variant_id']
                    ]);

                } else {

                    $stmt = $conn->prepare("
                        UPDATE products
                        SET quantity = quantity - ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $item['quantity'],
                        $item['product_id']
                    ]);
                }
            }

            /* =========================
               XÓA GIỎ HÀNG
            ========================= */

            $stmt = $conn->prepare("
                DELETE FROM cart_items
                WHERE cart_id = ?
            ");

            $stmt->execute([$cart_id]);

            $conn->commit();

            header(
                "Location: orders.php?id=" . $order_id . "&success=1"
            );

            exit;

        } catch (Exception $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Thanh toán</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container py-5">

<h2 class="mb-4">Thanh toán</h2>

<?php if ($error): ?>

<div class="alert alert-danger">
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<div class="row">

<!-- THÔNG TIN GIAO HÀNG -->

<div class="col-md-7">

<div class="card">

<div class="card-body">

<h4 class="mb-4">
Thông tin giao hàng
</h4>

<form method="POST">

<div class="mb-3">

<label class="form-label">
Họ và tên
</label>

<input
type="text"
name="customer_name"
class="form-control"
required
value="<?= htmlspecialchars($_POST['customer_name'] ?? ($_SESSION['user']['name'] ?? '')) ?>">

</div>

<div class="mb-3">

<label class="form-label">
Số điện thoại
</label>

<input
type="text"
name="phone"
class="form-control"
required
value="<?= htmlspecialchars($_POST['phone'] ?? ($_SESSION['user']['phone'] ?? '')) ?>">

</div>

<div class="mb-3">

<label class="form-label">
Địa chỉ giao hàng
</label>

<textarea
name="address"
class="form-control"
rows="3"
required><?= htmlspecialchars($_POST['address'] ?? ($_SESSION['user']['address'] ?? '')) ?></textarea>

</div>

<div class="mb-3">

<label class="form-label">
Ghi chú
</label>

<textarea
name="note"
class="form-control"
rows="2"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>

</div>

<h5>Phương thức thanh toán</h5>

<div class="form-check">

<input
class="form-check-input"
type="radio"
name="payment_method"
value="cod"
checked>

<label class="form-check-label">
Thanh toán khi nhận hàng (COD)
</label>

</div>

<div class="form-check">

<input
class="form-check-input"
type="radio"
name="payment_method"
value="bank">

<label class="form-check-label">
Chuyển khoản ngân hàng
</label>

</div>

<button
type="submit"
class="btn btn-dark w-100 mt-4">

Xác nhận đặt hàng

</button>

</form>

</div>

</div>

</div>

<!-- TÓM TẮT ĐƠN -->

<div class="col-md-5">

<div class="card">

<div class="card-body">

<h4>Đơn hàng</h4>

<hr>

<?php foreach ($items as $item): ?>

<div class="d-flex justify-content-between mb-3">

<div>

<strong>
<?= htmlspecialchars($item['name']) ?>
</strong>

<br>

<small>

<?php if ($item['size']): ?>
Size <?= htmlspecialchars($item['size']) ?>
<?php endif; ?>

<?php if ($item['color']): ?>
- <?= htmlspecialchars($item['color']) ?>
<?php endif; ?>

× <?= $item['quantity'] ?>

</small>

</div>

<strong>

<?= number_format(
$item['price'] * $item['quantity'],
0,
',',
'.'
) ?>đ

</strong>

</div>

<?php endforeach; ?>

<hr>

<div class="d-flex justify-content-between">

<span>Tạm tính</span>

<strong>
<?= number_format($total, 0, ',', '.') ?>đ
</strong>

</div>

<div class="d-flex justify-content-between mt-2">

<span>Phí vận chuyển</span>

<strong>
<?= number_format($shipping_fee, 0, ',', '.') ?>đ
</strong>

</div>

<hr>

<div class="d-flex justify-content-between">

<strong>Tổng cộng</strong>

<strong class="text-danger fs-5">

<?= number_format($grand_total, 0, ',', '.') ?>đ

</strong>

</div>

</div>

</div>

</div>

</div>

</div>

</body>

</html>