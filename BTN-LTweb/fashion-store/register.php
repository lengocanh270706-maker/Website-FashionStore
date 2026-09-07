<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';
$success = '';
$name = '';
$email = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $agree = isset($_POST['agree']);
    /* KIỂM TRA DỮ LIỆU */
    if ($name === '' || $email === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } elseif (!$agree) {
        $error = 'Vui lòng đồng ý với Điều khoản & Chính sách bảo mật.';
    } else {
        /* KIỂM TRA EMAIL ĐÃ TỒN TẠI */
        $stmt = $conn->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = 'Email này đã được sử dụng.';
            $stmt->close();
        } else {
            $stmt->close();
            /* MÃ HÓA MẬT KHẨU */
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );
            /* THÊM USER */
            $stmt = $conn->prepare(
                "INSERT INTO users
                (name, email, password, phone, address, role, status)
                VALUES (?, ?, ?, ?, ?, 'user', 1)"
            );
            $stmt->bind_param(
                "sssss",
                $name,
                $email,
                $hashedPassword,
                $phone,
                $address
            );
            if ($stmt->execute()) {
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay.';
                $name = '';
                $email = '';
                $phone = '';
                $address = '';
            } else {
                $error = 'Đăng ký thất bại. Vui lòng thử lại.';
            }
            $stmt->close();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-9 col-lg-6">
                <!-- REGISTER CARD -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <!-- LOGO -->
                        <div class="text-center mb-4">
                            <div class="fw-bold fs-2">Mây</div>
                            <div class="text-muted small">FASHION STORE</div>
                        </div>

                        <!-- TITLE -->
                        <div class="text-center mb-4">
                            <h3 class="fw-bold mb-2">Đăng ký</h3>
                            <p class="text-muted mb-0">Tạo tài khoản mới để bắt đầu!</p>
                        </div>

                        <!-- TABS -->
                        <div class="d-flex border-bottom mb-4">
                            <a href="login.php" class="flex-fill text-center text-muted text-decoration-none pb-2">
                                Đăng nhập
                            </a>

                            <a href="register.php" class="flex-fill text-center text-dark text-decoration-none fw-semibold border-bottom border-dark border-2 pb-2">
                                Đăng ký
                            </a>
                        </div>

                        <!-- ERROR -->
                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger rounded-3 py-2 small">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <!-- SUCCESS -->
                        <?php if ($success !== ''): ?>
                            <div class="alert alert-success rounded-3 py-2 small">
                                <i class="bi bi-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                                <div class="mt-2">
                                    <a href="login.php" class="text-success fw-semibold text-decoration-none">
                                        Đăng nhập ngay →
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- REGISTER FORM -->
                        <form method="POST">
                            <!-- NAME -->
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold small">Họ và tên *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        class="form-control"
                                        placeholder="Nhập họ và tên"
                                        value="<?= htmlspecialchars($name) ?>"
                                        autocomplete="name"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- EMAIL -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small">Email *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        class="form-control"
                                        placeholder="Nhập email"
                                        value="<?= htmlspecialchars($email) ?>"
                                        autocomplete="email"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- PHONE -->
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-semibold small">Số điện thoại</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                    <input
                                        type="text"
                                        id="phone"
                                        name="phone"
                                        class="form-control"
                                        placeholder="Nhập số điện thoại"
                                        value="<?= htmlspecialchars($phone) ?>"
                                        autocomplete="tel"
                                    >
                                </div>
                            </div>

                            <!-- ADDRESS -->
                            <div class="mb-3">
                                <label for="address" class="form-label fw-semibold small">Địa chỉ</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                                    <input
                                        type="text"
                                        id="address"
                                        name="address"
                                        class="form-control"
                                        placeholder="Nhập địa chỉ"
                                        value="<?= htmlspecialchars($address) ?>"
                                        autocomplete="street-address"
                                    >
                                </div>
                            </div>

                            <!-- PASSWORD -->
                            <div class="mb-3">
                                <label for="register-password" class="form-label fw-semibold small">Mật khẩu *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                    <input
                                        type="password"
                                        id="register-password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Nhập mật khẩu"
                                        autocomplete="new-password"
                                        required
                                    >

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="togglePassword('register-password', this)"
                                        aria-label="Hiện mật khẩu"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>

                                <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự. </div>
                            </div>

                            <!-- CONFIRM PASSWORD -->
                            <div class="mb-3">
                                <label for="confirm-password" class="form-label fw-semibold small">Xác nhận mật khẩu *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                    <input
                                        type="password"
                                        id="confirm-password"
                                        name="confirm_password"
                                        class="form-control"
                                        placeholder="Nhập lại mật khẩu"
                                        autocomplete="new-password"
                                        required
                                    >

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="togglePassword('confirm-password', this)"
                                        aria-label="Hiện mật khẩu"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- AGREE -->
                            <div class="form-check mb-4">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="agree"
                                    value="1"
                                    id="agree"
                                    <?= isset($_POST['agree']) ? 'checked' : '' ?>
                                >

                                <label class="form-check-label small" for="agree">
                                    Tôi đồng ý với
                                    <a href="#" onclick="return false;" class="text-dark fw-semibold text-decoration-none">
                                        Điều khoản & Chính sách bảo mật
                                    </a>
                                </label>
                            </div>

                            <!-- REGISTER BUTTON -->
                            <button type="submit" class="btn btn-dark w-100 py-2 rounded-3 fw-semibold">
                                Đăng ký
                            </button>
                        </form>

                        <!-- DIVIDER -->
                        <div class="d-flex align-items-center my-4">
                            <hr class="flex-grow-1">
                            <span class="text-muted small mx-3">hoặc đăng ký với </span>
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

                        <!-- LOGIN -->
                        <div class="text-center mt-4">
                            <span class="text-muted small">Đã có tài khoản?</span>
                            <a href="login.php" class="text-dark fw-semibold small text-decoration-none">Đăng nhập ngay</a>
                        </div>
                    </div>
                </div>

                <!-- IMAGE -->
                <div class="text-center mt-4">
                    <img
                        src="../assets/images/auth/image7.png"
                        alt="Mây Fashion Store"
                        class="img-fluid rounded-4 shadow-sm"
                        style="max-height:220px;object-fit:cover;"
                    >
                </div>
            </div>
        </div>
    </div>
</section>

<script src="../assets/js/script.js"></script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>