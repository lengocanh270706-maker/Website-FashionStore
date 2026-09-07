<?php
require_once '../includes/database.php';

// Lấy các banner đang được Admin bật
$result = $conn->query("
    SELECT * 
    FROM banners 
    WHERE status = 1
    ORDER BY id DESC
");

// Gọi header
require_once '../includes/header.php';
require_once '../includes/menu.php';
?>

<main class="banner-page">
    <div class="container">
        <!-- Tiêu đề -->
        <div class="banner-heading">
            <h1>Tin tức & Ưu đãi</h1>
            <p>Cập nhật những thông tin mới nhất từ Mây Store</p>
        </div>

        <!-- Danh sách banner -->
        <div class="row g-4">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="banner-card">
                            <!-- Hình ảnh -->
                            <?php if (!empty($row['image'])): ?>
                                <img
                                    src="../uploads/banners/<?= htmlspecialchars($row['image']) ?>"
                                    alt="<?= htmlspecialchars($row['title']) ?>"
                                    class="banner-image"
                                >
                            <?php else: ?>

                                <div class="banner-image bg-light d-flex align-items-center justify-content-center">
                                    <span class="text-muted">Không có hình ảnh</span>
                                </div>
                            <?php endif; ?>

                            <!-- Nội dung -->
                            <div class="banner-content">
                                <h2 class="banner-title"><?= htmlspecialchars($row['title']) ?></h2>
                                <?php if (!empty($row['description'])): ?>
                                    <p class="banner-description"><?= htmlspecialchars($row['description']) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($row['link'])): ?>
                                    <a href="<?= htmlspecialchars($row['link']) ?>" class="banner-link">Xem chi tiết
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div class="col-12">
                    <div class="empty-banner">
                        <i class="bi bi-newspaper d-block"></i>
                        <h5>Chưa có tin tức</h5>
                        <p>Hiện tại Mây Store chưa có tin tức hoặc chương trình nào.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
// Gọi footer
require_once '../includes/footer.php';
?>