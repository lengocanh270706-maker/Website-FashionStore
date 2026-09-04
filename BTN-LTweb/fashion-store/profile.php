<?php

require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user = currentUser();

$message = '';
$error = '';

/*
|--------------------------------------------------------------------------
| XỬ LÝ UPLOAD AVATAR
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {

    $avatar = $_FILES['avatar'];

    if ($avatar['error'] === UPLOAD_ERR_OK) {

        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!in_array($avatar['type'], $allowedTypes, true)) {

            $error = 'Chỉ được upload ảnh JPG, PNG hoặc WEBP.';

        } elseif ($avatar['size'] > 5 * 1024 * 1024) {

            $error = 'Ảnh không được lớn hơn 5MB.';

        } else {

            $extension = strtolower(
                pathinfo($avatar['name'], PATHINFO_EXTENSION)
            );

            $fileName = 'avatar_' . $user['id'] . '_' . time() . '.' . $extension;

            $uploadDir = __DIR__ . '/../assets/uploads/avatars/';

            $uploadPath = $uploadDir . $fileName;

            $avatarUrl = '../assets/uploads/avatars/' . $fileName;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($avatar['tmp_name'], $uploadPath)) {

                $stmt = $conn->prepare(
                    "UPDATE users SET avatar = ? WHERE id = ?"
                );

                if ($stmt) {

                    $stmt->bind_param(
                        "si",
                        $avatarUrl,
                        $user['id']
                    );

                    if ($stmt->execute()) {

                        $_SESSION['user']['avatar'] = $avatarUrl;

                        $user = currentUser();

                        $message = 'Cập nhật ảnh đại diện thành công!';

                    } else {

                        $error = 'Không thể lưu ảnh đại diện vào tài khoản.';

                    }

                    $stmt->close();

                } else {

                    $error = 'Không thể cập nhật ảnh đại diện.';

                }

            } else {

                $error = 'Không thể tải ảnh lên máy chủ.';

            }
        }

    } else {

        $error = 'Vui lòng chọn ảnh để tải lên.';

    }
}

/*
|--------------------------------------------------------------------------
| XỬ LÝ CẬP NHẬT THÔNG TIN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {

        $error = 'Vui lòng nhập họ và tên.';

    } else {

        $stmt = $conn->prepare(
            "UPDATE users
             SET name = ?, phone = ?, address = ?
             WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param(
                "sssi",
                $name,
                $phone,
                $address,
                $user['id']
            );

            if ($stmt->execute()) {

                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['phone'] = $phone;
                $_SESSION['user']['address'] = $address;

                $user = currentUser();

                $message = 'Cập nhật thông tin thành công!';

            } else {

                $error = 'Có lỗi xảy ra khi cập nhật thông tin. Vui lòng thử lại.';

            }

            $stmt->close();

        } else {

            $error = 'Không thể thực hiện cập nhật. Vui lòng thử lại.';

        }
    }
}


/*
|--------------------------------------------------------------------------
| DỮ LIỆU HIỂN THỊ
|--------------------------------------------------------------------------
*/

$userName = $user['name'] ?? 'Khách hàng';
$userEmail = $user['email'] ?? '';
$userPhone = $user['phone'] ?? '';
$userAddress = $user['address'] ?? '';

$userRole = $user['role'] ?? 'user';

/*
 * Các trường này chỉ hiển thị nếu project của bạn
 * đã có dữ liệu tương ứng trong currentUser().
 */
$userDob = $user['dob']
    ?? $user['date_of_birth']
    ?? $user['birthday']
    ?? '';

$userGender = $user['gender'] ?? '';

$userCreatedAt = $user['created_at']
    ?? $user['joined_at']
    ?? $user['created_date']
    ?? '';

/*
|--------------------------------------------------------------------------
| ĐỊNH DẠNG NGÀY
|--------------------------------------------------------------------------
*/

function formatProfileDate($date)
{
    if (empty($date)) {
        return 'Chưa cập nhật';
    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return htmlspecialchars($date);
    }

    return date('d/m/Y', $timestamp);
}

