<?php
include __DIR__ . '/../config/baseURL.php';

session_start();
session_destroy();

header("Location: " . base_url('auth/login.php'));
exit();

?>