<?php
// File: config/authCheck.php
// Include file ini di setiap halaman yang memerlukan autentikasi

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for session recovery from browser close
if (!isset($_SESSION['user_id']) && isset($_COOKIE['session_recovery'])) {
    $recoveryData = json_decode($_COOKIE['session_recovery'], true);
    
    if (is_array($recoveryData) && isset($recoveryData['url'])) {
        $_SESSION['intended_url'] = $recoveryData['url'];
    }
}

// PENTING: Check remember me HANYA jika user belum login
if (!isset($_SESSION['user_id'])) {
    // Check remember me functionality
    require_once __DIR__ . '/../functions/checkRememberMe.php';
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check user role
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

// Function to check if user is admin
function isAdmin() {
    return getUserRole() === 'admin';
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

// Function to require admin access
function requireAdmin() {
    requireLogin(); // Ensure user is logged in first
    
    if (!isAdmin()) {
        // Non-admin users get redirected to their dashboard
        if (function_exists('base_url')) {
            header("Location: " . base_url('pages/index.php'));
        } else {
            header("Location: /pages/index.php");
        }
        exit();
    }
}

// Function to get current user info
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT id_user, username, email, full_name, phone, nrp, `rank`, role FROM users WHERE id_user = ?");
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

// Function untuk update session recovery
function updateSessionRecovery() {
    if (isLoggedIn() && isset($_SERVER['REQUEST_URI'])) {
        $recoveryData = [
            'url' => $_SERVER['REQUEST_URI'],
            'timestamp' => time()
        ];
        
        setcookie('session_recovery', 
                 json_encode($recoveryData), 
                 time() + (30 * 24 * 60 * 60), // 30 days
                 '/', 
                 '', 
                 isset($_SERVER['HTTPS']), 
                 true);
    }
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
    
    // Hapus session recovery cookie
    setcookie('session_recovery', '', time() - 3600, '/');
    
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