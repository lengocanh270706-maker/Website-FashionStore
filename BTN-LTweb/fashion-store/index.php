<?php

require_once '../includes/database.php';

include '../includes/header.php';
include '../includes/menu.php';

?>


<!-- =====================================================
     HERO
===================================================== -->

<section class="home-hero">

    <div class="container">

        <div class="hero-box">


            <!-- =================================================
                 HERO TEXT
            ================================================== -->

            <div class="hero-content">

                <!-- THE ULTIMATE -->
                <p class="hero-eyebrow">
                    <strong>THE ULTIMATE</strong>
                </p>


                <!-- MÂY STORE -->
                <h1 class="hero-title">
                    <strong>Mây Store</strong>
                </h1>


                <p class="hero-subtitle">
                    Each day one style, Enjoy life
                </p>


                <a
                    href="products.php"
                    class="hero-button"
                >

                    KHÁM PHÁ NGAY

                    <i class="bi bi-chevron-right"></i>

                </a>


                <!-- =================================================
                     HERO THUMBNAILS
                ================================================== -->

                <div class="hero-thumbnails">


                    <!-- THUMBNAIL 1 -->

                    <div class="hero-thumb">

                        <img
                            src="../assets/images/products/image1.png"
                            alt="Sản phẩm 1"
                        >

                    </div>


                    <!-- THUMBNAIL 2 -->

                    <div class="hero-thumb">

                        <img
                            src="../assets/images/products/image2.png"
                            alt="Sản phẩm 2"
                        >

                    </div>


                    <!-- THUMBNAIL 3 -->

                    <div class="hero-thumb">

                        <img
                            src="../assets/images/products/image3.png"
                            alt="Sản phẩm 3"
                        >

                    </div>


                </div>

            </div>


            <!-- =================================================
                 HERO BANNER
            ================================================== -->

            <div class="hero-image">

                <img
                    src="../assets/images/banner/image.png"
                    alt="Mây Fashion Store"
                >

            </div>


        </div>

    </div>

</section>


<!-- =====================================================
     BENEFITS
===================================================== -->

<section class="benefits">

    <div class="container">

        <div class="benefits-inner">


            <!-- 1 -->

            <div class="benefit-item">

                <div class="benefit-icon">
                    <i class="bi bi-truck"></i>
                </div>

                <div>

                    <h3 class="benefit-title">
                        MIỄN PHÍ GIAO HÀNG
                    </h3>

                    <p class="benefit-text">
                        Đơn từ 300K
                    </p>

                </div>

            </div>


            <!-- 2 -->

            <div class="benefit-item">

                <div class="benefit-icon">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </div>

                <div>

                    <h3 class="benefit-title">
                        ĐỔI TRẢ DỄ DÀNG
                    </h3>

                    <p class="benefit-text">
                        Trong vòng 7 ngày
                    </p>

                </div>

            </div>


            <!-- 3 -->

            <div class="benefit-item">

                <div class="benefit-icon">
                    <i class="bi bi-shield-check"></i>
                </div>

                <div>

                    <h3 class="benefit-title">
                        THANH TOÁN AN TOÀN
                    </h3>

                    <p class="benefit-text">
                        100% bảo mật thông tin
                    </p>

                </div>

            </div>


            <!-- 4 -->

            <div class="benefit-item">

                <div class="benefit-icon">
                    <i class="bi bi-headset"></i>
                </div>

                <div>

                    <h3 class="benefit-title">
                        HỖ TRỢ 24/7
                    </h3>

                    <p class="benefit-text">
                        1900 1234
                    </p>

                </div>

            </div>


        </div>

    </div>

</section>


<!-- =====================================================
     PRODUCTS
===================================================== -->

