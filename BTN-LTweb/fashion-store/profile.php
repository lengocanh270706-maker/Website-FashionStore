<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = currentUser();
$message = '';
$error = '';
$userId = (int)$user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_avatar' && isset($_FILES['avatar'])) {
        $avatar = $_FILES['avatar'];

        if ($avatar['error'] !== UPLOAD_ERR_OK) {
            $error = 'Vui lòng chọn ảnh để tải lên.';
        } elseif ($avatar['size'] > 5 * 1024 * 1024) {
            $error = 'Ảnh không được lớn hơn 5MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($avatar['tmp_name']);
            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];

            if (!isset($allowed[$mime])) {
                $error = 'Chỉ được upload ảnh JPG, PNG hoặc WEBP.';
            } else {
                $uploadDir = __DIR__ . '/../assets/uploads/avatars/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileName = 'avatar_' . $userId . '_' . time() . '.' . $allowed[$mime];
                $uploadPath = $uploadDir . $fileName;
                $avatarUrl = '../uploads/avatars/' . $fileName;

                if (move_uploaded_file($avatar['tmp_name'], $uploadPath)) {
                    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $stmt->bind_param("si", $avatarUrl, $userId);

                    if ($stmt->execute()) {
                        $_SESSION['user']['avatar'] = $avatarUrl;
                        $message = 'Cập nhật ảnh đại diện thành công!';
                    } else {
                        $error = 'Không thể lưu ảnh đại diện.';
                    }
                    $stmt->close();
                } else {
                    $error = 'Không thể tải ảnh lên máy chủ.';
                }
            }
        }
    }

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $dob = trim($_POST['dob'] ?? '');
        $gender = trim($_POST['gender'] ?? '');

        if ($name === '') {
            $error = 'Vui lòng nhập họ và tên.';
        } else {
            $stmt = $conn->prepare("
                UPDATE users
                SET name = ?, phone = ?, address = ?, dob = NULLIF(?, ''), gender = NULLIF(?, '')
                WHERE id = ?
            ");
            $stmt->bind_param("sssssi", $name, $phone, $address, $dob, $gender, $userId);

            if ($stmt->execute()) {
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['phone'] = $phone;
                $_SESSION['user']['address'] = $address;
                $_SESSION['user']['dob'] = $dob;
                $_SESSION['user']['gender'] = $gender;
                $message = 'Cập nhật thông tin thành công!';
            } else {
                $error = 'Không thể cập nhật thông tin.';
            }
            $stmt->close();
        }
    }

    if (isset($_POST['remove_avatar'])) {
        $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();

        if (!empty($old['avatar'])) {
            $file = __DIR__ . '/../assets/uploads/avatars/' . basename($old['avatar']);
            if (file_exists($file)) unlink($file);
        }

        $stmt = $conn->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        header("Location: profile.php");
        exit;
    }
}

