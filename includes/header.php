<?php 
require_once __DIR__ . '/../config/connect.php';
include_once __DIR__ . '/../config/baseURL.php';
require_once __DIR__ . '/../config/authCheck.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Username';
$profileImage = $isLoggedIn ? 
    (isset($_SESSION['profile_image']) ? base_url('public/uploads/profiles/' . $_SESSION['profile_image']) : 'https://img.freepik.com/free-psd/contact-icon-illustration-isolated_23-2151903337.jpg') 
    : 'https://img.freepik.com/free-psd/contact-icon-illustration-isolated_23-2151903337.jpg';
$userRole = $isLoggedIn ? $_SESSION['role'] : ''; // 'admin' atau 'user'
?>

<!DOCTYPE html>
<html lang="en" data-theme="light" x-data="{ 
    darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches), 
    sidebarOpen: false, 
    profileDropdownOpen: false, 
    isLoggedIn: <?= $isLoggedIn ? 'true' : 'false' ?>,
    init() {
        this.$watch('darkMode', value => {
            localStorage.setItem('theme', value ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', value ? 'dark' : 'light');
        });
        // Set initial theme
        document.documentElement.setAttribute('data-theme', this.darkMode ? 'dark' : 'light');
    }
}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prakerin Project</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= asset_url('images/bareskrim-logo.png') ?>" type="image/x-icon">
    <!-- DaisyUI + Tailwind CSS -->
    <link rel="stylesheet" href="<?= dist_url('css/style.css') ?>">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= base_url('public/icons/bootstrap-icons.css') ?>">
    <!-- AlpineJS -->
    <script src="<?= dist_url('js/bundle.js') ?>"></script>
    <!-- Sweetalert2 -->
    
    <style>
    .hamburger span {
        display: block;
        width: 24px;
        height: 2px;
        background-color: currentColor;
        transition: transform 0.3s, opacity 0.3s;
        margin: 5px 0;
    }

    .hamburger.active span:nth-child(1) {
        transform: translateY(7px) rotate(45deg);
    }

    .hamburger.active span:nth-child(2) {
        opacity: 0;
    }

    .hamburger.active span:nth-child(3) {
        transform: translateY(-7px) rotate(-45deg);
    }
    </style>
</head>

