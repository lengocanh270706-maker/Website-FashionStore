<?php

// Lấy dữ liệu bộ lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$size = isset($_GET['size']) ? trim($_GET['size']) : '';
$color = isset($_GET['color']) ? trim($_GET['color']) : '';


// Mảng điều kiện SQL
$conditions = [];
$params = [];
$types = '';


// Tìm kiếm theo tên sản phẩm
if ($search != '') {
    $conditions[] = "p.name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}


// Lọc theo danh mục
if ($category_id > 0) {
    $conditions[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}


// Lọc giá thấp nhất
if ($min_price > 0) {
    $conditions[] = "p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}


// Lọc giá cao nhất
if ($max_price > 0) {
    $conditions[] = "p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}


// Lọc size
if ($size != '') {
    $conditions[] = "pv.size = ?";
    $params[] = $size;
    $types .= "s";
}


// Lọc màu
if ($color != '') {
    $conditions[] = "pv.color = ?";
    $params[] = $color;
    $types .= "s";
}


// Tạo WHERE
$where = '';

if (count($conditions) > 0) {
    $where = " WHERE " . implode(" AND ", $conditions);
}
?>