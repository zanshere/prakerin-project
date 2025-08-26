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
    // Validasi input sesuai dengan form surat penyitaan
    $nomor_spsita = trim($_POST['nomor_spsita'] ?? '');
    $tanggal_spsita = $_POST['tanggal_spsita'] ?? '';
    $nama_tersangka = trim($_POST['nama_tersangka'] ?? '');
    $alamat_tersangka = trim($_POST['alamat_tersangka'] ?? '');
    $pasal_yang_disangkakan = trim($_POST['pasal_yang_disangkakan'] ?? '');
    $uraian_singkat_perkara = trim($_POST['uraian_singkat_perkara'] ?? '');
    $status = $_POST['status'] ?? 'aktif';
    $created_by = $_SESSION['user_id'];

    // Validasi field wajib
    if (empty($nomor_spsita)) {
        throw new Exception('Nomor SP.Sita harus diisi!');
    }

    if (empty($tanggal_spsita)) {
        throw new Exception('Tanggal SP.Sita harus diisi!');
    }

    if (empty($nama_tersangka)) {
        throw new Exception('Nama tersangka harus diisi!');
    }

    if (empty($alamat_tersangka)) {
        throw new Exception('Alamat tersangka harus diisi!');
    }

    if (empty($pasal_yang_disangkakan)) {
        throw new Exception('Pasal yang disangkakan harus diisi!');
    }

    if (empty($uraian_singkat_perkara)) {
        throw new Exception('Uraian singkat perkara harus diisi!');
    }

    // Validasi tanggal tidak boleh di masa depan
    if (strtotime($tanggal_spsita) > time()) {
        throw new Exception('Tanggal SP.Sita tidak boleh di masa depan!');
    }

    // Validasi nomor surat tidak boleh duplikat
    $sql_check = "SELECT COUNT(*) as total FROM surat_penyitaan 
                  WHERE nomor_spsita = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('s', $nomor_spsita);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result()->fetch_assoc();
    
    if ($result_check['total'] > 0) {
        throw new Exception("Nomor SP.Sita {$nomor_spsita} sudah digunakan!");
    }

    // Mulai transaksi MySQLi
    $conn->autocommit(FALSE);

    // Insert ke database surat_penyitaan
    $sql = "INSERT INTO surat_penyitaan (
                nomor_spsita, 
                tanggal_spsita, 
                nama_tersangka, 
                alamat_tersangka,
                pasal_yang_disangkakan,
                uraian_singkat_perkara,
                created_by,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssis', 
        $nomor_spsita, 
        $tanggal_spsita, 
        $nama_tersangka, 
        $alamat_tersangka,
        $pasal_yang_disangkakan,
        $uraian_singkat_perkara,
        $created_by,
        $status
    );
    
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Gagal menyimpan surat penyitaan ke database: ' . $conn->error);
    }

    $surat_id = $conn->insert_id;

    // Log aktivitas
    logAktivitas($conn, $created_by, 'sp_sita', $surat_id, 'create', 
                'Membuat surat penyitaan nomor: ' . $nomor_spsita);

    // Commit transaksi MySQLi
    $conn->commit();
    $conn->autocommit(TRUE);

    // Return JSON response sukses
    echo json_encode([
        'status' => 'success',
        'message' => "Surat Perintah Penyitaan berhasil dibuat dengan nomor: {$nomor_spsita}",
        'data' => [
            'surat_id' => $surat_id,
            'nomor_spsita' => $nomor_spsita,
            'tanggal_spsita' => $tanggal_spsita,
            'nama_tersangka' => $nama_tersangka
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

// Fungsi untuk log aktivitas
function logAktivitas($conn, $user_id, $jenis_surat, $id_surat, $aksi, $keterangan = null) {
    $sql = "INSERT INTO log_aktivitas (id_user, jenis_surat, id_surat, aksi, keterangan) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isiss', $user_id, $jenis_surat, $id_surat, $aksi, $keterangan);
    $stmt->execute();
}
?>