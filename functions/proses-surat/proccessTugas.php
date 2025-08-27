<?php
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../config/baseURL.php';

header('Content-Type: application/json');
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Silakan login kembali.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan.'
    ]);
    exit();
}

try {
    // Ambil data dari form
    $tanggal_spgas   = $_POST['tanggal_spgas'] ?? '';
    $jenis_tugas     = $_POST['jenis_tugas'] ?? '';
    $tujuan_tugas    = $_POST['tujuan_tugas'] ?? '';
    $tempat_tugas    = $_POST['tempat_tugas'] ?? '';
    $tanggal_mulai   = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $lama_tugas      = (int)($_POST['lama_tugas'] ?? 0);
    $keterangan      = $_POST['keterangan'] ?? '';
    $created_by      = $_SESSION['user_id'];

    // Validasi wajib isi
    if (empty($tanggal_spgas) || empty($jenis_tugas) || empty($tujuan_tugas) || empty($tempat_tugas) || empty($tanggal_mulai) || empty($tanggal_selesai) || $lama_tugas <= 0) {
        throw new Exception('Semua field wajib diisi dan lama tugas harus lebih dari 0 hari.');
    }

    // Validasi tanggal
    if (strtotime($tanggal_mulai) > strtotime($tanggal_selesai)) {
        throw new Exception('Tanggal mulai tidak boleh lebih besar dari tanggal selesai.');
    }

    // Generate nomor surat
    $nomor_spgas = generateNomorSurat($conn, 'sp_gas');

    $conn->autocommit(FALSE);

    // Insert ke tabel surat_tugas
    $sql = "INSERT INTO surat_tugas (
                nomor_spgas, tanggal_spgas, jenis_tugas, tujuan_tugas, tempat_tugas,
                tanggal_mulai, tanggal_selesai, lama_tugas, keterangan, created_by, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
       'sssssssisi',
    $nomor_spgas,     // s
    $tanggal_spgas,   // s
    $jenis_tugas,     // s
    $tujuan_tugas,    // s
    $tempat_tugas,    // s
    $tanggal_mulai,   // s
    $tanggal_selesai, // s
    $lama_tugas,      // i
    $keterangan,      // s
    $created_by       // i
    );

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan surat tugas: ' . $stmt->error);
    }

    $id_spgas = $conn->insert_id;

    // Log aktivitas
    logAktivitas($conn, $created_by, 'sp_gas', $id_spgas, 'create', 'Membuat surat tugas: ' . $nomor_spgas);

    $conn->commit();
    $conn->autocommit(TRUE);

    echo json_encode([
        'status' => 'success',
        'message' => "Surat tugas berhasil dibuat dengan nomor {$nomor_spgas}",
        'data' => [
            'id_spgas' => $id_spgas,
            'nomor_spgas' => $nomor_spgas
        ]
    ]);
} catch (Exception $e) {
    $conn->rollback();
    $conn->autocommit(TRUE);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Generate nomor surat
function generateNomorSurat($conn, $jenis_surat) {
    $tahun = date('Y');
    $sql = "SELECT nomor_terakhir, format_nomor FROM nomor_surat WHERE jenis_surat = ? AND tahun = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $jenis_surat, $tahun);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $nomor_baru = $result['nomor_terakhir'] + 1;
        $sql = "UPDATE nomor_surat SET nomor_terakhir = ?, updated_at = CURRENT_TIMESTAMP WHERE jenis_surat = ? AND tahun = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $nomor_baru, $jenis_surat, $tahun);
        $stmt->execute();
        $format_nomor = $result['format_nomor'];
    } else {
        $nomor_baru = 1;
        $format_nomor = "SP.Gas/{nomor}/RESKRIM/{tahun}";
        $sql = "INSERT INTO nomor_surat (jenis_surat, tahun, nomor_terakhir, format_nomor) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('siis', $jenis_surat, $tahun, $nomor_baru, $format_nomor);
        $stmt->execute();
    }

    $nomor_formatted = str_pad($nomor_baru, 3, '0', STR_PAD_LEFT);
    return str_replace(['{nomor}', '{tahun}'], [$nomor_formatted, $tahun], $format_nomor);
}

// Log aktivitas
function logAktivitas($conn, $user_id, $jenis_surat, $id_surat, $aksi, $keterangan = null) {
    $sql = "INSERT INTO log_aktivitas (id_user, jenis_surat, id_surat, aksi, keterangan) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isiss', $user_id, $jenis_surat, $id_surat, $aksi, $keterangan);
    $stmt->execute();
}
?>
