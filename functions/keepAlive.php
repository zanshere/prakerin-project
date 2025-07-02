<?php
// File: keepalive.php
require_once __DIR__ . '/../config/connect.php';
include_once __DIR__ . '/../config/baseURL.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Perbarui waktu session
$_SESSION['LAST_ACTIVITY'] = time();

// Jika menggunakan session timeout
ini_set('session.gc_maxlifetime', 1800); // 30 menit
session_set_cookie_params(1800);

// Kosongkan output
exit();
?>