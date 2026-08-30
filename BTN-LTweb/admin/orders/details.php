<?php
require_once '../../includes/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = $_POST['status'];
    $stmt_update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt_update->bind_param("si", $new_status, $order_id);
    $stmt_update->execute();
    header("Location: detail.php?id=" . $order_id);
    exit();
}

$order_query = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$order_query->bind_param("i", $order_id);
$order_query->execute();
$order = $order_query->get_result()->fetch_assoc();

if (!$order) {
    header("Location: index.php");
    exit();
}

$items_query = $conn->prepare("SELECT order_items.*, products.name FROM order_items LEFT JOIN products ON order_items.product_id = products.id WHERE order_id = ?");
$items_query->bind_param("i", $order_id);
$items_query->execute();
$items = $items_query->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?= htmlspecialchars($order['order_code']) ?> - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Chi tiết đơn hàng #<?= htmlspecialchars($order['order_code']) ?></h4>
            <a href="index.php" class="btn btn-secondary px-4">&larr; Quay lại danh sách</a>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4 h-100 rounded-4">
                    <h5 class="fw-bold mb-3">Thông tin khách hàng</h5>
                    <p class="mb-2"><strong>Họ tên:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                    <p class="mb-2"><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                    <p class="mb-2"><strong>Địa chỉ giao:</strong> <?= htmlspecialchars($order['address']) ?></p>
                    <p class="mb-2"><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note'] ?? 'Không có') ?></p>
                    <p class="mb-0"><strong>Thanh toán:</strong> <span class="badge bg-light text-dark border"><?= htmlspecialchars($order['payment_method']) ?></span></p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4 h-100 rounded-4">
                    <h5 class="fw-bold mb-3">Trạng thái xử lý đơn hàng</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Cập nhật trạng thái hoặc Xác nhận đơn:</label>
                            <select name="status" class="form-select">
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending (Chờ xác nhận)</option>
                                <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed (Đã xác nhận)</option>
                                <option value="shipping" <?= $order['status'] == 'shipping' ? 'selected' : '' ?>>Shipping (Đang giao)</option>
                                <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed (Hoàn thành)</option>
                                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled (Đã hủy)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 mt-2">Cập nhật trạng thái đơn</button>
                    </form>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm p-4 rounded-4">
                    <h5 class="fw-bold mb-3">Sản phẩm trong đơn hàng</h5>
                    <table class="table align-middle mb-0">
                        <thead class="text-muted border-bottom">
                            <tr>
                                <th>Tên sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = $items->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-medium"><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= number_format($item['price']) ?> đ</td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td class="text-end fw-bold"><?= number_format($item['price'] * $item['quantity']) ?> đ</td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <div class="text-end mt-3 pt-3 border-top">
                        <h5 class="fw-bold text-danger mb-0">Tổng cộng: <?= number_format($order['total_price']) ?> đ</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>