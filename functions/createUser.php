<?php
require_once __DIR__ . '/../config/connect.php';
include __DIR__ . '/../config/baseURL.php';

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('auth/login.php'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Ambil data dari form
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $full_name = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $nrp = $_POST['nrp'] ?? '';
        $rank = $_POST['rank'] ?? '';
        $role = $_POST['role'] ?? 'user';

        // Validasi input
        if (empty($username) || empty($email) || empty($password) || empty($full_name) || 
            empty($phone) || empty($nrp) || empty($rank)) {
            throw new Exception('All fields are required');
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Gunakan prepared statement untuk keamanan
        $stmt = $conn->prepare("INSERT INTO users 
                                 (username, email, password, full_name, phone, nrp, `rank`, created_at, updated_at) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        $stmt->bind_param("sssssss", 
            $username, 
            $email, 
            $hashedPassword, 
            $full_name, 
            $phone, 
            $nrp, 
            $rank
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'User created successfully!';
            header("Location: " . base_url('admin/manageUsers.php'));
            exit();
        } else {
            throw new Exception('Failed to create user: ' . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: " . base_url('admin/addUser.php'));
        exit();
    }
}

header("Location: " . base_url('admin/addUser.php'));
exit();