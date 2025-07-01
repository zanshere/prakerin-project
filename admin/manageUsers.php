<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../config/authCheck.php'; 

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('auth/login.php'));
    exit();
}

// Check for messages
$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Ambil data user dari database
$users = [];
$query = "SELECT id_user, username, email, full_name, phone, nrp, `rank`, created_at 
          FROM users ORDER BY created_at DESC";

// Check if connection exists
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($result = $conn->query($query)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    $result->free();
} else {
    // Handle query error
    $error = "Error fetching users: " . $conn->error;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-base-content">Manage Users</h1>
        <a href="<?= base_url('admin/addUser.php') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg mr-2"></i> Add User
        </a>
    </div>

    <?php if (empty($users)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle mr-2"></i>
        No users found in the database.
    </div>
    <?php else: ?>
    <div class="bg-base-100 rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Details</th>
                        <th>NRP</th>
                        <th>Rank</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id_user'] ?? '') ?></td>
                        <td>
                            <div class="flex items-center space-x-3">
                                <div>
                                    <div class="font-bold"><?= htmlspecialchars($user['full_name'] ?? '') ?></div>
                                    <div class="text-sm opacity-50"><?= htmlspecialchars($user['username'] ?? '') ?></div>
                                    <div class="text-sm opacity-50"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                                    <div class="text-sm opacity-50"><?= htmlspecialchars($user['phone'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($user['nrp'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['rank'] ?? '') ?></td>
                        <td><?= isset($user['created_at']) ? date('d M Y H:i', strtotime($user['created_at'])) : '' ?></td>
                        <td>
                            <div class="flex gap-2">
                                <a href="<?= base_url('admin/editUser.php?id=' . urlencode($user['id_user'] ?? '')) ?>" 
                                   class="btn btn-sm btn-outline btn-info">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button onclick="confirmDelete(<?= intval($user['id_user'] ?? 0) ?>)" 
                                        class="btn btn-sm btn-outline btn-error">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Fungsi untuk menampilkan notifikasi
function showAlert(icon, title, text) {
    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    const isDark = theme === 'dark';

    Swal.fire({
        icon: icon,
        title: title,
        text: text,
        confirmButtonColor: '#3b82f6',
        background: isDark ? '#1f2937' : '#ffffff',
        color: isDark ? '#ffffff' : '#1f2937'
    });
}

// Konfirmasi sebelum hapus
function confirmDelete(userId) {
    if (!userId || userId <= 0) {
        showAlert('error', 'Error', 'Invalid user ID');
        return;
    }

    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    const isDark = theme === 'dark';

    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        background: isDark ? '#1f2937' : '#ffffff',
        color: isDark ? '#ffffff' : '#1f2937'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url('functions/deleteUser.php?id=') ?>' + userId;
        }
    });
}

// Handle notifikasi
<?php if ($success): ?>
showAlert('success', 'Success', '<?= addslashes($success) ?>');
<?php endif; ?>

<?php if ($error): ?>
showAlert('error', 'Error', '<?= addslashes($error) ?>');
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>