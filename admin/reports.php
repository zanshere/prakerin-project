<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../config/authCheck.php'; 

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('auth/login.php'));
    exit();
}

// Check for alert message
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);

// Ambil data surat dari semua tabel
$reports = [];

// Check if connection exists
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Hitung statistik
$stats = [
    'surat_keluar' => 0,
    'sp_kap' => 0,
    'sp_han' => 0,
    'sp_sita' => 0,
    'sp_gas' => 0,
    'total' => 0,
    'bulan_ini' => 0,
    'aktif' => 0,
    'berlaku' => 0
];

try {
    // Query untuk menggabungkan semua jenis surat
    $query = "
        SELECT 
            'Surat Keluar' as jenis_surat,
            nomor_surat as nomor,
            tanggal_surat as tanggal,
            perihal as subjek,
            tujuan as detail,
            status,
            u.full_name as created_by_name,
            sk.created_at,
            sk.id_surat as id,
            'surat_keluar' as table_name
        FROM surat_keluar sk
        JOIN users u ON sk.created_by = u.id_user
        
        UNION ALL
        
        SELECT 
            'SP.Kap' as jenis_surat,
            nomor_spkap as nomor,
            tanggal_spkap as tanggal,
            CONCAT('Penangkapan - ', nama_tersangka) as subjek,
            pasal_yang_disangkakan as detail,
            status,
            u.full_name as created_by_name,
            sp.created_at,
            sp.id_spkap as id,
            'surat_penangkapan' as table_name
        FROM surat_penangkapan sp
        JOIN users u ON sp.created_by = u.id_user
        
        UNION ALL
        
        SELECT 
            'SP.Han' as jenis_surat,
            nomor_sphan as nomor,
            tanggal_sphan as tanggal,
            CONCAT('Penahanan - ', nama_tersangka) as subjek,
            pasal_yang_disangkakan as detail,
            status,
            u.full_name as created_by_name,
            sph.created_at,
            sph.id_sphan as id,
            'surat_penahanan' as table_name
        FROM surat_penahanan sph
        JOIN users u ON sph.created_by = u.id_user
        
        UNION ALL
        
        SELECT 
            'SP.Sita' as jenis_surat,
            nomor_spsita as nomor,
            tanggal_spsita as tanggal,
            CONCAT('Penyitaan - ', nama_tersangka) as subjek,
            pasal_yang_disangkakan as detail,
            status,
            u.full_name as created_by_name,
            sps.created_at,
            sps.id_spsita as id,
            'surat_penyitaan' as table_name
        FROM surat_penyitaan sps
        JOIN users u ON sps.created_by = u.id_user
        
        UNION ALL
        
        SELECT 
            'SP.Gas' as jenis_surat,
            nomor_spgas as nomor,
            tanggal_spgas as tanggal,
            jenis_tugas as subjek,
            tujuan_tugas as detail,
            status,
            u.full_name as created_by_name,
            spg.created_at,
            spg.id_spgas as id,
            'surat_tugas' as table_name
        FROM surat_tugas spg
        JOIN users u ON spg.created_by = u.id_user
        
        ORDER BY created_at DESC
    ";

    if ($result = $conn->query($query)) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $reports[] = $row;
                
                // Hitung statistik
                $stats['total']++;
                
                // Hitung berdasarkan jenis
                switch ($row['jenis_surat']) {
                    case 'Surat Keluar':
                        $stats['surat_keluar']++;
                        break;
                    case 'SP.Kap':
                        $stats['sp_kap']++;
                        break;
                    case 'SP.Han':
                        $stats['sp_han']++;
                        break;
                    case 'SP.Sita':
                        $stats['sp_sita']++;
                        break;
                    case 'SP.Gas':
                        $stats['sp_gas']++;
                        break;
                }
                
                // Hitung bulan ini
                $createdAt = strtotime($row['created_at']);
                $currentMonth = strtotime(date('Y-m-01'));
                if ($createdAt >= $currentMonth) {
                    $stats['bulan_ini']++;
                }
                
                // Hitung status aktif
                if (in_array(strtolower($row['status']), ['aktif', 'dikirim'])) {
                    $stats['aktif']++;
                }
                
                // Hitung status berlaku (asumsi semua status selain dibatalkan/expired)
                if (!in_array(strtolower($row['status']), ['dibatalkan', 'expired', 'rejected'])) {
                    $stats['berlaku']++;
                }
            }
        }
        $result->free();
    } else {
        // Handle query error
        $alert = [
            'type' => 'error',
            'title' => 'Error',
            'message' => "Error fetching reports: " . $conn->error
        ];
    }
} catch (Exception $e) {
    $alert = [
        'type' => 'error',
        'title' => 'Error',
        'message' => "Database error: " . $e->getMessage()
    ];
}

