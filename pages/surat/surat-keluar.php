<?php 
// Header
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-base-100 rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Form Register Surat Keluar</h1>

        <form id="formSuratKeluar" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="label font-medium">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="input input-bordered w-full" required>
                </div>
                <div>
                    <label class="label font-medium">Banyak Surat</label>
                    <input type="number" name="banyak_surat" class="input input-bordered w-full" placeholder="Contoh: 3" required min="1" max="100">
                </div>
            </div>

            <div class="mt-6">
                <label class="label font-medium">Kepada (Nama)</label>
                <input type="text" name="kepada" class="input input-bordered w-full" placeholder="Contoh: IPDA RUDOLF LUITTO PASARIBU" required>
            </div>

            <div class="mt-4">
                <label class="label font-medium">Perihal</label>
                <input type="text" name="perihal" class="input input-bordered w-full" placeholder="Contoh: Pemanggilan Saksi" required>
            </div>

            <div class="mt-4">
                <label class="label font-medium">Keterangan</label>
                <textarea name="keterangan" class="textarea textarea-bordered w-full" rows="3" placeholder="Isi keterangan tambahan di sini..."></textarea>
            </div>

            <div class="mt-6">
                <button type="submit" class="btn btn-primary" id="btnSubmit">
                    <i class="bi bi-save mr-2"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formSuratKeluar');
    const btnSubmit = document.getElementById('btnSubmit');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Disable button untuk mencegah double submit
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="loading loading-spinner loading-sm"></span>Menyimpan...';

        // Ambil data form
        const formData = new FormData(form);

        // Kirim data via AJAX
        fetch('<?= base_url('functions/proses-surat/proccessKeluar.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Enable button kembali
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-save mr-2"></i> Simpan Data';

            if (data.status === 'success') {
                // Tampilkan SweetAlert success
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Reset form setelah berhasil
                        // form.reset();
                        // Atau redirect jika diperlukan
                        window.location.href = '<?= base_url('pages/index.php') ?>';
                    }
                });
            } else {
                // Tampilkan SweetAlert error
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Enable button kembali
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-save mr-2"></i> Simpan Data';

            // Tampilkan error
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan pada sistem. Silakan coba lagi.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
        });
    });
});
</script>

<!-- Footer -->
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>