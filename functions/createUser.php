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
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $nrp = trim($_POST['nrp'] ?? '');
        $rank = $_POST['rank'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $profile_image = 'profil.jpg'; // default image

        // Validasi input
        if (empty($username) || empty($email) || empty($password) || empty($full_name) || 
            empty($phone) || empty($nrp) || empty($rank)) {
            throw new Exception('All fields are required');
        }

        // Validasi tambahan
        if (!preg_match('/^\d{8}$/', $nrp)) {
            throw new Exception('NRP must be exactly 8 digits');
        }

        if (!preg_match('/^[0-9+\-\s]+$/', $phone)) {
            throw new Exception('Phone number can only contain numbers, +, -, and spaces');
        }

        $valid_ranks = ['AKP', 'IPTU', 'IPDA', 'AIPTU', 'AIPDA', 'BRIPKA', 'BRIGPOL', 'BRIPTU', 'BRIPDA'];
        if (!in_array($rank, $valid_ranks)) {
            throw new Exception('Invalid rank selected');
        }

        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long');
        }

        // Check for existing username/email
        $check = $conn->prepare("SELECT id_user FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Username or email already exists');
        }
        $check->close();

        // Handle file upload
        if (!empty($_FILES['profile_image']['name'])) {
            $uploadDir = __DIR__ . '/../public/uploads/profiles/';
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            $file = $_FILES['profile_image'];
            
            // Validate file
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Invalid file type. Only JPG, PNG, GIF allowed.');
            }
            
            if ($file['size'] > $maxSize) {
                throw new Exception('File too large. Max 2MB allowed.');
            }
            
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $profile_image = uniqid('profile_') . '.' . $ext;
            
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $profile_image)) {
                throw new Exception('Failed to upload profile image.');
            }
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Gunakan prepared statement untuk keamanan
        $stmt = $conn->prepare("INSERT INTO users 
                               (username, email, password, full_name, phone, nrp, `rank`, role, profile_image, created_at, updated_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        $stmt->bind_param("sssssssss", 
            $username, 
            $email, 
            $hashedPassword, 
            $full_name, 
            $phone, 
            $nrp, 
            $rank,
            $role,
            $profile_image
        );

        if ($stmt->execute()) {
            $_SESSION['alert'] = [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'User created successfully!'
            ];
            header("Location: " . base_url('admin/manageUsers.php'));
            exit();
        } else {
            throw new Exception('Failed to create user: ' . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => $e->getMessage()
        ];
        header("Location: " . base_url('admin/addUser.php'));
        exit();
    }
}

header("Location: " . base_url('admin/addUser.php'));
exit();