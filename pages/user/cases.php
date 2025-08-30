<?php
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../config/authCheck.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('auth/login.php'));
    exit();
}

$user_id = $_SESSION['user_id'];

// Alert
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);

// Ambil semua surat milik user login
$cases = [];

// Query gabungan
$query = "
    SELECT 'Surat Tugas' AS jenis, id_spgas AS id, nomor_spgas AS nomor, tanggal_spgas AS tanggal, status, created_at 
    FROM surat_tugas WHERE created_by = $user_id
    UNION
    SELECT 'Surat Penangkapan', id_spkap, nomor_spkap, tanggal_spkap, status, created_at
    FROM surat_penangkapan WHERE created_by = $user_id
    UNION
    SELECT 'Surat Penyitaan', id_spsita, nomor_spsita, tanggal_spsita, status, created_at
    FROM surat_penyitaan WHERE created_by = $user_id
    UNION
    SELECT 'Surat Keluar', id_surat, nomor_surat, tanggal_surat, status, created_at
    FROM surat_keluar WHERE created_by = $user_id
    ORDER BY created_at DESC
";

if ($result = $conn->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $cases[] = $row;
    }
    $result->free();
} else {
    $alert = [
        'type' => 'error',
        'title' => 'Error',
        'message' => "Error fetching cases: " . $conn->error
    ];
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-base-content">My Cases</h1>
        <a href="<?= base_url('surat/addCase.php') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg mr-2"></i> Buat Surat Baru
        </a>
    </div>

    <?php if (empty($cases)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle mr-2"></i>
        Anda belum pernah membuat surat apapun.
    </div>
    <?php else: ?>
    <div class="bg-base-100 rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Jenis Surat</th>
                        <th>Nomor Surat</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cases as $case): ?>
                    <tr>
                        <td><?= htmlspecialchars($case['jenis']) ?></td>
                        <td><?= htmlspecialchars($case['nomor']) ?></td>
                        <td><?= htmlspecialchars($case['tanggal']) ?></td>
                        <td><?= htmlspecialchars($case['status']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($case['created_at'])) ?></td>
                        <td>
                            <div class="flex gap-2">
                                <a href="<?= base_url('surat/editCase.php?jenis=' . urlencode($case['jenis']) . '&id=' . urlencode($case['id'])) ?>"
                                    class="btn btn-sm btn-outline btn-info">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button onclick="confirmDelete('<?= $case['jenis'] ?>', <?= intval($case['id']) ?>)"
                                    class="btn btn-sm btn-outline btn-error">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
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

function confirmDelete(jenis, id) {
    Swal.fire({
        title: 'Yakin mau hapus?',
        text: "Data surat ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url('functions/deleteCase.php?jenis=') ?>' + encodeURIComponent(jenis) + '&id=' + id;
        }
    });
}

<?php if ($alert): ?>
document.addEventListener('DOMContentLoaded', function() {
    showAlert('<?= $alert['type'] ?>', '<?= $alert['title'] ?>', '<?= addslashes($alert['message']) ?>');
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
