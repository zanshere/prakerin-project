<?php
include __DIR__ . '/../config/baseURL.php';

// Pastikan hanya admin yang bisa akses
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . base_url());
    exit();
}

$requestId = $_GET['request_id'] ?? 0;

// Ambil data request
$stmt = $conn->prepare("SELECT r.*, u.username 
                       FROM password_reset_requests r
                       JOIN users u ON r.user_id = u.id
                       WHERE r.id = ? AND r.status = 'pending'");
$stmt->bind_param("i", $requestId);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    $_SESSION['admin_error'] = 'Request tidak valid';
    header("Location: " . base_url('admin/requestList.php'));
    exit();
}

// Generate password baru
$newPassword = bin2hex(random_bytes(4)); // Password sementara
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update password user
$updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$updateStmt->bind_param("si", $hashedPassword, $request['user_id']);
$updateStmt->execute();

// Update status request
$conn->query("UPDATE password_reset_requests SET status = 'completed' WHERE id = $requestId");

// Kirim notifikasi ke user (bisa via email atau sistem notifikasi)
$_SESSION['admin_success'] = "Password untuk {$request['username']} telah direset menjadi: $newPassword";
header("Location: " . base_url('admin/requestList.php'));
exit();
?>