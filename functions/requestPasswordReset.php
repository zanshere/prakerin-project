<?php
require_once __DIR__ . '/../config/connect.php';
include_once __DIR__ . '/../config/baseURL.php';

// Function to generate secure reset token
function generateResetToken() {
    return bin2hex(random_bytes(32));
}

// Function to create notification for admin
function createAdminNotification($conn, $title, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message) 
                           SELECT id_user, 'password_reset', ?, ? 
                           FROM users WHERE role = 'admin'");
    if ($stmt) {
        $stmt->bind_param("ss", $title, $message);
        $stmt->execute();
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Validasi input
    if (empty($email)) {
        $_SESSION['password_error'] = 'Email harus diisi';
        header("Location: " . base_url('auth/forgotPassword.php'));
        exit();
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['password_error'] = 'Format email tidak valid';
        header("Location: " . base_url('auth/forgotPassword.php'));
        exit();
    }

    try {
        // Cek apakah email terdaftar dan user aktif
        $stmt = $conn->prepare("SELECT id_user, username, full_name FROM users WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['password_error'] = 'Email tidak terdaftar atau akun tidak aktif';
            header("Location: " . base_url('auth/forgotPassword.php'));
            exit();
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Cek apakah sudah ada request pending dari user ini
        $checkStmt = $conn->prepare("SELECT id FROM password_reset_requests 
                                   WHERE user_id = ? AND status = 'pending' 
                                   AND request_date > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $checkStmt->bind_param("i", $user['id_user']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $_SESSION['password_error'] = 'Anda sudah memiliki permintaan reset password yang sedang diproses. Harap tunggu 24 jam sebelum mengajukan kembali.';
            header("Location: " . base_url('auth/forgotPassword.php'));
            exit();
        }
        $checkStmt->close();

        // Generate reset token
        $resetToken = generateResetToken();
        
        // Simpan request ke database
        $insertStmt = $conn->prepare("INSERT INTO password_reset_requests 
                                    (user_id, username, email, request_date, status, reset_token) 
                                    VALUES (?, ?, ?, NOW(), 'pending', ?)");
        $insertStmt->bind_param("isss", $user['id_user'], $user['username'], $email, $resetToken);
        
        if ($insertStmt->execute()) {
            $insertStmt->close();
            
            // Buat notifikasi untuk admin
            $notifTitle = "Permintaan Reset Password Baru";
            $notifMessage = "User {$user['full_name']} ({$user['username']}) mengajukan permintaan reset password untuk email: {$email}";
            createAdminNotification($conn, $notifTitle, $notifMessage);
            
            $_SESSION['password_message'] = 'Permintaan reset password telah dikirim ke admin. Anda akan mendapat email berisi password baru dalam waktu maksimal 1x24 jam. Silakan cek email Anda secara berkala.';
        } else {
            $_SESSION['password_error'] = 'Gagal mengajukan permintaan. Silakan coba lagi.';
        }

    } catch (Exception $e) {
        error_log("Error in password reset request: " . $e->getMessage());
        $_SESSION['password_error'] = 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
    }

    header("Location: " . base_url('auth/forgotPassword.php'));
    exit();
}

// Jika bukan POST request, redirect ke form
header("Location: " . base_url('auth/forgotPassword.php'));
exit();
?>