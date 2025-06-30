<?php
session_start();
require_once __DIR__ . '/../config/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validasi input
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Username dan password harus diisi';
        header("Location: " . base_url('auth/login.php'));
        exit();
    }
    
    // Cek user
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            // Login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Handle Remember Me
            if (isset($_POST['remember'])) {
                $token = bin2hex(random_bytes(32));
                $tokenHash = password_hash($token, PASSWORD_BCRYPT);
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // Simpan token di database
                $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user['id'], $tokenHash, $expires);
                $stmt->execute();
                
                // Set cookie (30 hari)
                setcookie(
                    'remember_token',
                    $user['id'] . ':' . $token,
                    [
                        'expires' => time() + 30 * 24 * 60 * 60,
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]
                );
                
                $_SESSION['login_success'] = 'Login berhasil! Anda akan tetap masuk selama 30 hari';
            } else {
                $_SESSION['login_success'] = 'Login berhasil! Selamat datang, ' . htmlspecialchars($user['username']);
            }
            
            header("Location: " . base_url());
            exit();
        }
    }
    
    $_SESSION['login_error'] = 'Username atau password salah';
    header("Location: " . base_url('auth/login.php'));
    exit();
}

header("Location: " . base_url('auth/login.php'));
exit();
?>