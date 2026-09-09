console.log('SCRIPT.JS ĐÃ CHẠY');

document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('.alert').forEach(a=>{
        setTimeout(()=>{
            a.style.transition='opacity 0.5s';
            a.style.opacity='0';
            setTimeout(()=>a.remove(),500);
        },4000);
    });

    const editButton=document.getElementById('profileEditButton');
    const cancelButton=document.getElementById('profileCancelButton');
    const info=document.getElementById('profileInfo');
    const form=document.getElementById('profileEditForm');

    editButton?.addEventListener('click',()=>{
        info.classList.add('d-none');
        form.classList.remove('d-none');
        editButton.classList.add('d-none');
    });

    cancelButton?.addEventListener('click',()=>{
        form.classList.add('d-none');
        info.classList.remove('d-none');
        editButton.classList.remove('d-none');
    });

    const addressButton=document.getElementById('addressEditButton');
    const addressInfo=document.getElementById('addressInfo');
    const addressForm=document.getElementById('addressEditForm');
    const addressCancel=document.getElementById('addressCancelButton');

    addressButton?.addEventListener('click',()=>{
        addressInfo.classList.add('d-none');
        addressForm.classList.remove('d-none');
        addressButton.classList.add('d-none');
    });

    addressCancel?.addEventListener('click',()=>{
        addressForm.classList.add('d-none');
        addressInfo.classList.remove('d-none');
        addressButton.classList.remove('d-none');
    });
});

function toggleChangePassword(id,button){
    const input=document.getElementById(id);
    const icon=button.querySelector('i');
    if(input.type==='password'){
        input.type='text';
        icon.classList.replace('bi-eye','bi-eye-slash');
    }else{
        input.type='password';
        icon.classList.replace('bi-eye-slash','bi-eye');
    }
}

const variants=window.productVariants||[];
let selectedColor='';
let selectedSize='';

document.querySelectorAll('.color-option').forEach(button=>{
    button.addEventListener('click',function(){
        document.querySelectorAll('.color-option').forEach(item=>{
            item.classList.remove('active','btn-dark');
            item.classList.add('btn-outline-dark');
        });

        this.classList.remove('btn-outline-dark');
        this.classList.add('active','btn-dark');

        selectedColor=String(this.dataset.color||'').trim();
        checkVariant();
    });
});

document.querySelectorAll('.size-option').forEach(button=>{
    button.addEventListener('click',function(){
        document.querySelectorAll('.size-option').forEach(item=>{
            item.classList.remove('active','btn-dark');
            item.classList.add('btn-outline-dark');
        });

        this.classList.remove('btn-outline-dark');
        this.classList.add('active','btn-dark');

        selectedSize=String(this.dataset.size||'').trim();
        checkVariant();
    });
});

function checkVariant(){
    const variantId=document.getElementById('variant_id');
    const quantity=document.getElementById('quantity');
    const stockText=document.getElementById('stockText');
    const message=document.getElementById('variantMessage');
    const addButton=document.getElementById('addCartBtn');

    if(!variants.length||!variantId||!quantity||!stockText||!message||!addButton)return;

    const needColor=window.hasColors;
    const needSize=window.hasSizes;

    if(needColor&&!selectedColor){
        variantId.value='';
        stockText.textContent='Vui lòng chọn màu';
        addButton.disabled=true;
        return;
    }

    if(needSize&&!selectedSize){
        variantId.value='';
        stockText.textContent='Vui lòng chọn size';
        addButton.disabled=true;
        return;
    }

    const found=variants.find(v=>{
        const color=String(v.color??'').trim();
        const size=String(v.size??'').trim();

        return (!needColor||color===selectedColor)&&
               (!needSize||size===selectedSize);
    });

    if(!found){
        variantId.value='';
        stockText.textContent='Phân loại này không tồn tại';
        message.textContent='Vui lòng chọn lại màu hoặc size.';
        addButton.disabled=true;
        return;
    }

    const stock=parseInt(found.quantity)||0;

    variantId.value=String(found.id);

    if(stock<=0){
        stockText.innerHTML='<span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Hết hàng</span>';
        addButton.disabled=true;
        message.textContent='Phiên bản này hiện đã hết hàng.';
        return;
    }

    stockText.innerHTML='<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Còn '+stock+' sản phẩm</span>';

    quantity.max=stock;

    if(parseInt(quantity.value)>stock){
        quantity.value=stock;
    }

    addButton.disabled=false;
    message.textContent='';
}

document.getElementById('cartForm')?.addEventListener('submit',function(e){
    const variantId=document.getElementById('variant_id');
    const quantity=document.getElementById('quantity');

    if(variants.length){
        if(!variantId||!variantId.value||parseInt(variantId.value)<=0){
            e.preventDefault();

            const message=document.getElementById('variantMessage');

            if(message){
                message.textContent='Vui lòng chọn đầy đủ phân loại sản phẩm trước khi thêm vào giỏ hàng.';
            }

            return;
        }
    }

    if(quantity&&parseInt(quantity.value)<=0){
        e.preventDefault();
        quantity.value=1;
    }
});

function changeMainImage(element,imageUrl){
    const mainImage=document.getElementById('mainProductImage');
    if(!mainImage)return;

    if(mainImage.tagName==='IMG'){
        mainImage.src=imageUrl;
    }else{
        const img=document.createElement('img');
        img.id='mainProductImage';
        img.src=imageUrl;
        img.alt=window.productName||'';
        img.className='w-100';
        img.style.height='520px';
        img.style.objectFit='contain';
        mainImage.replaceWith(img);
    }

    document.querySelectorAll('.product-thumbnail').forEach(item=>item.classList.remove('border-dark'));
    element.classList.add('border-dark');
}

