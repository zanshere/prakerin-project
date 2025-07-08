<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../config/authCheck.php';
require_once __DIR__ . '/../config/baseURL.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Access Denied',
        'message' => 'You do not have permission to perform this action'
    ];
    header("Location: " . base_url('auth/login.php'));
    exit();
}

// Get user ID from URL parameter
$user_id = $_GET['id'] ?? null;

// Validate user ID
if (!$user_id || !is_numeric($user_id)) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Invalid Request',
        'message' => 'Invalid user ID provided'
    ];
    header("Location: " . base_url('admin/manageUsers.php'));
    exit();
}

// Prevent self-deletion
if ($_SESSION['user_id'] == $user_id) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Operation Not Allowed',
        'message' => 'You cannot delete your own account'
    ];
    header("Location: " . base_url('admin/manageUsers.php'));
    exit();
}

// First, get user data to handle profile image deletion
$user = null;
$query = "SELECT profile_image FROM users WHERE id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Not Found',
        'message' => 'User not found in database'
    ];
    header("Location: " . base_url('admin/manageUsers.php'));
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Delete profile image if it's not the default one
if ($user['profile_image'] && $user['profile_image'] !== 'profil.jpg') {
    $imagePath = __DIR__ . '/../../public/uploads/profiles/' . $user['profile_image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// Delete remember tokens associated with this user
$deleteTokens = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
$deleteTokens->bind_param("i", $user_id);
$deleteTokens->execute();
$deleteTokens->close();

// Delete the user from database
$deleteQuery = "DELETE FROM users WHERE id_user = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Success',
            'message' => 'User account deleted successfully'
        ];
    } else {
        $_SESSION['alert'] = [
            'type' => 'info',
            'title' => 'Info',
            'message' => 'No user was deleted (user might not exist)'
        ];
    }
} else {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Error',
        'message' => 'Failed to delete user: ' . $stmt->error
    ];
}

$stmt->close();

// Redirect back to manage users page
header("Location: " . base_url('admin/manageUsers.php'));
exit();
?>