<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = currentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } elseif ($currentPassword === $newPassword) {
        $error = 'Mật khẩu mới phải khác mật khẩu hiện tại.';
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $account = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$account) {
            $error = 'Không tìm thấy tài khoản.';
        } elseif (!password_verify($currentPassword, $account['password'])) {
            $error = 'Mật khẩu hiện tại không đúng.';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $user['id']);
            if ($stmt->execute()) {
                $success = 'Đổi mật khẩu thành công!';
            } else {
                $error = 'Không thể đổi mật khẩu. Vui lòng thử lại.';
            }
            $stmt->close();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<div class="container-fluid py-4">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Đổi mật khẩu</h4>
        <small class="text-muted">Cập nhật mật khẩu để bảo vệ tài khoản của bạn</small>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 px-4 pt-4">
                    <h6 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2"></i>Thay đổi mật khẩu</h6>
                    <small class="text-muted">Nhập mật khẩu hiện tại và mật khẩu mới</small>
                </div>

                <div class="card-body p-4">
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger border-0 rounded-3 d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-circle"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($success !== ''): ?>
                        <div class="alert alert-success border-0 rounded-3 d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle"></i>
                            <span><?= htmlspecialchars($success) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4">
                            <label for="current_password" class="form-label fw-semibold">Mật khẩu hiện tại</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    class="form-control"
                                    placeholder="Nhập mật khẩu hiện tại"
                                    autocomplete="current-password"
                                    required
                                >

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="toggleChangePassword('current_password', this)"
                                    aria-label="Hiện mật khẩu"
                                >
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="new_password" class="form-label fw-semibold">Mật khẩu mới</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                <input
                                    type="password"
                                    id="new_password"
                                    name="new_password"
                                    class="form-control"
                                    placeholder="Nhập mật khẩu mới"
                                    minlength="6"
                                    autocomplete="new-password"
                                    required
                                >

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="toggleChangePassword('new_password', this)"
                                    aria-label="Hiện mật khẩu"
                                >
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="form-control"
                                    placeholder="Nhập lại mật khẩu mới"
                                    minlength="6"
                                    autocomplete="new-password"
                                    required
                                >

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="toggleChangePassword('confirm_password', this)"
                                    aria-label="Hiện mật khẩu"
                                >
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="profile.php" class="btn btn-outline-dark rounded-pill px-4">
                                <i class="bi bi-arrow-left me-2"></i>Quay lại
                            </a>

                            <button type="submit" class="btn btn-dark rounded-pill px-4">
                                <i class="bi bi-check2 me-2"></i>Cập nhật mật khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div
                            class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                            style="width:50px;height:50px;"
                        >
                            <i class="bi bi-shield-lock fs-4"></i>
                        </div>

                        <div>
                            <h6 class="fw-bold mb-1">Bảo mật tài khoản</h6>
                            <small class="text-muted">Lưu ý khi đặt mật khẩu</small>
                        </div>
                    </div>

                    <div class="small text-muted">
                        <div class="d-flex gap-2 mb-3">
                            <i class="bi bi-check-circle text-dark"></i>
                            <span>Mật khẩu có ít nhất 6 ký tự.</span>
                        </div>

                        <div class="d-flex gap-2 mb-3">
                            <i class="bi bi-check-circle text-dark"></i>
                            <span>Nên kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt.</span>
                        </div>

                        <div class="d-flex gap-2">
                            <i class="bi bi-check-circle text-dark"></i>
                            <span>Không sử dụng thông tin cá nhân làm mật khẩu.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php require_once __DIR__ . '/../includes/footer.php'; ?>