$stmt = $conn->prepare("
    SELECT id, name, email, phone, address, avatar, dob, gender, role, status, created_at
    FROM users WHERE id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?: $user;
$stmt->close();
$userName = $user['name'] ?? 'Khách hàng';
$userEmail = $user['email'] ?? '';
$userPhone = $user['phone'] ?? '';
$userAddress = $user['address'] ?? '';
$userAvatar = $user['avatar'] ?? '';
$userDob = $user['dob'] ?? '';
$userGender = $user['gender'] ?? '';
$userCreatedAt = $user['created_at'] ?? '';

function formatDate($date) {
    return empty($date) ? 'Chưa cập nhật' : date('d/m/Y', strtotime($date));
}

function formatGender($gender) {
    if (empty($gender)) return 'Chưa cập nhật';
    if (strtolower($gender) === 'female' || strtolower($gender) === 'nữ') return 'Nữ';
    if (strtolower($gender) === 'male' || strtolower($gender) === 'nam') return 'Nam';
    return htmlspecialchars($gender);
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<div class="container-fluid py-4">
    <div class="mb-4 text-center">
        <h4 class="fw-bold mb-1"><i class="bi bi-person me-2"></i>Thông tin tài khoản</h4>
        <small class="text-muted">Quản lý thông tin cá nhân và tài khoản của bạn</small>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3">
            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3">
            <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body text-center p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_avatar">
                        <label for="avatarUpload" class="d-inline-block position-relative" style="cursor:pointer;">
                            <?php if ($userAvatar): ?>
                                <img src="<?= htmlspecialchars($userAvatar) ?>" class="rounded-circle border" style="width:110px;height:110px;object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width:110px;height:110px;">
                                    <i class="bi bi-person fs-1 text-secondary"></i>
                                </div>
                            <?php endif; ?>
                            <span class="position-absolute bottom-0 end-0 bg-dark text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                                <i class="bi bi-camera"></i>
                            </span>
                        </label>
                        <input type="file" name="avatar" id="avatarUpload" accept=".jpg,.jpeg,.png,.webp" hidden onchange="this.form.submit()">
                    </form>

                    <h6 class="fw-bold mt-3 mb-1"><?= htmlspecialchars($userName) ?></h6>
                    <small class="text-muted d-block mb-2"><?= htmlspecialchars($userPhone ?: 'Chưa cập nhật SĐT') ?></small>
                    <span class="badge text-bg-dark rounded-pill"><i class="bi bi-patch-check me-1"></i>Thành viên</span>

                    <hr>

                    <?php if (!empty($user['avatar'])): ?>
                        <form method="POST">
                            <button type="submit" name="remove_avatar" class="btn btn-outline-danger btn-sm">Gỡ avatar</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action active py-3"><i class="bi bi-person me-2"></i>Thông tin tài khoản</a>
                    <a href="change-password.php" class="list-group-item list-group-item-action py-3"><i class="bi bi-lock me-2"></i>Đổi mật khẩu</a>
                    <a href="#" class="list-group-item list-group-item-action py-3"><i class="bi bi-geo-alt me-2"></i>Địa chỉ của tôi</a>
                    <a href="orders.php" class="list-group-item list-group-item-action py-3"><i class="bi bi-bag me-2"></i>Đơn hàng của tôi</a>
                    <a href="#" class="list-group-item list-group-item-action py-3"><i class="bi bi-star me-2"></i>Đánh giá của tôi</a>
                    <a href="logout.php" class="list-group-item list-group-item-action py-3 text-danger"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-2">Cần hỗ trợ?</h6>
                    <p class="text-muted small mb-3">Liên hệ với chúng tôi 24/7</p>
                    <div class="small mb-2"><i class="bi bi-telephone me-2"></i>1900 1234</div>
                    <div class="small"><i class="bi bi-envelope me-2"></i>support@may.com</div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Thông tin cá nhân</h5>
                        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3" id="profileEditButton">
                            <i class="bi bi-pencil me-1"></i>Chỉnh sửa
                        </button>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div id="profileInfo">
                        <div class="row g-4">
                            <div class="col-md-3 text-center">
                                <?php if ($userAvatar): ?>
                                    <img src="<?= htmlspecialchars($userAvatar) ?>" class="rounded-circle border" style="width:140px;height:140px;object-fit:cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light border mx-auto d-flex align-items-center justify-content-center" style="width:140px;height:140px;">
                                        <i class="bi bi-person display-5 text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-6"><small class="text-muted d-block mb-1">Họ và tên</small><strong><?= htmlspecialchars($userName) ?></strong></div>
                                    <div class="col-md-6"><small class="text-muted d-block mb-1">Số điện thoại</small><strong><?= htmlspecialchars($userPhone ?: 'Chưa cập nhật') ?></strong></div>
                                    <div class="col-md-6"><small class="text-muted d-block mb-1">Email</small><strong><?= htmlspecialchars($userEmail ?: 'Chưa cập nhật') ?></strong></div>
                                    <div class="col-md-6"><small class="text-muted d-block mb-1">Ngày sinh</small><strong><?= formatDate($userDob) ?></strong></div>
                                    <div class="col-md-6"><small class="text-muted d-block mb-1">Giới tính</small><strong><?= formatGender($userGender) ?></strong></div>
                                    <div class="col-md-6"><small class="text-muted d-block mb-1">Ngày tham gia</small><strong><?= formatDate($userCreatedAt) ?></strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" id="profileEditForm" class="d-none mt-4">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Họ và tên *</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($userName) ?>" class="form-control rounded-3" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" value="<?= htmlspecialchars($userEmail) ?>" class="form-control rounded-3" disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số điện thoại</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($userPhone) ?>" class="form-control rounded-3">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ngày sinh</label>
                                <input type="date" name="dob" value="<?= htmlspecialchars($userDob) ?>" class="form-control rounded-3">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Giới tính</label>
                                <select name="gender" class="form-select rounded-3">
                                    <option value="">Chưa cập nhật</option>
                                    <option value="Nam" <?= $userGender === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                    <option value="Nữ" <?= $userGender === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                                    <option value="Khác" <?= $userGender === 'Khác' ? 'selected' : '' ?>>Khác</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Địa chỉ giao hàng</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <select id="province1" class="form-select rounded-3" required>
                                            <option value="">-- Tỉnh / Thành phố --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select id="ward1" class="form-select rounded-3" required disabled>
                                            <option value="">-- Phường / Xã --</option>
                                        </select>
                                    </div>
                                </div>

                                <input type="text" id="detail1" class="form-control rounded-3 mt-2" placeholder="Số nhà, tên đường..." required>
                                <input type="hidden" name="address" id="address1">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-light rounded-pill px-4" id="profileCancelButton">Hủy</button>
                            <button type="submit" class="btn btn-dark rounded-pill px-4"><i class="bi bi-check2 me-1"></i>Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Địa chỉ mặc định</h5>
                        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3" id="addressEditButton">
                            <i class="bi bi-pencil me-1"></i>Chỉnh sửa
                        </button>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div id="addressInfo" class="d-flex gap-3">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
                            <i class="bi bi-geo-alt fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold mb-1">
                                <?= htmlspecialchars($userName) ?>
                                <span class="badge text-bg-light border rounded-pill ms-2">Mặc định</span>
                            </div>
                            <div class="text-muted small">
                                <?= nl2br(htmlspecialchars($userAddress ?: 'Chưa cập nhật địa chỉ')) ?>
                                <?php if ($userPhone): ?><br><?= htmlspecialchars($userPhone) ?><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <form method="POST" id="addressEditForm" class="d-none">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($userName) ?>">
                        <input type="hidden" name="phone" value="<?= htmlspecialchars($userPhone) ?>">
                        <input type="hidden" name="dob" value="<?= htmlspecialchars($userDob) ?>">
                        <input type="hidden" name="gender" value="<?= htmlspecialchars($userGender) ?>">

                        <label class="form-label fw-semibold">Địa chỉ giao hàng</label>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <select id="province2" class="form-select rounded-3" required>
                                    <option value="">-- Tỉnh / Thành phố --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select id="ward2" class="form-select rounded-3" required disabled>
                                    <option value="">-- Phường / Xã --</option>
                                </select>
                            </div>
                        </div>

                        <input type="text" id="detail2" class="form-control rounded-3 mt-2" placeholder="Số nhà, tên đường..." required>
                        <input type="hidden" name="address" id="address2">

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-light rounded-pill px-4" id="addressCancelButton">Hủy</button>
                            <button type="submit" class="btn btn-dark rounded-pill px-4"><i class="bi bi-check2 me-1"></i>Lưu địa chỉ</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-0">Tùy chọn</h5>
                </div>

                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action p-4">
                        <div class="d-flex align-items-center gap-3"><i class="bi bi-bell fs-5"></i><div class="flex-grow-1"><div class="fw-semibold">Thông báo</div><small class="text-muted">Quản lý tùy chọn nhận thông báo</small></div><i class="bi bi-chevron-right text-muted"></i></div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action p-4">
                        <div class="d-flex align-items-center gap-3"><i class="bi bi-shield-check fs-5"></i><div class="flex-grow-1"><div class="fw-semibold">Bảo mật tài khoản</div><small class="text-muted">Quản lý bảo mật và thiết bị đăng nhập</small></div><i class="bi bi-chevron-right text-muted"></i></div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action p-4">
                        <div class="d-flex align-items-center gap-3"><i class="bi bi-credit-card fs-5"></i><div class="flex-grow-1"><div class="fw-semibold">Phương thức thanh toán</div><small class="text-muted">Quản lý phương thức thanh toán</small></div><i class="bi bi-chevron-right text-muted"></i></div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action p-4 text-danger">
                        <div class="d-flex align-items-center gap-3"><i class="bi bi-trash3 fs-5"></i><div class="flex-grow-1"><div class="fw-semibold">Xóa tài khoản</div><small class="text-muted">Xóa vĩnh viễn tài khoản và dữ liệu</small></div><i class="bi bi-chevron-right text-muted"></i></div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js?v=4"></script>

<?php include '../includes/footer.php'; ?>