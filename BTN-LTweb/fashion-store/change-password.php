<?php

require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user = currentUser();

$error = '';
$success = '';

/* =========================================================
   XỬ LÝ ĐỔI MẬT KHẨU
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (
        $currentPassword === '' ||
        $newPassword === '' ||
        $confirmPassword === ''
    ) {

        $error = 'Vui lòng nhập đầy đủ thông tin.';

    } elseif (strlen($newPassword) < 6) {

        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';

    } elseif ($newPassword !== $confirmPassword) {

        $error = 'Mật khẩu xác nhận không khớp.';

    } elseif ($currentPassword === $newPassword) {

        $error = 'Mật khẩu mới phải khác mật khẩu hiện tại.';

    } else {

        /* Lấy mật khẩu hiện tại từ database */

        $stmt = $conn->prepare(
            "SELECT password FROM users WHERE id = ? LIMIT 1"
        );

        $stmt->bind_param("i", $user['id']);

        $stmt->execute();

        $result = $stmt->get_result();

        $account = $result->fetch_assoc();

        $stmt->close();


        if (!$account) {

            $error = 'Không tìm thấy tài khoản.';

        } elseif (
            !password_verify(
                $currentPassword,
                $account['password']
            )
        ) {

            $error = 'Mật khẩu hiện tại không đúng.';

        } else {

            /* Mã hóa mật khẩu mới */

            $hashedPassword = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );


            /* Cập nhật database */

            $stmt = $conn->prepare(
                "UPDATE users
                 SET password = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "si",
                $hashedPassword,
                $user['id']
            );


            if ($stmt->execute()) {

                $success = 'Đổi mật khẩu thành công!';

            } else {

                $error = 'Không thể đổi mật khẩu. Vui lòng thử lại.';
            }

            $stmt->close();
        }
    }
}


/* =========================================================
   HEADER + MENU
========================================================= */

require_once __DIR__ . '/../includes/header.php';

require_once __DIR__ . '/../includes/menu.php';

?>


<!-- =========================================================
     CHANGE PASSWORD PAGE
========================================================= -->

