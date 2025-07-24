<?php
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../config/baseURL.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Silakan login kembali.'
    ]);
    exit();
}

// Cek apakah form sudah di-submit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan.'
    ]);
    exit();
}

try {
    // Validasi input sesuai dengan form
    $tanggal_surat = $_POST['tanggal_surat'] ?? '';
    $banyak_surat = (int)($_POST['banyak_surat'] ?? 1);
    $kepada = $_POST['kepada'] ?? '';
    $perihal = $_POST['perihal'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $created_by = $_SESSION['user_id'];

    // Validasi field wajib
    if (empty($tanggal_surat) || empty($kepada) || empty($perihal)) {
        throw new Exception('Tanggal surat, kepada, dan perihal harus diisi!');
    }

    // Validasi banyak surat harus berupa angka positif
    if ($banyak_surat <= 0 || $banyak_surat > 100) {
        throw new Exception('Banyak surat harus antara 1-100!');
    }

    // Validasi tanggal tidak boleh di masa depan
    if (strtotime($tanggal_surat) > time()) {
        throw new Exception('Tanggal surat tidak boleh di masa depan!');
    }

    // Mulai transaksi MySQLi
    $conn->autocommit(FALSE);

    // Generate nomor surat base
    $nomor_surat_base = generateNomorSurat($conn, 'surat_keluar');

    // Proses multiple surat berdasarkan banyak_surat
    $surat_ids = [];
    $nomor_surat_list = [];
    
    for ($i = 1; $i <= $banyak_surat; $i++) {
        // Jika lebih dari 1 surat, tambahkan suffix
        $current_nomor = $nomor_surat_base;
        if ($banyak_surat > 1) {
            $current_nomor = $nomor_surat_base . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        // Insert ke database sesuai struktur tabel surat_keluar
        $sql = "INSERT INTO surat_keluar (
                    nomor_surat, 
                    tanggal_surat, 
                    perihal, 
                    tujuan, 
                    lampiran,
                    isi_surat, 
                    created_by, 
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'draft')";

        $stmt = $conn->prepare($sql);
        $lampiran = null; // Set lampiran sebagai null
        $stmt->bind_param('sssssis', $current_nomor, $tanggal_surat, $perihal, $kepada, $lampiran, $keterangan, $created_by);
        
        $result = $stmt->execute();

        if (!$result) {
            throw new Exception('Gagal menyimpan surat ke database: ' . $conn->error);
        }

        $surat_id = $conn->insert_id;
        $surat_ids[] = $surat_id;
        $nomor_surat_list[] = $current_nomor;

        // Log aktivitas
        logAktivitas($conn, $created_by, 'surat_keluar', $surat_id, 'create', 
                    'Membuat surat keluar nomor: ' . $current_nomor);
    }

    // Commit transaksi MySQLi
    $conn->commit();
    $conn->autocommit(TRUE);

    // Siapkan pesan sukses
    if ($banyak_surat == 1) {
        $message = "Surat keluar berhasil dibuat dengan nomor: {$nomor_surat_base}";
    } else {
        $message = "Berhasil membuat {$banyak_surat} surat keluar dengan nomor: {$nomor_surat_base} (dengan suffix -01, -02, dst.)";
    }

    // Return JSON response sukses
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'data' => [
            'nomor_surat_base' => $nomor_surat_base,
            'banyak_surat' => $banyak_surat,
            'surat_ids' => $surat_ids,
            'nomor_surat_list' => $nomor_surat_list
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    $conn->rollback();
    $conn->autocommit(TRUE);

    // Return JSON response error
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Fungsi untuk generate nomor surat
function generateNomorSurat($conn, $jenis_surat) {
    $tahun = date('Y');
    
    // Cek apakah sudah ada record untuk tahun ini
    $sql = "SELECT nomor_terakhir, format_nomor FROM nomor_surat 
            WHERE jenis_surat = ? AND tahun = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $jenis_surat, $tahun);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        // Update nomor terakhir
        $nomor_baru = $result['nomor_terakhir'] + 1;
        $sql = "UPDATE nomor_surat SET nomor_terakhir = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE jenis_surat = ? AND tahun = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $nomor_baru, $jenis_surat, $tahun);
        $stmt->execute();
        
        $format_nomor = $result['format_nomor'];
    } else {
        // Insert record baru untuk tahun ini
        $nomor_baru = 1;
        $format_nomor = "SPK/{nomor}/RESKRIM/{tahun}";
        $sql = "INSERT INTO nomor_surat (jenis_surat, tahun, nomor_terakhir, format_nomor) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('siis', $jenis_surat, $tahun, $nomor_baru, $format_nomor);
        $stmt->execute();
    }

    // Generate nomor surat sesuai format
    $nomor_formatted = str_pad($nomor_baru, 3, '0', STR_PAD_LEFT);
    $nomor_surat = str_replace(['{nomor}', '{tahun}'], [$nomor_formatted, $tahun], $format_nomor);

    return $nomor_surat;
}

// Fungsi untuk log aktivitas
function logAktivitas($conn, $user_id, $jenis_surat, $id_surat, $aksi, $keterangan = null) {
    $sql = "INSERT INTO log_aktivitas (id_user, jenis_surat, id_surat, aksi, keterangan) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isiss', $user_id, $jenis_surat, $id_surat, $aksi, $keterangan);
    $stmt->execute();
}
?>