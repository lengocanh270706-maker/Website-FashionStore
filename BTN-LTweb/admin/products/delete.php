<?php
require_once '../../includes/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM product_images WHERE product_id = $id");
    $conn->query("DELETE FROM products WHERE id = $id");
}

header("Location: index.php");
exit();
?>