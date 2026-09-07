<?php
/* SỐ SẢN PHẨM MỖI TRANG */
$limit = 8;
/* TRANG HIỆN TẠI */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
/* TỔNG SỐ TRANG */
$total_pages = (int)ceil($total_products / $limit);
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
}
/* VỊ TRÍ BẮT ĐẦU */
$offset = ($page - 1) * $limit;