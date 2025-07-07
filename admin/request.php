<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../config/authCheck.php';
require_once __DIR__ . '/../config/baseURL.php';

// Pastikan session sudah start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Validasi admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['admin_error'] = 'Unauthorized access';
    header("Location: " . base_url('auth/login.php'));
    exit();
}

// Handle messages
$message = $_SESSION['admin_message'] ?? '';
$error = $_SESSION['admin_error'] ?? '';
$successData = $_SESSION['admin_success'] ?? null;
unset($_SESSION['admin_message'], $_SESSION['admin_error'], $_SESSION['admin_success']);

// Get password reset requests
$requests = [];
$pendingCount = 0;

try {
    // Validasi koneksi database
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    $query = "SELECT prr.*, u.full_name, u.nrp, u.rank 
              FROM password_reset_requests prr 
              JOIN users u ON prr.user_id = u.id_user 
              ORDER BY 
                CASE WHEN prr.status = 'pending' THEN 1 ELSE 2 END,
                prr.request_date DESC";
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $requests = $result->fetch_all(MYSQLI_ASSOC);
    
    // Count pending requests
    foreach ($requests as $req) {
        if ($req['status'] === 'pending') {
            $pendingCount++;
        }
    }

} catch (Exception $e) {
    error_log("Error in request.php: " . $e->getMessage());
    $error = "Error loading requests: " . $e->getMessage();
}

// Force light theme for this page
$_SESSION['force_light_theme'] = true;
include __DIR__ . '/../includes/header.php';
?>

<style>
.status-badge {
    @apply px-2 py-1 rounded-full text-xs font-medium;
}

.status-pending {
    @apply bg-yellow-100 text-yellow-800;
}

.status-completed {
    @apply bg-green-100 text-green-800;
}

.status-rejected {
    @apply bg-red-100 text-red-800;
}
</style>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-base-content">
                <i class="bi bi-key mr-3 text-primary"></i>
                Password Reset Requests
            </h1>
            <p class="text-base-content/70 mt-2">Kelola permintaan reset password dari user</p>
        </div>

        <!-- Stats Card -->
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-figure text-warning">
                    <i class="bi bi-clock-history text-2xl"></i>
                </div>
                <div class="stat-title">Pending</div>
                <div class="stat-value text-warning"><?= $pendingCount ?></div>
                <div class="stat-desc">permintaan</div>
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-0">
            <?php if (empty($requests)): ?>
            <div class="text-center py-12">
                <i class="bi bi-inbox text-6xl text-base-content/20 mb-4"></i>
                <h3 class="text-xl font-semibold text-base-content/70 mb-2">Tidak ada permintaan</h3>
                <p class="text-base-content/50">Belum ada permintaan reset password dari user</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-base-200">
                        <tr>
                            <th class="font-bold">No</th>
                            <th class="font-bold">User Info</th>
                            <th class="font-bold">Email</th>
                            <th class="font-bold">Tanggal</th>
                            <th class="font-bold">Status</th>
                            <th class="font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $index => $request): ?>
                        <tr class="hover">
                            <td class="font-medium"><?= $index + 1 ?></td>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content rounded-full w-12">
                                            <span class="text-sm font-bold">
                                                <?= strtoupper(substr($request['full_name'], 0, 2)) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-base-content">
                                            <?= htmlspecialchars($request['full_name']) ?></div>
                                        <div class="text-sm text-base-content/70">
                                            <?= htmlspecialchars($request['rank']) ?> •
                                            <?= htmlspecialchars($request['nrp']) ?>
                                        </div>
                                        <div class="text-xs text-base-content/50">
                                            @<?= htmlspecialchars($request['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm"><?= htmlspecialchars($request['email']) ?></span>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <?= date('d/m/Y', strtotime($request['request_date'])) ?>
                                </div>
                                <div class="text-xs text-base-content/50">
                                    <?= date('H:i', strtotime($request['request_date'])) ?> WIB
                                </div>
                            </td>
                            <td>
                                <?php 
                                $statusClass = 'status-' . $request['status'];
                                $statusText = ucfirst($request['status']);
                                if ($request['status'] === 'pending') $statusText = 'Menunggu';
                                elseif ($request['status'] === 'completed') $statusText = 'Selesai';
                                elseif ($request['status'] === 'rejected') $statusText = 'Ditolak';
                                ?>
                                <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <?php if ($request['status'] === 'pending'): ?>
                                <div class="flex space-x-2">
                                    <button
                                        onclick="approveRequest(<?= $request['id'] ?>, '<?= htmlspecialchars($request['username']) ?>')"
                                        class="btn btn-success btn-sm">
                                        <i class="bi bi-check-lg"></i>
                                        Setuju
                                    </button>
                                    <button
                                        onclick="rejectRequest(<?= $request['id'] ?>, '<?= htmlspecialchars($request['username']) ?>')"
                                        class="btn btn-error btn-sm">
                                        <i class="bi bi-x-lg"></i>
                                        Tolak
                                    </button>
                                </div>
                                <?php else: ?>
                                <span class="text-base-content/50 text-sm">
                                    <?php if ($request['status'] === 'completed'): ?>
                                    <i class="bi bi-check-circle text-success"></i> Selesai diproses
                                    <?php else: ?>
                                    <i class="bi bi-x-circle text-error"></i> Ditolak
                                    <?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
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
        color: isDark ? '#ffffff' : '#1f2937',
        customClass: {
            popup: 'rounded-xl'
        }
    });
}

