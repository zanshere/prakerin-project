<?php

$footerTheme = $_SESSION['force_light_footer'] ?? false ? 'bg-gray-100 text-gray-800' : 'bg-neutral text-neutral-content';
unset($_SESSION['force_light_footer']);

?>

<footer class="footer sm:footer-horizontal <?= $footerTheme ?> items-center p-4 mt-auto">
    <aside class="grid-flow-col items-center gap-4">
        <img src="<?= base_url('assets/images/bareskrim-logo.png') ?>" alt="Bareskrim Logo" class="w-15 h-16 rounded" />
        <p>Copyright © <script>document.write(new Date().getFullYear())</script> - All rights reserved</p>
    </aside>
    <nav class="grid-flow-col gap-4 md:place-self-center md:justify-self-end text-xl">
        <a href="https://www.instagram.com/polsek_gunungputri/" target="_blank" aria-label="Instagram">
            <i class="bi bi-instagram"></i>
        </a>
        <a href="https://www.youtube.com/@polsekgunungputri8505" target="_blank" aria-label="YouTube">
            <i class="bi bi-youtube"></i>
        </a>
        <a href="https://maps.app.goo.gl/a2FYFbr12VNujPHu6" target="_blank" aria-label="Facebook">
            <i class="bi bi-geo-alt-fill"></i>
        </a>
    </nav>
</footer>