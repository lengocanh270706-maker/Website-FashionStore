<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 */
function isLoggedIn()
{
    return isset($_SESSION['user']);
}

/**
 * Lấy thông tin người dùng hiện tại
 */
function currentUser()
{
    return $_SESSION['user'] ?? null;
}

/**
 * Bắt buộc phải đăng nhập
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ../fashion-store/login.php');
        exit;
    }
}

/**
 * Kiểm tra người dùng có phải Admin không
 */
function isAdmin()
{
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

/**
 * Bắt buộc phải là Admin
 */
function requireAdmin()
{
    if (!isAdmin()) {
        header('Location: ../fashion-store/index.php');
        exit;
    }
}

/**
 * Đăng nhập người dùng
 */
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

/**
 * Đăng xuất
 */
function logoutUser()
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}
