<?php
require_once '../../includes/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: index.php");
    exit();
}

// Xử lý khi Admin cập nhật thông tin và phân quyền
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = $_POST['role']; // user hoặc admin
    $status = $_POST['status'];

    $stmt_update = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ?, role = ?, status = ? WHERE id = ?");
    $stmt_update->bind_param("ssssii", $name, $phone, $address, $role, $status, $id);
    
    if ($stmt_update->execute()) {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phân quyền & Sửa Người dùng - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 700px;">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h4 class="fw-bold mb-4">Chỉnh sửa người dùng & Phân quyền</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Họ và tên *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email (Không thể thay đổi)</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Vai trò (Phân quyền)</label>
                        <select name="role" class="form-select">
                            <option value="user" <?= ($user['role'] == 'user') ? 'selected' : '' ?>>User (Khách hàng)</option>
                            <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : '' ?>>Admin (Quản trị viên)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái tài khoản</label>
                    <select name="status" class="form-select w-50">
                        <option value="1" <?= ($user['status'] == 1) ? 'selected' : '' ?>>Hoạt động (Active)</option>
                        <option value="0" <?= ($user['status'] == 0) ? 'selected' : '' ?>>Bị khóa (Locked)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Địa chỉ</label>
                    <textarea name="address" rows="2" class="form-control"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>
                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
                    <button type="submit" class="btn btn-dark px-4">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>