<?php
require_once __DIR__ . '/../config/connect.php';

// Check for messages
$message = $_SESSION['password_message'] ?? '';
$error = $_SESSION['password_error'] ?? '';
unset($_SESSION['password_message'], $_SESSION['password_error']);

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . base_url());
    exit();
}

// Force light theme for this page
$_SESSION['force_light_theme'] = true;
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-grow pt-20 bg-base-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-base-100 rounded-xl shadow-md overflow-hidden p-8">
            <div class="text-center mb-8">
            <img src="<?= base_url('assets/images/bareskrim-logo.png') ?>" alt="Logo" class="h-16 mx-auto mb-4">
                <h1 class="text-2xl font-bold text-base-content">Reset Your Password</h1>
                <p class="text-sm text-base-content mt-2">Enter your email to receive a reset link</p>
            </div>

            <form method="POST" action="<?= base_url('auth/resetPassword.php') ?>">
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label" for="email">
                            <span class="label-text text-base-content">Email Address</span>
                        </label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" 
                               class="input input-bordered w-full bg-base-100 text-base-content" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-full mt-6">Send Reset Link</button>
                </div>
            </form>

            <div class="text-center mt-6">
                <a href="<?= base_url('auth/login.php') ?>" class="link link-primary text-sm">
                    <i class="bi bi-arrow-left mr-1"></i> Back to login
                </a>
            </div>
        </div>
    </div>
</main>

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
<?php if ($message): ?>
showAlert('success', 'Berhasil', '<?= addslashes($message) ?>');
<?php endif; ?>

<?php if ($error): ?>
showAlert('error', 'Gagal', '<?= addslashes($error) ?>');
<?php endif; ?>
</script>

</body>
</html>