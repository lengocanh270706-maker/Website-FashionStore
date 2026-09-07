<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$order_id = (int)($_GET['id'] ?? 0);
$status_text = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
];
$status_badge = [
    'pending' => 'text-bg-warning',
    'confirmed' => 'text-bg-info',
    'shipping' => 'text-bg-primary',
    'completed' => 'text-bg-success',
    'cancelled' => 'text-bg-danger'
];

/* HỦY ĐƠN */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $cancel_id = (int)($_POST['order_id'] ?? 0);

    if ($cancel_id > 0) {
        $stmt = $conn->prepare("
            SELECT id, status
            FROM orders
            WHERE id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $cancel_id, $user_id);
        $stmt->execute();
        $order_cancel = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$order_cancel) {
            $_SESSION['order_error'] = 'Không tìm thấy đơn hàng.';
        } elseif (!in_array($order_cancel['status'], ['pending', 'confirmed'])) {
            $_SESSION['order_error'] = 'Đơn hàng hiện tại không thể hủy.';
        } else {
            $stmt = $conn->prepare("
                UPDATE orders
                SET status = 'cancelled', updated_at = NOW()
                WHERE id = ? AND user_id = ?
                AND status IN ('pending', 'confirmed')
            ");
            $stmt->bind_param("ii", $cancel_id, $user_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $_SESSION['order_success'] = 'Hủy đơn hàng thành công.';
            } else {
                $_SESSION['order_error'] = 'Không thể hủy đơn hàng.';
            }
            $stmt->close();
        }
    }
    header("Location: orders.php" . ($order_id > 0 ? "?id=" . $order_id : ""));
    exit;
}