<section class="product-section">

    <div class="container">


        <!-- HEADING -->

        <div class="section-heading">

            <h2>
                SẢN PHẨM MỚI
            </h2>

            <a href="products.php">

                Xem tất cả

                <i class="bi bi-arrow-right"></i>

            </a>

        </div>


        <!-- =================================================
             PRODUCT GRID
        ================================================== -->

        <div class="product-grid">


            <!-- PRODUCT 1 -->

            <div class="product-card">

                <div class="product-card-image">

                    <span class="product-new">
                        NEW
                    </span>

                    <button
                        type="button"
                        class="product-favorite"
                        aria-label="Yêu thích sản phẩm 1"
                    >

                        <i class="bi bi-heart"></i>

                    </button>

                    <img
                        src="../assets/images/products/image1.png"
                        alt="Sản phẩm thời trang 1"
                    >

                </div>

                <div class="product-card-info">

                    <p class="product-card-name">
                        Sản phẩm thời trang
                    </p>

                    <p class="product-card-price">
                        Liên hệ
                    </p>

                </div>

            </div>


            <!-- PRODUCT 2 -->

            <div class="product-card">

                <div class="product-card-image">

                    <span class="product-new">
                        NEW
                    </span>

                    <button
                        type="button"
                        class="product-favorite"
                        aria-label="Yêu thích sản phẩm 2"
                    >

                        <i class="bi bi-heart"></i>

                    </button>

                    <img
                        src="../assets/images/products/image2.png"
                        alt="Sản phẩm thời trang 2"
                    >

                </div>

                <div class="product-card-info">

                    <p class="product-card-name">
                        Sản phẩm thời trang
                    </p>

                    <p class="product-card-price">
                        Liên hệ
                    </p>

                </div>

            </div>


            <!-- PRODUCT 3 -->

            <div class="product-card">

                <div class="product-card-image">

                    <span class="product-new">
                        NEW
                    </span>

                    <button
                        type="button"
                        class="product-favorite"
                        aria-label="Yêu thích sản phẩm 3"
                    >

                        <i class="bi bi-heart"></i>

                    </button>

                    <img
                        src="../assets/images/products/image3.png"
                        alt="Sản phẩm thời trang 3"
                    >

                </div>

                <div class="product-card-info">

                    <p class="product-card-name">
                        Sản phẩm thời trang
                    </p>

                    <p class="product-card-price">
                        Liên hệ
                    </p>

                </div>

            </div>


            <!-- PRODUCT 4 -->

            <div class="product-card">

                <div class="product-card-image">

                    <span class="product-new">
                        NEW
                    </span>

                    <button
                        type="button"
                        class="product-favorite"
                        aria-label="Yêu thích sản phẩm 4"
                    >

                        <i class="bi bi-heart"></i>

                    </button>

                    <img
                        src="../assets/images/products/image4.png"
                        alt="Sản phẩm thời trang 4"
                    >

                </div>

                <div class="product-card-info">

                    <p class="product-card-name">
                        Sản phẩm thời trang
                    </p>

                    <p class="product-card-price">
                        Liên hệ
                    </p>

                </div>

            </div>


            <!-- PRODUCT 5 -->

            <div class="product-card">

                <div class="product-card-image">

                    <span class="product-new">
                        NEW
                    </span>

                    <button
                        type="button"
                        class="product-favorite"
                        aria-label="Yêu thích sản phẩm 5"
                    >

                        <i class="bi bi-heart"></i>

                    </button>

                    <img
                        src="../assets/images/products/image5.png"
                        alt="Sản phẩm thời trang 5"
                    >

                </div>

                <div class="product-card-info">

                    <p class="product-card-name">
                        Sản phẩm thời trang
                    </p>

                    <p class="product-card-price">
                        Liên hệ
                    </p>

                </div>

            </div>


            <!-- PRODUCT 6 -->

            <div class="product-card">

                <div class="product-card-image">

                    <span class="product-new">
                        NEW
                    </span>

                    <button
                        type="button"
                        class="product-favorite"
                        aria-label="Yêu thích sản phẩm 6"
                    >

                        <i class="bi bi-heart"></i>

                    </button>

                    <img
                        src="../assets/images/products/image6.png"
                        alt="Sản phẩm thời trang 6"
                    >

                </div>

                <div class="product-card-info">

                    <p class="product-card-name">
                        Sản phẩm thời trang
                    </p>

                    <p class="product-card-price">
                        Liên hệ
                    </p>

                </div>

            </div>


        </div>

    </div>

</section>


<?php

include '../includes/footer.php';

?>
