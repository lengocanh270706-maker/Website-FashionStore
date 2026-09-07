<?php
/* KHỞI TẠO */
$conditions = ["p.status = 1"];
$params = [];
$types = "";

/*TÌM KIẾM*/
$search = '';
if (isset($_GET['q'])) {
    $search = trim($_GET['q']);
} elseif (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}
if ($search !== '') {
    $conditions[] = "p.name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

/* DANH MỤC*/
$category_id = isset($_GET['category'])
    ? (int)$_GET['category']
    : 0;
if ($category_id > 0) {
    $conditions[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

/*GIÁ THẤP NHẤT*/
$min_price = isset($_GET['min_price'])
    ? (float)$_GET['min_price']
    : 0;
if ($min_price > 0) {
    $conditions[] = "p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}


/*GIÁ CAO NHẤT */
$max_price = isset($_GET['max_price'])
    ? (float)$_GET['max_price']
    : 0;
if ($max_price > 0) {
    $conditions[] = "p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

/* SIZE*/
$size = isset($_GET['size'])
    ? trim($_GET['size'])
    : '';
if ($size !== '') {
    $conditions[] = "
        EXISTS (
            SELECT 1
            FROM product_variants pv_filter
            WHERE pv_filter.product_id = p.id
            AND pv_filter.size = ?
        )
    ";
    $params[] = $size;
    $types .= "s";
}

/*MÀU*/
$color = isset($_GET['color'])
    ? trim($_GET['color'])
    : '';
if ($color !== '') {
    $conditions[] = "
        EXISTS (
            SELECT 1
            FROM product_variants pv_filter
            WHERE pv_filter.product_id = p.id
            AND pv_filter.color = ?
        )
    ";
    $params[] = $color;
    $types .= "s";
}

/*TẠO WHERE*/
$where = " WHERE " . implode(" AND ", $conditions);