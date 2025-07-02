<?php
require_once __DIR__ . '/../config/connect.php';
include_once __DIR__ . '/../config/baseURL.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    error_log("Mulai proses Remember Me");
    
    try {
        $cookieValue = $_COOKIE['remember_token'];
        error_log("Cookie value: " . $cookieValue);

        // Validasi format cookie
        if (strpos($cookieValue, ':') === false) {
            error_log("Format cookie tidak valid - menghapus cookie");
            setcookie('remember_token', '', time() - 3600, '/');
            throw new Exception("Format cookie invalid");
        }
        
        list($userId, $token) = explode(':', $cookieValue, 2);
        error_log("Extracted user_id: $userId, token: $token");

        // Validasi userId
        if (!is_numeric($userId) || $userId <= 0) {
            error_log("User ID tidak valid - menghapus cookie");
            setcookie('remember_token', '', time() - 3600, '/');
            throw new Exception("Invalid user ID");
        }
        
        $userId = intval($userId);
        
        // Cek token di database
        $stmt = $conn->prepare("SELECT id, id_user, token_hash FROM remember_tokens 
                              WHERE user_id = ? AND expires_at > NOW()");
        if (!$stmt) {
            error_log("Gagal prepare statement: " . $conn->error);
            throw new Exception("Database error");
        }
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("Token tidak ditemukan atau expired");
            throw new Exception("Token not found");
        }
        
        $tokenFound = false;
        while ($row = $result->fetch_assoc()) {
            error_log("Memeriksa token untuk user: " . $row['id_user']);
            
            if (password_verify($token, $row['token_hash'])) {
                $tokenFound = true;
                error_log("Token valid, melanjutkan login");
                
                // Ambil data user lengkap
                $userStmt = $conn->prepare("SELECT id_user, username, email, role FROM users 
                                          WHERE id_user = ? AND status = 'active'");
                if (!$userStmt) {
                    error_log("Gagal prepare user statement: " . $conn->error);
                    break;
                }
                
                $userStmt->bind_param("i", $userId);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                
                if ($user = $userResult->fetch_assoc()) {
                    error_log("User ditemukan: " . $user['username']);
                    
                    // Set session
                    $_SESSION = [
                        'user_id' => $user['id_user'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'logged_in' => true
                    ];
                    
                    // Set redirect URL
                    $redirectUrl = ($user['role'] === 'admin') 
                        ? base_url('admin/dashboard.php') 
                        : base_url('pages/index.php');
                    
                    // Set session recovery cookie
                    $recoveryData = [
                        'url' => $redirectUrl,
                        'timestamp' => time()
                    ];

                    setcookie('session_recovery', 
                            json_encode($recoveryData), 
                            time() + (30 * 24 * 60 * 60), // 30 days
                            '/', 
                            '', 
                            isset($_SERVER['HTTPS']), 
                            true);
                    
                    // Set session untuk SweetAlert
                    $_SESSION['login_success'] = 'Anda telah login secara otomatis';
                    $_SESSION['redirect_url'] = $redirectUrl;
                    
                    error_log("Redirect ke login.php untuk menampilkan alert");
                    header("Location: " . base_url('auth/login.php'));
                    exit();
                } else {
                    error_log("User tidak aktif atau tidak ditemukan");
                }
                
                $userStmt->close();
                break;
            }
        }
        
        $stmt->close();
        
        if (!$tokenFound) {
            error_log("Token tidak valid - menghapus cookie");
            setcookie('remember_token', '', time() - 3600, '/');
            
            // Hapus token yang expired dari database
            $cleanupStmt = $conn->prepare("DELETE FROM remember_tokens 
                                         WHERE user_id = ? AND expires_at <= NOW()");
            if ($cleanupStmt) {
                $cleanupStmt->bind_param("i", $userId);
                $cleanupStmt->execute();
                $cleanupStmt->close();
            }
            
            $_SESSION['login_error'] = 'Sesi remember me telah kadaluarsa';
            header("Location: " . base_url('auth/login.php'));
            exit();
        }
    } catch (Exception $e) {
        error_log("Error Remember Me: " . $e->getMessage());
        $_SESSION['login_error'] = 'Terjadi kesalahan saat login otomatis';
        header("Location: " . base_url('auth/login.php'));
        exit();
    }
}
?>