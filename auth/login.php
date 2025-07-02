<?php
require_once __DIR__ . '/../config/connect.php';
include_once __DIR__ . '/../config/baseURL.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for login success/error messages
$loginError = $_SESSION['login_error'] ?? '';
$loginSuccess = $_SESSION['login_success'] ?? '';
$passwordMessage = $_SESSION['password_message'] ?? '';
$passwordError = $_SESSION['password_error'] ?? '';
$redirectUrl = $_SESSION['redirect_url'] ?? ($_SESSION['intended_url'] ?? base_url());

// Clear session messages after assigning to variables
unset(
    $_SESSION['login_error'], 
    $_SESSION['login_success'], 
    $_SESSION['password_message'], 
    $_SESSION['password_error'],
    $_SESSION['redirect_url'],
    $_SESSION['intended_url']
);

$logoutSuccess = $_SESSION['logout_success'] ?? '';
unset($_SESSION['logout_success']);

// Force light theme for this page
$_SESSION['force_light_theme'] = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-base-100 rounded-xl shadow-md overflow-hidden p-8">
        <div class="text-center mb-8">
            <img src="<?= asset_url('images/bareskrim-logo.png') ?>" alt="Logo" class="h-16 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-base-content">Login to Your Account</h1>
            <p class="text-sm text-base-content/70">Enter your credentials to access the system</p>
        </div>

        <form id="loginForm" method="POST" action="<?= base_url('functions/userLogin.php') ?>">
            <div class="space-y-4">
                <div class="form-control">
                    <label class="label" for="username">
                        <span class="label-text">Username</span>
                    </label>
                    <input type="text" id="username" name="username" placeholder="Enter your username"
                        class="input input-bordered w-full" required
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text">Password</span>
                    </label>
                    <input type="password" id="password" name="password" placeholder="Enter your password"
                        class="input input-bordered w-full" required>
                </div>
                <!-- Teks untuk toggle password -->
                <div class="flex items-center justify-start mt-2 gap-2">
                    <button type="button" onclick="togglePassword()" class="label-text">
                        <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        <span id="toggleText" class="text-sm cursor-pointer">Show Password</span>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <label class="cursor-pointer label justify-start gap-2">
                        <input type="checkbox" name="remember" class="checkbox checkbox-sm">
                        <span class="label-text">Remember me</span>
                    </label>
                    <a href="<?= base_url('auth/forgotPassword.php') ?>" class="text-sm link link-primary">Forgot
                        password?</a>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-full mt-6">Login</button>
            </div>
        </form>

        <div class="divider my-6">OR</div>

        <div class="text-center">
            <p class="text-sm text-base-content/70">Don't have an account? <a href="#" class="link link-primary">Contact
                    admin</a></p>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    const toggleText = document.getElementById('toggleText');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
        toggleText.textContent = 'Hide Password';
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
        toggleText.textContent = 'Show Password';
    }
}

// Fungsi untuk menampilkan notifikasi
function showAlert(icon, title, text, redirectUrl = null) {
    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    const isDark = theme === 'dark';

    Swal.fire({
        icon: icon,
        title: title,
        text: text,
        confirmButtonColor: '#3b82f6',
        background: isDark ? '#1f2937' : '#ffffff',
        color: isDark ? '#ffffff' : '#1f2937',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (redirectUrl && result.isConfirmed) {
            window.location.href = redirectUrl;
        }
    });
}

// Handle notifikasi
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($loginError): ?>
    showAlert('error', 'Login Gagal', '<?= addslashes($loginError) ?>');
    <?php endif; ?>

    <?php if ($loginSuccess): ?>
    showAlert('success', 'Login Berhasil', '<?= addslashes($loginSuccess) ?>', '<?= $redirectUrl ?>');
    <?php endif; ?>

    <?php if ($passwordMessage): ?>
    showAlert('info', 'Informasi', '<?= addslashes($passwordMessage) ?>');
    <?php endif; ?>

    <?php if ($passwordError): ?>
    showAlert('error', 'Terjadi Kesalahan', '<?= addslashes($passwordError) ?>');
    <?php endif; ?>

    <?php if ($logoutSuccess): ?>
    showAlert('success', 'Logout Berhasil', '<?= addslashes($logoutSuccess) ?>', '<?= $redirectUrl ?>');
    <?php endif; ?>
});

// Deteksi penutupan tab/browser
window.addEventListener('beforeunload', function(e) {
    // Hanya trigger jika user benar-benar menutup tab/browser
    if (!e.target.activeElement || 
        !['A', 'BUTTON', 'INPUT', 'FORM'].includes(e.target.activeElement.tagName)) {
        
        // Kirim ping ke server untuk memastikan session tetap aktif
        navigator.sendBeacon('<?= base_url('functions/keepAlive.php') ?>');
    }
});
</script>

</body>
</html>