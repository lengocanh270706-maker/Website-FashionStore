<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;

$isLoggedIn = isset($_SESSION['user']);

$isAdmin = $isLoggedIn && (($user['role'] ?? '') === 'admin');

?>

<!-- =====================================================
     TOPBAR
===================================================== -->

<div class="topbar">

    <div class="topbar-container">

        <div class="topbar-left">

            <span>
                Miễn phí vận chuyển cho đơn từ 300K
            </span>

            <span>
                Đổi trả dễ dàng trong 7 ngày
            </span>

        </div>


        <div class="topbar-right">

            <span>
                <i class="bi bi-telephone"></i>
                Hotline: 1900 1234
            </span>

        </div>

    </div>

</div>


<!-- =====================================================
     MAIN NAVBAR
===================================================== -->

<nav class="main-navbar">

    <div class="navbar-container">


        <!-- =================================================
             LOGO + MENU ICON
        ================================================== -->

        <div class="may-logo-wrapper">

            <!-- 3 GẠCH -->
            <button
                type="button"
                class="mobile-menu-button"
                aria-label="Mở menu"
            >
                <i class="bi bi-list"></i>
            </button>


            <!-- LOGO -->
            <a href="index.php" class="may-logo">

                <span class="may-logo-main">
                    MÂY
                </span>

                <span class="may-logo-sub">
                    FASHION STORE
                </span>

            </a>

        </div>


        <!-- =================================================
             NAVIGATION
        ================================================== -->

        <div class="main-nav">

            <a
                href="index.php"
                class="nav-item"
            >
                Trang chủ
            </a>

            <a
                href="products.php?gender=female"
                class="nav-item"
            >
                Nữ
            </a>

            <a
                href="products.php?gender=male"
                class="nav-item"
            >
                Nam
            </a>

            <a
                href="products.php"
                class="nav-item"
            >
                Shop
            </a>

            <a
                href="products.php"
                class="nav-item"
            >
                Bộ sưu tập
            </a>

            <a
                href="search.php"
                class="nav-item"
            >
                Tin tức
            </a>

        </div>


        <!-- =================================================
             NAVBAR ACTIONS
================================================== -->

        <div class="navbar-actions">


            <!-- =================================================
                 SEARCH
            ================================================== -->

            <form
                action="search.php"
                method="GET"
                class="header-search"
            >

                <input
                    type="text"
                    name="q"
                    placeholder="Tìm kiếm sản phẩm..."
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                >

                <button type="submit">

                    <i class="bi bi-search"></i>

                </button>

            </form>


            <!-- =================================================
                 ACCOUNT
            ================================================== -->

            <div class="header-account">


                <?php if ($isLoggedIn): ?>

                    <a
                        href="profile.php"
                        class="header-action"
                    >

                        <i class="bi bi-person"></i>

                        <span>
                            <?= htmlspecialchars($user['name'] ?? 'Tài khoản') ?>
                        </span>

                    </a>


                    <!-- ACCOUNT DROPDOWN -->

                    <div class="account-dropdown">

                        <a href="profile.php">

                            <i class="bi bi-person"></i>

                            Thông tin tài khoản

                        </a>


                        <a href="change-password.php">

                            <i class="bi bi-lock"></i>

                            Đổi mật khẩu

                        </a>


                        <?php if ($isAdmin): ?>

                            <a href="../admin/index.php">

                                <i class="bi bi-speedometer2"></i>

                                Trang quản trị

                            </a>

                        <?php endif; ?>


                        <div class="dropdown-divider"></div>


                        <a href="logout.php">

                            <i class="bi bi-box-arrow-right"></i>

                            Đăng xuất

                        </a>

                    </div>


                <?php else: ?>


                    <a
                        href="login.php"
                        class="header-action"
                    >

                        <i class="bi bi-person"></i>

                        <span>
                            Tài khoản
                        </span>

                    </a>


                <?php endif; ?>


            </div>


            <!-- =================================================
                 FAVORITE / YÊU THÍCH
================================================== -->

            <a
                href="favorite.php"
                class="header-action favorite-action"
            >

                <i class="bi bi-heart"></i>

                <span>
                    Yêu thích
                </span>

                <!-- SỐ LƯỢNG YÊU THÍCH -->
                <b class="favorite-count">
                    0
                </b>

            </a>


            <!-- =================================================
                 CART
            ================================================== -->

            <a
                href="cart.php"
                class="header-action cart-action"
            >

                <i class="bi bi-bag"></i>

                <span>
                    Giỏ hàng
                </span>

                <!-- SỐ LƯỢNG GIỎ HÀNG -->
                <b class="cart-count">
                    0
                </b>

            </a>


        </div>

    </div>

</nav>


<!-- =====================================================
     STYLE RIÊNG CHO MENU ICON + BADGE YÊU THÍCH
===================================================== -->

<style>

    /* ==========================================
       LOGO + ICON 3 GẠCH
    ========================================== */

    .may-logo-wrapper {
        width: 220px;

        display: flex;
        align-items: center;

        gap: 8px;

        flex-shrink: 0;
    }


    /* ICON 3 GẠCH */

    .mobile-menu-button {
        width: 24px;
        height: 24px;

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 0;

        border: none;

        background: transparent;

        color: #111;

        cursor: pointer;

        flex-shrink: 0;
    }


    .mobile-menu-button i {
        font-size: 20px;

        line-height: 1;
    }


    .mobile-menu-button:hover {
        color: #555;
    }


    /* ==========================================
       CHỮ MÂY
    ========================================== */

    .may-logo-main {
        font-family: Georgia, "Times New Roman", serif;

        font-size: 25px;

        line-height: 25px;

        letter-spacing: 1px;

        color: #111;

        font-weight: 700;
    }


    .may-logo-sub {
        display: block;

        margin-top: 2px;

        font-size: 5px;

        letter-spacing: 3px;

        color: #999;
    }


    /* ==========================================
       YÊU THÍCH
    ========================================== */

    .favorite-action {
        position: relative;
    }


    .favorite-count {
        position: absolute;

        top: -8px;
        right: -8px;

        width: 15px;
        height: 15px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #111;

        color: #fff;

        font-size: 8px;

        font-weight: 400;
line-height: 15px;
    }


    /* ==========================================
       CART BADGE
    ========================================== */

    .cart-action {
        position: relative;
    }


    .cart-count {
        position: absolute;

        top: -8px;
        right: -8px;

        width: 15px;
        height: 15px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #111;

        color: #fff;

        font-size: 8px;

        font-weight: 400;

        line-height: 15px;
    }


    /* ==========================================
       RESPONSIVE
    ========================================== */

    @media (max-width: 1100px) {

        .may-logo-wrapper {
            width: 170px;
        }

    }


    @media (max-width: 900px) {

        .may-logo-wrapper {
            width: 140px;
        }

    }


    @media (max-width: 768px) {

        .may-logo-wrapper {
            width: auto;
        }

    }

</style>
