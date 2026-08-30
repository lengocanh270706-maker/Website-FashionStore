<?php
require_once '../../includes/database.php';
$products = $conn->query("SELECT * FROM products");

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $payment_method = $_POST['payment_method'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    if (!empty($customer_name) && !empty($phone) && !empty($address) && !empty($product_id)) {
        // Lấy giá sản phẩm từ database
        $prod_query = $conn->query("SELECT price FROM products WHERE id = $product_id");
        $prod = $prod_query->fetch_assoc();
        $price = $prod['price'];
        $total_price = $price * $quantity;
        
        // Tạo mã đơn hàng ngẫu nhiên
        $order_code = 'MDC' . rand(10000, 99999);
        $status = 'pending';

        // Thêm vào bảng orders
        $stmt = $conn->prepare("INSERT INTO orders (order_code, customer_name, phone, address, total_price, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdss", $order_code, $customer_name, $phone, $address, $total_price, $payment_method, $status);
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            
            // Thêm chi tiết vào bảng order_items
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt_item->execute();

            header("Location: index.php");
            exit();
        } else {
            $error = "Lỗi cơ sở dữ liệu: " . $conn->error;
        }
    } else {
        $error = "Vui lòng điền đầy đủ các thông tin bắt buộc!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Đơn hàng - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 700px;">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h4 class="fw-bold mb-4">Thêm đơn hàng mới thủ công</h4>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên khách hàng *</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Nhập họ tên khách hàng..." required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Số điện thoại *</label>
                        <input type="text" name="phone" class="form-control" placeholder="0912345678" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Phương thức thanh toán</label>
                        <select name="payment_method" class="form-select">
                            <option value="COD">COD (Thanh toán khi nhận hàng)</option>
                            <option value="Banking">Banking (Chuyển khoản)</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Chọn sản phẩm *</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            <?php while($p = $products->fetch_assoc()): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= number_format($p['price']) ?>đ)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Số lượng *</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Địa chỉ giao hàng *</label>
                    <textarea name="address" rows="3" class="form-control" placeholder="Nhập địa chỉ nhận hàng..." required></textarea>
                </div>
                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
                    <button type="submit" class="btn btn-dark px-4">Tạo đơn hàng</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>