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
    <div class="container-fluid px-3">
        <div class="row align-items-center text-center">

            <div class="col-12 col-md-4">
                <span>Miễn phí vận chuyển cho đơn từ 300K</span>
            </div>

            <div class="col-md-4  ">
                <span>Đổi trả dễ dàng trong 7 ngày</span>
            </div>

            <div class="col-md-4  ">
                <span>
                    <i class="bi bi-telephone"></i>
                    Hotline: 1900 1234
                </span>
            </div>

        </div>
    </div>
</div>

<!-- MAIN NAVBAR -->
<nav class="main-navbar navbar navbar-expand-lg">
    <div class="navbar-container container-fluid">
        <div class="may-logo-wrapper d-flex align-items-center">
            <button class="navbar-toggler p-0 me-2" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#mainMenu"
                    aria-controls="mainMenu"
                    aria-expanded="false"
                    aria-label="Mở menu">
                <i class="bi bi-list"></i>
            </button>

            <a href="index.php" class="may-logo">
                <span class="may-logo-main">MÂY</span>
                <span class="may-logo-sub">FASHION STORE</span>
            </a>
        </div>

        <!-- MENU -->
        <div class="collapse navbar-collapse" id="mainMenu">
            <!-- NAVIGATION -->
            <div class="main-nav navbar-nav">
                <a href="index.php" class="nav-item nav-link">Trang chủ</a>
                <div class="dropdown">
                    <button class="nav-item nav-link dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        Shop
                    </button>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="products.php?gender=female">Nữ</a></li>
                        <li><a class="dropdown-item" href="products.php?gender=male">Nam</a></li>
                    </ul>
                </div>
                <a href="collections.php" class="nav-item nav-link">Bộ sưu tập</a>
                <a href="banners.php" class="nav-item nav-link">Tin tức</a>
            </div>

            <!-- ACTIONS -->
            <div class="navbar-actions ms-lg-auto">
                <!-- SEARCH -->
                <form action="search.php" method="GET" class="header-search">
                    <input
                        type="text"
                        name="q"
                        placeholder="Tìm kiếm sản phẩm..."
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                    >
                    <button type="submit"><i class="bi bi-search"></i></button>
                </form>

                <!-- ACCOUNT -->
                <div class="header-account">
                    <?php if ($isLoggedIn): ?>
                        <a href="profile.php" class="header-action">
                            <i class="bi bi-person"></i>
                            <span><?= htmlspecialchars($user['name'] ?? 'Tài khoản') ?></span>
                        </a>

                        <div class="account-dropdown">
                            <a href="profile.php"><i class="bi bi-person"></i>Thông tin tài khoản</a>
                            <a href="change-password.php"><i class="bi bi-lock"></i>Đổi mật khẩu</a>
                            <?php if ($isAdmin): ?>
                                <a href="../admin/dashboard.php"><i class="bi bi-speedometer2"></i>Trang quản trị</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php"><i class="bi bi-box-arrow-right"></i>Đăng xuất</a>
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
    </div>
</nav>