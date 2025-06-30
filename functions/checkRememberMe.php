<?php
require_once __DIR__ . '/../config/connect.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    list($userId, $token) = explode(':', $_COOKIE['remember_token'], 2);
    
    // Cek token di database
    $stmt = $conn->prepare("SELECT id, token_hash FROM remember_tokens WHERE user_id = ? AND expires_at > NOW()");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (password_verify($token, $row['token_hash'])) {
            // Token valid, login user
            $userStmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $user = $userStmt->get_result()->fetch_assoc();
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Generate token baru (rotation)
                $newToken = bin2hex(random_bytes(32));
                $newHash = password_hash($newToken, PASSWORD_BCRYPT);
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $updateStmt = $conn->prepare("UPDATE remember_tokens SET token_hash = ?, expires_at = ? WHERE id = ?");
                $updateStmt->bind_param("ssi", $newHash, $expires, $row['id']);
                $updateStmt->execute();
                
                // Update cookie
                setcookie(
                    'remember_token',
                    $user['id'] . ':' . $newToken,
                    [
                        'expires' => time() + 30 * 24 * 60 * 60,
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]
                );
                
                // Set session untuk notifikasi
                $_SESSION['login_success'] = 'Anda masuk otomatis melalui Remember Me';
                header("Location: " . base_url());
                exit();
            }
        }
    }
    
    // Token tidak valid, hapus cookie
    setcookie('remember_token', '', time() - 3600, '/');
    $_SESSION['login_error'] = 'Sesi remember me telah kadaluarsa, silakan login kembali';
    header("Location: " . base_url('auth/login.php'));
    exit();
}
?>