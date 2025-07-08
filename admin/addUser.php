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

        <form id="addUserForm" method="POST" action="<?= base_url('functions/createUser.php') ?>"
            enctype="multipart/form-data">
            <div class="space-y-4">
                <!-- Profile Image Upload -->
                <div class="form-control">
                    <label class="label" for="profile_image">
                        <span class="label-text">Profile Image</span>
                    </label>
                    <div class="flex flex-col items-center space-y-4">
                        <div class="avatar">
                            <div class="w-24 h-24 rounded-full">
                                <img id="imagePreview" src="<?= base_url('public/uploads/profiles/profil.jpg') ?>"
                                    alt="Profile Preview" class="object-cover">
                            </div>
                        </div>
                        <input type="file" id="profile_image" name="profile_image"
                            accept="image/jpeg,image/jpg,image/png,image/gif"
                            class="file-input file-input-bordered file-input-sm w-full max-w-xs">
                        <label class="label">
                            <span class="label-text-alt">Max 2MB (JPG, PNG, GIF)</span>
                        </label>
                    </div>
                </div>

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

// Preview image sebelum upload
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validasi ukuran file (2MB)
        if (file.size > 2 * 1024 * 1024) {
            showAlert('error', 'File Too Large', 'Please select an image smaller than 2MB');
            e.target.value = '';
            return;
        }

        // Validasi tipe file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            showAlert('error', 'Invalid File Type', 'Please select a valid image file (JPG, PNG, GIF)');
            e.target.value = '';
            return;
        }

        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        // Reset to default image if no file selected
        document.getElementById('imagePreview').src = '<?= base_url('public/uploads/profiles/profil.jpg') ?>';
    }
});

// Input mask untuk NRP (hanya angka)
document.getElementById('nrp').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').slice(0, 8);
});

// Input mask untuk phone number
document.getElementById('phone').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9+\-\s]/g, '');
});

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