function profileGender($gender)
{
    if (empty($gender)) {
        return 'Chưa cập nhật';
    }

    $gender = strtolower(trim($gender));

    if (
        $gender === 'female' ||
        $gender === 'nu' ||
        $gender === 'nữ'
    ) {
        return 'Nữ';
    }

    if (
        $gender === 'male' ||
        $gender === 'nam'
    ) {
        return 'Nam';
    }

    return htmlspecialchars($gender);
}


/*
|--------------------------------------------------------------------------
| HEADER + MENU
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';

?>

<style>

/* =========================================================
   PROFILE PAGE
========================================================= */

.profile-page {
    width: 100%;
    background: #fff;
    color: #111;
    font-family: "Montserrat", Arial, sans-serif;
}

.profile-page *,
.profile-page *::before,
.profile-page *::after {
    box-sizing: border-box;
}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.profile-container {
    width: min(1120px, calc(100% - 48px));
    margin: 0 auto;
    padding: 58px 0 90px;
}


/* =========================================================
   LAYOUT
========================================================= */

.profile-layout {
    display: grid;
    grid-template-columns: 235px minmax(0, 1fr);
    gap: 45px;
    align-items: start;
}


/* =========================================================
   SIDEBAR
========================================================= */

.profile-sidebar {
    width: 100%;
}


/* USER SUMMARY */

.profile-user-card {
    padding: 0 8px 25px;
    margin-bottom: 14px;
    border-bottom: 1px solid #e8e8e8;
}

.profile-user-top {
    display: flex;
    align-items: center;
    gap: 13px;
}


