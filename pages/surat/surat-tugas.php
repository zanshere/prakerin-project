<?php 
// Header
include_once __DIR__ . '/../../includes/header.php';


?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-base-content mb-4">
        Form Register Surat Perintah Tugas dan Surat Perintah Penyidikan (B8)
    </h1>

    <form id="formSuratTugas" method="POST">
    <div class="mb-4">
    <label class="label font-medium">Tanggal SP.Gas</label>
    <input type="date" name="tanggal_spgas" class="input input-bordered w-full" required>
</div>

<div class="mb-4">
    <label class="label font-medium">Jenis Tugas</label>
    <input type="text" name="jenis_tugas" class="input input-bordered w-full" required>
</div>

<div class="mb-4">
    <label class="label font-medium">Tujuan Tugas</label>
    <input type="text" name="tujuan_tugas" class="input input-bordered w-full" required>
</div>

<div class="mb-4">
    <label class="label font-medium">Tempat Tugas</label>
    <input type="text" name="tempat_tugas" class="input input-bordered w-full" required>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div>
        <label class="label font-medium">Tanggal Mulai</label>
        <input type="date" name="tanggal_mulai" class="input input-bordered w-full" required>
    </div>
    <div>
        <label class="label font-medium">Tanggal Selesai</label>
        <input type="date" name="tanggal_selesai" class="input input-bordered w-full" required>
    </div>
</div>

<div class="mb-6">
    <label class="label font-medium">Lama Bertugas</label>
    <input type="number" name="lama_tugas" class="input input-bordered w-full" required>
</div>

<div class="mb-4">
    <label class="label font-medium">Keterangan</label>
    <textarea name="keterangan" class="textarea textarea-bordered w-full" rows="2"></textarea>
</div>


    <div class="flex justify-end">
        <button type="submit" class="btn btn-primary" id="btnSubmit">
            <i class="bi bi-save mr-2"></i> Simpan Data
        </button>
    </div>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formSuratTugas');
    const btnSubmit = document.getElementById('btnSubmit');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Disable button untuk mencegah double submit
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="loading loading-spinner loading-sm"></span>Menyimpan...';

        // Ambil data form
        const formData = new FormData(form);

        // Kirim data via AJAX
        fetch('<?= base_url('functions/proses-surat/proccessTugas.php') ?>', {
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