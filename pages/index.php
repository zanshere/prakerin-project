<?php 
include_once __DIR__ . '/../includes/header.php';

// Database connection using mysqli
require_once __DIR__ . '/../config/connect.php';

// Function to get statistics from database
function getStatistics($conn) {
    $stats = [
        'surat_keluar' => 0,
        'spkap_aktif' => 0,
        'sphan_aktif' => 0,
        'total' => 0,
        'spsita_items' => 0,
        'spgas_aktif' => 0
    ];
    
    try {
        // Count Surat Keluar this month
        $query = "SELECT COUNT(*) as count 
                  FROM surat_keluar 
                  WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                  AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['surat_keluar'] = $row['count'];
        }
        
        // Count active SP.Kap
        $query = "SELECT COUNT(*) as count FROM surat_penangkapan WHERE status = 'aktif'";
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['spkap_aktif'] = $row['count'];
        }
        
        // Count active SP.Han
        $query = "SELECT COUNT(*) as count FROM surat_penahanan WHERE status = 'aktif'";
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['sphan_aktif'] = $row['count'];
        }
        
        // Count total letters
        $query = "SELECT 
                    (SELECT COUNT(*) FROM surat_keluar) +
                    (SELECT COUNT(*) FROM surat_penangkapan) +
                    (SELECT COUNT(*) FROM surat_penahanan) +
                    (SELECT COUNT(*) FROM surat_penyitaan) +
                    (SELECT COUNT(*) FROM surat_tugas) as total";
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['total'] = $row['total'];
        }
        
        // Count SP.Sita items
        $query = "SELECT COUNT(*) as count FROM barang_sitaan";
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['spsita_items'] = $row['count'];
        }
        
        // Count active SP.Gas
        $query = "SELECT COUNT(*) as count FROM surat_tugas WHERE status = 'aktif'";
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['spgas_aktif'] = $row['count'];
        }
        
    } catch (Exception $e) {
        // Stats already initialized with default values
        error_log("Error getting statistics: " . $e->getMessage());
    }
    
    return $stats;
}

// Function to get recent activities
function getRecentActivities($conn, $limit = 10) {
    $activities = [];
    
    try {
        $query = "SELECT 
                    la.aksi,
                    la.jenis_surat,
                    la.keterangan,
                    la.created_at,
                    u.full_name,
                    u.nrp,
                    u.rank,
                    u.profile_image
                  FROM log_aktivitas la
                  JOIN users u ON la.id_user = u.id_user
                  ORDER BY la.created_at DESC
                  LIMIT " . intval($limit);
        
        $result = $conn->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $activities[] = $row;
            }
        }
        
    } catch (Exception $e) {
        error_log("Error getting recent activities: " . $e->getMessage());
    }
    
    return $activities;
}

// Function to format jenis_surat for display
function formatJenisSurat($jenis_surat) {
    $formats = [
        'surat_keluar' => ['icon' => 'bi-send', 'name' => 'Surat Keluar', 'color' => 'primary'],
        'sp_kap' => ['icon' => 'bi-person-x', 'name' => 'SP.Kap', 'color' => 'warning'],
        'sp_han' => ['icon' => 'bi-lock', 'name' => 'SP.Han', 'color' => 'error'],
        'sp_sita' => ['icon' => 'bi-archive', 'name' => 'SP.Sita', 'color' => 'info'],
        'sp_gas' => ['icon' => 'bi-clipboard-check', 'name' => 'SP.Gas', 'color' => 'success']
    ];
    
    return $formats[$jenis_surat] ?? ['icon' => 'bi-file-earmark', 'name' => ucfirst(str_replace('_', ' ', $jenis_surat)), 'color' => 'neutral'];
}

// Function to format action for display
function formatAction($aksi) {
    $actions = [
        'create' => 'Dibuat',
        'update' => 'Diperbarui',
        'delete' => 'Dihapus',
        'view' => 'Dilihat',
        'print' => 'Dicetak'
    ];
    
    return $actions[$aksi] ?? ucfirst($aksi);
}

