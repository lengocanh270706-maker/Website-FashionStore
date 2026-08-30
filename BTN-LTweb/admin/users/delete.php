<?php
require_once '../../includes/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Thực hiện xóa người dùng theo ID
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Chuyển hướng về lại trang danh sách người dùng
header("Location: index.php");
exit();
?>