<?php
include __DIR__ . '/../config/connect.php';

// Check for login success/error messages
$loginError = $_SESSION['login_error'] ?? '';
$loginSuccess = $_SESSION['login_success'] ?? '';
unset($_SESSION['login_error'], $_SESSION['login_success']);

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . base_url());
    exit();
}

// Force light theme for this page
$_SESSION['force_light_theme'] = true;
include __DIR__ . '/../includes/header.php';

?>

<!-- <main class="pt-20 min-h-screen"> -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-md mx-auto bg-base-100 rounded-xl shadow-md overflow-hidden p-8">
            <div class="text-center mb-8">
                <img src="<?= base_url('assets/images/bareskrim-logo.png') ?>" alt="Logo" class="h-16 mx-auto mb-4">
                <h1 class="text-2xl font-bold text-base-content">Login to Your Account</h1>
                <p class="text-sm text-base-content/70">Enter your credentials to access the system</p>
            </div>

            <form id="loginForm" method="POST" action="<?= base_url('auth/userLogin.php') ?>">
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
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="Enter your password"
                                class="input input-bordered w-full pr-10" required>
                            <button type="button" onclick="togglePassword()" class="absolute right-3 top-3">
                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>
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
                <p class="text-sm text-base-content/70">Don't have an account? <a href="#"
                        class="link link-primary">Contact admin</a></p>
            </div>
        </div>
    </div>
</main>

<!-- AlpineJS -->
<script defer src="<?= base_url('dist/js/bundle.js') ?>"></script>

<script>
// Show alerts if there are messages
<?php if ($loginError): ?>
Swal.fire({
    icon: 'error',
    title: 'Login Failed',
    text: '<?= addslashes($loginError) ?>',
    confirmButtonColor: '#3b82f6',
});
<?php endif; ?>

<?php if ($loginSuccess): ?>
Swal.fire({
    icon: 'success',
    title: 'Login Successful',
    text: '<?= addslashes($loginSuccess) ?>',
    confirmButtonColor: '#3b82f6',
}).then(() => {
    window.location.href = '<?= base_url() ?>';
});
<?php endif; ?>

// Toggle password visibility
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    }
}
</script>

<!-- Footer -->
<?php 

$_SESSION['force_light_footer'] = true;
include __DIR__ . '/../includes/footer.php'; 

?>