// Function to get status badge for action
function getStatusBadge($aksi) {
    $badges = [
        'create' => 'badge-success',
        'update' => 'badge-warning',
        'delete' => 'badge-error',
        'view' => 'badge-info',
        'print' => 'badge-neutral'
    ];
    
    return $badges[$aksi] ?? 'badge-neutral';
}

// Function to format time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Baru saja';
    if ($time < 3600) return floor($time/60) . ' menit lalu';
    if ($time < 86400) return floor($time/3600) . ' jam lalu';
    if ($time < 2592000) return floor($time/86400) . ' hari lalu';
    if ($time < 31104000) return floor($time/2592000) . ' bulan lalu';
    return floor($time/31104000) . ' tahun lalu';
}

// Get data
$stats = getStatistics($conn);
$recentActivities = getRecentActivities($conn, 10);
?>

<!-- Hero Section -->
<div class="hero min-h-[40vh] bg-gradient-to-r from-primary/10 to-secondary/10">
    <div class="hero-content text-center">
        <div class="max-w-md">
            <h1 class="text-5xl font-bold text-primary">
                <i class="bi bi-file-earmark-text"></i>
                Sistem Surat
            </h1>
            <p class="py-6 text-lg opacity-70">
                Kelola berbagai jenis surat resmi Unit Reskrim dengan mudah dan efisien
            </p>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mx-auto px-4 py-12">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="stat bg-base-100 shadow-lg rounded-lg border border-base-300">
            <div class="stat-figure text-primary">
                <i class="bi bi-send text-3xl"></i>
            </div>
            <div class="stat-title">Surat Keluar</div>
            <div class="stat-value text-primary"><?= $stats['surat_keluar'] ?></div>
            <div class="stat-desc">Bulan ini</div>
        </div>
        
        <div class="stat bg-base-100 shadow-lg rounded-lg border border-base-300">
            <div class="stat-figure text-warning">
                <i class="bi bi-person-x text-3xl"></i>
            </div>
            <div class="stat-title">SP.Kap</div>
            <div class="stat-value text-warning"><?= $stats['spkap_aktif'] ?></div>
            <div class="stat-desc">Aktif</div>
        </div>
        
        <div class="stat bg-base-100 shadow-lg rounded-lg border border-base-300">
            <div class="stat-figure text-error">
                <i class="bi bi-lock text-3xl"></i>
            </div>
            <div class="stat-title">SP.Han</div>
            <div class="stat-value text-error"><?= $stats['sphan_aktif'] ?></div>
            <div class="stat-desc">Berlaku</div>
        </div>
        
        <div class="stat bg-base-100 shadow-lg rounded-lg border border-base-300">
            <div class="stat-figure text-success">
                <i class="bi bi-check-circle text-3xl"></i>
            </div>
            <div class="stat-title">Total</div>
            <div class="stat-value text-success"><?= $stats['total'] ?></div>
            <div class="stat-desc">Semua surat</div>
        </div>
    </div>

    <!-- Menu Cards Section -->
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold mb-4">Menu Surat</h2>
        <p class="text-base-content/70 max-w-2xl mx-auto">
            Pilih jenis surat yang ingin Anda kelola. Setiap menu memiliki fitur khusus sesuai dengan kebutuhan administrasi Unit Reskrim.
        </p>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Surat Keluar -->
        <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300 group">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-primary/10 text-primary rounded-full w-16 h-16 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                            <i class="bi bi-send text-2xl"></i>
                        </div>
                    </div>
                    <div class="badge badge-primary">Aktif</div>
                </div>
                
                <h3 class="card-title text-xl mb-2">Surat Keluar</h3>
                <p class="text-base-content/70 mb-4">
                    Kelola surat keluar resmi untuk berbagai keperluan administratif dan operasional unit
                </p>
                
                <div class="flex items-center justify-between text-sm text-base-content/60 mb-6">
                    <span><i class="bi bi-calendar3 mr-1"></i> <?= $stats['surat_keluar'] ?> bulan ini</span>
                    <span><i class="bi bi-clock mr-1"></i> Terakhir: 2 hari lalu</span>
                </div>
                
                <div class="card-actions justify-end">
                    <a href="<?= base_url('pages/surat/surat-keluar.php') ?>" class="btn btn-primary btn-sm group-hover:btn-outline transition-all duration-300">
                        <i class="bi bi-arrow-right mr-1"></i>
                        Kelola
                    </a>
                </div>
            </div>
        </div>

        <!-- Surat Penangkapan (SP.Kap) -->
        <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300 group">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-warning/10 text-warning rounded-full w-16 h-16 flex items-center justify-center group-hover:bg-warning group-hover:text-white transition-colors duration-300">
                            <i class="bi bi-person-x text-2xl"></i>
                        </div>
                    </div>
                    <div class="badge badge-warning">Penting</div>
                </div>
                
                <h3 class="card-title text-xl mb-2">SP.Kap</h3>
                <p class="text-base-content/70 mb-4">
                    Surat Perintah Penangkapan untuk tersangka dalam proses penyidikan kasus pidana
                </p>
                
                <div class="flex items-center justify-between text-sm text-base-content/60 mb-6">
                    <span><i class="bi bi-shield-exclamation mr-1"></i> <?= $stats['spkap_aktif'] ?> aktif</span>
                    <span><i class="bi bi-clock mr-1"></i> Urgent</span>
                </div>
                
                <div class="card-actions justify-end">
                    <a href="<?= base_url('pages/surat/surat-penangkapan.php') ?>" class="btn btn-warning btn-sm group-hover:btn-outline transition-all duration-300">
                        <i class="bi bi-arrow-right mr-1"></i>
                        Kelola
                    </a>
                </div>
            </div>
        </div>

        <!-- Surat Penahanan (SP.Han) -->
        <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300 group">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-error/10 text-error rounded-full w-16 h-16 flex items-center justify-center group-hover:bg-error group-hover:text-white transition-colors duration-300">
                            <i class="bi bi-lock text-2xl"></i>
                        </div>
                    </div>
                    <div class="badge badge-error">Kritis</div>
                </div>
                
                <h3 class="card-title text-xl mb-2">SP.Han</h3>
                <p class="text-base-content/70 mb-4">
                    Surat Perintah Penahanan untuk tersangka yang memenuhi syarat penahanan
                </p>
                
                <div class="flex items-center justify-between text-sm text-base-content/60 mb-6">
                    <span><i class="bi bi-lock-fill mr-1"></i> <?= $stats['sphan_aktif'] ?> berlaku</span>
                    <span><i class="bi bi-clock mr-1"></i> Monitoring</span>
                </div>
                
                <div class="card-actions justify-end">
                    <a href="<?= base_url('pages/surat/surat-penahanan.php') ?>" class="btn btn-error btn-sm group-hover:btn-outline transition-all duration-300">
                        <i class="bi bi-arrow-right mr-1"></i>
                        Kelola
                    </a>
                </div>
            </div>
        </div>

        <!-- Surat Sita (SP.Sita) -->
        <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300 group">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-info/10 text-info rounded-full w-16 h-16 flex items-center justify-center group-hover:bg-info group-hover:text-white transition-colors duration-300">
                            <i class="bi bi-archive text-2xl"></i>
                        </div>
                    </div>
                    <div class="badge badge-info">Inventaris</div>
                </div>
                
                <h3 class="card-title text-xl mb-2">SP.Sita</h3>
                <p class="text-base-content/70 mb-4">
                    Surat Perintah Penyitaan barang bukti dan aset terkait kasus yang ditangani
                </p>
                
                <div class="flex items-center justify-between text-sm text-base-content/60 mb-6">
                    <span><i class="bi bi-box-seam mr-1"></i> <?= $stats['spsita_items'] ?> item</span>
                    <span><i class="bi bi-clock mr-1"></i> Terdaftar</span>
                </div>
                
                <div class="card-actions justify-end">
                    <a href="<?= base_url('pages/surat/surat-sita.php') ?>" class="btn btn-info btn-sm group-hover:btn-outline transition-all duration-300">
                        <i class="bi bi-arrow-right mr-1"></i>
                        Kelola
                    </a>
                </div>
            </div>
        </div>

        <!-- Surat Tugas (SP.Gas) -->
        <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300 group">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-success/10 text-success rounded-full w-16 h-16 flex items-center justify-center group-hover:bg-success group-hover:text-white transition-colors duration-300">
                            <i class="bi bi-clipboard-check text-2xl"></i>
                        </div>
                    </div>
                    <div class="badge badge-success">Operasional</div>
                </div>
                
                <h3 class="card-title text-xl mb-2">SP.Gas</h3>
                <p class="text-base-content/70 mb-4">
                    Surat Perintah Tugas untuk penugasan khusus dan operasional lapangan
                </p>
                
                <div class="flex items-center justify-between text-sm text-base-content/60 mb-6">
                    <span><i class="bi bi-people mr-1"></i> <?= $stats['spgas_aktif'] ?> tugas</span>
                    <span><i class="bi bi-clock mr-1"></i> Aktif</span>
                </div>
                
                <div class="card-actions justify-end">
                    <a href="<?= base_url('pages/surat/surat-tugas.php') ?>" class="btn btn-success btn-sm group-hover:btn-outline transition-all duration-300">
                        <i class="bi bi-arrow-right mr-1"></i>
                        Kelola
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card bg-gradient-to-br from-primary/5 to-secondary/5 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300">
            <div class="card-body p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-secondary/10 text-secondary rounded-full w-16 h-16 flex items-center justify-center">
                            <i class="bi bi-plus-circle text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <h3 class="card-title text-xl mb-2 text-center">Quick Actions</h3>
                <p class="text-base-content/70 mb-4 text-center">
                    Akses cepat untuk membuat surat baru atau melihat laporan
                </p>
                
                <div class="space-y-2">
                    <button class="btn btn-secondary btn-sm w-full" onclick="document.getElementById('quick_create_modal').showModal()">
                        <i class="bi bi-plus mr-2"></i>
                        Buat Surat Baru
                    </button>
                    <a href="<?= base_url('pages/reports/') ?>" class="btn btn-outline btn-secondary btn-sm w-full">
                        <i class="bi bi-graph-up mr-2"></i>
                        Lihat Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="mt-16">
        <h3 class="text-2xl font-bold mb-6 flex items-center">
            <i class="bi bi-clock-history mr-3 text-primary"></i>
            Aktivitas Terbaru
        </h3>
        
        <div class="bg-base-100 rounded-lg shadow-lg border border-base-300 overflow-hidden">
            <?php if (empty($recentActivities)): ?>
                <div class="p-8 text-center">
                    <i class="bi bi-inbox text-4xl text-base-content/30 mb-4"></i>
                    <p class="text-base-content/60">Belum ada aktivitas terbaru</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr class="bg-base-200">
                                <th>Waktu</th>
                                <th>Jenis Surat</th>
                                <th>Aksi</th>
                                <th>User</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentActivities as $activity): 
                                $suratFormat = formatJenisSurat($activity['jenis_surat']);
                                $timeFormatted = date('H:i', strtotime($activity['created_at'])) . ' WIB';
                                $initials = '';
                                $nameParts = explode(' ', $activity['full_name']);
                                foreach ($nameParts as $part) {
                                    if (!empty($part)) {
                                        $initials .= strtoupper($part[0]);
                                    }
                                }
                                $initials = substr($initials, 0, 2);
                            ?>
                                <tr>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-medium"><?= $timeFormatted ?></span>
                                            <span class="text-xs text-base-content/60"><?= timeAgo($activity['created_at']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center">
                                            <i class="bi <?= $suratFormat['icon'] ?> text-<?= $suratFormat['color'] ?> mr-2"></i>
                                            <?= $suratFormat['name'] ?>
                                        </div>
                                    </td>
                                    <td><?= formatAction($activity['aksi']) ?></td>
                                    <td>
                                        <div class="flex items-center">
                                            <div class="avatar placeholder mr-2">
                                                <?php if ($activity['profile_image'] && $activity['profile_image'] != 'profil.jpg'): ?>
                                                    <div class="w-8 h-8 rounded-full">
                                                        <img src="<?= base_url('uploads/profiles/' . $activity['profile_image']) ?>" 
                                                             alt="<?= htmlspecialchars($activity['full_name']) ?>" 
                                                             class="w-full h-full object-cover rounded-full">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="bg-<?= $suratFormat['color'] ?> text-white rounded-full w-8 h-8">
                                                        <span class="text-xs"><?= $initials ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-medium text-sm"><?= htmlspecialchars($activity['full_name']) ?></span>
                                                <span class="text-xs text-base-content/60"><?= $activity['rank'] ?> - <?= $activity['nrp'] ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= getStatusBadge($activity['aksi']) ?>">
                                            <?= formatAction($activity['aksi']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($activity['keterangan'])): ?>
                                            <div class="tooltip" data-tip="<?= htmlspecialchars($activity['keterangan']) ?>">
                                                <span class="text-sm text-base-content/70">
                                                    <?= strlen($activity['keterangan']) > 30 ? 
                                                        htmlspecialchars(substr($activity['keterangan'], 0, 30)) . '...' : 
                                                        htmlspecialchars($activity['keterangan']) 
                                                    ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-xs text-base-content/40">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- View All Activities Link -->
                <div class="p-4 bg-base-200 text-center">
                    <a href="<?= base_url('pages/reports/activities.php') ?>" class="btn btn-ghost btn-sm">
                        <i class="bi bi-eye mr-2"></i>
                        Lihat Semua Aktivitas
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Create Modal -->
<dialog id="quick_create_modal" class="modal">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="font-bold text-lg mb-4">Buat Surat Baru</h3>
        <div class="grid grid-cols-1 gap-3">
            <a href="<?= base_url('pages/surat/surat-keluar.php?action=create') ?>" class="btn btn-outline btn-primary justify-start">
                <i class="bi bi-send mr-2"></i>
                Surat Keluar
            </a>
            <a href="<?= base_url('pages/surat/surat-penangkapan.php?action=create') ?>" class="btn btn-outline btn-warning justify-start">
                <i class="bi bi-person-x mr-2"></i>
                Surat Penangkapan (SP.Kap)
            </a>
            <a href="<?= base_url('pages/surat/surat-penahanan.php?action=create') ?>" class="btn btn-outline btn-error justify-start">
                <i class="bi bi-lock mr-2"></i>
                Surat Penahanan (SP.Han)
            </a>
            <a href="<?= base_url('pages/surat/surat-penyitaan.php?action=create') ?>" class="btn btn-outline btn-info justify-start">
                <i class="bi bi-archive mr-2"></i>
                Surat Penyitaan (SP.Sita)
            </a>
            <a href="<?= base_url('pages/surat/surat-tugas.php?action=create') ?>" class="btn btn-outline btn-success justify-start">
                <i class="bi bi-clipboard-check mr-2"></i>
                Surat Tugas (SP.Gas)
            </a>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<style>
    .card:hover {
        transform: translateY(-4px);
    }
    
    .card .avatar {
        transition: all 0.3s ease;
    }
    
    .hero {
        background-image: 
            radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.1) 0%, transparent 50%);
    }
    
    .table tbody tr:hover {
        background-color: rgba(var(--fallback-b1,oklch(var(--b1))), 0.5);
    }
    
    .tooltip:before {
        font-size: 0.75rem;
        line-height: 1rem;
        max-width: 200px;
        white-space: normal;
    }
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>