<?php
require_once '../../includes/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

if (isset($_POST['update_role'])) {
    $role = $_POST['role'];

    if ($role == 'user' || $role == 'admin') {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $id);
        $stmt->execute();
    }
    header("Location: view.php?id=$id");
    exit();
}
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xem người dùng - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light p-5">
<div class="container" style="max-width:700px">
    <div class="card border-0 shadow-sm p-4 rounded-4">
        <h4 class="fw-bold mb-4">Thông tin người dùng</h4>
        <div class="mb-3">
            <label class="fw-bold">Họ và tên</label>
            <div class="form-control bg-light"><?= htmlspecialchars($user['name']) ?></div>
        </div>

        <div class="mb-3">
            <label class="fw-bold">Email</label>
            <div class="form-control bg-light"><?= htmlspecialchars($user['email']) ?></div>
        </div>

        <div class="mb-3">
            <label class="fw-bold">Số điện thoại</label>
            <div class="form-control bg-light"><?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?></div>
        </div>

        <div class="mb-3">
            <label class="fw-bold">Địa chỉ</label>
            <div class="form-control bg-light"><?= htmlspecialchars($user['address'] ?? 'Chưa cập nhật') ?></div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold">Vai trò</label>
                <select name="role" form="roleForm" class="form-select">
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="fw-bold">Trạng thái</label>
                <div class="form-control bg-light">
                    <?= $user['status'] == 1 ? 'Hoạt động' : 'Bị khóa' ?>
                </div>
            </div>
        </div>

        <form method="POST" id="roleForm"></form>

        <div class="text-end mt-3">
            <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
            <button type="submit" name="update_role" form="roleForm" class="btn btn-dark px-4">Lưu</button>
        </div>
    </div>
</div>
</body>
</html>