<body class="min-h-screen">
    <!-- Navbar -->
    <div class="navbar bg-base-100 shadow-md fixed w-full z-50 px-4 lg:px-8">
        <div class="navbar-start">
            <!-- Mobile menu button - Hanya tampil jika user sudah login -->
            <?php if ($isLoggedIn): ?>
            <div class="dropdown lg:hidden">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="btn btn-ghost btn-circle hover:bg-base-200 transition-colors duration-300">
                    <div class="hamburger" :class="{ 'active': sidebarOpen }">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </button>
            </div>
            <?php endif; ?>

            <!-- Logo -->
            <a href="<?= base_url() ?>" class="btn btn-ghost normal-case text-xl font-bold">
                <img src="<?= asset_url('images/bareskrim-logo.png') ?>" alt="Logo" class="h-8 w-auto mr-2">
                <span class="hidden sm:inline text-base-content">
                    Unit Reskrim
                </span>
            </a>
        </div>

        <!-- Center Menu (Desktop) - Hanya tampil jika user sudah login -->
        <?php if ($isLoggedIn): ?>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1 gap-2">
                <?php if ($userRole === 'admin'): ?>
                <!-- Menu khusus admin -->
                <li>
                    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-ghost btn-sm">
                        <i class="bi bi-speedometer2 text-lg"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('admin/manageUsers.php') ?>" class="btn btn-ghost btn-sm">
                        <i class="bi bi-people text-lg"></i>
                        Manage Users
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('admin/reports.php') ?>" class="btn btn-ghost btn-sm">
                        <i class="bi bi-file-earmark-text text-lg"></i>
                        Reports
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('admin/request.php') ?>" class="btn btn-ghost btn-sm">
                        <i class="bi bi-stack text-lg"></i>
                        Request
                    </a>
                </li>
                <?php else: ?>
                <!-- Menu untuk user biasa -->
                <li>
                    <a href="<?= base_url('pages/user/dashboard.php') ?>" class="btn btn-ghost btn-sm">
                        <i class="bi bi-person text-lg"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('pages/user/cases.php') ?>" class="btn btn-ghost btn-sm">
                        <i class="bi bi-folder text-lg"></i>
                        My Cases
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="navbar-end gap-2">
            <!-- DaisyUI Theme Toggle -->
            <label class="swap swap-rotate btn btn-ghost btn-circle">
                <input type="checkbox" class="theme-controller" x-model="darkMode"
                    @change="darkMode = $event.target.checked" />

                <!-- Sun icon -->
                <i class="bi bi-sun-fill swap-off text-xl text-warning"></i>

                <!-- Moon icon -->
                <i class="bi bi-moon-fill swap-on text-xl text-info"></i>
            </label>

            <!-- User Section -->
            <?php if (!$isLoggedIn): ?>
            <div class="flex gap-2">
                <a href="<?= base_url('auth/login.php') ?>" class="btn btn-ghost btn-sm">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span class="hidden lg:inline">Login</span>
                </a>
            </div>
            <?php else: ?>
            <!-- Profile Dropdown for Desktop -->
            <div class="dropdown dropdown-end" x-data="{ open: false }">
                <button @click="open = !open" @click.outside="open = false" class="btn btn-ghost btn-circle avatar">
                    <div class="w-10 rounded-full">
                        <img src="<?= $profileImage ?>" alt="Profile" />
                    </div>
                </button>
                <div x-show="open" x-transition
                    class="absolute right-0 mt-2 w-56 origin-top-right rounded-md shadow-lg bg-base-100 text-base-content ring-1 ring-base-300 focus:outline-none z-50">
                    <div class="py-1" role="none">
                        <div class="px-4 py-2 border-b border-base-300">
                            <p class="text-sm font-semibold"><?= htmlspecialchars($username) ?></p>
                            <p class="text-xs text-base-content/70"><?= ucfirst($userRole) ?> Reskrim</p>
                        </div>
                        <a href="<?= base_url('pages/profile/profile.php') ?>"
                            class="block px-4 py-2 text-sm hover:bg-base-200">
                            <i class="bi bi-person mr-2"></i> Profile
                        </a>
                        <a href="<?= base_url('pages/profile/setting.php') ?>"
                            class="block px-4 py-2 text-sm hover:bg-base-200">
                            <i class="bi bi-gear mr-2"></i> Settings
                        </a>
                        <?php if ($userRole === 'admin'): ?>
                        <a href="<?= base_url('pages/admin/settings.php') ?>"
                            class="block px-4 py-2 text-sm hover:bg-base-200">
                            <i class="bi bi-shield-lock mr-2"></i> Admin Panel
                        </a>
                        <?php endif; ?>
                        <div class="border-t border-base-300"></div>
                        <a href="<?= base_url('auth/logout.php') ?>"
                            class="block px-4 py-2 text-sm text-error hover:bg-base-200">
                            <i class="bi bi-box-arrow-right mr-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Enhanced Mobile Sidebar - Hanya tampil jika user sudah login -->
    <?php if ($isLoggedIn): ?>
    <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 lg:hidden">
        <!-- Backdrop -->
        <div @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

        <!-- Sidebar -->
        <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="transform -translate-x-full" x-transition:enter-end="transform translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="transform translate-x-0"
            x-transition:leave-end="transform -translate-x-full"
            class="relative bg-base-100 w-80 h-full shadow-2xl border-r border-base-300">

            <!-- Sidebar Header -->
            <div class="flex items-center justify-between p-4 border-b border-base-300">
                <div class="flex items-center">
                    <img src="<?= asset_url('images/bareskrim-logo.png') ?>" alt="Logo" class="h-8 w-auto mr-3">
                    <span class="text-xl font-bold">
                        Unit Reskrim
                    </span>
                </div>
                <button @click="sidebarOpen = false" class="btn btn-ghost btn-sm btn-circle">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <!-- Sidebar Content -->
            <div class="overflow-y-auto h-full pb-20">
                <!-- Menu Items -->
                <ul class="menu p-4 gap-2">
                    <li>
                        <a href="<?= base_url() ?>" class="flex items-center">
                            <i class="bi bi-home text-xl text-primary mr-3"></i>
                            <span class="font-medium">Home</span>
                        </a>
                    </li>

                    <?php if ($userRole === 'admin'): ?>
                    <!-- Menu khusus admin -->
                    <li>
                        <a href="<?= base_url('pages/admin/dashboard.php') ?>" class="flex items-center">
                            <i class="bi bi-speedometer2 text-xl text-info mr-3"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('pages/admin/users.php') ?>" class="flex items-center">
                            <i class="bi bi-people text-xl text-warning mr-3"></i>
                            <span class="font-medium">Manage Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('pages/admin/reports.php') ?>" class="flex items-center">
                            <i class="bi bi-file-earmark-text text-xl text-success mr-3"></i>
                            <span class="font-medium">Reports</span>
                        </a>
                    </li>
                    <?php else: ?>
                    <!-- Menu untuk user biasa -->
                    <li>
                        <a href="<?= base_url('pages/user/dashboard.php') ?>" class="flex items-center">
                            <i class="bi bi-person text-xl text-success mr-3"></i>
                            <span class="font-medium">My Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('pages/user/cases.php') ?>" class="flex items-center">
                            <i class="bi bi-folder text-xl text-accent mr-3"></i>
                            <span class="font-medium">My Cases</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Menu yang bisa diakses semua role -->
                    <li>
                        <a href="<?= base_url('pages/profile/setting.php') ?>" class="flex items-center">
                            <i class="bi bi-gear text-xl text-secondary mr-3"></i>
                            <span class="font-medium">Settings</span>
                        </a>
                    </li>
                </ul>

                <div class="divider mx-4">Account</div>

                <!-- User Section -->
                <div class="p-4">
                    <div class="space-y-4">
                        <!-- User Info Card -->
                        <div class="card bg-base-200 border border-base-300">
                            <div class="card-body p-4">
                                <div class="flex items-center">
                                    <div class="avatar mr-3">
                                        <div class="w-12 rounded-full">
                                            <img src="<?= $profileImage ?>" alt="Profile" />
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-base"><?= htmlspecialchars($username) ?></h3>
                                        <p class="text-sm opacity-70">Role: <?= ucfirst($userRole) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Setelah navbar dan sidebar mobile -->
    <div class="min-h-screen flex flex-col">
        <!-- Konten utama akan ditempatkan di sini -->
        <main class="flex-grow pt-20">
            <div class="container mx-auto px-4 py-8">
                <!-- Content dari halaman yang meng-include header akan muncul di sini -->

                <script>
                // Fungsi untuk konfirmasi logout
                function confirmLogout(event) {
                    event.preventDefault();
                    const logoutUrl = event.currentTarget.getAttribute('href');
                    const theme = document.documentElement.getAttribute('data-theme') || 'light';
                    const isDark = theme === 'dark';

                    Swal.fire({
                        title: 'Konfirmasi Logout',
                        text: 'Apakah Anda yakin ingin logout?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3b82f6',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Logout',
                        cancelButtonText: 'Batal',
                        background: isDark ? '#1f2937' : '#ffffff',
                        color: isDark ? '#ffffff' : '#1f2937',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = logoutUrl;
                        }
                    });
                }

                // Tambahkan event listener untuk semua link logout
                document.querySelectorAll('a[href*="logout.php"]').forEach(link => {
                    link.addEventListener('click', confirmLogout);
                });
                </script>