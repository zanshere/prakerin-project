<?php
require_once __DIR__ . '/../config/connect.php';
include_once __DIR__ . '/../config/baseURL.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Only check remember me if user is not already logged in
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $cookieValue = $_COOKIE['remember_token'];
    
    // Validate cookie format
    if (strpos($cookieValue, ':') === false) {
        // Invalid cookie format, remove it
        setcookie('remember_token', '', time() - 3600, '/');
        return;
    }
    
    list($userId, $token) = explode(':', $cookieValue, 2);
    
    // Validate userId is numeric
    if (!is_numeric($userId) || $userId <= 0) {
        setcookie('remember_token', '', time() - 3600, '/');
        return;
    }
    
    $userId = intval($userId);
    
    // Cek token di database - sesuaikan dengan struktur tabel
    $stmt = $conn->prepare("SELECT id, token_hash FROM remember_tokens WHERE user_id = ? AND expires_at > NOW()");
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return;
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tokenFound = false;
    while ($row = $result->fetch_assoc()) {
        if (password_verify($token, $row['token_hash'])) {
            $tokenFound = true;
            
            // Token valid, ambil data user - sesuaikan dengan kolom tabel users
            $userStmt = $conn->prepare("SELECT id_user, username FROM users WHERE id_user = ?");
            if (!$userStmt) {
                error_log("Failed to prepare user statement: " . $conn->error);
                break;
            }
            
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $user = $userResult->fetch_assoc();
            
            if ($user) {
                // Set session untuk login otomatis
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                
                // Generate token baru untuk keamanan (token rotation)
                $newToken = bin2hex(random_bytes(32));
                $newHash = password_hash($newToken, PASSWORD_BCRYPT);
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // Update token di database
                $updateStmt = $conn->prepare("UPDATE remember_tokens SET token_hash = ?, expires_at = ? WHERE id = ?");
                if ($updateStmt) {
                    $updateStmt->bind_param("ssi", $newHash, $expires, $row['id']);
                    $updateStmt->execute();
                    
                    // Update cookie dengan token baru
                    $cookieOptions = [
                        'expires' => time() + (30 * 24 * 60 * 60), // 30 hari
                        'path' => '/',
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ];
                    
                    // Set secure cookie jika menggunakan HTTPS
                    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                        $cookieOptions['secure'] = true;
                    }
                    
                    setcookie('remember_token', $user['id_user'] . ':' . $newToken, $cookieOptions);
                    $updateStmt->close();
                }
                
                // Set session untuk notifikasi (opsional)
                $_SESSION['login_success'] = 'Anda masuk otomatis melalui Remember Me';
                
                // Log successful auto-login (opsional)
                error_log("Auto-login successful for user: " . $user['username']);
                
                $userStmt->close();
                
                // Redirect ke dashboard atau halaman utama
                if (function_exists('base_url')) {
                    header("Location: " . base_url());
                } else {
                    header("Location: /");
                }
                exit();
            } else {
                // User tidak ditemukan, hapus token
                $deleteStmt = $conn->prepare("DELETE FROM remember_tokens WHERE id = ?");
                if ($deleteStmt) {
                    $deleteStmt->bind_param("i", $row['id']);
                    $deleteStmt->execute();
                    $deleteStmt->close();
                }
            }
            
            $userStmt->close();
            break;
        }
    }
    
    $stmt->close();
    
    // Jika tidak ada token yang valid atau user tidak ditemukan
    if (!$tokenFound) {
        // Hapus cookie yang tidak valid
        setcookie('remember_token', '', time() - 3600, '/');
        
        // Hapus semua token expired untuk user ini
        $cleanupStmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ? AND expires_at <= NOW()");
        if ($cleanupStmt) {
            $cleanupStmt->bind_param("i", $userId);
            $cleanupStmt->execute();
            $cleanupStmt->close();
        }
        
        // Set pesan error (opsional)
        $_SESSION['login_error'] = 'Sesi remember me telah kadaluarsa, silakan login kembali';
        
        // Redirect ke halaman login
        if (function_exists('base_url')) {
            header("Location: " . base_url('auth/login.php'));
        } else {
            header("Location: /auth/login.php");
        }
        exit();
    }
}

// Function untuk membersihkan token expired (dapat dipanggil secara berkala)
function cleanupExpiredTokens($conn) {
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE expires_at <= NOW()");
    if ($stmt) {
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
    return 0;
}

// Cleanup token expired (jalankan dengan probabilitas rendah untuk performance)
if (rand(1, 100) <= 5) { // 5% chance
    cleanupExpiredTokens($conn);
}
?>