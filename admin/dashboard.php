<?php 
require_once __DIR__ . '/../config/connect.php'; 
require_once __DIR__ . '/../config/authCheck.php';

// Force light theme for this page
$_SESSION['force_light_theme'] = true;
include __DIR__ . '/../includes/header.php';

// Update session recovery dengan URL saat ini
updateSessionRecovery();
?>

<section class="p-6">
 <h1 class="text-2xl font-bold text-base-content mb-4">Dashboard Reskrim</h1>

  <!-- Statistik Ringkas -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-base-100 shadow rounded-xl p-4 text-base-content">
    <p class="text-sm">Total Kasus</p>
    <h2 class="text-2xl font-semibold text-primary">42</h2>
    </div>
    <div class="bg-base-100 shadow rounded-xl p-4 text-base-content">
    <p class="text-sm">Kasus Pencurian</p>
    <h2 class="text-2xl font-semibold text-primary">42</h2>
    </div>
    <div class="bg-base-100 shadow rounded-xl p-4 text-base-content">
    <p class="text-sm">Kasus Penipuan</p>
    <h2 class="text-2xl font-semibold text-primary">42</h2>
    </div>
    <div class="bg-base-100 shadow rounded-xl p-4 text-base-content">
    <p class="text-sm">Kasus Kekerasan</p>
    <h2 class="text-2xl font-semibold text-primary">42</h2>
    </div>
  </div>

  <!-- Grafik Kasus per Bulan -->
  <div class="bg-white shadow rounded-xl p-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">Grafik Kasus per Bulan</h2>
    <canvas id="kasusChart" class="w-full h-64"></canvas>
  </div>
</section>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('kasusChart').getContext('2d');
  const kasusChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
      datasets: [{
        label: 'Jumlah Kasus',
        data: [10, 14, 9, 7, 12, 6, 6, 6, 6, 6, 6, 6], // Dummy data
        backgroundColor: 'rgba(59, 130, 246, 0.7)',
        borderColor: 'rgba(59, 130, 246, 1)',
        borderWidth: 1,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 2
          }
        }
      }
    }
  });
</script>

<?php
include __DIR__ . '/../includes/footer.php';
?>