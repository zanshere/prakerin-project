<?php 
require_once __DIR__ . '/../../config/authCheck.php';
include_once __DIR__ . '/../../config/baseURL.php';
// Header
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-base-content mb-4">Form Surat Perintah Penangkapan (SP.Kap)</h1>

    <div class="bg-base-100 rounded-xl shadow-md p-6">
        <form id="formSuratPenangkapan" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="label font-medium">Nomor SP.Kap</label>
                    <input type="text" name="nomor_spkap" class="input input-bordered w-full" required>
                </div>
                <div>
                    <label class="label font-medium">Tanggal SP.Kap</label>
                    <input type="date" name="tanggal_spkap" class="input input-bordered w-full" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="label font-medium">Nama Tersangka</label>
                <input type="text" name="nama_tersangka" class="input input-bordered w-full" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="label font-medium">Alias</label>
                    <input type="text" name="alias" class="input input-bordered w-full">
                </div>
                <div>
                    <label class="label font-medium">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="input input-bordered w-full" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="label font-medium">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="input input-bordered w-full" required>
                </div>
                <div>
                    <label class="label font-medium">Umur</label>
                    <input type="number" name="umur" class="input input-bordered w-full" required min="1">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="label font-medium">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="select select-bordered w-full" required>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="label font-medium">Kebangsaan</label>
                    <input type="text" name="kebangsaan" class="input input-bordered w-full" value="Indonesia" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="label font-medium">Agama</label>
                    <select name="agama" class="select select-bordered w-full" required>
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Katolik">Katolik</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Buddha">Buddha</option>
                        <option value="Konghucu">Konghucu</option>
                    </select>
                </div>
                <div>
                    <label class="label font-medium">Pekerjaan</label>
                    <input type="text" name="pekerjaan" class="input input-bordered w-full" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="label font-medium">Alamat</label>
                <textarea name="alamat" class="textarea textarea-bordered w-full" rows="3" required></textarea>
            </div>

            <div class="mb-4">
                <label class="label font-medium">Pasal yang Disangkakan</label>
                <textarea name="pasal_yang_disangkakan" class="textarea textarea-bordered w-full" rows="3" required></textarea>
            </div>

            <div class="mb-4">
                <label class="label font-medium">Uraian Singkat Perkara</label>
                <textarea name="uraian_singkat_perkara" class="textarea textarea-bordered w-full" rows="4" required></textarea>
            </div>

            <div class="mb-6">
                <label class="label font-medium">Masa Berlaku</label>
                <input type="date" name="masa_berlaku" class="input input-bordered w-full" required>
            </div>

            <div class="mb-6">
                <label class="label font-medium">Status</label>
                <select name="status" class="select select-bordered w-full">
                    <option value="aktif" selected>Aktif</option>
                    <option value="expired">Expired</option>
                    <option value="dibatalkan">Dibatalkan</option>
                </select>
            </div>

            <div class="flex justify-end">
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
    const form = document.getElementById('formSuratPenangkapan');
    const btnSubmit = document.getElementById('btnSubmit');

    // Auto-calculate age based on birth date
    const tanggalLahirInput = document.querySelector('input[name="tanggal_lahir"]');
    const umurInput = document.querySelector('input[name="umur"]');
    
    if (tanggalLahirInput && umurInput) {
        tanggalLahirInput.addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age > 0) {
                umurInput.value = age;
            }
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Disable button untuk mencegah double submit
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="loading loading-spinner loading-sm"></span>Menyimpan...';

        // Ambil data form
        const formData = new FormData(form);

        // Kirim data via AJAX
        fetch('<?= base_url('functions/proses-surat/proccessTangkap.php') ?>', {
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
                        form.reset();
                        // Atau redirect jika diperlukan
                        // window.location.href = '<?= base_url('pages/index.php') ?>';
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