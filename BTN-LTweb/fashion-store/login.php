<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    } else {
        $stmt = $conn->prepare(
            "SELECT * FROM users
             WHERE email = ? OR phone = ?
             LIMIT 1"
        );
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        if (!$user) {
            $error = 'Email hoặc mật khẩu không đúng.';
        } elseif ((int)$user['status'] !== 1) {
            $error = 'Tài khoản của bạn đã bị khóa.';
        } else {
            $passwordCorrect = false;
            // Mật khẩu đã được hash
            if (password_verify($password, $user['password'])) {
                $passwordCorrect = true;
            }
            // Tài khoản cũ: mật khẩu dạng chữ thường
            elseif ($password === $user['password']) {
                $newPassword = password_hash($password,PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->bind_param("si",$newPassword, $user['id']);
                $update->execute();
                $update->close();
                $user['password'] = $newPassword;
                $passwordCorrect = true;
            }
            if (!$passwordCorrect) {
                $error = 'Email hoặc mật khẩu không đúng.';
            } else {
                loginUser($user);
                // Admin → Dashboard
                if ($user['role'] === 'admin') {
                    header('Location: ../admin/dashboard.php');
                }
                // User → Trang chủ Fashion Store
                else {
                    header('Location: index.php');
                }
                exit;
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <!-- LOGIN CARD -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <!-- LOGO -->
                        <div class="text-center mb-4">
                            <div class="fw-bold fs-2">Mây</div>
                            <div class="text-muted small">
                                FASHION STORE
                            </div>
                        </div>

                        <!-- TITLE -->
                        <div class="text-center mb-4">
                            <h3 class="fw-bold mb-2">Đăng nhập</h3>
                            <p class="text-muted mb-0">Chào mừng bạn quay trở lại!</p>
                        </div>

                        <!-- TAB -->
                        <div class="d-flex border-bottom mb-4">
                            <a href="login.php" class="flex-fill text-center text-dark text-decoration-none fw-semibold border-bottom border-dark border-2 pb-2">
                                Đăng nhập
                            </a>

                            <a href="register.php" class="flex-fill text-center text-muted text-decoration-none pb-2">
                                Đăng ký
                            </a>
                        </div>

                        <!-- ERROR -->
                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger py-2 small rounded-3">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <!-- FORM -->
                        <form method="POST">
                            <!-- EMAIL / PHONE -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small">Email hoặc số điện thoại</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                    <input
                                        type="text"
                                        id="email"
                                        name="email"
                                        class="form-control"
                                        placeholder="Nhập email hoặc số điện thoại"
                                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                        autocomplete="email"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- PASSWORD -->
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold small">Mật khẩu</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Nhập mật khẩu"
                                        autocomplete="current-password"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- REMEMBER + FORGOT -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="remember"
                                        id="remember"
                                    >

                                    <label class="form-check-label small" for="remember">Ghi nhớ đăng nhập</label>
                                </div>

                                <a href="forgot-password.php" class="text-dark small text-decoration-none">
                                    Quên mật khẩu?
                                </a>
                            </div>

                            <!-- BUTTON -->
                            <button type="submit" class="btn btn-dark w-100 py-2 rounded-3 fw-semibold">Đăng nhập</button>
                        </form>

                        <!-- DIVIDER -->
                        <div class="d-flex align-items-center my-4">
                            <hr class="flex-grow-1">
                            <span class="text-muted small mx-3">hoặc đăng nhập với</span>
                            <hr class="flex-grow-1">
                        </div>

                        <!-- SOCIAL -->
                        <div class="row g-2">
                            <!-- GOOGLE -->
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100 py-2 rounded-3">
                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        class="me-1"
                                        aria-hidden="true"
                                    >
                                        <path
                                            fill="#EA4335"
                                            d="M21.35 12.27c0-.79-.07-1.55-.21-2.27H12v4.3h5.22a4.47 4.47 0 0 1-1.94 2.94v2.45h3.14c1.84-1.69 2.93-4.18 2.93-7.42z"
                                        />

                                        <path
                                            fill="#4285F4"
                                            d="M12 21.8c2.63 0 4.84-.87 6.45-2.36l-3.14-2.45c-.87.58-1.98.93-3.31.93-2.54 0-4.69-1.72-5.46-4.03H3.3v2.53A9.75 9.75 0 0 0 12 21.8z"
                                        />

                                        <path
                                            fill="#FBBC05"
                                            d="M6.54 13.89A5.86 5.86 0 0 1 6.23 12c0-.66.11-1.3.31-1.89V7.58H3.3A9.75 9.75 0 0 0 2.25 12c0 1.57.38 3.05 1.05 4.42l3.24-2.53z"
                                        />

                                        <path
                                            fill="#34A853"
                                            d="M12 6.08c1.43 0 2.71.49 3.72 1.45l2.79-2.79C16.84 3.16 14.63 2.2 12 2.2a9.75 9.75 0 0 0-8.7 5.38l3.24 2.53C7.31 7.8 9.46 6.08 12 6.08z"
                                        />
                                    </svg>
                                    Google
                                </button>
                            </div>

                            <!-- FACEBOOK -->
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100 py-2 rounded-3">
                                    <i class="bi bi-facebook me-1"></i>Facebook
                                </button>
                            </div>
                        </div>

                        <!-- REGISTER -->
                        <div class="text-center mt-4">
                            <span class="text-muted small">Chưa có tài khoản?</span>
                            <a href="register.php" class="text-dark fw-semibold small text-decoration-none">
                                Đăng ký ngay
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>