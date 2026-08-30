<?php
require_once '../../includes/database.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = $_POST['role']; // user hoặc admin
    $status = $_POST['status'];

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Kiểm tra email đã tồn tại hay chưa
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $error = "Email này đã được sử dụng bởi tài khoản khác!";
        } else {
            // Mã hóa mật khẩu bảo mật
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $name, $email, $hashed_password, $phone, $address, $role, $status);
            
            if ($stmt->execute()) {
                header("Location: index.php");
                exit();
            } else {
                $error = "Lỗi cơ sở dữ liệu: " . $conn->error;
            }
        }
    } else {
        $error = "Vui lòng nhập đầy đủ các trường bắt buộc (Họ tên, Email, Mật khẩu)!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Người dùng - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 700px;">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h4 class="fw-bold mb-4">Thêm người dùng mới thủ công</h4>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Họ và tên *</label>
                    <input type="text" name="name" class="form-control" placeholder="Nhập họ tên..." required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Mật khẩu *</label>
                        <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu..." required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" placeholder="0912345678">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Vai trò (Phân quyền)</label>
                        <select name="role" class="form-select">
                            <option value="user">User (Khách hàng)</option>
                            <option value="admin">Admin (Quản trị viên)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái tài khoản</label>
                    <select name="status" class="form-select w-50">
                        <option value="1">Hoạt động (Active)</option>
                        <option value="0">Bị khóa (Locked)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Địa chỉ</label>
                    <textarea name="address" rows="2" class="form-control" placeholder="Nhập địa chỉ..."></textarea>
                </div>
                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
                    <button type="submit" class="btn btn-dark px-4">Lưu người dùng</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>