<section class="change-password-page">

    <div class="change-password-container">


        <!-- =====================================================
             BREADCRUMB
        ====================================================== -->

        <div class="change-password-breadcrumb">

            <a href="index.php">
                Trang chủ
            </a>

            <span>›</span>

            <a href="profile.php">
                Tài khoản
            </a>

            <span>›</span>

            <strong>
                Đổi mật khẩu
            </strong>

        </div>


        <!-- =====================================================
             MAIN LAYOUT
        ====================================================== -->

        <div class="change-password-layout">


            <!-- =================================================
                 SIDEBAR
            ================================================== -->

            <aside class="change-password-sidebar">


                <!-- USER -->

                <div class="change-password-user">

                    <div class="change-password-avatar">

                        <i class="bi bi-person"></i>

                    </div>


                    <div class="change-password-user-info">

                        <h3>
                            <?= htmlspecialchars($user['name'] ?? 'Khách hàng') ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật SĐT') ?>
                        </p>

                        <span>
                            <i class="bi bi-patch-check"></i>
                            Thành viên
                        </span>

                    </div>

                </div>


                <!-- SIDEBAR MENU -->

                <nav class="change-password-sidebar-menu">


                    <!-- THÔNG TIN TÀI KHOẢN -->

                    <a
                        href="profile.php"
                        class="change-password-sidebar-link"
                    >

                        <i class="bi bi-person"></i>

                        <span>
                            Thông tin tài khoản
                        </span>

                    </a>


                    <!-- ĐƠN HÀNG -->

                    <a
                        href="orders.php"
                        class="change-password-sidebar-link"
                    >

                        <i class="bi bi-bag"></i>

                        <span>
                            Đơn hàng của tôi
                        </span>

                    </a>


                    <!-- ĐỊA CHỈ -->

                    <a
                        href="profile.php"
                        class="change-password-sidebar-link"
                    >

                        <i class="bi bi-geo-alt"></i>

                        <span>
                            Địa chỉ của tôi
                        </span>

                    </a>


                    <!-- ĐỔI MẬT KHẨU -->

                    <a
                        href="change-password.php"
                        class="change-password-sidebar-link active"
                    >

                        <i class="bi bi-key"></i>

                        <span>
                            Đổi mật khẩu
                        </span>

                    </a>


                    <!-- ĐĂNG XUẤT -->

                    <a
                        href="logout.php"
                        class="change-password-sidebar-link"
                    >

                        <i class="bi bi-box-arrow-right"></i>

                        <span>
                            Đăng xuất
                        </span>

                    </a>

                </nav>

            </aside>


            <!-- =================================================
                 MAIN CONTENT
            ================================================== -->

            <main class="change-password-main">


                <!-- PAGE TITLE -->

                <div class="change-password-title">

                    <h1>
                        ĐỔI MẬT KHẨU
                    </h1>

                    <p>
                        Cập nhật mật khẩu để bảo vệ tài khoản của bạn.
                    </p>

                </div>


                <!-- CONTENT CARD -->

                <div class="change-password-content">


                    <!-- FORM -->

                    <div class="change-password-form-box">


                        <!-- ERROR -->

                        <?php if ($error !== ''): ?>

                            <div class="change-password-alert change-password-alert-error">

                                <i class="bi bi-exclamation-circle"></i>

                                <span>
                                    <?= htmlspecialchars($error) ?>
                                </span>

                            </div>

                        <?php endif; ?>


                        <!-- SUCCESS -->

                        <?php if ($success !== ''): ?>

                            <div class="change-password-alert change-password-alert-success">

                                <i class="bi bi-check-circle"></i>

                                <span>
                                    <?= htmlspecialchars($success) ?>
                                </span>

                            </div>

                        <?php endif; ?>


                        <form method="POST">


                            <!-- MẬT KHẨU HIỆN TẠI -->

                            <div class="change-password-group">

                                <label for="current_password">
                                    Mật khẩu hiện tại
                                </label>


                                <div class="change-password-input">

                                    <i class="bi bi-lock"></i>

                                    <input
                                        type="password"
                                        id="current_password"
                                        name="current_password"
                                        placeholder="Nhập mật khẩu hiện tại"
                                        autocomplete="current-password"
                                        required
                                    >


                                    <button
                                        type="button"
                                        class="change-password-toggle"
                                        onclick="toggleChangePassword(
                                            'current_password',
                                            this
                                        )"
                                        aria-label="Hiện mật khẩu"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </button>

                                </div>

                            </div>


                            <!-- MẬT KHẨU MỚI -->

                            <div class="change-password-group">

                                <label for="new_password">
                                    Mật khẩu mới
                                </label>


                                <div class="change-password-input">

                                    <i class="bi bi-lock"></i>

                                    <input
                                        type="password"
                                        id="new_password"
                                        name="new_password"
                                        placeholder="Nhập mật khẩu mới"
                                        minlength="6"
                                        autocomplete="new-password"
                                        required
                                    >


                                    <button
                                        type="button"
                                        class="change-password-toggle"
                                        onclick="toggleChangePassword(
                                            'new_password',
                                            this
                                        )"
                                        aria-label="Hiện mật khẩu"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </button>

                                </div>

                            </div>


                            <!-- XÁC NHẬN MẬT KHẨU -->

                            <div class="change-password-group">

                                <label for="confirm_password">
                                    Xác nhận mật khẩu mới
                                </label>


                                <div class="change-password-input">

                                    <i class="bi bi-lock"></i>

                                    <input
                                        type="password"
                                        id="confirm_password"
                                        name="confirm_password"
                                        placeholder="Nhập lại mật khẩu mới"
                                        minlength="6"
                                        autocomplete="new-password"
                                        required
                                    >


                                    <button
                                        type="button"
                                        class="change-password-toggle"
                                        onclick="toggleChangePassword(
                                            'confirm_password',
                                            this
                                        )"
                                        aria-label="Hiện mật khẩu"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </button>

                                </div>

                            </div>


                            <!-- BUTTON -->

                            <button
                                type="submit"
                                class="change-password-submit"
                            >

                                Cập nhật mật khẩu

                            </button>


                            <!-- BACK -->

                            <div class="change-password-back">

                                <a href="profile.php">
                                    Quay lại hồ sơ
                                </a>

                            </div>

                        </form>

                    </div>


                    <!-- =================================================
                         RIGHT NOTE
                    ================================================== -->

                    <aside class="change-password-note">

                        <h2>
                            Lưu ý
                        </h2>


                        <ul>

                            <li>
                                Mật khẩu phải có ít nhất 6 ký tự.
                            </li>

                            <li>
                                Bao gồm chữ hoa, chữ thường,
                                số và ký tự đặc biệt.
                            </li>

                            <li>
                                Không nên dùng thông tin cá nhân
                                để đặt mật khẩu.
                            </li>

                        </ul>


                        <!-- LOCK ICON -->

                        <div class="change-password-lock">

                            <i class="bi bi-lock"></i>

                            <span class="lock-stars">
                                • • • •
                            </span>

                        </div>

                    </aside>


                </div>

            </main>

        </div>

    </div>

