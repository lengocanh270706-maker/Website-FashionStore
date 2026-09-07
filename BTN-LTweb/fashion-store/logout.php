<?php
session_start();

// Xóa toàn bộ session
$_SESSION = [];

// Xóa cookie session
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Hủy session
session_destroy();

// Quay về trang login
header("Location: login.php");
exit();