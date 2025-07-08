<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../config/authCheck.php';
require_once __DIR__ . '/../config/baseURL.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('auth/login.php'));
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Error',
        'message' => 'Invalid request method'
    ];
    header("Location: " . base_url('admin/manageUsers.php'));
    exit();
}

// Get and validate input
$user_id = $_POST['user_id'] ?? null;
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$nrp = trim($_POST['nrp'] ?? '');
$rank = $_POST['rank'] ?? '';
$role = $_POST['role'] ?? '';
$current_profile_image = $_POST['current_profile_image'] ?? 'profil.jpg';
$remove_image = isset($_POST['remove_image']) && $_POST['remove_image'] == 'on';

// Validation
$errors = [];

if (!$user_id || !is_numeric($user_id)) {
    $errors[] = 'Invalid user ID';
}

if (empty($username)) {
    $errors[] = 'Username is required';
} elseif (strlen($username) > 100) {
    $errors[] = 'Username cannot exceed 100 characters';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
} elseif (strlen($email) > 100) {
    $errors[] = 'Email cannot exceed 100 characters';
}

if (empty($full_name)) {
    $errors[] = 'Full name is required';
} elseif (strlen($full_name) > 150) {
    $errors[] = 'Full name cannot exceed 150 characters';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required';
} elseif (strlen($phone) > 50) {
    $errors[] = 'Phone number cannot exceed 50 characters';
} elseif (!preg_match('/^[0-9+\-\s]+$/', $phone)) {
    $errors[] = 'Phone number can only contain numbers, +, -, and spaces';
}

if (empty($nrp)) {
    $errors[] = 'NRP is required';
} elseif (!preg_match('/^\d{8}$/', $nrp)) {
    $errors[] = 'NRP must be exactly 8 digits';
}

$valid_ranks = ['AKP', 'IPTU', 'IPDA', 'AIPTU', 'AIPDA', 'BRIPKA', 'BRIGPOL', 'BRIPTU', 'BRIPDA'];
if (empty($rank) || !in_array($rank, $valid_ranks)) {
    $errors[] = 'Invalid rank selected';
}

$valid_roles = ['user', 'admin'];
if (empty($role) || !in_array($role, $valid_roles)) {
    $errors[] = 'Invalid role selected';
}

// If password is provided, validate it
if (!empty($password)) {
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }
}

// Check if there are validation errors
if (!empty($errors)) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Validation Error',
        'message' => implode('<br>', $errors)
    ];
    header("Location: " . base_url('admin/editUser.php?id=' . urlencode($user_id)));
    exit();
}

// Check if username already exists for other users
$check_username_query = "SELECT id_user FROM users WHERE username = ? AND id_user != ?";
if ($stmt = $conn->prepare($check_username_query)) {
    $stmt->bind_param("si", $username, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Username already exists'
        ];
        header("Location: " . base_url('admin/editUser.php?id=' . urlencode($user_id)));
        exit();
    }
    $stmt->close();
}

// Check if email already exists for other users
$check_email_query = "SELECT id_user FROM users WHERE email = ? AND id_user != ?";
if ($stmt = $conn->prepare($check_email_query)) {
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Email already exists'
        ];
        header("Location: " . base_url('admin/editUser.php?id=' . urlencode($user_id)));
        exit();
    }
    $stmt->close();
}

// Handle profile image upload
$profile_image = $current_profile_image;
$uploadDir = __DIR__ . '/../../public/uploads/profiles/';

// If remove image is checked
if ($remove_image) {
    // Delete old image if it's not the default one
    if ($current_profile_image && $current_profile_image !== 'profil.jpg') {
        $oldImagePath = $uploadDir . $current_profile_image;
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
    }
    $profile_image = 'profil.jpg';
} 
// If new image is uploaded
elseif (!empty($_FILES['profile_image']['name'])) {
    $file = $_FILES['profile_image'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    // Validate file
    if (!in_array($file['type'], $allowedTypes)) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Invalid file type. Only JPG, PNG, GIF allowed.'
        ];
        header("Location: " . base_url('admin/editUser.php?id=' . urlencode($user_id)));
        exit();
    }

    if ($file['size'] > $maxSize) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'File too large. Max 2MB allowed.'
        ];
        header("Location: " . base_url('admin/editUser.php?id=' . urlencode($user_id)));
        exit();
    }

    // Delete old image if it's not the default one
    if ($current_profile_image && $current_profile_image !== 'profil.jpg') {
        $oldImagePath = $uploadDir . $current_profile_image;
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
    }

    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $profile_image = uniqid('profile_') . '.' . $ext;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $profile_image)) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Failed to upload profile image.'
        ];
        header("Location: " . base_url('admin/editUser.php?id=' . urlencode($user_id)));
        exit();
    }
}

// Prepare update query
if (!empty($password)) {
    // Update with password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $update_query = "UPDATE users SET username = ?, email = ?, password = ?, full_name = ?, phone = ?, nrp = ?, `rank` = ?, role = ?, profile_image = ?, updated_at = CURRENT_TIMESTAMP WHERE id_user = ?";
    
    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("sssssssssi", $username, $email, $hashed_password, $full_name, $phone, $nrp, $rank, $role, $profile_image, $user_id);
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Database Error',
            'message' => 'Database error occurred'
        ];
        header("Location: " . base_url('admin/editUser.php?id=' . urlencode($user_id)));
        exit();
    }
} else {
    // Update without password
    $update_query = "UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, nrp = ?, `rank` = ?, role = ?, profile_image = ?, updated_at = CURRENT_TIMESTAMP WHERE id_user = ?";
    
    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("ssssssssi", $username, $email, $full_name, $phone, $nrp, $rank, $role, $profile_image, $user_id);
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Database Error',
            'message' => 'Database error occurred'
        ];
        header("Location: " . base_url('admin/editUser.php?id=' . urlencode($user_id)));
        exit();
    }
}

// Execute the update
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Update session data if editing current user
        if ($_SESSION['user_id'] == $user_id) {
            $_SESSION['username'] = $username;
            $_SESSION['profile_image'] = $profile_image;
            $_SESSION['role'] = $role;
        }
        
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Success',
            'message' => 'User updated successfully'
        ];
    } else {
        $_SESSION['alert'] = [
            'type' => 'info',
            'title' => 'Info',
            'message' => 'No changes were made or user not found'
        ];
    }
} else {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Error',
        'message' => 'Failed to update user: ' . $stmt->error
    ];
}

$stmt->close();

// Redirect back to manage users
header("Location: " . base_url('admin/manageUsers.php'));
exit();
?>