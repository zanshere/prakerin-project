// Import AlpineJS
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

// Pastikan DOM sudah sepenuhnya dimuat
document.addEventListener('DOMContentLoaded', () => {
  // Inisialisasi Alpine
  window.Alpine = Alpine;
  Alpine.start();

  // Inisialisasi Chart.js
  window.Chart = Chart;
});