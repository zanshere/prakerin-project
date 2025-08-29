<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../config/authCheck.php'; 
include_once __DIR__ . '/../config/baseURL.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('auth/login.php'));
    exit();
}

// Check parameters
$table_name = $_GET['table'] ?? '';
$id = $_GET['id'] ?? 0;

// Debug: Log parameters
error_log("Detail.php accessed with table: $table_name, id: $id");

if (empty($table_name) || empty($id)) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Error',
        'message' => 'Parameter tidak valid. Table: ' . $table_name . ', ID: ' . $id
    ];
    header("Location: " . base_url('admin/reports.php'));
    exit();
}

// Validasi table name
$valid_tables = ['surat_keluar', 'surat_penangkapan', 'surat_penahanan', 'surat_penyitaan', 'surat_tugas'];
if (!in_array($table_name, $valid_tables)) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Error',
        'message' => 'Jenis surat tidak valid: ' . $table_name
    ];
    header("Location: " . base_url('admin/reports.php'));
    exit();
}

// Get data based on table name
$data = [];
$jenis_surat = '';

try {
    switch ($table_name) {
        case 'surat_keluar':
            $jenis_surat = 'Surat Keluar';
            $query = "SELECT sk.*, u.full_name as created_by_name, u.nrp, u.rank 
                     FROM surat_keluar sk 
                     JOIN users u ON sk.created_by = u.id_user 
                     WHERE sk.id_surat = ?";
            break;
            
        case 'surat_penangkapan':
            $jenis_surat = 'SP.Kap';
            $query = "SELECT sp.*, u.full_name as created_by_name, u.nrp, u.rank 
                     FROM surat_penangkapan sp 
                     JOIN users u ON sp.created_by = u.id_user 
                     WHERE sp.id_spkap = ?";
            break;
            
        case 'surat_penahanan':
            $jenis_surat = 'SP.Han';
            $query = "SELECT sph.*, u.full_name as created_by_name, u.nrp, u.rank 
                     FROM surat_penahanan sph 
                     JOIN users u ON sph.created_by = u.id_user 
                     WHERE sph.id_sphan = ?";
            break;
            
        case 'surat_penyitaan':
            $jenis_surat = 'SP.Sita';
            $query = "SELECT sps.*, u.full_name as created_by_name, u.nrp, u.rank 
                     FROM surat_penyitaan sps 
                     JOIN users u ON sps.created_by = u.id_user 
                     WHERE sps.id_spsita = ?";
            break;
            
        case 'surat_tugas':
            $jenis_surat = 'SP.Gas';
            $query = "SELECT st.*, u.full_name as created_by_name, u.nrp, u.rank 
                     FROM surat_tugas st 
                     JOIN users u ON st.created_by = u.id_user 
                     WHERE st.id_spgas = ?";
            break;
    }
    
    // Debug: Log the query
    error_log("Executing query: $query with id: $id");
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Data tidak ditemukan untuk ' . $jenis_surat . ' dengan ID: ' . $id
        ];
        header("Location: " . base_url('admin/reports.php'));
        exit();
    }
    
    $data = $result->fetch_assoc();
    $stmt->close();
    
    // Debug: Log data retrieval
    error_log("Data retrieved: " . print_r($data, true));
    
} catch (Exception $e) {
    // Log the error
    error_log("Database error in detail.php: " . $e->getMessage());
    
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Error',
        'message' => "Terjadi kesalahan sistem: " . $e->getMessage()
    ];
    header("Location: " . base_url('admin/reports.php'));
    exit();
}

// Check for alert message
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);

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

// Get additional data for specific surat types
$barang_sitaan = [];
$personel_tugas = [];

