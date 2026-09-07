<?php
require_once '../../includes/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT image FROM banners WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$banner = $stmt->get_result()->fetch_assoc();

if ($banner) {
    $stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if (!empty($banner['image'])) {
        $image = '../../uploads/banners/' . $banner['image'];

        if (file_exists($image)) {
            unlink($image);
        }
    }
}

header('Location: index.php');
exit;