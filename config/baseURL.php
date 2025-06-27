<?php 

if(!defined('BASE_URL')) {
    define('BASE_URL', 'https://localhost/git-project/prakerin-project/'); // Sesuaikan dengan punya masing masing
}

function base_url($path = '') {
    return BASE_URL . ltrim($path, '/'); // penggunaan : base_url('assets/image/test.png')
}

?>