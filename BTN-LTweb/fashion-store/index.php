<?php
require_once '../includes/database.php';
include '../includes/header.php';
include '../includes/menu.php';

$flashSale=null;

$result=$conn->query("
    SELECT id,title,image,link,start_at,end_at
    FROM banners
    WHERE status=1
    AND start_at IS NOT NULL
    AND end_at IS NOT NULL
    AND start_at<=NOW()
    AND end_at>NOW()
    ORDER BY end_at ASC
    LIMIT 1
");

if($result && $result->num_rows>0){
    $flashSale=$result->fetch_assoc();
}
?>

<!-- HERO -->
<section class="home-hero py-3 py-md-4">
    <div class="container px-2 px-md-4">
        <div class="row g-0 align-items-stretch bg-light">

            <div class="col-12 col-md-5 d-flex align-items-center">
                <div class="hero-content p-4 p-md-5 w-100">
                    <p class="hero-eyebrow mb-2 mb-md-3">THE ULTIMATE</p>
                    <h1 class="hero-title mb-2 mb-md-3">Mây Store</h1>
                    <p class="hero-subtitle mb-3 mb-md-4">Each day one style, Enjoy Life</p>
                    <a href="products.php" class="hero-button mb-4">KHÁM PHÁ NGAY
                        <i class="bi bi-chevron-right"></i>
                    </a>

                    <div class="row row-cols-4 g-2">
                        <div class="col">
                            <div class="hero-thumb">
                                <img src="../uploads/images/image1.jpg" alt="Sản phẩm 1">
                            </div>
                        </div>

                        <div class="col">
                            <div class="hero-thumb">
                                <img src="../uploads/images/image2.jpg" alt="Sản phẩm 2">
                            </div>
                        </div>

                        <div class="col">
                            <div class="hero-thumb">
                                <img src="../uploads/images/image3.png" alt="Sản phẩm 3">
                            </div>
                        </div>

                        <div class="col">
                            <div class="hero-thumb">
                                <img src="../uploads/images/image4.jpg" alt="Sản phẩm 4">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-12 col-md-7">
                <div class="hero-image w-100">
                    <img src="../uploads/images/image.png" alt="Mây Fashion Store">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FLASH SALE -->
<?php if($flashSale): ?>
<section class="py-4">
    <div class="container">
        <div class="px-4 py-4 shadow" style="background:#8b7565;color:white;">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <small class="fw-semibold" style="letter-spacing:2px;">FLASH SALE</small>
                    <h2 class="fw-semibold mb-1 mt-2"><?=htmlspecialchars($flashSale['title'])?></h2>
                    <p class="mb-0 opacity-75">Ưu đãi có thời hạn – nhanh tay kẻo lỡ!</p>
                </div>

                <div class="col-md-5">
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <div class="text-center">
                            <span id="days" class="bg-white text-dark d-flex justify-content-center align-items-center fw-semibold" style="width:60px;height:52px;font-size:22px;">00</span>
                            <small class="d-block mt-1">Ngày</small>
                        </div>

                        <span class="fw-bold fs-5">:</span>

                        <div class="text-center">
                            <span id="hours" class="bg-white text-dark d-flex justify-content-center align-items-center fw-semibold" style="width:60px;height:52px;font-size:22px;">00</span>
                            <small class="d-block mt-1">Giờ</small>
                        </div>

                        <span class="fw-bold fs-5">:</span>

                        <div class="text-center">
                            <span id="minutes" class="bg-white text-dark d-flex justify-content-center align-items-center fw-semibold" style="width:60px;height:52px;font-size:22px;">00</span>
                            <small class="d-block mt-1">Phút</small>
                        </div>

                        <span class="fw-bold fs-5">:</span>

                        <div class="text-center">
                            <span id="seconds" class="bg-white text-dark d-flex justify-content-center align-items-center fw-semibold" style="width:60px;height:52px;font-size:22px;">00</span>
                            <small class="d-block mt-1">Giây</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 text-center">
                    <?php if(!empty($flashSale['link'])): ?>
                        <a href="<?=htmlspecialchars($flashSale['link'])?>" class="btn btn-light px-4 py-3 fw-semibold">
                            MUA NGAY <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const endTime=new Date("<?=date('Y-m-d\TH:i:s',strtotime($flashSale['end_at']))?>").getTime();

const countdown=setInterval(()=>{
    const distance=endTime-new Date().getTime();

    if(distance<=0){
        clearInterval(countdown);
        document.querySelector('.py-4').style.display='none';
        return;
    }

    document.getElementById('days').innerText=Math.floor(distance/86400000).toString().padStart(2,'0');
    document.getElementById('hours').innerText=Math.floor(distance%86400000/3600000).toString().padStart(2,'0');
    document.getElementById('minutes').innerText=Math.floor(distance%3600000/60000).toString().padStart(2,'0');
    document.getElementById('seconds').innerText=Math.floor(distance%60000/1000).toString().padStart(2,'0');
},1000);
</script>
<?php endif; ?>

<!-- BENEFITS -->
<section class="benefits">
    <div class="container">
        <div class="benefits-inner">
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-truck"></i>
                </div>

                <div>
                    <h3 class="benefit-title">MIỄN PHÍ GIAO HÀNG</h3>
                    <p class="benefit-text">Đơn từ 300K</p>
                </div>
            </div>

            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </div>

                <div>
                    <h3 class="benefit-title">ĐỔI TRẢ DỄ DÀNG</h3>
                    <p class="benefit-text">Trong vòng 7 ngày</p>
                </div>
            </div>

            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-shield-check"></i>
                </div>

                <div>
                    <h3 class="benefit-title">THANH TOÁN AN TOÀN</h3>
                    <p class="benefit-text">100% bảo mật thông tin</p>
                </div>
            </div>

            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-headset"></i>
                </div>

                <div>
                    <h3 class="benefit-title">HỖ TRỢ 24/7</h3>
                    <p class="benefit-text">1900 1234</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCTS -->
<section class="product-section">
    <div class="container-fluid">
        <div class="section-heading">
            <div>
                <p class="section-label">BỘ SƯU TẬP</p>
                <h2>SẢN PHẨM MỚI</h2>
            </div>

            <a href="products.php">Xem tất cả
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3 g-md-4">

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-new">NEW</span>
                        <img src="../uploads/products/p1.jpg" alt="Sản phẩm 1">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NỮ</p>
                        <p class="product-card-name">Sản phẩm thời trang</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-new">NEW</span>
                        <img src="../uploads/products/p2.jpg" alt="Sản phẩm 2">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NỮ</p>
                        <p class="product-card-name">Sản phẩm thời trang</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-new">NEW</span>
                        <img src="../uploads/products/p3.jpg" alt="Sản phẩm 3">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NỮ</p>
                        <p class="product-card-name">Sản phẩm thời trang</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-new">NEW</span>
                        <img src="../uploads/products/p4.jpg" alt="Sản phẩm 4">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NAM</p>
                        <p class="product-card-name">Sản phẩm thời trang</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-new">NEW</span>
                        <img src="../uploads/products/p5.jpg" alt="Sản phẩm 5">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NAM</p>
                        <p class="product-card-name">Sản phẩm thời trang</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-new">NEW</span>
                        <img src="../uploads/products/p6.jpg" alt="Sản phẩm 6">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NAM</p>
                        <p class="product-card-name">Sản phẩm thời trang</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- COLLECTION BANNER -->
<section class="collection-banner py-3 py-md-4">
    <div class="container px-2 px-md-4">
        <div class="row g-0 align-items-stretch bg-light">

            <div class="col-12 col-md-5 d-flex align-items-center">
                <div class="collection-banner-content p-4 p-md-5 w-100">
                    <p class="collection-label mb-2 mb-md-3">NEW COLLECTION 2026</p>
                    <h2 class="mb-2 mb-md-3">THỜI TRANG<br>CHO PHONG CÁCH CỦA BẠN</h2>
                    <p class="mb-0">Khám phá những thiết kế mới nhất từ Mây Store</p>
                </div>
            </div>

            <div class="col-12 col-md-7">
                <div class="collection-banner-image w-100 h-100">
                    <img src="../uploads/images/collection.jpg" alt="Bộ sưu tập Mây Store">
                </div>
            </div>

        </div>
    </div>
</section>


<!-- BEST SELLERS -->
<section class="product-section best-seller-section">
    <div class="container-fluid">
        <div class="section-heading">
            <div>
                <p class="section-label">ĐƯỢC YÊU THÍCH</p>
                <h2>SẢN PHẨM BÁN CHẠY</h2>
            </div>

            <a href="products.php">Xem tất cả
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3 g-md-4">

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-best">BEST SELLER</span>
                        <img src="../uploads/products/b1.jpg" alt="Sản phẩm bán chạy 1">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NỮ</p>
                        <p class="product-card-name">Đầm nữ thanh lịch</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-best">BEST SELLER</span>
                        <img src="../uploads/products/b2.jpg" alt="Sản phẩm bán chạy 2">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NỮ</p>
                        <p class="product-card-name">Áo nữ phong cách</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-best">BEST SELLER</span>
                        <img src="../uploads/products/b3.jpg" alt="Sản phẩm bán chạy 3">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NỮ</p>
                        <p class="product-card-name">Set đồ nữ hiện đại</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-best">BEST SELLER</span>
                        <img src="../uploads/products/b4.jpg" alt="Sản phẩm bán chạy 4">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NAM</p>
                        <p class="product-card-name">Áo nam basic</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-best">BEST SELLER</span>
                        <img src="../uploads/products/b5.jpg" alt="Sản phẩm bán chạy 5">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NAM</p>
                        <p class="product-card-name">Quần nam thanh lịch</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product-card">
                    <div class="product-card-image">
                        <span class="product-best">BEST SELLER</span>
                        <img src="../uploads/products/b6.jpg" alt="Sản phẩm bán chạy 6">
                    </div>

                    <div class="product-card-info">
                        <p class="product-card-category">THỜI TRANG NAM</p>
                        <p class="product-card-name">Áo sơ mi nam</p>
                        <p class="product-card-price">Liên hệ</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php
$posts=$conn->query("SELECT * FROM posts WHERE status=1 ORDER BY created_at DESC LIMIT 3");
?>

<section class="news-section">
    <div class="container">
        <div class="section-heading news-heading">
            <div>
                <p class="section-label">MÂY MAGAZINE</p>
                <h2>BÀI VIẾT</h2>
            </div>

            <a href="#">Xem tất cả <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="news-grid">
            <?php while($post=$posts->fetch_assoc()): ?>
                <article class="news-card">
                    <div class="news-image">
                        <img src="../uploads/posts/<?=htmlspecialchars($post['image'])?>" alt="<?=htmlspecialchars($post['title'])?>">
                    </div>

                    <div class="news-content">
                        <p class="news-date"><?=date('d.m.Y',strtotime($post['created_at']))?></p>
                        <h3><?=htmlspecialchars($post['title'])?></h3>
                        <p><?=htmlspecialchars(mb_substr(strip_tags($post['content']),0,100))?>...</p>
                        <a href="#">ĐỌC THÊM <i class="bi bi-arrow-right"></i></a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- NEWSLETTER -->
<section class="newsletter">
    <div class="container">
        <div class="newsletter-box">
            <div class="newsletter-content">
                <p class="section-label">STAY CONNECTED</p>
                <h2>NHẬN ƯU ĐÃI TỪ MÂY STORE</h2>
                <p>Đăng ký email để nhận thông tin sản phẩm mới, bộ sưu tập và ưu đãi đặc biệt.</p>
            </div>

            <form class="newsletter-form" action="#" method="POST">
                <input type="email" name="email" placeholder="Nhập email của bạn..." required>
                <button type="submit">ĐĂNG KÝ<i class="bi bi-arrow-right"></i></button>
            </form>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>