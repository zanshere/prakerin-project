<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../config/authCheck.php';
require_once __DIR__ . '/../config/baseURL.php';

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Access Denied',
        'message' => 'Please login first'
    ];
    header("Location: " . base_url('auth/login.php'));
    exit();
}

// Check for alert message from previous request
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);

// Get user ID from URL parameter
$user_id = $_GET['id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Invalid Request',
        'message' => 'Invalid user ID provided'
    ];
    header("Location: " . base_url('admin/manageUsers.php'));
    exit();
}

// Fetch user data
$user = null;
$query = "SELECT id_user, username, email, full_name, phone, nrp, `rank`, role 
          FROM users WHERE id_user = ?";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Not Found',
            'message' => 'User not found in database'
        ];
        header("Location: " . base_url('admin/manageUsers.php'));
        exit();
    }
    $stmt->close();
} else {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Database Error',
        'message' => 'Failed to prepare database query'
    ];
    header("Location: " . base_url('admin/manageUsers.php'));
    exit();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-base-100 rounded-xl shadow-md overflow-hidden p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-base-content">Edit User</h1>
            <p class="text-sm text-base-content/70">Update user information</p>
        </div>

        <form id="editUserForm" method="POST" action="<?= base_url('functions/updateUser.php') ?>">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id_user']) ?>">
            
            <div class="space-y-4">
                <div class="form-control">
                    <label class="label" for="username">
                        <span class="label-text">Username</span>
                    </label>
                    <input type="text" id="username" name="username" placeholder="Enter username"
                        class="input input-bordered w-full" required maxlength="100"
                        value="<?= htmlspecialchars($user['username']) ?>">
                    <label class="label">
                        <span class="label-text-alt">Max 100 characters</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label" for="email">
                        <span class="label-text">Email</span>
                    </label>
                    <input type="email" id="email" name="email" placeholder="Enter email"
                        class="input input-bordered w-full" required maxlength="100"
                        value="<?= htmlspecialchars($user['email']) ?>">
                    <label class="label">
                        <span class="label-text-alt">Valid email address</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text">Password</span>
                    </label>
                    <input type="password" id="password" name="password" placeholder="Leave blank to keep current password"
                        class="input input-bordered w-full" minlength="6">
                    <label class="label">
                        <span class="label-text-alt">Minimum 6 characters</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label" for="full_name">
                        <span class="label-text">Full Name</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" placeholder="Enter full name"
                        class="input input-bordered w-full" required maxlength="150"
                        value="<?= htmlspecialchars($user['full_name']) ?>">
                    <label class="label">
                        <span class="label-text-alt">Max 150 characters</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label" for="phone">
                        <span class="label-text">Phone Number</span>
                    </label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter phone number"
                        class="input input-bordered w-full" required maxlength="50"
                        value="<?= htmlspecialchars($user['phone']) ?>">
                    <label class="label">
                        <span class="label-text-alt">Format: +62xxx or 08xxx</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label" for="nrp">
                        <span class="label-text">NRP</span>
                    </label>
                    <input type="text" id="nrp" name="nrp" placeholder="Enter NRP" 
                        class="input input-bordered w-full" required maxlength="8" minlength="8"
                        pattern="\d{8}" title="Must be exactly 8 digits"
                        value="<?= htmlspecialchars($user['nrp']) ?>">
                    <label class="label">
                        <span class="label-text-alt">8 digit number</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label" for="rank">
                        <span class="label-text">Rank</span>
                    </label>
                    <select id="rank" name="rank" class="select select-bordered w-full" required>
                        <option value="" disabled>Select rank</option>
                        <option value="AKP" <?= $user['rank'] == 'AKP' ? 'selected' : '' ?>>AKP</option>
                        <option value="IPTU" <?= $user['rank'] == 'IPTU' ? 'selected' : '' ?>>IPTU</option>
                        <option value="IPDA" <?= $user['rank'] == 'IPDA' ? 'selected' : '' ?>>IPDA</option>
                        <option value="AIPTU" <?= $user['rank'] == 'AIPTU' ? 'selected' : '' ?>>AIPTU</option>
                        <option value="AIPDA" <?= $user['rank'] == 'AIPDA' ? 'selected' : '' ?>>AIPDA</option>
                        <option value="BRIPKA" <?= $user['rank'] == 'BRIPKA' ? 'selected' : '' ?>>BRIPKA</option>
                        <option value="BRIGPOL" <?= $user['rank'] == 'BRIGPOL' ? 'selected' : '' ?>>BRIGPOL</option>
                        <option value="BRIPTU" <?= $user['rank'] == 'BRIPTU' ? 'selected' : '' ?>>BRIPTU</option>
                        <option value="BRIPDA" <?= $user['rank'] == 'BRIPDA' ? 'selected' : '' ?>>BRIPDA</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label" for="role">
                        <span class="label-text">Role</span>
                    </label>
                    <select id="role" name="role" class="select select-bordered w-full" required>
                        <option value="" disabled>Select role</option>
                        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <div class="flex gap-4 mt-6">
                    <button type="submit" class="btn btn-primary flex-1">Update User</button>
                    <a href="<?= base_url('admin/manageUsers.php') ?>" class="btn btn-outline flex-1">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

// Handle notifikasi dari session
<?php if ($alert): ?>
document.addEventListener('DOMContentLoaded', function() {
    showAlert('<?= $alert['type'] ?>', '<?= $alert['title'] ?>', '<?= addslashes($alert['message']) ?>');
});
<?php endif; ?>

// Form validation
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    // Validate NRP (should be 8 digits)
    const nrp = document.getElementById('nrp').value;
    if (!/^\d{8}$/.test(nrp)) {
        e.preventDefault();
        showAlert('error', 'Invalid NRP', 'NRP must be exactly 8 digits');
        return;
    }
    
    // Validate phone number (should contain only numbers, +, -, and spaces)
    const phone = document.getElementById('phone').value;
    if (!/^[0-9+\-\s]+$/.test(phone)) {
        e.preventDefault();
        showAlert('error', 'Invalid Phone Number', 'Phone number can only contain numbers, +, -, and spaces');
        return;
    }

    // Validate password if provided
    const password = document.getElementById('password').value;
    if (password && password.length < 6) {
        e.preventDefault();
        showAlert('error', 'Invalid Password', 'Password must be at least 6 characters long');
        return;
    }
});

// Input mask for phone number
document.getElementById('phone').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9+\-\s]/g, '');
});

// Input mask for NRP (only numbers)
document.getElementById('nrp').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').slice(0, 8);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>