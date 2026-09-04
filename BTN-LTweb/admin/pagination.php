<?php

// Số sản phẩm trên một trang
$limit = 8;

// Trang hiện tại
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

if ($page < 1) {
    $page = 1;
}

// Vị trí bắt đầu
$offset = ($page - 1) * $limit;


// Tính tổng số trang
$total_pages = ceil($total_products / $limit);

?>