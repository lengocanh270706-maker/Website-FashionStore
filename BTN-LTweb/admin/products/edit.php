<?php
require_once '../../includes/database.php';

if(!isset($_GET['id'])){header("Location:index.php");exit;}
$id=(int)$_GET['id'];

$categories=$conn->query("SELECT * FROM categories");

$stmt=$conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$product=$stmt->get_result()->fetch_assoc();

if(!$product){header("Location:index.php");exit;}

$images=$conn->query("SELECT * FROM product_images WHERE product_id=$id");
$variants=$conn->query("SELECT * FROM product_variants WHERE product_id=$id ORDER BY id");

if($_SERVER['REQUEST_METHOD']=='POST'){
    $name=trim($_POST['name']);
    $category_id=(int)$_POST['category_id'];
    $price=(float)$_POST['price'];
    $quantity=(int)$_POST['quantity'];
    $description=trim($_POST['description']);
    $status=(int)$_POST['status'];
    $main_image=$product['main_image'];

    if(isset($_FILES['main_image'])&&$_FILES['main_image']['error']==0){
        $ext=strtolower(pathinfo($_FILES['main_image']['name'],PATHINFO_EXTENSION));
        $base=pathinfo($_FILES['main_image']['name'],PATHINFO_FILENAME);
        $main_image=$base.'.'.$ext;
        move_uploaded_file($_FILES['main_image']['tmp_name'],'../../uploads/products/'.$main_image);
    }

    $stmt=$conn->prepare("UPDATE products SET category_id=?,name=?,price=?,description=?,quantity=?,main_image=?,status=? WHERE id=?");
    $stmt->bind_param("isdsisii",$category_id,$name,$price,$description,$quantity,$main_image,$status,$id);

    if($stmt->execute()){
        if(isset($_POST['variant_quantity'])){
            $stmt_variant=$conn->prepare("UPDATE product_variants SET quantity=? WHERE id=? AND product_id=?");

            foreach($_POST['variant_quantity'] as $variant_id=>$variant_quantity){
                $variant_id=(int)$variant_id;
                $variant_quantity=(int)$variant_quantity;
                $stmt_variant->bind_param("iii",$variant_quantity,$variant_id,$id);
                $stmt_variant->execute();
            }
        }

        if(!empty($_FILES['sub_images']['name'][0])){
            $stmt_img=$conn->prepare("INSERT INTO product_images(product_id,image) VALUES(?,?)");
            $base=pathinfo($main_image,PATHINFO_FILENAME);

            foreach($_FILES['sub_images']['name'] as $key=>$file){
                if($_FILES['sub_images']['error'][$key]!=0)continue;

                $ext=strtolower(pathinfo($file,PATHINFO_EXTENSION));
                $image=$base.($key+1).'.'.$ext;

                if(move_uploaded_file($_FILES['sub_images']['tmp_name'][$key],'../../uploads/products/'.$image)){
                    $stmt_img->bind_param("is",$id,$image);
                    $stmt_img->execute();
                }
            }
        }

        header("Location:index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light p-5">

<div class="container" style="max-width:800px">
    <div class="card border-0 shadow-sm p-4 rounded-4">

        <h4 class="fw-bold mb-4">Chỉnh sửa sản phẩm: #SP<?=$product['id']?></h4>

        <form method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label fw-bold">Tên sản phẩm *</label>
                <input type="text" name="name" class="form-control" value="<?=htmlspecialchars($product['name'])?>" required>
            </div>

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Danh mục *</label>
                    <select name="category_id" class="form-select">
                        <?php while($cat=$categories->fetch_assoc()): ?>
                        <option value="<?=$cat['id']?>" <?=$cat['id']==$product['category_id']?'selected':''?>>
                            <?=htmlspecialchars($cat['name'])?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Giá bán (VNĐ) *</label>
                    <input type="number" name="price" class="form-control" value="<?=$product['price']?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Tồn kho</label>
                    <input type="number" name="quantity" id="totalQuantity" class="form-control" value="<?=$product['quantity']?>" min="0" readonly>
                </div>

            </div>

            <?php if($variants&&$variants->num_rows>0): ?>
            <div class="mb-4">
                <label class="form-label fw-bold">Màu / Size / Tồn kho</label>

                <?php while($variant=$variants->fetch_assoc()): ?>
                <div class="row g-2 mb-2">

                    <div class="col-md-4">
                        <input type="text" class="form-control" value="<?=htmlspecialchars($variant['color'])?>" readonly>
                    </div>

                    <div class="col-md-4">
                        <input type="text" class="form-control" value="<?=htmlspecialchars($variant['size'])?>" readonly>
                    </div>

                    <div class="col-md-4">
                        <input type="number"
                               name="variant_quantity[<?=$variant['id']?>]"
                               class="form-control"
                               value="<?=$variant['quantity']?>"
                               min="0">
                    </div>

                </div>
                <?php endwhile; ?>

            </div>
            <?php endif; ?>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Ảnh chính hiện tại</label>

                    <div class="mb-2">
                        <img src="../../uploads/products/<?=htmlspecialchars($product['main_image'])?>"
                             style="width:70px;height:70px;object-fit:cover;border-radius:6px">
                    </div>

                    <input type="file" name="main_image" class="form-control" accept="image/*">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Ảnh phụ hiện tại</label>

                    <div class="d-flex gap-2 flex-wrap mb-2">
                        <?php while($img=$images->fetch_assoc()): ?>
                        <img src="../../uploads/products/<?=htmlspecialchars($img['image'])?>"
                             style="width:70px;height:70px;object-fit:cover;border-radius:6px">
                        <?php endwhile; ?>
                    </div>

                    <label class="form-label">Thêm ảnh phụ mới</label>
                    <input type="file" name="sub_images[]" class="form-control" accept="image/*" multiple>
                </div>

            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Trạng thái</label>
                <select name="status" class="form-select w-50">
                    <option value="1" <?=$product['status']==1?'selected':''?>>Còn hàng</option>
                    <option value="0" <?=$product['status']==0?'selected':''?>>Hết hàng</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Mô tả sản phẩm</label>
                <textarea name="description" rows="4" class="form-control"><?=htmlspecialchars($product['description'])?></textarea>
            </div>

            <div class="text-end">
                <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
                <button type="submit" class="btn btn-dark px-4">Cập nhật thay đổi</button>
            </div>

        </form>

    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const totalQuantity = document.getElementById('totalQuantity');
    const variantInputs = document.querySelectorAll('input[name^="variant_quantity["]');

    function updateTotalQuantity() {
        let total = 0;

        variantInputs.forEach(function (input) {
            total += parseInt(input.value) || 0;
        });

        if (totalQuantity) {
            totalQuantity.value = total;
        }
    }

    variantInputs.forEach(function (input) {
        input.addEventListener('input', updateTotalQuantity);
    });

    // Tính ngay khi mở trang
    updateTotalQuantity();
});
</script>

</body>
</html>