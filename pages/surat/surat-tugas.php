<?php 
// Header
include_once __DIR__ . '/../../includes/header.php';


?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-base-content mb-4">Form Register Surat Perintah Tugas dan Surat Perintah Penyidikan (B8)</h1>

    <form action="proses_register.php" method="POST">
        <div class="bg-base-100 rounded-xl shadow-md p-6">
            <div class="mb-4">
                <label class="label font-medium">No. Urut</label>
                <input type="text" name="keterangan" class="input input-bordered w-full">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="label font-medium">Surat Perintah Penyidikan</label>
                    <input type="number" name="no_urut" class="input input-bordered w-full" required>
                </div>
                <div>
                    <label class="label font-medium">Surat Perintah Tugas</label>
                    <input type="date" name="tanggal" class="input input-bordered w-full" required>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="label font-medium">Petugas yang Melaksanakan</label>
                    <textarea name="petugas" class="textarea textarea-bordered w-full" rows="2" required></textarea>
                </div>
                <div>
                    <label class="label font-medium">Keterangan</label>
                    <textarea name="penyita" class="textarea textarea-bordered w-full" rows="2" required></textarea>
                </div>
            </div>

            <div class="mb-4">
                <label class="label font-medium">Keperluan</label>
                <input type="text" name="tempat" class="input input-bordered w-full" required>
            </div>

            <div class="mb-6">
                <label class="label font-medium">Lama Bertugas</label>
                <input type="text" name="nomor_tanggal_surat" class="input input-bordered w-full" required>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save mr-2"></i> Simpan Data
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Footer -->
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>