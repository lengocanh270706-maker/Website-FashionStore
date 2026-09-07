<?php
require_once '../../includes/database.php';

$categories=$conn->query("SELECT * FROM categories ORDER BY name");
$error="";
$allowed=['jpg','jpeg','png','gif','webp'];
$upload_dir='../../uploads/products/';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']);
    $category_id=(int)$_POST['category_id'];
    $price=(float)$_POST['price'];
    $quantity=(int)$_POST['quantity'];
    $description=trim($_POST['description']);
    $status=(int)$_POST['status'];

    $colors=array_filter(array_map('trim',$_POST['colors']??[]));
    $sizes=array_filter(array_map('trim',$_POST['sizes']??[]));
    $variant_quantities=$_POST['variant_quantity']??[];

    if($name===""||$category_id<=0||$price<=0) $error="Vui lòng điền đầy đủ thông tin bắt buộc!";
    elseif($_FILES['main_image']['error']!==UPLOAD_ERR_OK) $error="Vui lòng chọn ảnh chính!";
    else{
        if(!is_dir($upload_dir)) mkdir($upload_dir,0777,true);

        $ext=strtolower(pathinfo($_FILES['main_image']['name'],PATHINFO_EXTENSION));
        $base=pathinfo($_FILES['main_image']['name'],PATHINFO_FILENAME);

        if(!in_array($ext,$allowed)) $error="Ảnh không đúng định dạng!";
        else{
            $main_image=$base.'.'.$ext;

            if(!move_uploaded_file($_FILES['main_image']['tmp_name'],$upload_dir.$main_image))
                $error="Không thể tải ảnh lên!";
        }
    }

    if($error===""){
        $stmt=$conn->prepare("INSERT INTO products (category_id,name,price,description,quantity,main_image,status) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("isdsisi",$category_id,$name,$price,$description,$quantity,$main_image,$status);

        if($stmt->execute()){
            $product_id=$conn->insert_id;

            if($colors||$sizes){
                $stmt_variant=$conn->prepare("INSERT INTO product_variants (product_id,color,size,quantity) VALUES (?,?,?,?)");

                $colors=$colors?:[''];
                $sizes=$sizes?:[''];

                foreach($colors as $color){
                    foreach($sizes as $size){
                        $key=md5($color.'_'.$size);
                        $variant_quantity=(int)($variant_quantities[$key]??0);

                        $stmt_variant->bind_param("issi",$product_id,$color,$size,$variant_quantity);
                        $stmt_variant->execute();
                    }
                }

                $stmt_variant->close();
            }

            if(!empty($_FILES['sub_images']['name'])){
                $stmt_img=$conn->prepare("INSERT INTO product_images (product_id,image) VALUES (?,?)");

                foreach($_FILES['sub_images']['name'] as $key=>$file_name){
                    if($_FILES['sub_images']['error'][$key]!==UPLOAD_ERR_OK) continue;

                    $ext=strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
                    if(!in_array($ext,$allowed)) continue;

                    $image=$base.($key+1).'.'.$ext;

                    if(move_uploaded_file($_FILES['sub_images']['tmp_name'][$key],$upload_dir.$image)){
                        $stmt_img->bind_param("is",$product_id,$image);
                        $stmt_img->execute();
                    }
                }

                $stmt_img->close();
            }

            header("Location:index.php");
            exit;
        }

        $error="Lỗi cơ sở dữ liệu: ".$stmt->error;
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm - Mây Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light p-5">

<div class="container" style="max-width:800px">
    <div class="card border-0 shadow-sm p-4 rounded-4">

        <h4 class="fw-bold mb-4">Thêm sản phẩm mới</h4>

        <?php if($error): ?>
        <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label fw-bold">Tên sản phẩm *</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Danh mục *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php while($cat=$categories->fetch_assoc()): ?>
                        <option value="<?=$cat['id']?>"><?=htmlspecialchars($cat['name'])?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Giá bán (VNĐ) *</label>
                    <input type="number" name="price" class="form-control" min="0" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Tồn kho tổng</label>
                    <input type="number" name="quantity" class="form-control" value="0" min="0">
                </div>

            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Màu sắc</label>
                <div id="colors">
                    <div class="input-group mb-2">
                        <input type="text" name="colors[]" class="form-control color-input" placeholder="Ví dụ: Đen">
                        <button type="button" class="btn btn-outline-danger" onclick="removeItem(this)">×</button>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addColor()">+ Thêm màu</button>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Size</label>
                <div id="sizes">
                    <div class="input-group mb-2">
                        <input type="text" name="sizes[]" class="form-control size-input" placeholder="Ví dụ: S">
                        <button type="button" class="btn btn-outline-danger" onclick="removeItem(this)">×</button>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addSize()">+ Thêm size</button>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Tồn kho theo màu / size</label>
                <div id="variants"></div>
            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Ảnh chính *</label>
                    <input type="file" name="main_image" class="form-control" accept="image/*" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Ảnh phụ</label>
                    <input type="file" name="sub_images[]" class="form-control" accept="image/*" multiple>
                </div>

            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Trạng thái</label>
                <select name="status" class="form-select w-50">
                    <option value="1">Còn hàng</option>
                    <option value="0">Hết hàng</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Mô tả sản phẩm</label>
                <textarea name="description" rows="4" class="form-control"></textarea>
            </div>

            <div class="text-end">
                <a href="index.php" class="btn btn-secondary px-4 me-2">Quay lại</a>
                <button type="submit" class="btn btn-dark px-4">Lưu sản phẩm</button>
            </div>

        </form>
    </div>
</div>

<script>
function addColor(){
    document.getElementById('colors').insertAdjacentHTML('beforeend',`
        <div class="input-group mb-2">
            <input type="text" name="colors[]" class="form-control color-input" placeholder="Ví dụ: Đen">
            <button type="button" class="btn btn-outline-danger" onclick="removeItem(this)">×</button>
        </div>
    `);
    updateVariants();
}

function addSize(){
    document.getElementById('sizes').insertAdjacentHTML('beforeend',`
        <div class="input-group mb-2">
            <input type="text" name="sizes[]" class="form-control size-input" placeholder="Ví dụ: S">
            <button type="button" class="btn btn-outline-danger" onclick="removeItem(this)">×</button>
        </div>
    `);
    updateVariants();
}

function removeItem(button){
    button.parentElement.remove();
    updateVariants();
}

function updateVariants(){
    const colors=[...document.querySelectorAll('.color-input')]
        .map(i=>i.value.trim()).filter(Boolean);

    const sizes=[...document.querySelectorAll('.size-input')]
        .map(i=>i.value.trim()).filter(Boolean);

    const box=document.getElementById('variants');
    box.innerHTML='';

    if(!colors.length&&!sizes.length)return;

    const colorList=colors.length?colors:[''];
    const sizeList=sizes.length?sizes:[''];

    colorList.forEach(color=>{
        sizeList.forEach(size=>{
            const key=btoa(unescape(encodeURIComponent(color+'_'+size)));

            box.insertAdjacentHTML('beforeend',`
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <input type="text" class="form-control" value="${color||'Không có màu'}" readonly>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" value="${size||'Không có size'}" readonly>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="variant_quantity[${md5Key(color+'_'+size)}]" class="form-control" value="0" min="0">
                    </div>
                </div>
            `);
        });
    });
}

function md5Key(str){
    let hash=0;
    for(let i=0;i<str.length;i++){
        hash=((hash<<5)-hash)+str.charCodeAt(i);
        hash|=0;
    }
    return Math.abs(hash);
}

document.addEventListener('input',function(e){
    if(e.target.classList.contains('color-input')||e.target.classList.contains('size-input')){
        updateVariants();
    }
});
</script>

</body>
</html>