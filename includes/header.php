<?php 
require_once __DIR__ . '/../config/connect.php';
include __DIR__ . '/../config/baseURL.php';
require_once __DIR__ . '/../functions/checkRememberMe.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Username';
$profileImage = $isLoggedIn ? 
    (isset($_SESSION['profile_image']) ? base_url('public/uploads/profiles/' . $_SESSION['profile_image']) : 'https://via.placeholder.com/40') 
    : 'https://via.placeholder.com/40';
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
    <link rel="shortcut icon" href="<?= base_url('assets/images/bareskrim-logo.png') ?>" type="image/x-icon">
    <!-- DaisyUI + Tailwind CSS -->
    <link rel="stylesheet" href="<?= base_url('dist/css/style.css') ?>">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= base_url('public/icons/bootstrap-icons.css') ?>">
    <!-- AlpineJS -->
    <script defer src="<?= base_url('dist/js/bundle.js') ?>"></script>
    <!-- Sweetalert2 -->
    <script src="<?= base_url('dist/js/sweetalert2.all.min.js') ?>"></script>
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
                <img src="<?= base_url('assets/images/bareskrim-logo.png') ?>" alt="Logo" class="h-8 w-auto mr-2">
                <span class="hidden sm:inline text-base-content">
                    Unit Reskrim
                </span>
            </a>
        </div>

        <!-- Center Menu (Desktop) - Hanya tampil jika user sudah login -->
        <?php if ($isLoggedIn): ?>
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1 gap-2">
                    <li>
                        <a href="#" class="btn btn-ghost btn-sm">
                            <i class="bi bi-house text-lg"></i>
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="#" class="btn btn-ghost btn-sm">
                            <i class="bi bi-info text-lg"></i>
                            About
                        </a>
                    </li>
                    <li>
                        <a href="#" class="btn btn-ghost btn-sm">
                            <i class="bi bi-graph-up text-lg"></i>
                            Stats
                        </a>
                    </li>
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
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar"
                        @click="profileDropdownOpen = !profileDropdownOpen">
                        <div class="w-10 rounded-full">
                            <img src="<?= $profileImage ?>" alt="Profile" />
                        </div>
                    </div>
                    <ul x-show="profileDropdownOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        @click.away="profileDropdownOpen = false" tabindex="0"
                        class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300">
                        <li class="menu-title">
                            <span class="font-semibold"><?= htmlspecialchars($username) ?></span>
                        </li>
                        <li>
                            <a href="<?= base_url('pages/profile/profile.php') ?>" class="justify-between">
                                <span><i class="bi bi-person mr-2"></i>Profile</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('pages/profile/setting.php') ?>">
                                <i class="bi bi-gear mr-2"></i>Settings
                            </a>
                        </li>
                        <div class="divider my-1"></div>
                        <li>
                            <a href="<?= base_url('auth/logout.php') ?>" class="text-error">
                                <i class="bi bi-box-arrow-right mr-2"></i>Logout
                            </a>
                        </li>
                    </ul>
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
                    <img src="<?= base_url('assets/images/bareskrim-logo.png') ?>" alt="Logo" class="h-8 w-auto mr-3">
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
                        <a href="#" class="flex items-center">
                            <i class="bi bi-home text-xl text-primary mr-3"></i>
                            <span class="font-medium">Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center">
                            <i class="bi bi-info text-xl text-success mr-3"></i>
                            <span class="font-medium">About</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="bi bi-graph-up text-xl text-secondary mr-3"></i>
                            <span class="font-medium">Stats</span>
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
                                        <p class="text-sm opacity-70">Active now</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <ul class="menu bg-base-200 rounded-box">
                            <li>
                                <a href="<?= base_url('pages/profile/profile.php') ?>">
                                    <i class="bi bi-person text-primary"></i>
                                    Profile
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('pages/profile/setting.php') ?>">
                                    <i class="bi bi-gear text-secondary"></i>
                                    Settings
                                </a>
                            </li>
                            <div class="divider my-1"></div>
                            <li>
                                <a href="<?= base_url('auth/logout.php') ?>" class="text-error">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
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