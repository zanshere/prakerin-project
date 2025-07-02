<footer class="footer sm:footer-horizontal items-center p-4 w-full" 
    x-bind:class="darkMode ? 'bg-neutral text-neutral-content' : 'bg-gray-100 text-gray-800'"
    style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 40;">
    <div class="container mx-auto flex flex-col sm:flex-row justify-between items-center">
        <aside class="flex items-center gap-4">
            <img src="<?= base_url('assets/images/bareskrim-logo.png') ?>" alt="Bareskrim Logo" class="w-15 h-16 rounded" />
            <p class="text-sm sm:text-base">Copyright © <script>document.write(new Date().getFullYear())</script> - All rights reserved</p>
        </aside>
        <nav class="flex gap-4 mt-4 sm:mt-0 text-xl">
            <a href="https://www.instagram.com/polsek_gunungputri/" target="_blank" aria-label="Instagram" class="hover:text-blue-500 transition-colors">
                <i class="bi bi-instagram"></i>
            </a>
            <a href="https://www.youtube.com/@polsekgunungputri8505" target="_blank" aria-label="YouTube" class="hover:text-red-500 transition-colors">
                <i class="bi bi-youtube"></i>
            </a>
            <a href="https://maps.app.goo.gl/a2FYFbr12VNujPHu6" target="_blank" aria-label="Location" class="hover:text-green-500 transition-colors">
                <i class="bi bi-geo-alt-fill"></i>
            </a>
        </nav>
    </div>
</footer>