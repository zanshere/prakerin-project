<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../config/authCheck.php';
require_once __DIR__ . '/../config/baseURL.php';

// Pastikan session sudah start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Validasi admin
if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Session not initialized']);
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$action = $_POST['action'] ?? '';
$requestId = intval($_POST['request_id'] ?? 0);

if (empty($action) || $requestId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    // Mulai transaksi
    $conn->begin_transaction();

    // Validasi koneksi database
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    // Ambil data request dengan prepared statement
    $stmt = $conn->prepare("SELECT prr.*, u.id_user, u.username 
                           FROM password_reset_requests prr
                           JOIN users u ON prr.user_id = u.id_user
                           WHERE prr.id = ? AND prr.status = 'pending'");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $requestId);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $request = $result->fetch_assoc();
    $stmt->close();

    if (!$request) {
        throw new Exception("Invalid or already processed request");
    }

    if ($action === 'approve') {
        // Generate password baru (8 karakter alfanumerik)
        $newPassword = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 8);
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password user
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ?");
        if (!$updateStmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $updateStmt->bind_param("si", $hashedPassword, $request['user_id']);
        if (!$updateStmt->execute()) {
            throw new Exception("Execute failed: " . $updateStmt->error);
        }
        $updateStmt->close();

        // Update status request
        $updateRequest = $conn->prepare("UPDATE password_reset_requests 
                                       SET status = 'completed', 
                                           completed_by = ?, 
                                           completed_date = NOW() 
                                       WHERE id = ?");
        if (!$updateRequest) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $adminId = $_SESSION['user_id'];
        $updateRequest->bind_param("ii", $adminId, $requestId);
        if (!$updateRequest->execute()) {
            throw new Exception("Execute failed: " . $updateRequest->error);
        }
        $updateRequest->close();

        // Commit transaksi
        $conn->commit();

        echo json_encode([
            'success' => true,
            'new_password' => $newPassword,
            'message' => 'Password has been reset successfully'
        ]);
        
    } elseif ($action === 'reject') {
        // Update status request menjadi rejected
        $updateRequest = $conn->prepare("UPDATE password_reset_requests 
                                       SET status = 'rejected', 
                                           completed_by = ?, 
                                           completed_date = NOW() 
                                       WHERE id = ?");
        if (!$updateRequest) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $adminId = $_SESSION['user_id'];
        $updateRequest->bind_param("ii", $adminId, $requestId);
        if (!$updateRequest->execute()) {
            throw new Exception("Execute failed: " . $updateRequest->error);
        }
        $updateRequest->close();

        // Commit transaksi
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Request has been rejected'
        ]);
    } else {
        throw new Exception("Invalid action");
    }

} catch (Exception $e) {
    // Rollback transaksi jika error
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
    }
    
    error_log("Error in processPasswordReset.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'System error: ' . $e->getMessage()
    ]);
}
?>