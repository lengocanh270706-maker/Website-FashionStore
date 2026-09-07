<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$order_code = trim($_GET['order_code'] ?? '');

if ($order_code === '') {
    header('Location: products.php');
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$stmt = $conn->prepare("
    SELECT order_code,total_price,customer_name,phone,address,payment_method,status,created_at
    FROM orders
    WHERE order_code = ? AND user_id = ?
    LIMIT 1
");
$stmt->bind_param("si",$order_code,$user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: products.php');
    exit;
}

include '../includes/header.php';
include '../includes/menu.php';
?>

<div class="container py-5">
    <div class="card border-0 shadow-sm rounded-4 mx-auto text-center" style="max-width:650px;">
        <div class="card-body p-5">
            <div class="mb-4">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width:80px;height:80px;">
                    <i class="bi bi-check-circle-fill text-success" style="font-size:45px;"></i>
                </div>
            </div>

            <h2 class="fw-bold mb-2">Đặt hàng thành công!</h2>
            <p class="text-muted mb-4">Cảm ơn bạn đã mua hàng tại Mây Store.</p>

            <div class="bg-light rounded-4 p-4 text-start mb-4">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Mã đơn hàng</span>
                    <strong><?= htmlspecialchars($order['order_code']) ?></strong>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Người nhận</span>
                    <span><?= htmlspecialchars($order['customer_name']) ?></span>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Số điện thoại</span>
                    <span><?= htmlspecialchars($order['phone']) ?></span>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Thanh toán</span>
                    <span>
                        <?= $order['payment_method'] === 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản ngân hàng' ?>
                    </span>
                </div>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">Tổng tiền</span>
                    <strong class="fs-5"><?= number_format($order['total_price'],0,',','.') ?>đ</strong>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-2">
                <a href="products.php" class="btn btn-outline-dark rounded-pill px-4">
                    <i class="bi bi-bag me-2"></i>Tiếp tục mua sắm
                </a>

                <a href="orders.php" class="btn btn-dark rounded-pill px-4">
                    <i class="bi bi-receipt me-2"></i>Xem đơn hàng
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>