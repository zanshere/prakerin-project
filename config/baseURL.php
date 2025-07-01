<?php
$manualBaseUrl = 'https://localhost/git-project/prakerin-project/'; // Sesuaikan URL disini

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim($manualBaseUrl, '/') . '/');
}

function base_url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

function asset_url($path = '') {
    return base_url('assets/' . ltrim($path, '/'));
}
?>