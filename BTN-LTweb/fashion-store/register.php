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


    /* =====================================================
       KIỂM TRA DỮ LIỆU
    ===================================================== */

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


        /* =================================================
           KIỂM TRA EMAIL ĐÃ TỒN TẠI
        ================================================== */

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


            /* =================================================
               MÃ HÓA MẬT KHẨU
            ================================================== */

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /* =================================================
               THÊM USER
            ================================================= */

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


<!-- =====================================================
     REGISTER PAGE
===================================================== -->

<section class="auth-page">

    <div class="container">


        <!-- =================================================
             PAGE TITLE
        ================================================== -->

        <h2 class="auth-page-title">
            2. Đăng nhập
        </h2>


        <!-- =================================================
             REGISTER CONTAINER
        ================================================== -->

        <div class="auth-container">


            <!-- =================================================
                 BÊN TRÁI - FORM ĐĂNG KÝ
            ================================================== -->

            <div class="auth-form">


                <!-- =================================================
                     TABS
                ================================================== -->

                <div class="auth-tabs">

                    <a
                        href="login.php"
                        class="auth-tab"
                    >
                        Đăng nhập
                    </a>

                    <a
                        href="register.php"
                        class="auth-tab active"
                    >
                        Đăng ký
                    </a>

                </div>


                <!-- =================================================
                     LOGO
                ================================================== -->

                <div class="auth-logo">
                    Mây
                </div>


                <!-- =================================================
                     TITLE
                ================================================== -->

                <h1 class="auth-title">
                    Đăng ký
                </h1>

                <p class="auth-subtitle">
                    Tạo tài khoản mới để bắt đầu!
                </p>


                <!-- =================================================
                     ERROR
                ================================================== -->

                <?php if ($error !== ''): ?>

                    <div class="auth-error">
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endif; ?>


                <!-- =================================================
                     SUCCESS
                ================================================== -->

                <?php if ($success !== ''): ?>

                    <div class="auth-success">
                        <?= htmlspecialchars($success) ?>
                    </div>

                <?php endif; ?>


                <!-- =================================================
                     REGISTER FORM
                ================================================== -->

                <form method="POST">


                    <!-- =================================================
                         HỌ VÀ TÊN
                    ================================================== -->

                    <div class="auth-form-group">

                        <label for="name">
                            Họ và tên *
                        </label>

                        <div class="auth-input">

                            <i class="bi bi-person"></i>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                placeholder="Nhập họ và tên"
                                value="<?= htmlspecialchars($name) ?>"
                                autocomplete="name"
                                required
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         EMAIL
                    ================================================== -->

                    <div class="auth-form-group">

                        <label for="email">
                            Email *
                        </label>

                        <div class="auth-input">

                            <i class="bi bi-envelope"></i>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Nhập email"
                                value="<?= htmlspecialchars($email) ?>"
                                autocomplete="email"
                                required
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         SỐ ĐIỆN THOẠI
                    ================================================== -->

                    <div class="auth-form-group">

                        <label for="phone">
                            Số điện thoại
                        </label>

                        <div class="auth-input">

                            <i class="bi bi-telephone"></i>

                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                placeholder="Nhập số điện thoại"
                                value="<?= htmlspecialchars($phone) ?>"
                                autocomplete="tel"
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         MẬT KHẨU
                    ================================================== -->

                    <div class="auth-form-group">

                        <label for="register-password">
                            Mật khẩu *
                        </label>

                        <div class="auth-input">

                            <i class="bi bi-lock-fill"></i>

                            <input
                                type="password"
                                id="register-password"
                                name="password"
                                placeholder="Nhập mật khẩu"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="auth-password-toggle"
                                onclick="togglePassword('register-password', this)"
                                aria-label="Hiện mật khẩu"
                            >
                                <i class="bi bi-eye"></i>
                            </button>

                        </div>

                    </div>


                    <!-- =================================================
                         XÁC NHẬN MẬT KHẨU
                    ================================================== -->

                    <div class="auth-form-group">

                        <label for="confirm-password">
                            Xác nhận mật khẩu *
                        </label>

                        <div class="auth-input">

                            <i class="bi bi-lock-fill"></i>

                            <input
                                type="password"
                                id="confirm-password"
                                name="confirm_password"
                                placeholder="Nhập lại mật khẩu"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="auth-password-toggle"
                                onclick="togglePassword('confirm-password', this)"
                                aria-label="Hiện mật khẩu"
                            >
                                <i class="bi bi-eye"></i>
                            </button>

                        </div>

                    </div>


                    <!-- =================================================
                         ĐIỀU KHOẢN
                    ================================================== -->

                    <div class="auth-agree">

                        <label>

                            <input
                                type="checkbox"
                                name="agree"
                                value="1"
                                <?= isset($_POST['agree']) ? 'checked' : '' ?>
                            >

                            <span>
                                Tôi đồng ý với
                                <a href="#" onclick="return false;">
                                    Điều khoản & Chính sách bảo mật
                                </a>
                            </span>

                        </label>

                    </div>


                    <!-- =================================================
                         REGISTER BUTTON
                    ================================================== -->

                    <button
                        type="submit"
                        class="auth-login-button"
                    >
                        Đăng ký
                    </button>

                </form>


                <!-- =================================================
                     DIVIDER
                ================================================== -->

                <div class="auth-divider">

                    <span></span>

                    <p>
                        hoặc đăng ký với
                    </p>

                    <span></span>

                </div>


                <!-- =================================================
                     SOCIAL REGISTER
                ================================================== -->

                <div class="auth-social">


                    <!-- =================================================
                         GOOGLE - LOGO 4 MÀU CHÍNH THỨC
                    ================================================== -->

                    <button
                        type="button"
                        class="auth-social-button"
                    >

                        <svg
                            class="google-logo"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >

                            <!-- RED -->
                            <path
                                fill="#EA4335"
                                d="M21.35 12.27c0-.79-.07-1.55-.21-2.27H12v4.3h5.22a4.47 4.47 0 0 1-1.94 2.94v2.45h3.14c1.84-1.69 2.93-4.18 2.93-7.42z"
                            />

                            <!-- BLUE -->
                            <path
                                fill="#4285F4"
                                d="M12 21.8c2.63 0 4.84-.87 6.45-2.36l-3.14-2.45c-.87.58-1.98.93-3.31.93-2.54 0-4.69-1.72-5.46-4.03H3.3v2.53A9.75 9.75 0 0 0 12 21.8z"
                            />

                            <!-- YELLOW -->
                            <path
                                fill="#FBBC05"
                                d="M6.54 13.89A5.86 5.86 0 0 1 6.23 12c0-.66.11-1.3.31-1.89V7.58H3.3A9.75 9.75 0 0 0 2.25 12c0 1.57.38 3.05 1.05 4.42l3.24-2.53z"
                            />

                            <!-- GREEN -->
                            <path
                                fill="#34A853"
                                d="M12 6.08c1.43 0 2.71.49 3.72 1.45l2.79-2.79C16.84 3.16 14.63 2.2 12 2.2a9.75 9.75 0 0 0-8.7 5.38l3.24 2.53C7.31 7.8 9.46 6.08 12 6.08z"
                            />

                        </svg>

                        <span>
                            Google
                        </span>

                    </button>


                    <!-- =================================================
                         FACEBOOK
                    ================================================== -->

                    <button
                        type="button"
                        class="auth-social-button"
                    >

                        <i class="bi bi-facebook"></i>

                        <span>
                            Facebook
                        </span>

                    </button>

                </div>


                <!-- =================================================
                     LOGIN
                ================================================== -->

                <div class="auth-register">

                    <span>
                        Đã có tài khoản?
                    </span>

                    <a href="login.php">
                        Đăng nhập ngay
                    </a>

                </div>


            </div>


            <!-- =================================================
                 BÊN PHẢI - ẢNH
            ================================================== -->

            <div class="auth-image">

                <img
                    src="../assets/images/auth/image7.png"
                    alt="Mây Fashion Store"
                >

            </div>


        </div>

    </div>

</section>


<!-- =====================================================
     PASSWORD TOGGLE
===================================================== -->

<script>

function togglePassword(inputId, button) {

    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');

    if (input.type === 'password') {

        input.type = 'text';

        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');

        button.setAttribute(
            'aria-label',
            'Ẩn mật khẩu'
        );

    } else {

        input.type = 'password';

        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');

        button.setAttribute(
            'aria-label',
            'Hiện mật khẩu'
        );
    }

}

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>