function setupAddress(provinceId,wardId,detailId,addressId,oldProvince='',oldWard=''){
    const province=document.getElementById(provinceId);
    const ward=document.getElementById(wardId);
    const detail=document.getElementById(detailId);
    const address=document.getElementById(addressId);

    if(!province||!ward||!detail||!address)return null;

    fetch('https://provinces.open-api.vn/api/v2/')
        .then(res=>res.json())
        .then(data=>{
            data.forEach(p=>province.innerHTML+=`<option value="${p.code}">${p.name}</option>`);

            if(oldProvince){
                [...province.options].forEach(o=>{
                    if(o.text===oldProvince)province.value=o.value;
                });
                province.dispatchEvent(new Event('change'));
            }
        });

    province.addEventListener('change',()=>{
        ward.innerHTML='<option value="">-- Phường / Xã --</option>';
        ward.disabled=true;
        if(!province.value)return;

        fetch(`https://provinces.open-api.vn/api/v2/p/${province.value}?depth=2`)
            .then(res=>res.json())
            .then(data=>{
                data.wards.forEach(w=>ward.innerHTML+=`<option value="${w.code}">${w.name}</option>`);
                ward.disabled=false;

                if(oldWard){
                    [...ward.options].forEach(o=>{
                        if(o.text===oldWard)ward.value=o.value;
                    });
                }
            });
    });

    document.addEventListener('DOMContentLoaded', function () {

    const changeBtn = document.getElementById('changeAddressBtn');
    const savedBox = document.getElementById('savedAddressBox');
    const changeBox = document.getElementById('changeAddressBox');
    const addressInput = document.getElementById('address');
    const province = document.getElementById('province');
    const ward = document.getElementById('ward');
    const detailAddress = document.getElementById('detail_address');

    // Bấm "Thay đổi"
    if (changeBtn) {
        changeBtn.addEventListener('click', function () {
            savedBox.classList.add('d-none');
            changeBox.classList.remove('d-none');
            if (province) province.required = true;
            if (ward) ward.required = true;
            if (detailAddress) detailAddress.required = true;
            // Không dùng địa chỉ cũ nữa
            addressInput.value = '';
        });
    }

    // Ghép địa chỉ mới
    function updateAddress() {
        if (!addressInput) return;
        const provinceText = province?.options[province.selectedIndex]?.text || '';
        const wardText =ward?.options[ward.selectedIndex]?.text || '';
        const detail =detailAddress?.value.trim() || '';
        let parts = [];
        if (detail) {
            parts.push(detail);
        }

        if (ward && ward.value) {
            parts.push(wardText);
        }

        if (province && province.value) {
            parts.push(provinceText);
        }

        addressInput.value = parts.join(', ');
    }

    province?.addEventListener('change', updateAddress);
    ward?.addEventListener('change', updateAddress);
    detailAddress?.addEventListener('input', updateAddress);
});

    function updateAddress(){
        address.value=[
            detail.value.trim(),
            ward.value&&ward.options[ward.selectedIndex].text,
            province.value&&province.options[province.selectedIndex].text
        ].filter(Boolean).join(', ');
    }

    ward.addEventListener('change',updateAddress);
    detail.addEventListener('input',updateAddress);

    return ()=>{
        if(!province.value||!ward.value||!detail.value.trim()){
            alert('Vui lòng nhập đầy đủ địa chỉ giao hàng!');
            return false;
        }
        updateAddress();
        return true;
    };
}

const checkoutAddress=setupAddress('province','ward','detail_address','address');

function changeAddress(){
    const saved=document.getElementById('savedAddressBox');
    const newBox=document.getElementById('newAddressBox');
    if(saved)saved.style.display='none';
    if(newBox)newBox.style.display='block';
}

document.getElementById('checkoutForm')?.addEventListener('submit',e=>{
    const newBox=document.getElementById('newAddressBox');
    const address=document.getElementById('address');

    if(newBox&&newBox.style.display!=='none'&&checkoutAddress&&!checkoutAddress()){
        e.preventDefault();
        return;
    }

    if(address&&!address.value.trim()){
        e.preventDefault();
        alert('Vui lòng nhập địa chỉ giao hàng!');
    }
});

const saveAddress1=setupAddress('province1','ward1','detail1','address1');
const saveAddress2=setupAddress('province2','ward2','detail2','address2');

document.getElementById('profileEditForm')?.addEventListener('submit',e=>{
    if(saveAddress1&&!saveAddress1())e.preventDefault();
});

document.getElementById('addressEditForm')?.addEventListener('submit',e=>{
    if(saveAddress2&&!saveAddress2())e.preventDefault();
});

const type=document.getElementById('link_type');
const productId=document.getElementById('product_id');
const link=document.getElementById('link');
const productBox=document.getElementById('product_box');

type?.addEventListener('change',function(){
    const show=this.value==='product';

    if(productId)productId.style.display=show?'block':'none';
    if(productBox)productBox.style.display=show?'block':'none';

    if(link){
        if(this.value==='home'){
            link.value='/Website-FashionStore/BTN-LTweb/fashion-store/index.php';
        }else if(this.value==='products'){
            link.value='/Website-FashionStore/BTN-LTweb/fashion-store/products.php';
        }else{
            link.value='';
        }
    }

    if(productId)productId.required=show;
});

productId?.addEventListener('input',function(){
    if(this.value&&link){
        link.value='/Website-FashionStore/BTN-LTweb/fashion-store/product-detail.php?id='+this.value;
    }
});

const orderAddress=setupAddress(
    'province',
    'ward',
    'detail_address',
    'address',
    window.oldProvince||'',
    window.oldWard||''
);

document.getElementById('orderForm')?.addEventListener('submit',e=>{
    if(orderAddress&&!orderAddress())e.preventDefault();
});

