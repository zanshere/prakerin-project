<?php
// File: config/auth_check.php
// Include file ini di setiap halaman yang memerlukan autentikasi

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check remember me functionality
require_once __DIR__ . '/../functions/checkRememberMe.php';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to require login (redirect to login if not logged in)
function requireLogin($redirectUrl = null) {
    if (!isLoggedIn()) {
        if ($redirectUrl === null) {
            $redirectUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        }
        
        // Store intended URL in session
        $_SESSION['intended_url'] = $redirectUrl;
        
        // Redirect to login
        if (function_exists('base_url')) {
            header("Location: " . base_url('auth/login.php'));
        } else {
            header("Location: /auth/login.php");
        }
        exit();
    }
}

// Function to get current user info
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT id_user, username, email, full_name, phone, nrp, `rank` FROM users WHERE id_user = ?");
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}

// Function untuk logout
function logout() {
    // Hapus remember token jika ada
    if (isset($_COOKIE['remember_token'])) {
        require_once __DIR__ . '/connect.php';
        
        $cookieValue = $_COOKIE['remember_token'];
        if (strpos($cookieValue, ':') !== false) {
            list($userId, $token) = explode(':', $cookieValue, 2);
            $userId = intval($userId);
            
            // Hapus token dari database
            $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        // Hapus cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Hapus session
    session_unset();
    session_destroy();
    
    // Start new session for messages
    session_start();
    $_SESSION['logout_success'] = 'Anda telah berhasil logout';
    
    // Redirect to login
    if (function_exists('base_url')) {
        header("Location: " . base_url('auth/login.php'));
    } else {
        header("Location: /auth/login.php");
    }
    exit();
}
?>