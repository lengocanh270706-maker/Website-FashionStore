<?php
session_start();

require_once 'includes/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    AND status IN ('completed', 'cancelled')
    ORDER BY created_at DESC
");

$stmt->execute([$user_id]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_text = [

    'completed' => 'Hoàn thành',

    'cancelled' => 'Đã hủy'

];

?>

<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Lịch sử mua hàng</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container py-5">

<h2 class="mb-4">
<i class="bi bi-clock-history"></i>
Lịch sử mua hàng
</h2>

<?php if (empty($orders)): ?>

<div class="alert alert-info">

Bạn chưa có đơn hàng hoàn thành hoặc đã hủy.

</div>

<a href="products.php" class="btn btn-dark">
Tiếp tục mua hàng
</a>

<?php else: ?>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>

<th>Mã đơn</th>

<th>Ngày đặt</th>

<th>Người nhận</th>

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

<?= htmlspecialchars(
$order['customer_name']
) ?>

</td>

<td>

<strong>

<?= number_format(
$order['total_price'],
0,
',',
'.'
) ?>đ

</strong>

</td>

<td>

<?php if ($order['status'] === 'completed'): ?>

<span class="badge bg-success">
Hoàn thành
</span>

<?php else: ?>

<span class="badge bg-danger">
Đã hủy
</span>

<?php endif; ?>

</td>

<td>

<a
href="orders.php?id=<?= $order['id'] ?>"
class="btn btn-sm btn-outline-dark">

Xem đơn

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>

</div>

</body>

</html>