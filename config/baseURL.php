<?php
// Deteksi otomatis protocol (http/https)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';

// Deteksi hostname server
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Modifikasi khusus untuk menghilangkan /auth/ dari path dasar
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$basePath = rtrim(str_replace('\\', '/', $scriptDir), '/');

// Hapus '/auth' dari basePath jika ada
if (strpos($basePath, '/auth') !== false) {
    $basePath = str_replace('/auth', '', $basePath);
}

// Konfigurasi manual override (optional)
$manualBaseUrl = ''; // Contoh: 'https://example.com/my-project/'

if (!defined('BASE_URL')) {
    define('BASE_URL', 
        !empty($manualBaseUrl) 
            ? rtrim($manualBaseUrl, '/') . '/'  // Jika manual di-set
            : $protocol . $host . $basePath . '/' // Auto-detection
    );
}

function base_url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

function asset_url($path = '') {
    return base_url('assets/' . ltrim($path, '/'));
}
?>