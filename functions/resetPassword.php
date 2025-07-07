<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../config/authCheck.php';
require_once __DIR__ . '/../config/baseURL.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['admin_error'] = 'Unauthorized access';
    header("Location: " . base_url('auth/login.php'));
    exit();
}

// Validasi request_id
$requestId = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;
if ($requestId <= 0) {
    $_SESSION['admin_error'] = 'Invalid request ID';
    header("Location: " . base_url('admin/request.php'));
    exit();
}

try {
    // Mulai transaksi
    $conn->begin_transaction();

    // Ambil data request dengan JOIN yang benar
    $stmt = $conn->prepare("SELECT r.*, u.id_user, u.username 
                           FROM password_reset_requests r
                           JOIN users u ON r.user_id = u.id_user
                           WHERE r.id = ? AND r.status = 'pending'");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();
    $stmt->close();

    if (!$request) {
        $_SESSION['admin_error'] = 'Invalid or already processed request';
        header("Location: " . base_url('admin/request.php'));
        exit();
    }

    // Generate password baru
    $newPassword = bin2hex(random_bytes(4)); // 8 karakter random
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password user
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ?");
    if (!$updateStmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $updateStmt->bind_param("si", $hashedPassword, $request['user_id']);
    if (!$updateStmt->execute()) {
        throw new Exception("Failed to update password: " . $updateStmt->error);
    }
    $updateStmt->close();

    // Update status request
    $updateRequest = $conn->prepare("UPDATE password_reset_requests 
                                   SET status = 'completed', 
                                       completed_by = ?, 
                                       completed_date = NOW() 
                                   WHERE id = ?");
    if (!$updateRequest) {
        throw new Exception("Database error: " . $conn->error);
    }

    $adminId = $_SESSION['user_id'];
    $updateRequest->bind_param("ii", $adminId, $requestId);
    if (!$updateRequest->execute()) {
        throw new Exception("Failed to update request: " . $updateRequest->error);
    }
    $updateRequest->close();

    // Commit transaksi
    $conn->commit();

    // Set success message
    $_SESSION['admin_success'] = [
        'title' => 'Password Reset Success',
        'message' => "Password for {$request['username']} has been reset to: $newPassword",
        'new_password' => $newPassword
    ];

} catch (Exception $e) {
    // Rollback transaksi jika error
    $conn->rollback();
    error_log("Error in resetPassword.php: " . $e->getMessage());
    $_SESSION['admin_error'] = 'System error occurred. Please try again later.';
}

header("Location: " . base_url('admin/request.php'));
exit();
?>