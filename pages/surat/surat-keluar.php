<?php 
// Header
include_once __DIR__ . '/../../includes/header.php';


?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-base-100 rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Form Register Surat Keluar</h1>

        <form action="proses_surat.php" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="label font-medium">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="input input-bordered w-full" required>
                </div>
                <div>
                    <label class="label font-medium">Banyak Surat</label>
                    <input type="number" name="banyak_surat" class="input input-bordered w-full" placeholder="Contoh: 3" required>
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
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save mr-2"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>


<!-- Footer -->
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>