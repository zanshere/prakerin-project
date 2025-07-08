<?php
require_once __DIR__ . '/../config/connect.php';
include_once __DIR__ . '/../config/baseURL.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validasi input
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Username dan password harus diisi';
        header("Location: " . base_url('auth/login.php'));
        exit();
    }
    
    // Cek user (termasuk role)
    $stmt = $conn->prepare("SELECT id_user, username, password, role, profile_image FROM users WHERE username = ?");
    if (!$stmt) {
        $_SESSION['login_error'] = 'Database error: ' . $conn->error;
        header("Location: " . base_url('auth/login.php'));
        exit();
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            // Login berhasil
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_image'] = $user['profile_image'];
            
            // Handle Remember Me
            if (isset($_POST['remember']) && !empty($_POST['remember'])) {
                $token = bin2hex(random_bytes(32));
                $tokenHash = password_hash($token, PASSWORD_BCRYPT);
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $userId = intval($user['id_user']);
                if ($userId > 0) {
                    // Hapus token lama
                    $cleanupStmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ? AND expires_at < NOW()");
                    if ($cleanupStmt) {
                        $cleanupStmt->bind_param("i", $userId);
                        $cleanupStmt->execute();
                        $cleanupStmt->close();
                    }
                    
                    // Simpan token baru
                    $tokenStmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
                    if ($tokenStmt) {
                        $tokenStmt->bind_param("iss", $userId, $tokenHash, $expires);
                        
                        if ($tokenStmt->execute()) {
                            $cookieValue = $userId . ':' . $token;
                            $cookieOptions = [
                                'expires' => time() + (30 * 24 * 60 * 60),
                                'path' => '/',
                                'httponly' => true,
                                'samesite' => 'Strict'
                            ];
                            
                            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                                $cookieOptions['secure'] = true;
                            }
                            
                            setcookie('remember_token', $cookieValue, $cookieOptions);
                            $_SESSION['login_success'] = 'Login berhasil! Anda akan tetap masuk selama 30 hari';
                        }
                        $tokenStmt->close();
                    }
                }
            } else {
                $_SESSION['login_success'] = 'Login berhasil! Selamat datang, ' . htmlspecialchars($user['username']);
            }
            
            $stmt->close();
            
            // Set URL redirect berdasarkan role
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
            
            // Simpan redirect_url di session
            $_SESSION['redirect_url'] = $redirectUrl;
            
            // Redirect ke login.php untuk menampilkan SweetAlert
            header("Location: " . base_url('auth/login.php'));
            exit();
        }
    }
    
    $stmt->close();
    $_SESSION['login_error'] = 'Username atau password salah';
    header("Location: " . base_url('auth/login.php'));
    exit();
}

// Jika bukan POST request, redirect ke login
header("Location: " . base_url('auth/login.php'));
exit();
?>