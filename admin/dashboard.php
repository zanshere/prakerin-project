<?php 
require_once __DIR__ . '/../config/connect.php'; 
require_once __DIR__ . '/../config/authCheck.php';

// Update session recovery dengan URL saat ini
updateSessionRecovery();

// Force light theme for this page
$_SESSION['force_light_theme'] = true;
include __DIR__ . '/../includes/header.php';

?>