</section>


<!-- =========================================================
     STYLE RIÊNG CHO TRANG ĐỔI MẬT KHẨU
     
     QUAN TRỌNG:
     Các class đều bắt đầu bằng change-password-
     để KHÔNG đụng CSS của Login/Register/Profile.
========================================================= -->

<style>

/* =========================================================
   PAGE
========================================================= */

.change-password-page {

    width: 100%;

    background: #fff;

    color: #111;

    font-family:
        "Montserrat",
        Arial,
        sans-serif;
}


.change-password-page *,
.change-password-page *::before,
.change-password-page *::after {

    box-sizing: border-box;
}


/* =========================================================
   CONTAINER
========================================================= */

.change-password-container {

    width: min(
        1120px,
        calc(100% - 48px)
    );

    margin: 0 auto;

    padding: 0 0 60px;
}


/* =========================================================
   BREADCRUMB
========================================================= */

.change-password-breadcrumb {

    display: flex;

    align-items: center;

    gap: 8px;

    min-height: 50px;

    font-size: 9px;

    color: #999;

    border-bottom: 1px solid #f0f0f0;
}


.change-password-breadcrumb a {

    color: #777;

    text-decoration: none;
}


.change-password-breadcrumb a:hover {

    color: #111;
}


.change-password-breadcrumb span {

    color: #bbb;

    font-size: 13px;
}


.change-password-breadcrumb strong {

    color: #111;

    font-weight: 600;
}


/* =========================================================
   MAIN LAYOUT
========================================================= */

.change-password-layout {

    display: grid;

    grid-template-columns:
        235px
        minmax(0, 1fr);

    gap: 45px;

    align-items: start;

    padding-top: 30px;
}


/* =========================================================
   SIDEBAR
========================================================= */

.change-password-sidebar {

    width: 100%;
}


/* =========================================================
   USER CARD
========================================================= */

.change-password-user {

    padding: 0 8px 25px;

    margin-bottom: 14px;

    border-bottom: 1px solid #e8e8e8;
}


.change-password-user {

    display: flex;

    align-items: center;

    gap: 13px;
}


.change-password-avatar {

    width: 62px;

    height: 62px;

    min-width: 62px;

    border-radius: 50%;

    border: 1px solid #dedede;

    background: #fafafa;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;
}


.change-password-avatar i {

    font-size: 26px;

    color: #333;
}


.change-password-user-info {

    min-width: 0;
}


.change-password-user-info h3 {

    margin: 0 0 6px;

    font-size: 13px;

    line-height: 1.35;

    font-weight: 600;

    color: #111;

    word-break: break-word;
}


.change-password-user-info p {

    margin: 0 0 6px;

    font-size: 10px;

    color: #555;
}


.change-password-user-info span {

    display: inline-flex;

    align-items: center;

    gap: 4px;

    font-size: 9px;

    color: #927b42;
}


.change-password-user-info span i {

    font-size: 9px;
}


/* =========================================================
   SIDEBAR MENU
========================================================= */

.change-password-sidebar-menu {

    width: 100%;
}


