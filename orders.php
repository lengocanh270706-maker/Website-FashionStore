<?php
session_start();

require_once 'includes/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$order_id = (int)($_GET['id'] ?? 0);

/* =========================
   NẾU CÓ ID → XEM CHI TIẾT
========================= */

if ($order_id > 0) {

    $stmt = $conn->prepare("
        SELECT *
        FROM orders
        WHERE id = ? AND user_id = ?
    ");

    $stmt->execute([
        $order_id,
        $user_id
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Không tìm thấy đơn hàng.");
    }

    $stmt = $conn->prepare("
        SELECT
            oi.*,
            p.name,
            p.main_image,
            pv.size,
            pv.color
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        LEFT JOIN product_variants pv ON oi.variant_id = pv.id
        WHERE oi.order_id = ?
    ");

    $stmt->execute([$order_id]);

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {

    /* =========================
       DANH SÁCH ĐƠN
    ========================= */

    $stmt = $conn->prepare("
        SELECT *
        FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");

    $stmt->execute([$user_id]);

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Đơn hàng</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container py-5">

<?php if ($order_id > 0): ?>

<!-- =========================
     CHI TIẾT ĐƠN HÀNG
========================= -->

<a href="orders.php" class="btn btn-outline-secondary mb-4">
← Quay lại
</a>

<?php if (isset($_GET['success'])): ?>

<div class="alert alert-success">

🎉 Đặt hàng thành công!

<br>

Mã đơn hàng:
<strong>
<?= htmlspecialchars($order['order_code']) ?>
</strong>

</div>

<?php endif; ?>

<h2>
Chi tiết đơn hàng
</h2>

<p>
Mã đơn:
<strong>
<?= htmlspecialchars($order['order_code']) ?>
</strong>
</p>

<p>
Ngày đặt:
<?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
</p>

<p>
Trạng thái:

<?php

$status_text = [

    'pending' => 'Chờ xác nhận',

    'confirmed' => 'Đã xác nhận',

    'shipping' => 'Đang giao',

    'completed' => 'Hoàn thành',

    'cancelled' => 'Đã hủy'

];

?>

<span class="badge bg-primary">

<?= $status_text[$order['status']] ?? $order['status'] ?>

</span>

</p>

<div class="card mb-4">

<div class="card-body">

<h5>Thông tin nhận hàng</h5>

<p>
<strong>Họ tên:</strong>
<?= htmlspecialchars($order['customer_name']) ?>
</p>

<p>
<strong>SĐT:</strong>
<?= htmlspecialchars($order['phone']) ?>
</p>

<p>
<strong>Địa chỉ:</strong>
<?= htmlspecialchars($order['address']) ?>
</p>

<p>
<strong>Ghi chú:</strong>
<?= htmlspecialchars($order['note'] ?: 'Không có') ?>
</p>

<p>
<strong>Thanh toán:</strong>

<?= $order['payment_method'] == 'cod'
    ? 'Thanh toán khi nhận hàng'
    : 'Chuyển khoản' ?>

</p>

</div>

</div>

<table class="table align-middle">

<thead class="table-dark">

<tr>

<th>Sản phẩm</th>

<th>Phân loại</th>

<th>Đơn giá</th>

<th>Số lượng</th>

<th>Thành tiền</th>

</tr>

</thead>

<tbody>

<?php foreach ($items as $item): ?>

<tr>

<td>

<strong>
<?= htmlspecialchars($item['name']) ?>
</strong>

</td>

<td>

<?= htmlspecialchars($item['size'] ?? '') ?>

<?= $item['color']
    ? ' - ' . htmlspecialchars($item['color'])
    : '' ?>

</td>

<td>

<?= number_format(
$item['price'],
0,
',',
'.'
) ?>đ

</td>

<td>
<?= $item['quantity'] ?>
</td>

<td>

<?= number_format(
$item['price'] * $item['quantity'],
0,
',',
'.'
) ?>đ

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<div class="text-end">

<h4>

Tổng tiền:

<span class="text-danger">

<?= number_format(
$order['total_price'],
0,
',',
'.'
) ?>đ

</span>

</h4>

</div>

<?php else: ?>

<!-- =========================
     DANH SÁCH ĐƠN HÀNG
========================= -->

<h2 class="mb-4">
Đơn hàng của tôi
</h2>

<?php if (empty($orders)): ?>

<div class="text-center py-5">

<h4>
Bạn chưa có đơn hàng nào.
</h4>

<a href="products.php" class="btn btn-dark">
Mua sắm ngay
</a>

</div>

<?php else: ?>

<div class="table-responsive">

<table class="table table-bordered align-middle">

<thead class="table-dark">

<tr>

<th>Mã đơn</th>

<th>Ngày đặt</th>

<th>Tổng tiền</th>

<th>Trạng thái</th>

<th></th>

</tr>

</thead>

<tbody>

<?php foreach ($orders as $order): ?>

<tr>

<td>
<strong>
<?= htmlspecialchars($order['order_code']) ?>
</strong>
</td>

<td>
<?= date(
'd/m/Y H:i',
strtotime($order['created_at'])
) ?>
</td>

<td>

<?= number_format(
$order['total_price'],
0,
',',
'.'
) ?>đ

</td>

<td>

<?php

$badge = [

    'pending' => 'bg-warning text-dark',

    'confirmed' => 'bg-info',

    'shipping' => 'bg-primary',

    'completed' => 'bg-success',

    'cancelled' => 'bg-danger'

];

?>

<span class="badge <?= $badge[$order['status']] ?? 'bg-secondary' ?>">

<?= $status_text[$order['status']] ?? $order['status'] ?>

</span>

</td>

<td>

<a
href="orders.php?id=<?= $order['id'] ?>"
class="btn btn-sm btn-outline-dark">

Xem chi tiết

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>

<?php endif; ?>

</div>

</body>

</html>