/* CHI TIẾT ĐƠN HÀNG */
if ($order_id > 0) {
    $stmt = $conn->prepare("
        SELECT *
        FROM orders
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
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
        ORDER BY oi.id ASC
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    /* DANH SÁCH ĐƠN HÀNG */
    $stmt = $conn->prepare("
        SELECT *
        FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

include '../includes/header.php';
include '../includes/menu.php';
?>

<div class="container-fluid py-4">
<?php if (isset($_SESSION['order_success'])): ?>
    <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
        <i class="bi bi-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['order_success']) ?>
    </div>
    <?php unset($_SESSION['order_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['order_error'])): ?>
    <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['order_error']) ?>
    </div>
    <?php unset($_SESSION['order_error']); ?>
<?php endif; ?>
<?php if ($order_id > 0): ?>

    <!-- CHI TIẾT ĐƠN HÀNG -->
    <div class="mb-4">
        <a href="orders.php" class="text-dark text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i>Quay lại đơn hàng
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
            <div class="fw-semibold mb-1">
                <i class="bi bi-check-circle me-2"></i>Đặt hàng thành công!
            </div>
            <small>Mã đơn hàng:<strong><?= htmlspecialchars($order['order_code']) ?></strong></small>
        </div>
    <?php endif; ?>

    <!-- HEADER ĐƠN HÀNG -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h4 class="fw-bold mb-2"><i class="bi bi-receipt me-2"></i>Chi tiết đơn hàng</h4>
                    <div class="text-muted small mb-1">
                        Mã đơn:
                        <strong class="text-dark">
                            <?= htmlspecialchars($order['order_code']) ?>
                        </strong>
                    </div>

                    <div class="text-muted small">
                        Ngày đặt:
                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                    </div>
                </div>

                <div class="text-end">
                    <span class="badge rounded-pill px-3 py-2 <?= $status_badge[$order['status']] ?? 'text-bg-secondary' ?>">
                        <?= $status_text[$order['status']] ?? $order['status'] ?>
                    </span>

                    <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                        <form method="POST" class="mt-3" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này không?');">
                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                            <button type="submit" name="cancel_order" class="btn btn-outline-danger rounded-pill px-3">
                                <i class="bi bi-x-circle me-1"></i>
                                Hủy đơn hàng
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- THÔNG TIN NHẬN HÀNG -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4"><i class="bi bi-person-vcard me-2"></i>Thông tin nhận hàng</h6>
                    <div class="mb-3">
                        <small class="text-muted d-block">Họ tên</small>
                        <span class="fw-semibold"><?= htmlspecialchars($order['customer_name']) ?></span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Số điện thoại</small>
                        <span><?= htmlspecialchars($order['phone']) ?></span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Địa chỉ</small>
                        <span><?= htmlspecialchars($order['address']) ?></span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Thanh toán</small>
                        <span>
                            <?= $order['payment_method'] === 'cod'
                                ? 'Thanh toán khi nhận hàng'
                                : 'Chuyển khoản' ?>
                        </span>
                    </div>

                    <div>
                        <small class="text-muted d-block">Ghi chú</small>
                        <span><?= htmlspecialchars($order['note'] ?: 'Không có') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SẢN PHẨM -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 px-4 py-3">
                    <h6 class="fw-bold mb-0">Sản phẩm trong đơn</h6>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Sản phẩm</th>
                                <th class="py-3">Phân loại</th>
                                <th class="py-3">Đơn giá</th>
                                <th class="py-3">SL</th>
                                <th class="py-3 pe-4">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($items as $item): ?>
                            <?php
                            $item_total = (float)$item['price'] * (int)$item['quantity'];
                            ?>
                            <tr>
                                <td class="px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (!empty($item['main_image'])): ?>
                                            <img
                                                src="../uploads/products/<?= htmlspecialchars($item['main_image']) ?>"
                                                alt="<?= htmlspecialchars($item['name']) ?>"
                                                class="rounded-3 border"
                                                style="width:60px;height:75px;object-fit:cover;"
                                            >

                                        <?php else: ?>
                                            <div
                                                class="rounded-3 border bg-light d-flex align-items-center justify-content-center"
                                                style="width:60px;height:75px;"
                                            >
                                                <i class="bi bi-image text-secondary"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span class="fw-semibold"><?= htmlspecialchars($item['name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <?php if (!empty($item['size'])): ?>
                                            Size: <?= htmlspecialchars($item['size']) ?>
                                        <?php endif; ?>

                                        <?php if (!empty($item['color'])): ?>
                                            <br>
                                            Màu: <?= htmlspecialchars($item['color']) ?>
                                        <?php endif; ?>

                                        <?php if (empty($item['size']) && empty($item['color'])): ?>
                                            Không có
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td><?= number_format($item['price'], 0, ',', '.') ?>đ</td>
                                <td><?= (int)$item['quantity'] ?></td>
                                <td class="pe-4 fw-bold"><?= number_format($item_total, 0, ',', '.') ?>đ</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Tổng tiền</span>
                        <span class="fw-bold fs-4"><?= number_format($order['total_price'], 0, ',', '.') ?>đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>

    <!-- DANH SÁCH ĐƠN HÀNG -->
    <div class="mb-4">
        <h4 class="fw-bold mb-1 text-center">
            <i class="bi bi-receipt me-2"></i>Đơn hàng của tôi
        </h4>

        <div class="text-center">
            <small class="text-muted">Theo dõi tình trạng các đơn hàng của bạn</small>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-receipt display-3 text-secondary"></i>
                <h5 class="fw-bold mt-3">Bạn chưa có đơn hàng nào</h5>
                <p class="text-muted mb-4">Hãy khám phá các sản phẩm mới nhất của Mây.</p>
                <a href="products.php" class="btn btn-dark rounded-pill px-4">
                    <i class="bi bi-shop me-2"></i>Mua sắm ngay
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 px-4 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Danh sách đơn hàng</h6>
                    <span class="text-muted small"><?= count($orders) ?> đơn hàng</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">Mã đơn</th>
                            <th class="py-3">Ngày đặt</th>
                            <th class="py-3">Tổng tiền</th>
                            <th class="py-3">Trạng thái</th>
                            <th class="py-3 pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="px-4">
                                <span class="fw-semibold"><?= htmlspecialchars($order['order_code']) ?></span>
                            </td>

                            <td>
                                <span class="text-muted small"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                            </td>

                            <td>
                                <span class="fw-bold"><?= number_format($order['total_price'], 0, ',', '.') ?>đ</span>
                            </td>

                            <td>
                                <span class="badge rounded-pill px-3 py-2 <?= $status_badge[$order['status']] ?? 'text-bg-secondary' ?>">
                                    <?= $status_text[$order['status']] ?? $order['status'] ?>
                                </span>
                            </td>

                            <td class="pe-4">
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="orders.php?id=<?= (int)$order['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i>Xem chi tiết
                                    </a>

                                    <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                        <form method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này không?');">
                                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                            <button type="submit" name="cancel_order" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="bi bi-x-circle me-1"></i>Hủy đơn
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>