function approveRequest(requestId, username) {
    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    const isDark = theme === 'dark';

    Swal.fire({
        title: 'Konfirmasi Persetujuan',
        html: `Apakah Anda yakin ingin menyetujui permintaan reset password untuk user <strong>${username}</strong>?<br><br><small class="text-warning">Password baru akan di-generate secara otomatis.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-check-lg mr-2"></i>Ya, Setuju',
        cancelButtonText: 'Batal',
        background: isDark ? '#1f2937' : '#ffffff',
        color: isDark ? '#ffffff' : '#1f2937',
        customClass: {
            popup: 'rounded-xl'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang membuat password baru',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                background: isDark ? '#1f2937' : '#ffffff',
                color: isDark ? '#ffffff' : '#1f2937',
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Process request
            fetch('<?= base_url("functions/processPasswordReset.php") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=approve&request_id=${requestId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            html: `Password untuk user <strong>${username}</strong> telah direset.<br><br>
                               <div class="bg-base-200 p-4 rounded-lg mt-4">
                                   <strong>Password Baru:</strong><br>
                                   <code class="text-lg font-mono bg-base-300 px-2 py-1 rounded">${data.new_password}</code><br>
                                   <small class="text-warning">Salin password ini dan kirimkan ke user</small>
                               </div>`,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3b82f6',
                            background: isDark ? '#1f2937' : '#ffffff',
                            color: isDark ? '#ffffff' : '#1f2937',
                            customClass: {
                                popup: 'rounded-xl'
                            }
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Unknown error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Error!', error.message || 'Terjadi kesalahan sistem');
                });
        }
    });
}

function rejectRequest(requestId, username) {
    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    const isDark = theme === 'dark';

    Swal.fire({
        title: 'Konfirmasi Penolakan',
        html: `Apakah Anda yakin ingin menolak permintaan reset password untuk user <strong>${username}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-x-lg mr-2"></i>Ya, Tolak',
        cancelButtonText: 'Batal',
        background: isDark ? '#1f2937' : '#ffffff',
        color: isDark ? '#ffffff' : '#1f2937',
        customClass: {
            popup: 'rounded-xl'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Process rejection
            fetch('<?= base_url("functions/processPasswordReset.php") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=reject&request_id=${requestId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'Berhasil!', `Permintaan dari ${username} telah ditolak`);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', 'Gagal!', data.message ||
                            'Terjadi kesalahan saat memproses permintaan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Error!', 'Terjadi kesalahan sistem');
                });
        }
    });
}

// Handle messages from server
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($error): ?>
    showAlert('error', 'Error!', '<?= addslashes($error) ?>');
    <?php endif; ?>

    <?php if ($message): ?>
    showAlert('success', 'Success!', '<?= addslashes($message) ?>');
    <?php endif; ?>

    <?php if ($successData): ?>
    Swal.fire({
        title: '<?= addslashes($successData['title'] ?? 'Success') ?>',
        html: `<?= addslashes($successData['message'] ?? '') ?><br><br>
                   <div class="bg-base-200 p-4 rounded-lg mt-4">
                       <strong>Password Baru:</strong><br>
                       <code class="text-lg font-mono bg-base-300 px-2 py-1 rounded"><?= addslashes($successData['new_password'] ?? '') ?></code><br>
                       <small class="text-warning">Salin password ini dan kirimkan ke user</small>
                   </div>`,
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3b82f6',
        background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1f2937' :
            '#ffffff',
        color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#ffffff' : '#1f2937',
        customClass: {
            popup: 'rounded-xl'
        }
    });
    <?php endif; ?>
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>