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

            /*
             * Mật khẩu tạm thời cho đồ án.
             * Sau này nếu cần có thể đổi sang OTP/email.
             */
            $temporaryPassword = '123456';

            $hashedPassword = password_hash(
                $temporaryPassword,
                PASSWORD_DEFAULT
            );

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

                $success =
                    'Khôi phục mật khẩu thành công! ' .
                    'Mật khẩu tạm thời: 123456';

            } else {

                $error =
                    'Không thể khôi phục mật khẩu. Vui lòng thử lại.';
            }

            $stmt->close();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Quên mật khẩu - MÂY</title>

    <style>

        /* =====================================================
           RESET RIÊNG CHO TRANG QUÊN MẬT KHẨU
        ===================================================== */

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #ffffff;

            font-family: Arial, Helvetica, sans-serif;

            color: #111111;
        }


        /* =====================================================
           KHUNG TRANG
        ===================================================== */

        .forgot-page {
            width: 100%;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px 20px;
        }


        /* =====================================================
           KHUNG FORM
        ===================================================== */

        .forgot-card {
            width: 100%;
            max-width: 420px;

            padding: 42px 40px 36px;

            background: #ffffff;

            border: 1px solid #e5e5e5;
            border-radius: 8px;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, 0.06);
        }


        /* =====================================================
           TIÊU ĐỀ
        ===================================================== */

        .forgot-title {
            margin: 0 0 30px;

            text-align: center;

            font-size: 26px;
            line-height: 1.25;

            font-weight: 600;

            color: #111111;
        }


        /* =====================================================
           Ô SỐ ĐIỆN THOẠI / EMAIL
        ===================================================== */

        .forgot-form-group {
            width: 100%;
            margin: 0 0 20px;
        }

        .forgot-label {
            display: flex;
            align-items: center;

            gap: 7px;

            margin: 0 0 9px;

            font-size: 13px;
            line-height: 1.4;

            font-weight: 500;

            color: #222222;
        }

        .forgot-label-icon {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            width: 17px;
            height: 17px;

            font-size: 15px;
            line-height: 1;
        }

        .forgot-input {
            width: 100%;
            height: 46px;

            padding: 0 13px;

            border: 1px solid #d8d8d8;
            border-radius: 5px;

            outline: none;

            background: #ffffff;

            color: #222222;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .forgot-input::placeholder {
            color: #a0a0a0;
        }

        .forgot-input:focus {
            border-color: #111111;

            box-shadow:
                0 0 0 2px rgba(0, 0, 0, 0.04);
        }


        /* =====================================================
           NÚT KHÔI PHỤC
        ===================================================== */

        .forgot-button {
            width: 100%;
            height: 44px;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 0;

            border: 1px solid #111111;
            border-radius: 5px;

            background: #111111;
            color: #ffffff;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            font-weight: 600;

            cursor: pointer;

            transition:
                background 0.2s ease,
                border-color 0.2s ease;
        }

        .forgot-button:hover {
            background: #333333;
            border-color: #333333;
        }


        /* =====================================================
           QUAY LẠI ĐĂNG NHẬP
        ===================================================== */

        .forgot-back {
            display: block;

            margin-top: 22px;

            text-align: center;

            color: #555555;

            font-size: 12px;
            line-height: 1.4;

            text-decoration: underline;

            transition: color 0.2s ease;
        }

        .forgot-back:hover {
            color: #111111;
        }


        /* =====================================================
           THÔNG BÁO
        ===================================================== */

        .forgot-message {
            width: 100%;

            margin: 0 0 18px;

            padding: 10px 12px;

            border-radius: 5px;

            font-size: 12px;
            line-height: 1.5;
        }

        .forgot-error {
            border: 1px solid #f0cccc;

            background: #fff6f6;

            color: #b42318;
        }

        .forgot-success {
            border: 1px solid #cfe5d3;

            background: #f5fff6;

            color: #287a36;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 480px) {

            .forgot-page {
                padding: 20px 15px;
            }

            .forgot-card {
                max-width: 100%;

                padding: 32px 22px 28px;

                border-radius: 7px;
            }

            .forgot-title {
                margin-bottom: 26px;

                font-size: 23px;
            }

            .forgot-label {
                font-size: 12px;
            }

            .forgot-input {
                height: 44px;

                font-size: 12px;
            }

            .forgot-button {
                height: 43px;

                font-size: 12px;
            }

            .forgot-back {
                font-size: 11px;
            }
        }

    </style>

</head>

<body>

    <main class="forgot-page">

        <div class="forgot-card">

            <h1 class="forgot-title">
                Quên mật khẩu
            </h1>


            <?php if ($error !== ''): ?>

                <div class="forgot-message forgot-error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <?php if ($success !== ''): ?>

                <div class="forgot-message forgot-success">
                    <?= htmlspecialchars($success) ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <div class="forgot-form-group">

                    <label
                        for="account"
                        class="forgot-label"
                    >

                        <span class="forgot-label-icon">
                            👤
                        </span>

                        <span>
                            Số điện thoại / Email
                        </span>

                    </label>


                    <input
                        type="text"
                        id="account"
                        name="account"
                        class="forgot-input"
                        placeholder="Nhập số điện thoại hoặc email"
                        value="<?= htmlspecialchars($_POST['account'] ?? '') ?>"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="forgot-button"
                >
                    Khôi phục mật khẩu
                </button>

            </form>


            <a
                href="login.php"
                class="forgot-back"
            >
                Quay lại đăng nhập
            </a>

        </div>

    </main>

</body>

</html>
