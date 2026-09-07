<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account = trim($_POST['account'] ?? '');
    if ($account === '') {
        $error = 'Vui lòng nhập số điện thoại hoặc email.';
    } else {
        $stmt = $conn->prepare(
            "SELECT id
             FROM users
             WHERE (email = ? OR phone = ?)
             AND status = 1
             LIMIT 1"
        );
        $stmt->bind_param("ss", $account, $account);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        if (!$user) {
            $error = 'Số điện thoại hoặc email không tồn tại.';
        } else {
            $temporaryPassword = '123456';
            $hashedPassword = password_hash($temporaryPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "UPDATE users
                 SET password = ?
                 WHERE id = ?"
            );
            $stmt->bind_param("si", $hashedPassword, $user['id']);
            if ($stmt->execute()) {
                $success = 'Khôi phục mật khẩu thành công! Mật khẩu tạm thời: 123456';
            } else {
                $error = 'Không thể khôi phục mật khẩu. Vui lòng thử lại.';
            }
            $stmt->close();
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="mb-3"><i class="bi bi-key fs-1"></i></div>
                        <h4 class="fw-bold mb-2">Quên mật khẩu</h4>
                        <p class="text-muted small mb-0">Nhập email hoặc số điện thoại để khôi phục mật khẩu</p>
                    </div>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger rounded-3 small">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success !== ''): ?>
                        <div class="alert alert-success rounded-3 small">
                            <i class="bi bi-check-circle me-2"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4">
                            <label for="account" class="form-label fw-semibold small">Số điện thoại / Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                <input
                                    type="text"
                                    id="account"
                                    name="account"
                                    class="form-control"
                                    placeholder="Nhập số điện thoại hoặc email"
                                    value="<?= htmlspecialchars($_POST['account'] ?? '') ?>"
                                    required
                                >
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 rounded-pill py-2">
                            <i class="bi bi-arrow-clockwise me-2"></i>Khôi phục mật khẩu
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="login.php" class="text-dark text-decoration-none small">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>