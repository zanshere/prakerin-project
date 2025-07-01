<?php
require_once __DIR__ . '/../config/connect.php';    
require_once __DIR__ . '/../config/authCheck.php';    

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('auth/login.php'));
    exit();
}

// Check for messages
$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

include __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-base-100 rounded-xl shadow-md overflow-hidden p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-base-content">Add New User</h1>
            <p class="text-sm text-base-content/70">Fill the form to create a new user account</p>
        </div>

        <form id="addUserForm" method="POST" action="<?= base_url('functions/createUser.php') ?>">
            <div class="space-y-4">
                <div class="form-control">
                    <label class="label" for="username">
                        <span class="label-text">Username</span>
                    </label>
                    <input type="text" id="username" name="username" placeholder="Enter username"
                        class="input input-bordered w-full" required maxlength="100">
                </div>

                <div class="form-control">
                    <label class="label" for="email">
                        <span class="label-text">Email</span>
                    </label>
                    <input type="email" id="email" name="email" placeholder="Enter email"
                        class="input input-bordered w-full" required maxlength="100">
                </div>

                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text">Password</span>
                    </label>
                    <input type="password" id="password" name="password" placeholder="Enter password"
                        class="input input-bordered w-full" required>
                </div>

                <div class="form-control">
                    <label class="label" for="full_name">
                        <span class="label-text">Full Name</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" placeholder="Enter full name"
                        class="input input-bordered w-full" required maxlength="150">
                </div>

                <div class="form-control">
                    <label class="label" for="phone">
                        <span class="label-text">Phone Number</span>
                    </label>
                    <input type="text" id="phone" name="phone" placeholder="Enter phone number"
                        class="input input-bordered w-full" required maxlength="50">
                </div>

                <div class="form-control">
                    <label class="label" for="nrp">
                        <span class="label-text">NRP</span>
                    </label>
                    <input type="text" id="nrp" name="nrp" placeholder="Enter NRP" class="input input-bordered w-full"
                        required maxlength="8">
                </div>

                <div class="form-control">
                    <label class="label" for="rank">
                        <span class="label-text">Rank</span>
                    </label>
                    <select id="rank" name="rank" class="select select-bordered w-full" required>
                        <option value="" disabled selected>Select rank</option>
                        <option value="AKP">AKP</option>
                        <option value="IPTU">IPTU</option>
                        <option value="IPDA">IPDA</option>
                        <option value="AIPTU">AIPTU</option>
                        <option value="AIPDA">AIPDA</option>
                        <option value="BRIPKA">BRIPKA</option>
                        <option value="BRIGPOL">BRIGPOL</option>
                        <option value="BRIPTU">BRIPTU</option>
                        <option value="BRIPDA">BRIPDA</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label" for="role">
                        <span class="label-text">Role</span>
                    </label>
                    <select id="role" name="role" class="select select-bordered w-full" required>
                        <option value="" disabled selected>Select role</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-full mt-6">Create User</button>
            </div>
        </form>
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
        color: isDark ? '#ffffff' : '#1f2937'
    });
}

// Handle notifikasi
<?php if ($success): ?>
document.addEventListener('DOMContentLoaded', function() {
    showAlert('success', 'Success', <?= json_encode($success) ?>);
});
<?php endif; ?>

<?php if ($error): ?>
document.addEventListener('DOMContentLoaded', function() {
    showAlert('error', 'Error', <?= json_encode($error) ?>);
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>