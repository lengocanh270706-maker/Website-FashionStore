<?php
require_once '../../includes/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM categories WHERE id = $id");
}

header("Location: index.php");
exit();
?>