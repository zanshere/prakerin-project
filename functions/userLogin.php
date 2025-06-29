<?php 
session_start();
include __DIR__ . '/../config/baseURL.php';
require_once __DIR__ . '/../config/connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . base_url());
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Username and password are required';
        header("Location: " . base_url('auth/login.php'));
        exit();
    }

    // Check user credentials
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_success'] = 'Welcome back, ' . htmlspecialchars($user['username']) . '!';
            header("Location: " . base_url());
            exit();
        }
    }
    
    // If we reach here, login failed
    $_SESSION['login_error'] = 'Invalid username or password';
    header("Location: " . base_url('auth/login.php'));
    exit();
}

// If not a POST request, redirect to login
header("Location: " . base_url('auth/login.php'));
exit();
?>