try {
    if ($table_name === 'surat_penyitaan') {
        $query_barang = "SELECT * FROM barang_sitaan WHERE id_spsita = ?";
        $stmt_barang = $conn->prepare($query_barang);
        $stmt_barang->bind_param("i", $id);
        $stmt_barang->execute();
        $result_barang = $stmt_barang->get_result();
        
        if ($result_barang->num_rows > 0) {
            while ($row = $result_barang->fetch_assoc()) {
                $barang_sitaan[] = $row;
            }
        }
        $stmt_barang->close();
    }
    
    if ($table_name === 'surat_tugas') {
        $query_personel = "SELECT pt.*, u.full_name, u.nrp, u.rank 
                          FROM personel_tugas pt 
                          JOIN users u ON pt.id_user = u.id_user 
                          WHERE pt.id_spgas = ? 
                          ORDER BY pt.peran";
        $stmt_personel = $conn->prepare($query_personel);
        $stmt_personel->bind_param("i", $id);
        $stmt_personel->execute();
        $result_personel = $stmt_personel->get_result();
        
        if ($result_personel->num_rows > 0) {
            while ($row = $result_personel->fetch_assoc()) {
                $personel_tugas[] = $row;
            }
        }
        $stmt_personel->close();
    }
} catch (Exception $e) {
    // Log error but don't stop execution
    error_log("Error fetching additional data: " . $e->getMessage());
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Detail <?= htmlspecialchars($jenis_surat) ?></h1>
            <p class="text-sm opacity-70 mt-1">Informasi lengkap surat</p>
        </div>
        <div class="flex gap-2">
            <button onclick="history.back()" class="btn btn-outline btn-sm">
                <i class="bi bi-arrow-left mr-2"></i> Kembali
            </button>
            <button onclick="printDetail()" class="btn btn-outline btn-sm">
                <i class="bi bi-printer mr-2"></i> Print
            </button>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="bg-base-100 rounded-xl shadow-md p-4 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <span class="badge <?= getStatusBadgeClass($data['status'] ?? '') ?> text-lg">
                    <?= ucfirst($data['status'] ?? 'Tidak diketahui') ?>
                </span>
            </div>
            <div class="text-sm text-gray-500">
                Dibuat oleh: <?= htmlspecialchars($data['created_by_name'] ?? '') ?> (<?= htmlspecialchars($data['nrp'] ?? '') ?> - <?= htmlspecialchars($data['rank'] ?? '') ?>)
            </div>
        </div>
    </div>

    <?php if (empty($data)): ?>
    <div class="alert alert-error">
        <i class="bi bi-exclamation-triangle mr-2"></i>
        Data tidak ditemukan atau terjadi kesalahan.
    </div>
    <?php else: ?>
    
    <?php if ($table_name === 'surat_keluar'): ?>
    <!-- Detail Surat Keluar -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Surat</h3>
            <div class="space-y-3">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Nomor Surat</span>
                    </label>
                    <div class="text-lg font-mono"><?= htmlspecialchars($data['nomor_surat'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Tanggal Surat</span>
                    </label>
                    <div><?= isset($data['tanggal_surat']) ? date('d M Y', strtotime($data['tanggal_surat'])) : '' ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Perihal</span>
                    </label>
                    <div class="text-lg"><?= htmlspecialchars($data['perihal'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Tujuan</span>
                    </label>
                    <div><?= htmlspecialchars($data['tujuan'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Lampiran</span>
                    </label>
                    <div><?= htmlspecialchars($data['lampiran'] ?? '-') ?></div>
                </div>
            </div>
        </div>

        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Isi Surat</h3>
            <div class="prose max-w-none">
                <?= nl2br(htmlspecialchars($data['isi_surat'] ?? '')) ?>
            </div>
            
            <?php if (!empty($data['file_surat'])): ?>
            <div class="mt-6">
                <label class="label">
                    <span class="label-text font-semibold">File Surat</span>
                </label>
                <a href="<?= base_url('uploads/' . $data['file_surat']) ?>" 
                   target="_blank" 
                   class="btn btn-outline btn-sm">
                    <i class="bi bi-download mr-2"></i> Download File
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php elseif ($table_name === 'surat_penangkapan'): ?>
    <!-- Detail SP.Kap -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Surat Penangkapan</h3>
            <div class="space-y-3">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Nomor SP.Kap</span>
                    </label>
                    <div class="text-lg font-mono"><?= htmlspecialchars($data['nomor_spkap'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Tanggal SP.Kap</span>
                    </label>
                    <div><?= isset($data['tanggal_spkap']) ? date('d M Y', strtotime($data['tanggal_spkap'])) : '' ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Masa Berlaku</span>
                    </label>
                    <div><?= isset($data['masa_berlaku']) ? date('d M Y', strtotime($data['masa_berlaku'])) : '' ?></div>
                </div>
            </div>
        </div>

        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Tersangka</h3>
            <div class="space-y-3">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Nama Tersangka</span>
                    </label>
                    <div class="text-lg"><?= htmlspecialchars($data['nama_tersangka'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Alias</span>
                    </label>
                    <div><?= htmlspecialchars($data['alias'] ?? '-') ?></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Tempat Lahir</span>
                        </label>
                        <div><?= htmlspecialchars($data['tempat_lahir'] ?? '') ?></div>
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Tanggal Lahir</span>
                        </label>
                        <div><?= isset($data['tanggal_lahir']) ? date('d M Y', strtotime($data['tanggal_lahir'])) : '' ?></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Umur</span>
                        </label>
                        <div><?= htmlspecialchars($data['umur'] ?? '') ?> tahun</div>
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Jenis Kelamin</span>
                        </label>
                        <div><?= htmlspecialchars($data['jenis_kelamin'] ?? '') ?></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Kebangsaan</span>
                        </label>
                        <div><?= htmlspecialchars($data['kebangsaan'] ?? '') ?></div>
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Agama</span>
                        </label>
                        <div><?= htmlspecialchars($data['agama'] ?? '') ?></div>
                    </div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Pekerjaan</span>
                    </label>
                    <div><?= htmlspecialchars($data['pekerjaan'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Alamat</span>
                    </label>
                    <div><?= nl2br(htmlspecialchars($data['alamat'] ?? '')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6">
        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Perkara</h3>
            <div class="space-y-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Pasal yang Disangkakan</span>
                    </label>
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($data['pasal_yang_disangkakan'] ?? '')) ?>
                    </div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Uraian Singkat Perkara</span>
                    </label>
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($data['uraian_singkat_perkara'] ?? '')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php elseif ($table_name === 'surat_penahanan'): ?>
    <!-- Detail SP.Han -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Surat Penahanan</h3>
            <div class="space-y-3">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Nomor SP.Han</span>
                    </label>
                    <div class="text-lg font-mono"><?= htmlspecialchars($data['nomor_sphan'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Tanggal SP.Han</span>
                    </label>
                    <div><?= isset($data['tanggal_sphan']) ? date('d M Y', strtotime($data['tanggal_sphan'])) : '' ?></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Tanggal Mulai</span>
                        </label>
                        <div><?= isset($data['tanggal_mulai']) ? date('d M Y', strtotime($data['tanggal_mulai'])) : '' ?></div>
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Tanggal Berakhir</span>
                        </label>
                        <div><?= isset($data['tanggal_berakhir']) ? date('d M Y', strtotime($data['tanggal_berakhir'])) : '' ?></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Lama Penahanan</span>
                        </label>
                        <div><?= htmlspecialchars($data['lama_penahanan'] ?? '') ?> hari</div>
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Jenis Penahanan</span>
                        </label>
                        <div><?= htmlspecialchars($data['jenis_penahanan'] ?? '') ?></div>
                    </div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Tempat Penahanan</span>
                    </label>
                    <div><?= htmlspecialchars($data['tempat_penahanan'] ?? '') ?></div>
                </div>
            </div>
        </div>

        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Tersangka</h3>
            <div class="space-y-3">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Nama Tersangka</span>
                    </label>
                    <div class="text-lg"><?= htmlspecialchars($data['nama_tersangka'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Alias</span>
                    </label>
                    <div><?= htmlspecialchars($data['alias'] ?? '-') ?></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Tempat Lahir</span>
                        </label>
                        <div><?= htmlspecialchars($data['tempat_lahir'] ?? '') ?></div>
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Tanggal Lahir</span>
                        </label>
                        <div><?= isset($data['tanggal_lahir']) ? date('d M Y', strtotime($data['tanggal_lahir'])) : '' ?></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Umur</span>
                        </label>
                        <div><?= htmlspecialchars($data['umur'] ?? '') ?> tahun</div>
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Jenis Kelamin</span>
                        </label>
                        <div><?= htmlspecialchars($data['jenis_kelamin'] ?? '') ?></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Kebangsaan</span>
                        </label>
                        <div><?= htmlspecialchars($data['kebangsaan'] ?? '') ?></div>
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Agama</span>
                        </label>
                        <div><?= htmlspecialchars($data['agama'] ?? '') ?></div>
                    </div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Pekerjaan</span>
                    </label>
                    <div><?= htmlspecialchars($data['pekerjaan'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Alamat</span>
                    </label>
                    <div><?= nl2br(htmlspecialchars($data['alamat'] ?? '')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6">
        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Perkara</h3>
            <div class="space-y-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Pasal yang Disangkakan</span>
                    </label>
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($data['pasal_yang_disangkakan'] ?? '')) ?>
                    </div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Uraian Singkat Perkara</span>
                    </label>
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($data['uraian_singkat_perkara'] ?? '')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php elseif ($table_name === 'surat_penyitaan'): ?>
    <!-- Detail SP.Sita - DIPERBAIKI -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Surat Penyitaan</h3>
            <div class="space-y-3">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Nomor SP.Sita</span>
                    </label>
                    <div class="text-lg font-mono"><?= htmlspecialchars($data['nomor_spsita'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Tanggal SP.Sita</span>
                    </label>
                    <div><?= isset($data['tanggal_spsita']) ? date('d M Y', strtotime($data['tanggal_spsita'])) : '' ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Status</span>
                    </label>
                    <div>
                        <span class="badge <?= getStatusBadgeClass($data['status'] ?? '') ?>">
                            <?= ucfirst($data['status'] ?? '') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Tersangka</h3>
            <div class="space-y-3">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Nama Tersangka</span>
                    </label>
                    <div class="text-lg"><?= htmlspecialchars($data['nama_tersangka'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Alamat Tersangka</span>
                    </label>
                    <div><?= nl2br(htmlspecialchars($data['alamat_tersangka'] ?? '')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6">
        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Perkara</h3>
            <div class="space-y-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Pasal yang Disangkakan</span>
                    </label>
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($data['pasal_yang_disangkakan'] ?? '')) ?>
                    </div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Uraian Singkat Perkara</span>
                    </label>
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($data['uraian_singkat_perkara'] ?? '')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barang Sitaan -->
    <?php if (!empty($barang_sitaan)): ?>
    <div class="bg-base-100 rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Daftar Barang Sitaan</h3>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th>Merk/Type</th>
                        <th>Jumlah</th>
                        <th>Kondisi</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($barang_sitaan as $barang): ?>
                    <tr>
                        <td><?= htmlspecialchars($barang['nama_barang']) ?></td>
                        <td>
                            <?= htmlspecialchars($barang['merk'] ?? '') ?>
                            <?= !empty($barang['type']) ? '/ ' . htmlspecialchars($barang['type']) : '' ?>
                        </td>
                        <td><?= $barang['jumlah'] ?> <?= $barang['satuan'] ?></td>
                        <td>
                            <span class="badge <?= $barang['kondisi'] === 'Baik' ? 'badge-success' : ($barang['kondisi'] === 'Rusak' ? 'badge-warning' : 'badge-error') ?>">
                                <?= $barang['kondisi'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($barang['keterangan'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-base-100 rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Daftar Barang Sitaan</h3>
        <div class="alert alert-info">
            <i class="bi bi-info-circle mr-2"></i>
            Tidak ada barang sitaan yang tercatat.
        </div>
    </div>
    <?php endif; ?>

    <?php elseif ($table_name === 'surat_tugas'): ?>
    <!-- Detail SP.Gas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Surat Tugas</h3>
            <div class="space-y-3">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Nomor SP.Gas</span>
                    </label>
                    <div class="text-lg font-mono"><?= htmlspecialchars($data['nomor_spgas'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Tanggal SP.Gas</span>
                    </label>
                    <div><?= isset($data['tanggal_spgas']) ? date('d M Y', strtotime($data['tanggal_spgas'])) : '' ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Jenis Tugas</span>
                    </label>
                    <div class="text-lg"><?= htmlspecialchars($data['jenis_tugas'] ?? '') ?></div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Tempat Tugas</span>
                    </label>
                    <div><?= htmlspecialchars($data['tempat_tugas'] ?? '') ?></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Tanggal Mulai</span>
                        </label>
                        <div><?= isset($data['tanggal_mulai']) ? date('d M Y', strtotime($data['tanggal_mulai'])) : '' ?></div>
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Tanggal Selesai</span>
                        </label>
                        <div><?= isset($data['tanggal_selesai']) ? date('d M Y', strtotime($data['tanggal_selesai'])) : '' ?></div>
                    </div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Lama Tugas</span>
                    </label>
                    <div><?= $data['lama_tugas'] ?? '' ?> hari</div>
                </div>
            </div>
        </div>

        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Detail Tugas</h3>
            <div class="space-y-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Tujuan Tugas</span>
                    </label>
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($data['tujuan_tugas'] ?? '')) ?>
                    </div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Keterangan</span>
                    </label>
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($data['keterangan'] ?? '')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Personel Tugas -->
    <?php if (!empty($personel_tugas)): ?>
    <div class="bg-base-100 rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Personel Tugas</h3>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NRP</th>
                        <th>Pangkat</th>
                        <th>Jabatan dalam Tugas</th>
                        <th>Peran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personel_tugas as $personel): ?>
                    <tr>
                        <td><?= htmlspecialchars($personel['full_name']) ?></td>
                        <td><?= htmlspecialchars($personel['nrp']) ?></td>
                        <td><?= htmlspecialchars($personel['rank']) ?></td>
                        <td><?= htmlspecialchars($personel['jabatan_dalam_tugas']) ?></td>
                        <td>
                            <span class="badge <?= $personel['peran'] === 'Ketua' ? 'badge-primary' : ($personel['peran'] === 'Pengawas' ? 'badge-secondary' : 'badge-info') ?>">
                                <?= $personel['peran'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-base-100 rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Personel Tugas</h3>
        <div class="alert alert-info">
            <i class="bi bi-info-circle mr-2"></i>
            Tidak ada personel yang ditugaskan.
        </div>
    </div>
    <?php endif; ?>

    <?php endif; ?>

    <!-- Timestamp Information -->
    <div class="bg-base-100 rounded-xl shadow-md p-6">
        <h3 class="text-lg font-semibold mb-4">Informasi Sistem</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Dibuat Pada</span>
                </label>
                <div><?= isset($data['created_at']) ? date('d M Y H:i', strtotime($data['created_at'])) : '' ?></div>
            </div>
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Diperbarui Pada</span>
                </label>
                <div><?= isset($data['updated_at']) ? date('d M Y H:i', strtotime($data['updated_at'])) : '' ?></div>
            </div>
        </div>
    </div>
    
    <?php endif; // end if empty($data) ?>
</div>

<script>
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

// Function to print detail
function printDetail() {
    window.print();
}

// Handle alert notifikasi
<?php if ($alert): ?>
document.addEventListener('DOMContentLoaded', function() {
    showAlert('<?= $alert['type'] ?>', '<?= $alert['title'] ?>', '<?= addslashes($alert['message']) ?>');
});
<?php endif; ?>
</script>

<style>
@media print {
    .btn, .bg-base-100.rounded-xl.shadow-md.p-4 {
        display: none !important;
    }
    
    .container {
        max-width: none !important;
        padding: 0 !important;
    }
    
    .badge {
        border: 1px solid #ccc;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
    }
    
    .prose {
        font-size: 12px;
    }
}

.label {
    padding: 0.25rem 0;
}

.label-text {
    font-size: 0.875rem;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>