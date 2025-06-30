<?php
session_start();
require_once __DIR__ . '/../config/connect.php';

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

header("Location: " . base_url() . "?remember_login=true");
exit();
?>