<?php
require_once __DIR__ . '/../config/connect.php';
include __DIR__ . '/../config/baseURL.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hapus remember me token
if (isset($_COOKIE['remember_token'])) {
    list($userId, $token) = explode(':', $_COOKIE['remember_token'], 2);
    
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    setcookie('remember_token', '', time() - 3600, '/');
}

// Hapus session
$_SESSION = array();
session_destroy();

// Set session baru untuk menampilkan alert
session_start();
$_SESSION['logout_success'] = 'Anda telah berhasil logout.';
$_SESSION['redirect_url'] = base_url('auth/login.php');

header("Location: " . base_url());
exit();
?>