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
            "SELECT * FROM users WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {

            $error = 'Email hoặc mật khẩu không đúng.';

        } elseif (!password_verify($password, $user['password'])) {

            $error = 'Email hoặc mật khẩu không đúng.';

        } elseif ((int)$user['status'] !== 1) {

            $error = 'Tài khoản của bạn đã bị khóa.';

        } else {

            loginUser($user);

            if ($user['role'] === 'admin') {

                header('Location: ../admin/index.php');

            } else {

                header('Location: index.php');

            }

            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

?>

<section class="auth-page">

    <div class="container">

        <!-- TIÊU ĐỀ -->
        <h2 class="auth-page-title">
            2. Đăng nhập
        </h2>


        <!-- KHUNG LOGIN -->
        <div class="auth-container">


            <!-- =========================
                 BÊN TRÁI
            ========================== -->

            <div class="auth-form">


                <!-- TAB -->
                <div class="auth-tabs">

                    <a
                        href="login.php"
                        class="auth-tab active"
                    >
                        Đăng nhập
                    </a>

                    <a
                        href="register.php"
                        class="auth-tab"
                    >
                        Đăng ký
                    </a>

                </div>


                <!-- LOGO -->
                <div class="auth-logo">
                    Mây
                </div>


                <!-- TIÊU ĐỀ -->
                <h1 class="auth-title">
                    Đăng nhập
                </h1>

                <p class="auth-subtitle">
                    Chào mừng bạn quay trở lại!
                </p>


                <!-- THÔNG BÁO LỖI -->
                <?php if ($error !== ''): ?>

                    <div class="auth-error">
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endif; ?>


                <!-- FORM -->
                <form method="POST">


                    <!-- EMAIL -->
                    <div class="auth-form-group">

                        <label for="email">
                            Email hoặc số điện thoại
                        </label>

                        <div class="auth-input">

                            <i class="bi bi-envelope"></i>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Nhập email hoặc số điện thoại"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                autocomplete="email"
                                required
                            >

                        </div>

                    </div>


                    <!-- MẬT KHẨU -->
                    <div class="auth-form-group">

                        <div class="auth-label-row">

                            <label for="password">
                                Mật khẩu
                            </label>

                            <a href="forgot-password.php">
                                Quên mật khẩu?
                            </a>

                        </div>


                        <div class="auth-input">

                            <i class="bi bi-lock-fill"></i>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Nhập mật khẩu"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                    </div>


                    <!-- GHI NHỚ -->
                    <div class="auth-remember">

                        <label>

                            <input
                                type="checkbox"
                                name="remember"
                            >

                            <span>
                                Ghi nhớ đăng nhập
                            </span>

                        </label>

                    </div>


                    <!-- BUTTON -->
                    <button
                        type="submit"
                        class="auth-login-button"
                    >
                        Đăng nhập
                    </button>

                </form>


                <!-- HOẶC -->
                <div class="auth-divider">

                    <span></span>

                    <p>
                        hoặc đăng nhập với
                    </p>

                    <span></span>

                </div>


                <!-- SOCIAL -->
                <div class="auth-social">

                    <button 
    type="button" 
    class="auth-social-button"
>
    <svg
        class="google-logo"
        viewBox="0 0 24 24"
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

    <span>
        Google
    </span>
</button>


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


                <!-- ĐĂNG KÝ -->
                <div class="auth-register">

                    <span>
                        Chưa có tài khoản?
                    </span>

                    <a href="register.php">
                        Đăng ký ngay
                    </a>

                </div>

            </div>


            <!-- =========================
                 BÊN PHẢI - ẢNH
            ========================== -->

            <div class="auth-image">

                <img
                    src="../assets/images/auth/image7.png"
                    alt="Mây Fashion Store"
                >

            </div>

        </div>

    </div>

</section>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>