// Function to get status badge class
function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'aktif':
        case 'dikirim':
            return 'badge-success';
        case 'draft':
        case 'pending':
            return 'badge-warning';
        case 'expired':
        case 'berakhir':
        case 'selesai':
            return 'badge-info';
        case 'dibatalkan':
        case 'rejected':
            return 'badge-error';
        case 'arsip':
            return 'badge-neutral';
        default:
            return 'badge-ghost';
    }
}

// Function to get letter type badge class
function getLetterTypeBadgeClass($type) {
    switch ($type) {
        case 'Surat Keluar':
            return 'badge-primary';
        case 'SP.Kap':
            return 'badge-secondary';
        case 'SP.Han':
            return 'badge-accent';
        case 'SP.Sita':
            return 'badge-info';
        case 'SP.Gas':
            return 'badge-success';
        default:
            return 'badge-ghost';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Reports</h1>
            <p class="text-sm opacity-70 mt-1">Daftar semua surat yang telah dibuat</p>
        </div>
        <div class="flex gap-2">
            <button onclick="exportToCSV()" class="btn btn-outline btn-sm">
                <i class="bi bi-download mr-2"></i> Export CSV
            </button>
            <button onclick="printReport()" class="btn btn-outline btn-sm">
                <i class="bi bi-printer mr-2"></i> Print All
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-base-100 rounded-xl shadow-md p-4 flex items-center">
            <div class="bg-primary/10 p-3 rounded-lg mr-4">
                <i class="bi bi-envelope text-primary text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold"><?= $stats['surat_keluar'] ?></h3>
                <p class="text-sm text-gray-500">Surat Keluar</p>
            </div>
        </div>
        <div class="bg-base-100 rounded-xl shadow-md p-4 flex items-center">
            <div class="bg-secondary/10 p-3 rounded-lg mr-4">
                <i class="bi bi-person-fill-lock text-secondary text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold"><?= $stats['sp_kap'] ?></h3>
                <p class="text-sm text-gray-500">SP.Kap</p>
            </div>
        </div>
        <div class="bg-base-100 rounded-xl shadow-md p-4 flex items-center">
            <div class="bg-accent/10 p-3 rounded-lg mr-4">
                <i class="bi bi-building text-accent text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold"><?= $stats['sp_han'] ?></h3>
                <p class="text-sm text-gray-500">SP.Han</p>
            </div>
        </div>
        <div class="bg-base-100 rounded-xl shadow-md p-4 flex items-center">
            <div class="bg-info/10 p-3 rounded-lg mr-4">
                <i class="bi bi-box text-info text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold"><?= $stats['sp_sita'] + $stats['sp_gas'] ?></h3>
                <p class="text-sm text-gray-500">SP.Sita & SP.Gas</p>
            </div>
        </div>
    </div>

    <!-- Additional Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-base-100 rounded-xl shadow-md p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold"><?= $stats['bulan_ini'] ?></h3>
                    <p class="text-sm text-gray-500">Bulan ini</p>
                </div>
                <i class="bi bi-calendar-month text-primary text-xl"></i>
            </div>
        </div>
        <div class="bg-base-100 rounded-xl shadow-md p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold"><?= $stats['aktif'] ?></h3>
                    <p class="text-sm text-gray-500">Aktif</p>
                </div>
                <i class="bi bi-check-circle text-success text-xl"></i>
            </div>
        </div>
        <div class="bg-base-100 rounded-xl shadow-md p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold"><?= $stats['berlaku'] ?></h3>
                    <p class="text-sm text-gray-500">Berlaku</p>
                </div>
                <i class="bi bi-shield-check text-info text-xl"></i>
            </div>
        </div>
        <div class="bg-base-100 rounded-xl shadow-md p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold"><?= $stats['total'] ?></h3>
                    <p class="text-sm text-gray-500">Semua surat</p>
                </div>
                <i class="bi bi-archive text-gray-500 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-base-100 rounded-xl shadow-md p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="label">
                    <span class="label-text">Jenis Surat</span>
                </label>
                <select id="filterType" class="select select-bordered w-full" onchange="filterReports()">
                    <option value="">Semua Jenis</option>
                    <option value="Surat Keluar">Surat Keluar</option>
                    <option value 'SP.Kap'>SP.Kap</option>
                    <option value="SP.Han">SP.Han</option>
                    <option value="SP.Sita">SP.Sita</option>
                    <option value="SP.Gas">SP.Gas</option>
                </select>
            </div>
            <div>
                <label class="label">
                    <span class="label-text">Status</span>
                </label>
                <select id="filterStatus" class="select select-bordered w-full" onchange="filterReports()">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="draft">Draft</option>
                    <option value="dikirim">Dikirim</option>
                    <option value="selesai">Selesai</option>
                    <option value="expired">Expired</option>
                    <option value="dibatalkan">Dibatalkan</option>
                    <option value="arsip">Arsip</option>
                </select>
            </div>
            <div>
                <label class="label">
                    <span class="label-text">Tanggal Dari</span>
                </label>
                <input type="date" id="filterDateFrom" class="input input-bordered w-full" onchange="filterReports()">
            </div>
            <div>
                <label class="label">
                    <span class="label-text">Tanggal Sampai</span>
                </label>
                <input type="date" id="filterDateTo" class="input input-bordered w-full" onchange="filterReports()">
            </div>
        </div>
    </div>

    <?php if (empty($reports)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle mr-2"></i>
        Tidak ada surat yang ditemukan dalam database.
    </div>
    <?php else: ?>
    <div class="bg-base-100 rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full" id="reportsTable">
                <thead>
                    <tr>
                        <th>Jenis Surat</th>
                        <th>Nomor Surat</th>
                        <th>Tanggal</th>
                        <th>Subjek</th>
                        <th>Detail</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Dibuat Pada</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="reportsTableBody">
                    <?php foreach ($reports as $report): ?>
                    <tr class="report-row" 
                        data-type="<?= htmlspecialchars($report['jenis_surat'] ?? '') ?>"
                        data-status="<?= htmlspecialchars($report['status'] ?? '') ?>"
                        data-date="<?= htmlspecialchars($report['tanggal'] ?? '') ?>">
                        <td>
                            <span class="badge <?= getLetterTypeBadgeClass($report['jenis_surat'] ?? '') ?>">
                                <?= htmlspecialchars($report['jenis_surat'] ?? '') ?>
                            </span>
                        </td>
                        <td>
                            <span class="font-mono text-sm">
                                <?= htmlspecialchars($report['nomor'] ?? '') ?>
                            </span>
                        </td>
                        <td><?= isset($report['tanggal']) ? date('d M Y', strtotime($report['tanggal'])) : '' ?></td>
                        <td>
                            <div class="max-w-xs">
                                <div class="font-semibold truncate">
                                    <?= htmlspecialchars($report['subjek'] ?? '') ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="max-w-sm">
                                <div class="text-sm opacity-70 line-clamp-2">
                                    <?= htmlspecialchars(substr($report['detail'] ?? '', 0, 100)) ?>
                                    <?= strlen($report['detail'] ?? '') > 100 ? '...' : '' ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($report['status'] ?? '') ?>">
                                <?= ucfirst(htmlspecialchars($report['status'] ?? '')) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($report['created_by_name'] ?? '') ?></td>
                        <td><?= isset($report['created_at']) ? date('d M Y H:i', strtotime($report['created_at'])) : '' ?></td>
                        <td>
                            <div class="flex space-x-2">
                                <button onclick="viewDetail('<?= $report['table_name'] ?>', <?= $report['id'] ?>)" 
                                        class="btn btn-xs btn-outline btn-info">
                                    <i class="bi bi-eye"></i> Lihat
                                </button>
                                <button onclick="printSingle('<?= $report['table_name'] ?>', <?= $report['id'] ?>)" 
                                        class="btn btn-xs btn-outline btn-primary">
                                    <i class="bi bi-printer"></i> Print
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="mt-6 bg-base-100 rounded-xl shadow-md p-6">
        <h3 class="text-lg font-semibold mb-4">Ringkasan Laporan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h4 class="font-medium mb-2">Total Surat: <span id="totalSurat"><?= count($reports) ?></span></h4>
                <div class="text-sm space-y-1" id="summaryByType">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            <div>
                <h4 class="font-medium mb-2">Status Surat</h4>
                <div class="text-sm space-y-1" id="summaryByStatus">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Data reports untuk JavaScript
const reportsData = <?= json_encode($reports) ?>;

// Fungsi untuk menampilkan notifikasi
function showAlert(icon, title, text) {
    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    const isDark = theme === 'dark';

    Swal.fire({
        icon: icon,
        title: title,
        html: text,
        confirmButtonColor: '#3b82f6',
        background: isDark ? '#1f2937' : '#ffffff',
        color: isDark ? '#ffffff' : '#1f2937'
    });
}

// Function to view detail - PERBAIKAN DI SINI
function viewDetail(tableName, id) {
    // Redirect to detail page with both table and id parameters
    window.location.href = 'detail.php?table=' + tableName + '&id=' + id;
}

// Function to print single report
function printSingle(tableName, id) {
    // Open print page for specific document
    let url = '';
    switch(tableName) {
        case 'surat_keluar':
            url = 'surat_keluar/print.php?id=' + id;
            break;
        case 'surat_penangkapan':
            url = 'surat_penangkapan/print.php?id=' + id;
            break;
        case 'surat_penahanan':
            url = 'surat_penahanan/print.php?id=' + id;
            break;
        case 'surat_penyitaan':
            url = 'surat_penyitaan/print.php?id=' + id;
            break;
        case 'surat_tugas':
            url = 'surat_tugas/print.php?id=' + id;
            break;
        default:
            showAlert('error', 'Error', 'Tidak dapat mencetak surat');
            return;
    }
    
    window.open(url, '_blank');
}

// Function to filter reports
function filterReports() {
    const typeFilter = document.getElementById('filterType').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const dateFromFilter = document.getElementById('filterDateFrom').value;
    const dateToFilter = document.getElementById('filterDateTo').value;
    
    const rows = document.querySelectorAll('.report-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const type = row.getAttribute('data-type');
        const status = row.getAttribute('data-status');
        const date = row.getAttribute('data-date');
        
        let showRow = true;
        
        // Filter by type
        if (typeFilter && type !== typeFilter) {
            showRow = false;
        }
        
        // Filter by status
        if (statusFilter && status !== statusFilter) {
            showRow = false;
        }
        
        // Filter by date range
        if (dateFromFilter && date < dateFromFilter) {
            showRow = false;
        }
        
        if (dateToFilter && date > dateToFilter) {
            showRow = false;
        }
        
        if (showRow) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    updateSummary();
}

// Function to update statistics and summary
function updateSummary() {
    const visibleRows = document.querySelectorAll('.report-row:not([style*="display: none"])');
    
    // Count by type
    const typeCount = {};
    const statusCount = {};
    
    visibleRows.forEach(row => {
        const type = row.getAttribute('data-type');
        const status = row.getAttribute('data-status');
        
        typeCount[type] = (typeCount[type] || 0) + 1;
        statusCount[status] = (statusCount[status] || 0) + 1;
    });
    
    // Update total
    document.getElementById('totalSurat').textContent = visibleRows.length;
    
    // Update summary by type
    const summaryByType = document.getElementById('summaryByType');
    summaryByType.innerHTML = '';
    for (const [type, count] of Object.entries(typeCount)) {
        summaryByType.innerHTML += `<div>${type}: ${count}</div>`;
    }
    
    // Update summary by status
    const summaryByStatus = document.getElementById('summaryByStatus');
    summaryByStatus.innerHTML = '';
    for (const [status, count] of Object.entries(statusCount)) {
        summaryByStatus.innerHTML += `<div>${ucfirst(status)}: ${count}</div>`;
    }
}

// Helper function to capitalize first letter
function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Function to export to CSV
function exportToCSV() {
    const visibleRows = document.querySelectorAll('.report-row:not([style*="display: none"])');
    
    if (visibleRows.length === 0) {
        showAlert('warning', 'Peringatan', 'Tidak ada data untuk diekspor');
        return;
    }
    
    let csv = 'Jenis Surat,Nomor Surat,Tanggal,Subjek,Status,Dibuat Oleh,Dibuat Pada\n';
    
    visibleRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = [];
        
        // Extract data from cells (skip detail column for CSV)
        rowData.push('"' + cells[0].textContent.trim() + '"'); // Jenis Surat
        rowData.push('"' + cells[1].textContent.trim() + '"'); // Nomor
        rowData.push('"' + cells[2].textContent.trim() + '"'); // Tanggal
        rowData.push('"' + cells[3].textContent.trim() + '"'); // Subjek
        rowData.push('"' + cells[5].textContent.trim() + '"'); // Status
        rowData.push('"' + cells[6].textContent.trim() + '"'); // Dibuat Oleh
        rowData.push('"' + cells[7].textContent.trim() + '"'); // Dibuat Pada
        
        csv += rowData.join(',') + '\n';
    });
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'laporan_surat_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showAlert('success', 'Berhasil', 'Laporan berhasil diekspor ke CSV');
}

// Function to print report
function printReport() {
    window.print();
}

// Initialize summary on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
});

// Handle alert notifikasi
<?php if ($alert): ?>
document.addEventListener('DOMContentLoaded', function() {
    showAlert('<?= $alert['type'] ?>', '<?= $alert['title'] ?>', '<?= addslashes($alert['message']) ?>');
});
<?php endif; ?>
</script>

<style>
@media print {
    .btn, .select, .input, .alert, .bg-base-100.rounded-xl.shadow-md.p-4 {
        display: none !important;
    }
    
    .container {
        max-width: none !important;
        padding: 0 !important;
    }
    
    table {
        font-size: 12px;
        width: 100%;
    }
    
    .badge {
        border: 1px solid #ccc;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
    }
    
    th, td {
        padding: 4px 6px;
    }
}

.line-clamp-2 {
    display: -webkit-box;
    --webkit-line-clamp: 2;
    --webkit-box-orient: vertical;
    overflow: hidden;
}

.stat {
    transition: all 0.3s ease;
}

.stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>