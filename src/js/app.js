// Import AlpineJS
import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';

// Pastikan DOM sudah sepenuhnya dimuat
document.addEventListener('DOMContentLoaded', () => {
  // Inisialisasi Alpine
  window.Alpine = Alpine;
  Alpine.start();

  // Inisialisasi Chart.js
  window.Chart = Chart;

  // Inisialisasi Sweetalert2
  window.Swal = Swal;
});