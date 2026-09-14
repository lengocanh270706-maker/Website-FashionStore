<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

$client_id = '95727767938-9ku0hbs1o22q30iob8c70uv5r3135fpg.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri = 'http://localhost/Website-FashionStore/BTN-LTweb/fashion-store/google_callback.php';

// Kiểm tra xem Google có trả về mã code không
if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // 1. Dùng cURL để đổi mã code lấy Access Token từ Google
    $token_url = 'https://oauth2.googleapis.com/token';
    $post_data = [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $token_data = json_decode($response, true);

    if (isset($token_data['access_token'])) {
        $access_token = $token_data['access_token'];

        // 2. Dùng Access Token để lấy thông tin cá nhân (Email, Tên)
        $userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $access_token;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $userinfo_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $user_response = curl_exec($ch);
        curl_close($ch);

        $google_user = json_decode($user_response, true);

        if (isset($google_user['email'])) {
            $email = $google_user['email'];
            $name = $google_user['name'] ?? 'Google User';

            // 3. Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu chưa
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user) {
                if ((int)$user['status'] !== 1) {
                    echo "<script>alert('Tài khoản của bạn đã bị khóa!'); window.location='login.php';</script>";
                    exit;
                }
                $logged_in_user = $user;
            } else {
                // Tự động đăng ký mới nếu chưa có, gán giá trị mặc định cho phone và address để tránh lỗi Database
                $role = 'user';
                $status = 1;
                $phone = ''; 
                $address = '';
                $default_password = password_hash(rand(100000, 999999), PASSWORD_DEFAULT);

                $insert = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insert->bind_param("ssssssi", $name, $email, $default_password, $phone, $address, $role, $status);
                $insert->execute();
                $new_user_id = $insert->insert_id;
                $insert->close();

                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
                $stmt->bind_param("i", $new_user_id);
                $stmt->execute();
                $logged_in_user = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }

            // 4. Thiết lập Session đăng nhập
            if (function_exists('loginUser')) {
                loginUser($logged_in_user);
            } else {
                $_SESSION['user_id'] = $logged_in_user['id'];
                $_SESSION['user_role'] = $logged_in_user['role'];
                $_SESSION['user_name'] = $logged_in_user['name'];
            }

            // 5. Điều hướng
            if ($logged_in_user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        }
    }
}

// Nếu lỗi xác thực, quay về trang đăng nhập
header('Location: login.php');
exit;
?>