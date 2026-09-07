<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

/* KIỂM TRA ĐĂNG NHẬP */
function isLoggedIn()
{
    return isset($_SESSION['user']);
}

/* LẤY THÔNG TIN USER */
function currentUser()
{
    return $_SESSION['user'] ?? null;
}

/* BẮT BUỘC ĐĂNG NHẬP */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}

/* KIỂM TRA ADMIN */
function isAdmin()
{
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

/* BẮT BUỘC ADMIN */
function requireAdmin()
{
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit;
    }
}

/* ĐĂNG NHẬP */
function loginUser($user)
{
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'address' => $user['address'],
        'avatar' => $user['avatar'] ?? '',
        'role' => $user['role'],
        'status' => $user['status']
    ];
}

/* ĐĂNG XUẤT */
function logoutUser()
{
    $_SESSION = [];
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
    session_destroy();
}