.change-password-sidebar-link {

    width: 100%;

    min-height: 49px;

    padding: 0 12px;

    display: flex;

    align-items: center;

    gap: 13px;

    border-bottom: 1px solid #eeeeee;

    color: #555;

    text-decoration: none;

    font-size: 11px;

    transition:
        background .2s ease,
        color .2s ease;
}


.change-password-sidebar-link i {

    width: 18px;

    text-align: center;

    font-size: 15px;

    color: #555;
}


.change-password-sidebar-link:hover {

    background: #fafafa;

    color: #111;
}


.change-password-sidebar-link:hover i {

    color: #111;
}


.change-password-sidebar-link.active {

    background: #f5f5f5;

    color: #111;

    font-weight: 600;
}


.change-password-sidebar-link.active i {

    color: #111;
}


/* =========================================================
   MAIN
========================================================= */

.change-password-main {

    min-width: 0;
}


/* =========================================================
   TITLE
========================================================= */

.change-password-title {

    margin-bottom: 28px;
}


.change-password-title h1 {

    margin: 0 0 7px;

    font-family:
        "Playfair Display",
        Georgia,
        serif;

    font-size: 26px;

    line-height: 1.25;

    font-weight: 600;

    color: #111;
}


.change-password-title p {

    margin: 0;

    font-size: 10px;

    color: #888;
}


/* =========================================================
   CONTENT
========================================================= */

.change-password-content {

    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        265px;

    gap: 28px;

    align-items: start;
}


/* =========================================================
   FORM BOX
========================================================= */

.change-password-form-box {

    width: 100%;

    border: 1px solid #dedede;

    background: #fff;

    padding: 25px 28px 22px;
}


/* =========================================================
   ALERT
========================================================= */

.change-password-alert {

    width: 100%;

    display: flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 18px;

    padding: 10px 12px;

    border: 1px solid #dedede;

    font-size: 10px;

    line-height: 1.4;
}


.change-password-alert-error {

    background: #fafafa;

    color: #333;
}


.change-password-alert-success {

    background: #fafafa;

    color: #333;
}


.change-password-alert i {

    font-size: 13px;
}


/* =========================================================
   FORM GROUP
========================================================= */

.change-password-group {

    margin-bottom: 19px;
}


.change-password-group label {

    display: block;

    margin: 0 0 7px;

    font-size: 10px;

    font-weight: 500;

    color: #222;
}


/* =========================================================
   INPUT
========================================================= */

.change-password-input {

    position: relative;

    width: 100%;

    height: 43px;

    display: flex;

    align-items: center;

    border: 1px solid #ddd;

    background: #fff;

    transition:
        border-color .2s ease,
        box-shadow .2s ease;
}


.change-password-input:focus-within {

    border-color: #999;

    box-shadow:
        0 0 0 2px
        rgba(0, 0, 0, .025);
}


.change-password-input > i:first-child {

    width: 40px;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    color: #888;

    font-size: 13px;
}


.change-password-input input {

    width: 100%;

    height: 41px;

    min-width: 0;

    padding: 0 8px 0 0;

    border: none;

    outline: none;

    background: transparent;

    color: #222;

    font-family:
        "Montserrat",
        Arial,
        sans-serif;

    font-size: 10px;
}


.change-password-input input::placeholder {

    color: #aaa;
}


/* =========================================================
   EYE BUTTON
========================================================= */

.change-password-toggle {

    width: 40px;

    height: 41px;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    padding: 0;

    border: none;

    background: transparent;

    color: #777;

    cursor: pointer;
}


.change-password-toggle:hover {

    color: #111;
}


.change-password-toggle i {

    font-size: 14px;
}


/* =========================================================
   SUBMIT
========================================================= */

.change-password-submit {

    width: 100%;

    height: 42px;

    margin-top: 5px;

    border: 1px solid #111;

    background: #111;

    color: #fff;

    font-family:
        "Montserrat",
        Arial,
        sans-serif;

    font-size: 10px;

    font-weight: 600;

    cursor: pointer;

    transition:
        background .2s ease,
        border-color .2s ease;
}


.change-password-submit:hover {

    background: #333;

    border-color: #333;
}


/* =========================================================
   BACK
========================================================= */

.change-password-back {

    margin-top: 16px;

    text-align: center;
}


