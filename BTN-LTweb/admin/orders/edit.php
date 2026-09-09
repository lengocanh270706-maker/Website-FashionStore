<?php
require_once '../../includes/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];

    $stmt_update = $conn->prepare("UPDATE orders SET customer_name = ?, phone = ?, address = ?, payment_method = ?, status = ? WHERE id = ?");
    $stmt_update->bind_param("sssssi", $customer_name, $phone, $address, $payment_method, $status, $id);

    if ($stmt_update->execute()) {
        header("Location: index.php");
        exit();
    }
}

$address_parts = array_map('trim', explode(',', $order['address']));
$detail_address = $address_parts[0] ?? '';
$old_ward = $address_parts[1] ?? '';
$old_province = $address_parts[2] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Đơn hàng - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light p-5">

<div class="container" style="max-width:700px">
    <div class="card border-0 shadow-sm p-4 rounded-4">

        <h4 class="fw-bold mb-4">
            Chỉnh sửa đơn hàng: #<?= htmlspecialchars($order['order_code']) ?>
        </h4>

        <form method="POST" id="orderForm">

            <div class="mb-3">
                <label class="form-label fw-bold">Tên khách hàng *</label>
                <input type="text" name="customer_name" class="form-control"
                       value="<?= htmlspecialchars($order['customer_name']) ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Số điện thoại *</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= htmlspecialchars($order['phone']) ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Phương thức thanh toán</label>
                    <select name="payment_method" class="form-select">
                        <option value="COD" <?= $order['payment_method'] == 'COD' ? 'selected' : '' ?>>
                            COD (Thanh toán khi nhận hàng)
                        </option>
                        <option value="Banking" <?= $order['payment_method'] == 'Banking' ? 'selected' : '' ?>>
                            Banking (Chuyển khoản)
                        </option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Trạng thái đơn hàng</label>
                <select name="status" class="form-select">
                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending (Chờ xác nhận)</option>
                    <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed (Đã xác nhận)</option>
                    <option value="shipping" <?= $order['status'] == 'shipping' ? 'selected' : '' ?>>Shipping (Đang giao)</option>
                    <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed (Hoàn thành)</option>
                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled (Đã hủy)</option>
                </select>
            </div>

            <label class="form-label fw-bold">Địa chỉ giao hàng *</label>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <select name="province" id="province" class="form-select" required>
                        <option value="">-- Tỉnh / Thành phố --</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <select name="province" id="ward" class="form-select" required disabled>
                        <option value="">-- Phường / Xã --</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <input type="text" name="province" id="detail_address" class="form-control"
                       value="<?= htmlspecialchars($detail_address) ?>"
                       placeholder="Số nhà, tên đường..." required>

                <input type="hidden" name="address" id="address">
            </div>

            <div class="text-end">
                <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
                <button type="submit" class="btn btn-dark px-4">Cập nhật đơn hàng</button>
            </div>

        </form>
    </div>
</div>

<script src="../../assets/js/script.js?v=10"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){
    const orderForm=document.getElementById('orderForm');
    if(orderForm){
        const orderAddress=setupAddress(
            'province',
            'ward',
            'detail_address',
            'address',
            <?= json_encode($old_province) ?>,
            <?= json_encode($old_ward) ?>
        );
        orderForm.addEventListener('submit',function(e){
            if(orderAddress&&!orderAddress()){
                e.preventDefault();
            }
        });
    }
});
</script>

</body>
</html>