.profile-user-avatar {
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


.profile-user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-upload-form {
    width: 100%;
    height: 100%;
}

.avatar-upload-label {
    width: 100%;
    height: 100%;

    display: flex;
    align-items: center;
    justify-content: center;

    cursor: pointer;
}


.profile-user-avatar i {
    font-size: 26px;
    color: #333;
}


.profile-user-info {
    min-width: 0;
}


.profile-user-name {
    margin: 0 0 6px;

    font-size: 13px;
    line-height: 1.35;

    font-weight: 600;

    color: #111;

    word-break: break-word;
}


.profile-user-phone {
    margin: 0 0 6px;

    font-size: 10px;

    color: #555;
}


.profile-member {
    display: inline-flex;
    align-items: center;
    gap: 4px;

    font-size: 9px;

    color: #927b42;
}


.profile-member i {
    font-size: 9px;
}


/* SIDEBAR MENU */

.profile-sidebar-menu {
    width: 100%;
}


.profile-sidebar-link {
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


.profile-sidebar-link i {
    width: 18px;

    text-align: center;

    font-size: 15px;

    color: #555;
}


.profile-sidebar-link:hover {
    background: #fafafa;
    color: #111;
}


.profile-sidebar-link:hover i {
    color: #111;
}


.profile-sidebar-link.active {
    background: #f5f5f5;
    color: #111;

    font-weight: 600;
}


.profile-sidebar-link.active i {
    color: #111;
}


/* =========================================================
   SUPPORT BOX
========================================================= */

.profile-support {
    margin-top: 75px;

    padding: 18px 16px;

    border: 1px solid #dedede;

    background: #fff;
}


.profile-support-title {
    margin: 0 0 8px;

    font-size: 12px;
    font-weight: 600;

    color: #111;
}


.profile-support-text {
    margin: 0 0 12px;

    font-size: 10px;
    line-height: 1.5;

    color: #444;
}


.profile-support-row {
    display: flex;
    align-items: center;

    gap: 9px;

    margin-bottom: 7px;

    font-size: 10px;

    color: #444;
}


.profile-support-row:last-child {
    margin-bottom: 0;
}


.profile-support-row i {
    width: 15px;
    text-align: center;

    font-size: 13px;
}


/* =========================================================
   MAIN CONTENT
========================================================= */

.profile-main {
    min-width: 0;
}


/* PAGE TITLE */

.profile-page-title {
    margin-bottom: 19px;
}


.profile-page-title h1 {
    margin: 0 0 6px;

    font-family: "Playfair Display", Georgia, serif;

    font-size: 26px;
    line-height: 1.25;

    font-weight: 600;

    color: #111;
}


.profile-page-title p {
    margin: 0;

    font-size: 10px;

    color: #888;
}


/* =========================================================
   ALERT
========================================================= */

.profile-alert {
    display: flex;
    align-items: center;

    gap: 9px;

    margin-bottom: 18px;

    padding: 11px 14px;

    border: 1px solid #dedede;

    font-size: 10px;
}


.profile-alert-success {
    background: #fafafa;
    color: #333;
}


.profile-alert-error {
    background: #fafafa;
    color: #333;
}


.profile-alert i {
    font-size: 13px;
}


/* =========================================================
   INFORMATION CARD
========================================================= */

.profile-card {
    border: 1px solid #dedede;

    background: #fff;

    margin-bottom: 16px;
}


.profile-card-header {
    min-height: 52px;

    padding: 0 16px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    border-bottom: 1px solid #e5e5e5;
}


.profile-card-header h2 {
    margin: 0;

    font-size: 13px;

    font-weight: 600;

    color: #111;
}


.profile-edit-button {
    height: 31px;

    padding: 0 13px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 6px;

    border: 1px solid #d5d5d5;

    background: #fff;

    color: #222;

    font-family: "Montserrat", Arial, sans-serif;

    font-size: 9px;

    cursor: pointer;

    transition: all .2s ease;
}


.profile-edit-button:hover {
    background: #111;
    border-color: #111;
    color: #fff;
}


.profile-edit-button i {
    font-size: 10px;
}


/* =========================================================
   PERSONAL INFORMATION
========================================================= */

.profile-info-body {
    padding: 20px;
}


.profile-info-layout {
    display: grid;

    grid-template-columns: 125px minmax(0, 1fr);

    gap: 25px;
}


/* AVATAR */

.profile-detail-avatar {
    display: flex;
    justify-content: center;
    align-items: flex-start;
}


.profile-detail-avatar-circle {
    width: 94px;
    height: 94px;

    border-radius: 50%;

    border: 1px solid #dedede;

    background: #fafafa;

    display: flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;
}


.profile-detail-avatar-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-detail-avatar-circle .avatar-upload-form {
    width: 100%;
    height: 100%;
}

.profile-detail-avatar-circle .avatar-upload-label {
    width: 100%;
    height: 100%;
}

.profile-detail-avatar-circle i {
    font-size: 38px;
    color: #555;
}


/* DATA */

.profile-data {
    width: 100%;
}


.profile-data-row {
    min-height: 39px;

    display: grid;

    grid-template-columns: 190px minmax(0, 1fr);

    align-items: center;

    border-bottom: 1px solid #eeeeee;
}


.profile-data-row:first-child {
    border-top: 0;
}


.profile-data-row:last-child {
    border-bottom: 0;
}


.profile-data-label {
    font-size: 10px;

    color: #666;
}


.profile-data-value {
    font-size: 10px;

    font-weight: 500;

    color: #111;

    word-break: break-word;
}


/* =========================================================
   ADDRESS CARD
========================================================= */

.profile-address-body {
    min-height: 100px;

    padding: 19px 20px;

    display: flex;
    align-items: center;

    gap: 16px;
}


.profile-address-icon {
    width: 48px;
    height: 48px;
    min-width: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #fafafa;

    border: 1px solid #eeeeee;
}


.profile-address-icon i {
    font-size: 21px;
    color: #333;
}


.profile-address-content {
    flex: 1;
    min-width: 0;
}


.profile-address-title {
    margin: 0 0 7px;

    font-size: 10px;

    font-weight: 600;

    color: #111;
}


.profile-address-text {
    margin: 0;

    font-size: 10px;

    line-height: 1.6;

    color: #444;
}


.profile-default-badge {
    display: inline-flex;

    margin-left: 8px;

    padding: 3px 7px;

    border: 1px solid #e5dcc7;

    background: #faf7ee;

    color: #806b3d;

    font-size: 8px;

    font-weight: 500;
}


/* =========================================================
   OPTIONS
========================================================= */

.profile-options {
    margin-top: 16px;
}


.profile-option {
    min-height: 52px;

    padding: 0 18px;

    display: grid;

    grid-template-columns: 28px 185px minmax(0, 1fr) 20px;

    align-items: center;

    border-bottom: 1px solid #eeeeee;

    text-decoration: none;

    color: #111;

    transition: background .2s ease;
}


.profile-option:first-child {
    border-top: 1px solid #eeeeee;
}


.profile-option:hover {
    background: #fafafa;
}


.profile-option-icon {
    font-size: 15px;
}


.profile-option-title {
    font-size: 10px;

    font-weight: 600;
}


.profile-option-description {
    font-size: 9px;

    color: #888;
}


.profile-option-arrow {
    text-align: right;

    font-size: 12px;

    color: #777;
}


/* =========================================================
   EDIT FORM
========================================================= */

.profile-edit-form {
    display: none;

    padding: 20px;

    border-top: 1px solid #eeeeee;

    background: #fafafa;
}


.profile-edit-form.show {
    display: block;
}


.profile-form-group {
    margin-bottom: 15px;
}


.profile-form-group:last-child {
    margin-bottom: 0;
}


.profile-form-group label {
    display: block;

    margin-bottom: 6px;

    font-size: 10px;

    font-weight: 500;

    color: #444;
}


.profile-input-wrapper {
    position: relative;
}


.profile-input-wrapper input,
.profile-input-wrapper textarea {
    width: 100%;

    border: 1px solid #dcdcdc;

    border-radius: 0;

    background: #fff;

    color: #222;

    font-family: "Montserrat", Arial, sans-serif;

    font-size: 10px;

    outline: none;

    transition: border-color .2s ease;
}


.profile-input-wrapper input {
    height: 40px;

    padding: 0 12px;
}


.profile-input-wrapper textarea {
    min-height: 80px;

    padding: 11px 12px;

    resize: vertical;
}


.profile-input-wrapper input:focus,
.profile-input-wrapper textarea:focus {
    border-color: #111;
}


.profile-email-disabled {
    background: #f2f2f2 !important;

    color: #888 !important;

    cursor: not-allowed;
}


.profile-form-actions {
    display: flex;

    justify-content: flex-end;

    gap: 8px;

    margin-top: 18px;
}


.profile-form-cancel,
.profile-form-save {
    height: 35px;

    padding: 0 17px;

    border-radius: 0;

    font-family: "Montserrat", Arial, sans-serif;

    font-size: 9px;

    cursor: pointer;

    text-decoration: none;
}


.profile-form-cancel {
    border: 1px solid #d5d5d5;

    background: #fff;

    color: #444;
}


.profile-form-save {
    border: 1px solid #111;

    background: #111;

    color: #fff;
}


.profile-form-save:hover {
    background: #333;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .profile-container {
        width: min(100% - 32px, 760px);

        padding-top: 42px;
    }

    .profile-layout {
        grid-template-columns: 200px minmax(0, 1fr);

        gap: 28px;
    }

    .profile-info-layout {
        grid-template-columns: 100px minmax(0, 1fr);

        gap: 18px;
    }

    .profile-data-row {
        grid-template-columns: 135px minmax(0, 1fr);
    }

    .profile-option {
        grid-template-columns: 28px 150px minmax(0, 1fr) 20px;
    }

}


@media (max-width: 700px) {

    .profile-container {
        width: calc(100% - 28px);

        padding: 30px 0 55px;
    }

    .profile-layout {
        grid-template-columns: 1fr;

        gap: 30px;
    }

    .profile-support {
        margin-top: 25px;
    }

    .profile-info-layout {
        grid-template-columns: 1fr;

        gap: 20px;
    }

    .profile-detail-avatar {
        justify-content: flex-start;
    }

    .profile-data-row {
        grid-template-columns: 135px minmax(0, 1fr);
    }

    .profile-option {
        grid-template-columns: 28px 1fr 20px;
    }

    .profile-option-description {
        display: none;
    }

}


@media (max-width: 480px) {

    .profile-page-title h1 {
        font-size: 23px;
    }

    .profile-card-header {
        padding: 0 12px;
    }

    .profile-info-body {
        padding: 15px;
    }

    .profile-data-row {
        grid-template-columns: 115px minmax(0, 1fr);
    }

    .profile-data-label,
    .profile-data-value {
        font-size: 9px;
    }

    .profile-address-body {
        padding: 15px;
    }

    .profile-option {
        padding: 0 12px;
    }

    .profile-form-actions {
        flex-direction: column;
    }

    .profile-form-cancel,
    .profile-form-save {
        width: 100%;
    }

}

</style>


<main class="profile-page">

    <div class="profile-container">

        <div class="profile-layout">


            <!-- =================================================
                 SIDEBAR
            ================================================== -->

            <aside class="profile-sidebar">


                <!-- USER CARD -->

                <div class="profile-user-card">

                    <div class="profile-user-top">

                        <div class="profile-user-avatar">

    <form
        method="POST"
        enctype="multipart/form-data"
        class="avatar-upload-form"
    >

        <label
            for="avatarUpload"
            class="avatar-upload-label"
            title="Đổi ảnh đại diện"
        >

            <?php if (!empty($user['avatar'])): ?>

                <img
                    src="<?= htmlspecialchars($user['avatar']) ?>"
                    alt="Ảnh đại diện"
                >

            <?php else: ?>

                <i class="bi bi-person"></i>

            <?php endif; ?>

        </label>

        <input
            type="file"
            name="avatar"
            id="avatarUpload"
            accept="image/jpeg,image/png,image/webp"
            hidden
        >

    </form>

</div>


                        <div class="profile-user-info">

                            <p class="profile-user-name">
                                <?= htmlspecialchars($userName) ?>
                            </p>

                            <p class="profile-user-phone">
                                <?= htmlspecialchars($userPhone ?: 'Chưa cập nhật SĐT') ?>
                            </p>

                            <span class="profile-member">

                                <i class="bi bi-patch-check"></i>

                                Thành viên

                            </span>

                        </div>

                    </div>

                </div>


                <!-- MENU -->

                <nav class="profile-sidebar-menu">


                    <a
                        href="profile.php"
                        class="profile-sidebar-link active"
                    >

                        <i class="bi bi-person"></i>

                        <span>
                            Thông tin tài khoản
                        </span>

                    </a>


                    <a
                        href="change-password.php"
                        class="profile-sidebar-link"
                    >

                        <i class="bi bi-lock"></i>

                        <span>
                            Đổi mật khẩu
                        </span>

                    </a>


                    <a
                        href="address.php"
                        class="profile-sidebar-link"
                    >

                        <i class="bi bi-geo-alt"></i>

                        <span>
                            Địa chỉ của tôi
                        </span>

                    </a>


                    <a
                        href="orders.php"
                        class="profile-sidebar-link"
                    >

                        <i class="bi bi-bag"></i>

                        <span>
                            Đơn hàng của tôi
                        </span>

                    </a>


                    <a
                        href="favorite.php"
                        class="profile-sidebar-link"
                    >

                        <i class="bi bi-heart"></i>

                        <span>
                            Sản phẩm yêu thích
                        </span>

                    </a>


                    <a
                        href="reviews.php"
                        class="profile-sidebar-link"
                    >

                        <i class="bi bi-star"></i>

                        <span>
                            Đánh giá của tôi
                        </span>

                    </a>


                    <a
                        href="logout.php"
                        class="profile-sidebar-link"
                    >

                        <i class="bi bi-box-arrow-right"></i>

                        <span>
                            Đăng xuất
                        </span>

                    </a>

                </nav>


                <!-- SUPPORT -->

                <div class="profile-support">

                    <h3 class="profile-support-title">
                        Cần hỗ trợ?
                    </h3>

                    <p class="profile-support-text">
                        Liên hệ với chúng tôi 24/7
                    </p>

                    <div class="profile-support-row">

                        <i class="bi bi-telephone"></i>

                        <span>
                            1900 1234
                        </span>

                    </div>

                    <div class="profile-support-row">

                        <i class="bi bi-envelope"></i>

                        <span>
                            support@may.com
                        </span>

                    </div>

                </div>


            </aside>


            <!-- =================================================
                 MAIN
            ================================================== -->

            <section class="profile-main">


                <!-- PAGE TITLE -->

                <div class="profile-page-title">

                    <h1>
                        Thông tin tài khoản
                    </h1>

                    <p>
                        Quản lý thông tin cá nhân và tài khoản của bạn
                    </p>

                </div>


                <!-- SUCCESS -->

                <?php if ($message !== ''): ?>

                    <div class="profile-alert profile-alert-success">

                        <i class="bi bi-check-circle"></i>

                        <span>
                            <?= htmlspecialchars($message) ?>
                        </span>

                    </div>

                <?php endif; ?>


                <!-- ERROR -->

                <?php if ($error !== ''): ?>

                    <div class="profile-alert profile-alert-error">

                        <i class="bi bi-exclamation-circle"></i>

                        <span>
                            <?= htmlspecialchars($error) ?>
                        </span>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     PERSONAL INFORMATION
                ================================================== -->

                <div class="profile-card">


                    <div class="profile-card-header">

                        <h2>
                            Thông tin cá nhân
                        </h2>

                        <button
                            type="button"
                            class="profile-edit-button"
                            id="profileEditButton"
                        >

                            <i class="bi bi-pencil"></i>

                            Chỉnh sửa

                        </button>

                    </div>


                    <!-- INFORMATION DISPLAY -->

                    <div class="profile-info-body">

                        <div class="profile-info-layout">


                            <!-- AVATAR -->

                            <div class="profile-detail-avatar">

                                <div class="profile-detail-avatar-circle">

    <form
        method="POST"
        enctype="multipart/form-data"
        class="avatar-upload-form"
    >

        <label
            for="avatarUploadLarge"
            class="avatar-upload-label"
            title="Đổi ảnh đại diện"
        >

            <?php if (!empty($user['avatar'])): ?>

                <img
                    src="<?= htmlspecialchars($user['avatar']) ?>"
                    alt="Ảnh đại diện"
                >

            <?php else: ?>

                <i class="bi bi-person"></i>

            <?php endif; ?>

        </label>

        <input
            type="file"
            name="avatar"
            id="avatarUploadLarge"
            accept="image/jpeg,image/png,image/webp"
            hidden
        >

    </form>

</div>

                            </div>


                            <!-- INFORMATION -->

                            <div class="profile-data">


                                <div class="profile-data-row">

                                    <div class="profile-data-label">
                                        Họ và tên
                                    </div>

                                    <div class="profile-data-value">
                                        <?= htmlspecialchars($userName) ?>
                                    </div>

                                </div>


                                <div class="profile-data-row">

                                    <div class="profile-data-label">
                                        Số điện thoại
                                    </div>

                                    <div class="profile-data-value">
                                        <?= htmlspecialchars($userPhone ?: 'Chưa cập nhật') ?>
                                    </div>

                                </div>


                                <div class="profile-data-row">

                                    <div class="profile-data-label">
                                        Email
                                    </div>

                                    <div class="profile-data-value">
                                        <?= htmlspecialchars($userEmail ?: 'Chưa cập nhật') ?>
                                    </div>

                                </div>


                                <div class="profile-data-row">

                                    <div class="profile-data-label">
                                        Ngày sinh
                                    </div>

                                    <div class="profile-data-value">
                                        <?= formatProfileDate($userDob) ?>
                                    </div>

                                </div>


                                <div class="profile-data-row">

                                    <div class="profile-data-label">
                                        Giới tính
                                    </div>

                                    <div class="profile-data-value">
                                        <?= profileGender($userGender) ?>
                                    </div>

                                </div>


                                <div class="profile-data-row">

                                    <div class="profile-data-label">
                                        Tài khoản thành viên
                                    </div>

                                    <div class="profile-data-value">
                                        Thành viên
                                    </div>

                                </div>


                                <div class="profile-data-row">

                                    <div class="profile-data-label">
                                        Ngày tham gia
                                    </div>

                                    <div class="profile-data-value">
                                        <?= formatProfileDate($userCreatedAt) ?>
                                    </div>

                                </div>


                            </div>

                        </div>

                    </div>


                    <!-- =================================================
                         EDIT FORM
                    ================================================== -->

                    <form
                        method="POST"
                        class="profile-edit-form"
                        id="profileEditForm"
                    >


                        <div class="profile-form-group">

                            <label for="profile-name">
                                Họ và tên *
                            </label>

                            <div class="profile-input-wrapper">

                                <input
                                    type="text"
                                    id="profile-name"
                                    name="name"
                                    value="<?= htmlspecialchars($userName) ?>"
                                    required
                                >

                            </div>

                        </div>


                        <div class="profile-form-group">

                            <label for="profile-email">
                                Email
                            </label>

                            <div class="profile-input-wrapper">

                                <input
                                    type="email"
                                    id="profile-email"
                                    value="<?= htmlspecialchars($userEmail) ?>"
                                    class="profile-email-disabled"
                                    disabled
                                >

                            </div>

                        </div>


                        <div class="profile-form-group">

                            <label for="profile-phone">
                                Số điện thoại
                            </label>

                            <div class="profile-input-wrapper">

                                <input
                                    type="text"
                                    id="profile-phone"
                                    name="phone"
                                    value="<?= htmlspecialchars($userPhone) ?>"
                                    placeholder="Nhập số điện thoại"
                                >

                            </div>

                        </div>


                        <div class="profile-form-group">

                            <label for="profile-address">
                                Địa chỉ
                            </label>

                            <div class="profile-input-wrapper">

                                <textarea
                                    id="profile-address"
                                    name="address"
                                    placeholder="Nhập địa chỉ"
                                ><?= htmlspecialchars($userAddress) ?></textarea>

                            </div>

                        </div>


                        <div class="profile-form-actions">

                            <button
                                type="button"
                                class="profile-form-cancel"
                                id="profileCancelButton"
                            >
                                Hủy
                            </button>

                            <button
                                type="submit"
                                class="profile-form-save"
                            >
                                Lưu thay đổi
                            </button>

                        </div>

                    </form>


                </div>


                <!-- =================================================
                     DEFAULT ADDRESS
                ================================================== -->

                <div class="profile-card">


                    <div class="profile-card-header">

                        <h2>
                            Địa chỉ mặc định
                        </h2>

                        <button
                            type="button"
                            class="profile-edit-button"
                            id="addressEditButton"
                        >

                            <i class="bi bi-pencil"></i>

                            Chỉnh sửa

                        </button>

                    </div>


                    <div class="profile-address-body">

                        <div class="profile-address-icon">

                            <i class="bi bi-geo-alt"></i>

                        </div>


                        <div class="profile-address-content">

                            <p class="profile-address-title">

                                <?= htmlspecialchars($userName) ?>

                                <span class="profile-default-badge">
                                    Mặc định
                                </span>

                            </p>

                            <p class="profile-address-text">

                                <?= nl2br(
                                    htmlspecialchars(
                                        $userAddress ?: 'Chưa cập nhật địa chỉ'
                                    )
                                ) ?>

                                <?php if ($userPhone !== ''): ?>

                                    <br>

                                    <?= htmlspecialchars($userPhone) ?>

                                <?php endif; ?>

                            </p>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     OPTIONS
                ================================================== -->

                <div class="profile-card profile-options">


                    <div class="profile-card-header">

                        <h2>
                            Tùy chọn
                        </h2>

                    </div>


                    <a
                        href="notifications.php"
                        class="profile-option"
                    >

                        <i class="bi bi-bell profile-option-icon"></i>

                        <span class="profile-option-title">
                            Thông báo
                        </span>

                        <span class="profile-option-description">
                            Quản lý tùy chọn nhận thông báo từ chúng tôi
                        </span>

                        <i class="bi bi-chevron-right profile-option-arrow"></i>

                    </a>


                    <a
                        href="security.php"
                        class="profile-option"
                    >

                        <i class="bi bi-shield-check profile-option-icon"></i>

                        <span class="profile-option-title">
                            Bảo mật tài khoản
                        </span>

                        <span class="profile-option-description">
                            Quản lý bảo mật và thiết bị đăng nhập
                        </span>

                        <i class="bi bi-chevron-right profile-option-arrow"></i>

                    </a>


                    <a
                        href="payment.php"
                        class="profile-option"
                    >

                        <i class="bi bi-credit-card profile-option-icon"></i>

                        <span class="profile-option-title">
                            Phương thức thanh toán
                        </span>

                        <span class="profile-option-description">
                            Quản lý thẻ và tài khoản thanh toán
                        </span>

                        <i class="bi bi-chevron-right profile-option-arrow"></i>

                    </a>


                    <a
                        href="delete-account.php"
                        class="profile-option"
                    >

                        <i class="bi bi-trash3 profile-option-icon"></i>

                        <span class="profile-option-title">
                            Xóa tài khoản
                        </span>

                        <span class="profile-option-description">
                            Xóa vĩnh viễn tài khoản và dữ liệu của bạn
                        </span>

                        <i class="bi bi-chevron-right profile-option-arrow"></i>

                    </a>


                </div>


            </section>

        </div>

    </div>

</main>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const editButton = document.getElementById('profileEditButton');

    const cancelButton = document.getElementById('profileCancelButton');

    const editForm = document.getElementById('profileEditForm');

    const addressEditButton = document.getElementById('addressEditButton');


    if (editButton && editForm) {

        editButton.addEventListener('click', function () {

            editForm.classList.toggle('show');

            if (editForm.classList.contains('show')) {

                editButton.innerHTML =
                    '<i class="bi bi-x"></i> Đóng chỉnh sửa';

                editForm.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });

            } else {

                editButton.innerHTML =
                    '<i class="bi bi-pencil"></i> Chỉnh sửa';

            }

        });

    }


    if (cancelButton && editForm) {

        cancelButton.addEventListener('click', function () {

            editForm.classList.remove('show');

            if (editButton) {

                editButton.innerHTML =
                    '<i class="bi bi-pencil"></i> Chỉnh sửa';

            }

        });

    }


    /*
     * Nút "Chỉnh sửa" ở phần địa chỉ
     * sẽ mở luôn form thông tin cá nhân.
     */

    if (addressEditButton && editForm) {

        addressEditButton.addEventListener('click', function () {

            editForm.classList.add('show');

            if (editButton) {

                editButton.innerHTML =
                    '<i class="bi bi-x"></i> Đóng chỉnh sửa';

            }

            const addressInput =
                document.getElementById('profile-address');

            if (addressInput) {

                addressInput.focus();

            }

            editForm.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });

        });

    }

});

    const avatarUpload = document.getElementById('avatarUpload');

    if (avatarUpload) {

        avatarUpload.addEventListener('change', function () {

            if (this.files.length > 0) {
                this.closest('form').submit();
            }

        });

    }


    const avatarUploadLarge =
        document.getElementById('avatarUploadLarge');

    if (avatarUploadLarge) {

        avatarUploadLarge.addEventListener('change', function () {

            if (this.files.length > 0) {
                this.closest('form').submit();
            }

        });

    }

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>