.change-password-back a {

    font-size: 9px;

    color: #555;

    text-decoration: none;
}


.change-password-back a:hover {

    color: #111;

    text-decoration: underline;
}


/* =========================================================
   RIGHT NOTE
========================================================= */

.change-password-note {

    min-height: 300px;

    padding: 25px 24px;

    background: #faf9f5;

    border: 1px solid #f0eee7;
}


.change-password-note h2 {

    margin: 0 0 18px;

    font-size: 13px;

    font-weight: 600;

    color: #111;
}


.change-password-note ul {

    margin: 0;

    padding-left: 17px;
}


.change-password-note li {

    margin-bottom: 15px;

    font-size: 10px;

    line-height: 1.6;

    color: #444;
}


.change-password-note li:last-child {

    margin-bottom: 0;
}


/* =========================================================
   LOCK
========================================================= */

.change-password-lock {

    position: relative;

    width: 90px;

    height: 95px;

    margin: 35px auto 0;

    display: flex;

    align-items: flex-end;

    justify-content: center;
}


.change-password-lock::before {

    content: "";

    position: absolute;

    top: 4px;

    left: 21px;

    width: 48px;

    height: 55px;

    border: 4px solid #555;

    border-bottom: none;

    border-radius:
        28px
        28px
        0
        0;
}


.change-password-lock::after {

    content: "";

    position: absolute;

    bottom: 8px;

    left: 7px;

    width: 76px;

    height: 47px;

    border: 3px solid #555;

    background: transparent;

    border-radius: 6px;
}


.change-password-lock i {

    position: absolute;

    z-index: 2;

    bottom: 26px;

    left: 39px;

    font-size: 18px;

    color: #555;
}


.lock-stars {

    position: absolute;

    z-index: 3;

    bottom: 15px;

    left: 24px;

    font-size: 8px;

    letter-spacing: 3px;

    color: #555;
}


/* =========================================================
   RESPONSIVE - 900px
========================================================= */

@media (max-width: 900px) {

    .change-password-layout {

        grid-template-columns: 1fr;

        gap: 30px;
    }


    .change-password-sidebar {

        border-bottom: 1px solid #eee;

        padding-bottom: 20px;
    }


    .change-password-user {

        max-width: 350px;
    }


    .change-password-content {

        grid-template-columns: 1fr;
    }


    .change-password-note {

        min-height: auto;
    }


    .change-password-lock {

        display: none;
    }

}


/* =========================================================
   RESPONSIVE - 600px
========================================================= */

@media (max-width: 600px) {

    .change-password-container {

        width: calc(100% - 30px);

        padding-bottom: 40px;
    }


    .change-password-breadcrumb {

        font-size: 8px;
    }


    .change-password-layout {

        padding-top: 20px;
    }


    .change-password-title h1 {

        font-size: 22px;
    }


    .change-password-form-box {

        padding: 20px;
    }


    .change-password-sidebar-link {

        min-height: 44px;

        font-size: 10px;
    }


    .change-password-note {

        padding: 20px;
    }

}


/* =========================================================
   RESPONSIVE - 480px
========================================================= */

@media (max-width: 480px) {

    .change-password-container {

        width: calc(100% - 24px);
    }


    .change-password-user {

        padding-left: 0;

        padding-right: 0;
    }


    .change-password-avatar {

        width: 52px;

        height: 52px;

        min-width: 52px;
    }


    .change-password-avatar i {

        font-size: 22px;
    }


    .change-password-user-info h3 {

        font-size: 11px;
    }


    .change-password-user-info p {

        font-size: 9px;
    }


    .change-password-title h1 {

        font-size: 20px;
    }


    .change-password-title p {

        font-size: 9px;
    }


    .change-password-form-box {

        padding: 18px 16px;
    }


    .change-password-input {

        height: 41px;
    }


    .change-password-input input {

        font-size: 9px;
    }

}

</style>


<!-- =========================================================
     PASSWORD TOGGLE
========================================================= -->

<script>

function toggleChangePassword(inputId, button) {

    const input =
        document.getElementById(inputId);

    const icon =
        button.querySelector('i');


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
