<?php 
// Header
include_once __DIR__ . '/../../includes/header.php';


?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Form Register Surat Perintah Penangkapan (B5)</h1>

    <form action="proses_penangkapan.php" method="POST">
        <div class="bg-base-100 rounded-xl shadow-md p-6 space-y-4">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="label font-medium">No</label>
                    <input type="number" name="no" class="input input-bordered w-full" required>
                </div>
                <div>
                    <label class="label font-medium">Tanggal</label>
                    <input type="date" name="tanggal" class="input input-bordered w-full" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="label font-medium">Petugas</label>
                    <textarea name="petugas" class="textarea textarea-bordered w-full" rows="3" placeholder="Contoh: BRIPDA A / 123456"></textarea>
                </div>
                <div>
                    <label class="label font-medium">Data Tersangka</label>
                    <textarea name="tersangka" class="textarea textarea-bordered w-full" rows="3" placeholder="Nama, TTL, Pekerjaan, Warga Negara"></textarea>
                </div>
            </div>

            <div>
                <label class="label font-medium">Dasar Penangkapan</label>
                <textarea name="dasar_penangkapan" class="textarea textarea-bordered w-full" rows="3" placeholder="Nomor & Tanggal Surat"></textarea>
            </div>

            <div>
                <label class="label font-medium">Yang Memerintahkan</label>
                <input name="pememerintah" type="text" class="input input-bordered w-full" placeholder="Contoh: KA">
            </div>

            <div>
                <label class="label font-medium">Keterangan</label>
                <input name="keterangan" type="text" class="input input-bordered w-full">
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save mr-2"></i> Simpan Surat
            </button>
        </div>
    </form>
</div>


<!-- Footer -->
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>