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
    // Validasi input sesuai dengan form surat penangkapan
    $nomor_spkap = trim($_POST['nomor_spkap'] ?? '');
    $tanggal_spkap = $_POST['tanggal_spkap'] ?? '';
    $nama_tersangka = trim($_POST['nama_tersangka'] ?? '');
    $alias = trim($_POST['alias'] ?? '');
    $tempat_lahir = trim($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $umur = (int)($_POST['umur'] ?? 0);
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $kebangsaan = trim($_POST['kebangsaan'] ?? 'Indonesia');
    $agama = $_POST['agama'] ?? '';
    $pekerjaan = trim($_POST['pekerjaan'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $pasal_yang_disangkakan = trim($_POST['pasal_yang_disangkakan'] ?? '');
    $uraian_singkat_perkara = trim($_POST['uraian_singkat_perkara'] ?? '');
    $masa_berlaku = $_POST['masa_berlaku'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    $created_by = $_SESSION['user_id'];

    // Validasi field wajib
    if (empty($nomor_spkap)) {
        throw new Exception('Nomor SP.Kap harus diisi!');
    }

    if (empty($tanggal_spkap)) {
        throw new Exception('Tanggal SP.Kap harus diisi!');
    }

    if (empty($nama_tersangka)) {
        throw new Exception('Nama tersangka harus diisi!');
    }

    if (empty($tempat_lahir)) {
        throw new Exception('Tempat lahir harus diisi!');
    }

    if (empty($tanggal_lahir)) {
        throw new Exception('Tanggal lahir harus diisi!');
    }

    if ($umur <= 0) {
        throw new Exception('Umur harus berupa angka positif!');
    }

    if (empty($jenis_kelamin)) {
        throw new Exception('Jenis kelamin harus diisi!');
    }

    if (empty($agama)) {
        throw new Exception('Agama harus diisi!');
    }

    if (empty($pekerjaan)) {
        throw new Exception('Pekerjaan harus diisi!');
    }

    if (empty($alamat)) {
        throw new Exception('Alamat harus diisi!');
    }

    if (empty($pasal_yang_disangkakan)) {
        throw new Exception('Pasal yang disangkakan harus diisi!');
    }

    if (empty($uraian_singkat_perkara)) {
        throw new Exception('Uraian singkat perkara harus diisi!');
    }

    if (empty($masa_berlaku)) {
        throw new Exception('Masa berlaku harus diisi!');
    }

    // Validasi tanggal tidak boleh di masa depan
    if (strtotime($tanggal_spkap) > time()) {
        throw new Exception('Tanggal SP.Kap tidak boleh di masa depan!');
    }

    if (strtotime($masa_berlaku) < strtotime($tanggal_spkap)) {
        throw new Exception('Masa berlaku tidak boleh sebelum tanggal SP.Kap!');
    }

    // Validasi nomor surat tidak boleh duplikat
    $sql_check = "SELECT COUNT(*) as total FROM surat_penangkapan 
                  WHERE nomor_spkap = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('s', $nomor_spkap);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result()->fetch_assoc();
    
    if ($result_check['total'] > 0) {
        throw new Exception("Nomor SP.Kap {$nomor_spkap} sudah digunakan!");
    }

    // Mulai transaksi MySQLi
    $conn->autocommit(FALSE);

    // Insert ke database surat_penangkapan
    $sql = "INSERT INTO surat_penangkapan (
                nomor_spkap, 
                tanggal_spkap, 
                nama_tersangka, 
                alias,
                tempat_lahir,
                tanggal_lahir,
                umur,
                jenis_kelamin,
                kebangsaan,
                agama,
                pekerjaan,
                alamat,
                pasal_yang_disangkakan,
                uraian_singkat_perkara,
                masa_berlaku,
                created_by,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssisissssssis', 
        $nomor_spkap, 
        $tanggal_spkap, 
        $nama_tersangka, 
        $alias,
        $tempat_lahir,
        $tanggal_lahir,
        $umur,
        $jenis_kelamin,
        $kebangsaan,
        $agama,
        $pekerjaan,
        $alamat,
        $pasal_yang_disangkakan,
        $uraian_singkat_perkara,
        $masa_berlaku,
        $created_by,
        $status
    );
    
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Gagal menyimpan surat penangkapan ke database: ' . $conn->error);
    }

    $surat_id = $conn->insert_id;

    // Log aktivitas
    logAktivitas($conn, $created_by, 'sp_kap', $surat_id, 'create', 
                'Membuat surat penangkapan nomor: ' . $nomor_spkap);

    // Commit transaksi MySQLi
    $conn->commit();
    $conn->autocommit(TRUE);

    // Return JSON response sukses
    echo json_encode([
        'status' => 'success',
        'message' => "Surat Perintah Penangkapan berhasil dibuat dengan nomor: {$nomor_spkap}",
        'data' => [
            'surat_id' => $surat_id,
            'nomor_spkap' => $nomor_spkap,
            'tanggal_spkap' => $tanggal_spkap,
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