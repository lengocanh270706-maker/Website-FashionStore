<?php 
 
require_once  __DIR__ . '/../includes/database.php'; 
require_once  __DIR__ . '/../includes/filter.php'; 
 
$gender = $_GET['gender'] ?? ''; 
 
if ($gender === 'female' || $gender === 'male') { 
    $where .= " AND p.gender = ?"; 
    $params[] = $gender; 
    $types .= "s"; 
} 
 
/* ĐẾM TỔNG SẢN PHẨM */ 
$count_sql = " 
    SELECT COUNT(*) AS total 
    FROM products p 
    $where 
"; 
$count_stmt = $conn->prepare($count_sql); 
if (!empty($params)) { 
    $count_stmt->bind_param($types, ...$params); 
} 
$count_stmt->execute(); 
$count_result = $count_stmt->get_result(); 
$total_products = (int)$count_result->fetch_assoc()['total']; 
$count_stmt->close(); 
 
/* PHÂN TRANG */ 
require_once __DIR__ . '/../includes/pagination.php'; 
 
/* LẤY DANH SÁCH SẢN PHẨM */ 
$product_sql = " 
    SELECT 
        p.id, 
        p.name, 
        p.price, 
        p.quantity, 
        p.main_image, 
        p.description, 
        c.name AS category_name,
        COUNT(pv.id) AS variant_count,
        COALESCE(SUM(pv.quantity),0) AS variant_stock
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN product_variants pv ON p.id = pv.product_id
    $where 
    GROUP BY p.id,p.name,p.price,p.quantity,p.main_image,p.description,c.name,p.created_at
    ORDER BY p.created_at DESC, p.id DESC 
    LIMIT ? OFFSET ? 
"; 
 
$product_stmt = $conn->prepare($product_sql); 
$product_params = $params; 
$product_types = $types . "ii"; 
$product_params[] = $limit; 
$product_params[] = $offset; 
$product_stmt->bind_param( 
    $product_types, 
    ...$product_params 
); 
 
$product_stmt->execute(); 
$products = $product_stmt->get_result(); 
 
/* URL PHÂN TRANG */ 
function buildProductPageUrl($page) 
{ 
    $query = $_GET; 
    $query['page'] = $page; 
    return '?' . http_build_query($query); 
} 
 
include __DIR__ . '/../includes/header.php'; 
include __DIR__ . '/../includes/menu.php'; 
?> 
 
<div class="container-fluid py-4"> 
    <!-- TIÊU ĐỀ --> 
    <div class="mb-4 text-center"> 
        <h4 class="fw-bold mb-1"><i class="bi bi-shop me-2"></i>Sản phẩm</h4> 
        <small class="text-muted">Khám phá các sản phẩm thời trang của Mây</small> 
    </div> 
 
    <!-- SỐ LƯỢNG --> 
    <div class="d-flex justify-content-center align-items-center mb-4"> 
        <span class="text-muted small">Có <strong class="text-dark"><?= $total_products ?></strong> sản phẩm</span> 
    </div> 
 
    <!-- DANH SÁCH SẢN PHẨM --> 
    <div class="row g-4"> 
        <?php if ($products->num_rows > 0): ?> 
            <?php while ($product = $products->fetch_assoc()): ?> 
                <div class="col-12 col-sm-6 col-md-4 col-lg-3"> 
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100"> 
                        <!-- ẢNH --> 
                        <?php if (!empty($product['main_image'])): ?> 
                            <div style="height:300px;"> 
                                <img 
                                    src="../uploads/products/<?= htmlspecialchars($product['main_image']) ?>" 
                                    alt="<?= htmlspecialchars($product['name']) ?>" 
                                    class="w-100 h-100" 
                                    style="object-fit:contain;" 
                                > 
                            </div> 
 
                        <?php else: ?> 
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height:300px;"> 
                                <i class="bi bi-image fs-1 text-secondary"></i> 
                            </div> 
 
                        <?php endif; ?> 
 
                        <!-- THÔNG TIN --> 
                        <div class="card-body d-flex flex-column p-3"> 
                            <small class="text-muted mb-1"><?= htmlspecialchars($product['category_name'] ?? 'Chưa phân loại') ?></small> 
                            <h6 class="fw-semibold mb-2"><?= htmlspecialchars($product['name']) ?></h6> 
                            <div class="fw-bold fs-5 mb-2"> 
                                <?= number_format($product['price'],0,',','.') ?> ₫ 
                            </div> 
 
                            <?php if ((int)$product['variant_count'] > 0 ? (int)$product['variant_stock'] > 0 : (int)$product['quantity'] > 0): ?> 
                                <div class="text-success small mb-3"> 
                                    <i class="bi bi-check-circle me-1"></i>Còn hàng 
                                </div> 
 
                            <?php else: ?> 
                                <div class="text-danger small mb-3"> 
                                    <i class="bi bi-x-circle me-1"></i>Hết hàng 
                                </div> 
                            <?php endif; ?> 
 
                            <a href="product-detail.php?id=<?= (int)$product['id'] ?>" class="btn btn-dark w-100 rounded-pill mt-auto"> 
                                <i class="bi bi-eye me-1"></i>Xem chi tiết 
                            </a> 
                        </div> 
                    </div> 
                </div> 
            <?php endwhile; ?> 
        <?php else: ?> 
 
            <div class="col-12"> 
                <div class="card border-0 shadow-sm rounded-4"> 
                    <div class="card-body text-center py-5"> 
                        <i class="bi bi-box-seam display-4 text-secondary"></i> 
                        <h5 class="fw-bold mt-3">Không tìm thấy sản phẩm</h5> 
                        <p class="text-muted mb-0">Hiện tại không có sản phẩm phù hợp với tìm kiếm của bạn.</p> 
                    </div> 
                </div> 
            </div> 
        <?php endif; ?> 
    </div> 
 
    <!-- PHÂN TRANG --> 
    <?php if ($total_pages > 1): ?> 
        <nav class="mt-5" aria-label="Phân trang sản phẩm"> 
            <ul class="pagination justify-content-center"> 
                <!-- PREVIOUS --> 
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"> 
                    <?php if ($page > 1): ?> 
                        <a 
                            class="page-link rounded-start-pill" 
                            href="<?= htmlspecialchars( 
                                buildProductPageUrl($page - 1) 
                            ) ?>" 
                        > 
                            <i class="bi bi-chevron-left"></i> 
                        </a> 
                    <?php else: ?> 
                        <span class="page-link rounded-start-pill"><i class="bi bi-chevron-left"></i></span> 
                    <?php endif; ?> 
                </li> 
 
                <!-- NUMBER --> 
                <?php for ($i = 1; $i <= $total_pages; $i++): ?> 
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>"> 
                        <a 
                            class="page-link" 
                            href="<?= htmlspecialchars( 
                                buildProductPageUrl($i) 
                            ) ?>" 
                        > 
                            <?= $i ?> 
                        </a> 
                    </li> 
                <?php endfor; ?> 
 
                <!-- NEXT --> 
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>"> 
                    <?php if ($page < $total_pages): ?> 
                        <a 
                            class="page-link rounded-end-pill" 
                            href="<?= htmlspecialchars( 
                                buildProductPageUrl($page + 1) 
                            ) ?>" 
                        > 
                            <i class="bi bi-chevron-right"></i> 
                        </a> 
                    <?php else: ?> 
                        <span class="page-link rounded-end-pill"><i class="bi bi-chevron-right"></i></span> 
                    <?php endif; ?> 
                </li> 
            </ul> 
        </nav> 
    <?php endif; ?> 
</div> 
 
<?php include '../includes/footer.php'; ?>
