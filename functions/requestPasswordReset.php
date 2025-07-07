<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../config/baseURL.php';

// Pastikan session sudah start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to generate secure reset token
function generateResetToken() {
    return bin2hex(random_bytes(32));
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
        // Validasi koneksi database
        if (!$conn || $conn->connect_error) {
            throw new Exception("Koneksi database gagal");
        }

        // Cek apakah email terdaftar
        $stmt = $conn->prepare("SELECT id_user, username, full_name FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Error persiapan query: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception("Error eksekusi query: " . $stmt->error);
        }

        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['password_error'] = 'Email tidak terdaftar';
            header("Location: " . base_url('auth/forgotPassword.php'));
            exit();
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Cek apakah sudah ada request pending dalam 24 jam terakhir
        $checkStmt = $conn->prepare("SELECT id FROM password_reset_requests 
                                   WHERE user_id = ? AND status = 'pending' 
                                   AND request_date > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        if (!$checkStmt) {
            throw new Exception("Error persiapan query: " . $conn->error);
        }

        $checkStmt->bind_param("i", $user['id_user']);
        if (!$checkStmt->execute()) {
            throw new Exception("Error eksekusi query: " . $checkStmt->error);
        }

        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $_SESSION['password_error'] = 'Anda sudah memiliki permintaan reset password yang sedang diproses';
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
        if (!$insertStmt) {
            throw new Exception("Error persiapan query: " . $conn->error);
        }

        $insertStmt->bind_param("isss", $user['id_user'], $user['username'], $email, $resetToken);
        
        if ($insertStmt->execute()) {
            $_SESSION['password_message'] = 'Permintaan reset password telah dikirim ke admin. Anda akan mendapat email berisi password baru dalam waktu maksimal 1x24 jam.';
        } else {
            throw new Exception("Error eksekusi query: " . $insertStmt->error);
        }

        $insertStmt->close();

    } catch (Exception $e) {
        error_log("Error in requestPasswordReset.php: " . $e->getMessage());
        $_SESSION['password_error'] = 'Terjadi kesalahan sistem. Silakan coba lagi nanti. Error: ' . $e->getMessage();
    }

    header("Location: " . base_url('auth/forgotPassword.php'));
    exit();
}

// Jika bukan POST request, redirect ke form
header("Location: " . base_url('auth/forgotPassword.php'));
exit();
?>