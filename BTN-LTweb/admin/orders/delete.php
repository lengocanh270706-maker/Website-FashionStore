<?php
require_once '../../includes/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM order_items WHERE order_id = $id");
    $conn->query("DELETE FROM orders WHERE id = $id");
}

header("Location: index.php");
exit();
?>