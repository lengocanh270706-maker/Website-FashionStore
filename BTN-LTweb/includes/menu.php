<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../includes/database.php";

$user = $_SESSION['user'] ?? null;
$isLoggedIn = isset($_SESSION['user']);
$isAdmin = $isLoggedIn && (($user['role'] ?? '') === 'admin');

$cart_count = 0;

if ($isLoggedIn) {
    $user_id = (int)$user['id'];

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM cart_items ci
        INNER JOIN cart c ON ci.cart_id = c.id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $cart_count = (int)$result->fetch_assoc()['total'];

    $stmt->close();
}
?>

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-container">
        <div class="topbar-left">
            <span>Miễn phí vận chuyển cho đơn từ 300K</span>
        </div>

        <div class="topbar-mid">
            <span>Đổi trả dễ dàng trong 7 ngày</span>
        </div>

        <div class="topbar-right">
            <span><i class="bi bi-telephone"></i>Hotline: 1900 1234</span>
        </div>
    </div>
</div>

<!-- MAIN NAVBAR -->
<nav class="main-navbar">
    <div class="navbar-container">

        <!-- LOGO + MENU ICON -->
        <div class="may-logo-wrapper">
            <button type="button" class="mobile-menu-button" aria-label="Mở menu">
                <i class="bi bi-list"></i>
            </button>

            <a href="index.php" class="may-logo">
                <span class="may-logo-main">MÂY</span>
                <span class="may-logo-sub">FASHION STORE</span>
            </a>
        </div>

        <!-- NAVIGATION -->
        <div class="main-nav">
            <a href="index.php" class="nav-item">Trang chủ</a>
            <a href="products.php?gender=female" class="nav-item">Nữ</a>
            <a href="products.php?gender=male" class="nav-item">Nam</a>
            <a href="products.php" class="nav-item">Shop</a>
            <a href="collections.php" class="nav-item">Bộ sưu tập</a>
            <a href="banners.php" class="nav-item">Tin tức</a>
        </div>

        <!-- NAVBAR ACTIONS -->
        <div class="navbar-actions">

            <!-- SEARCH -->
            <form action="search.php" method="GET" class="header-search">
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

            <!-- ACCOUNT -->
            <div class="header-account">
                <?php if ($isLoggedIn): ?>

                    <a href="profile.php" class="header-action">
                        <i class="bi bi-person"></i>
                        <span><?= htmlspecialchars($user['name'] ?? 'Tài khoản') ?></span>
                    </a>

                    <div class="account-dropdown">
                        <a href="profile.php">
                            <i class="bi bi-person"></i>Thông tin tài khoản
                        </a>

                        <a href="change-password.php">
                            <i class="bi bi-lock"></i>Đổi mật khẩu
                        </a>

                        <?php if ($isAdmin): ?>
                            <a href="../admin/dashboard.php">
                                <i class="bi bi-speedometer2"></i>Trang quản trị
                            </a>
                        <?php endif; ?>

                        <div class="dropdown-divider"></div>

                        <a href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>Đăng xuất
                        </a>
                    </div>

                <?php else: ?>

                    <a href="login.php" class="header-action">
                        <i class="bi bi-person"></i>
                        <span>Tài khoản</span>
                    </a>

                <?php endif; ?>
            </div>

            <!-- CART -->
            <a href="cart.php" class="header-action cart-action">
                <i class="bi bi-bag"></i>
                <span>Giỏ hàng</span>
                <b class="cart-count"><?= $cart_count ?></b>
            </a>

        </div>
    </div>
</nav>