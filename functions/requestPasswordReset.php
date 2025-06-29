<?php
session_start();
include __DIR__ . '/../config/baseURL.php';
include __DIR__ . '/../config/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    // Validasi input
    if (empty($username) || empty($email)) {
        $_SESSION['password_error'] = 'Username dan email harus diisi';
        header("Location: " . base_url('auth/forgotPassword.php'));
        exit();
    }

    // Cek apakah username dan email cocok
    $stmt = $conn->prepare("SELECT id_user FROM users WHERE username = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['password_error'] = 'Username dan email tidak cocok';
        header("Location: " . base_url('auth/forgotPassword.php'));
        exit();
    }

    $user = $result->fetch_assoc();
    
    // Simpan request ke database
    $stmt = $conn->prepare("INSERT INTO password_reset_requests (user_id, username, email, request_date, status) VALUES (?, ?, ?, NOW(), 'pending')");
    $stmt->bind_param("iss", $user['id_user'], $username, $email);
    
    if ($stmt->execute()) {
        $_SESSION['password_message'] = 'Permintaan reset password telah dikirim ke admin. Silakan hubungi admin untuk proses selanjutnya.';
    } else {
        $_SESSION['password_error'] = 'Gagal mengajukan permintaan. Silakan coba lagi.';
    }

    header("Location: " . base_url('auth/forgotPassword.php'));
    exit();
}

header("Location: " . base_url('auth/forgotPassword.php'));
exit();
?>