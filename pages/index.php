<?php 
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<div class="hero min-h-[40vh] bg-gradient-to-r from-primary/10 to-secondary/10">
    <div class="hero-content text-center">
        <div class="max-w-md">
            <h1 class="text-5xl font-bold text-primary">
                <i class="bi bi-file-earmark-text"></i>
                Sistem Surat
            </h1>
            <p class="py-6 text-lg opacity-70">
                Kelola berbagai jenis surat resmi Unit Reskrim dengan mudah dan efisien
            </p>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mx-auto px-4 py-12">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="stat bg-base-100 shadow-lg rounded-lg border border-base-300">
            <div class="stat-figure text-primary">
                <i class="bi bi-send text-3xl"></i>
            </div>
            <div class="stat-title">Surat Keluar</div>
            <div class="stat-value text-primary">25</div>
            <div class="stat-desc">Bulan ini</div>
        </div>
        
        <div class="stat bg-base-100 shadow-lg rounded-lg border border-base-300">
            <div class="stat-figure text-warning">
                <i class="bi bi-person-x text-3xl"></i>
            </div>
            <div class="stat-title">SP.Kap</div>
            <div class="stat-value text-warning">8</div>
            <div class="stat-desc">Aktif</div>
        </div>
        
        <div class="stat bg-base-100 shadow-lg rounded-lg border border-base-300">
            <div class="stat-figure text-error">
                <i class="bi bi-lock text-3xl"></i>
            </div>
            <div class="stat-title">SP.Han</div>
            <div class="stat-value text-error">3</div>
            <div class="stat-desc">Berlaku</div>
        </div>
        
        <div class="stat bg-base-100 shadow-lg rounded-lg border border-base-300">
            <div class="stat-figure text-success">
                <i class="bi bi-check-circle text-3xl"></i>
            </div>
            <div class="stat-title">Total</div>
            <div class="stat-value text-success">156</div>
            <div class="stat-desc">Semua surat</div>
        </div>
    </div>

    <!-- Menu Cards Section -->
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold mb-4">Menu Surat</h2>
        <p class="text-base-content/70 max-w-2xl mx-auto">
            Pilih jenis surat yang ingin Anda kelola. Setiap menu memiliki fitur khusus sesuai dengan kebutuhan administrasi Unit Reskrim.
        </p>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Surat Keluar -->
        <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300 group">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-primary/10 text-primary rounded-full w-16 h-16 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                            <i class="bi bi-send text-2xl"></i>
                        </div>
                    </div>
                    <div class="badge badge-primary">Aktif</div>
                </div>
                
                <h3 class="card-title text-xl mb-2">Surat Keluar</h3>
                <p class="text-base-content/70 mb-4">
                    Kelola surat keluar resmi untuk berbagai keperluan administratif dan operasional unit
                </p>
                
                <div class="flex items-center justify-between text-sm text-base-content/60 mb-6">
                    <span><i class="bi bi-calendar3 mr-1"></i> 25 bulan ini</span>
                    <span><i class="bi bi-clock mr-1"></i> Terakhir: 2 hari lalu</span>
                </div>
                
                <div class="card-actions justify-end">
                    <button class="btn btn-primary btn-sm group-hover:btn-outline transition-all duration-300">
                        <i class="bi bi-arrow-right mr-1"></i>
                        Kelola
                    </button>
                </div>
            </div>
        </div>

        <!-- Surat Penangkapan (SP.Kap) -->
        <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300 group">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-warning/10 text-warning rounded-full w-16 h-16 flex items-center justify-center group-hover:bg-warning group-hover:text-white transition-colors duration-300">
                            <i class="bi bi-person-x text-2xl"></i>
                        </div>
                    </div>
                    <div class="badge badge-warning">Penting</div>
                </div>
                
                <h3 class="card-title text-xl mb-2">SP.Kap</h3>
                <p class="text-base-content/70 mb-4">
                    Surat Perintah Penangkapan untuk tersangka dalam proses penyidikan kasus pidana
                </p>
                
                <div class="flex items-center justify-between text-sm text-base-content/60 mb-6">
                    <span><i class="bi bi-shield-exclamation mr-1"></i> 8 aktif</span>
                    <span><i class="bi bi-clock mr-1"></i> Urgent</span>
                </div>
                
                <div class="card-actions justify-end">
                    <button class="btn btn-warning btn-sm group-hover:btn-outline transition-all duration-300">
                        <i class="bi bi-arrow-right mr-1"></i>
                        Kelola
                    </button>
                </div>
            </div>
        </div>

        <!-- Surat Penahanan (SP.Han) -->
        <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300 group">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-error/10 text-error rounded-full w-16 h-16 flex items-center justify-center group-hover:bg-error group-hover:text-white transition-colors duration-300">
                            <i class="bi bi-lock text-2xl"></i>
                        </div>
                    </div>
                    <div class="badge badge-error">Kritis</div>
                </div>
                
                <h3 class="card-title text-xl mb-2">SP.Han</h3>
                <p class="text-base-content/70 mb-4">
                    Surat Perintah Penahanan untuk tersangka yang memenuhi syarat penahanan
                </p>
                
                <div class="flex items-center justify-between text-sm text-base-content/60 mb-6">
                    <span><i class="bi bi-lock-fill mr-1"></i> 3 berlaku</span>
                    <span><i class="bi bi-clock mr-1"></i> Monitoring</span>
                </div>
                
                <div class="card-actions justify-end">
                    <button class="btn btn-error btn-sm group-hover:btn-outline transition-all duration-300">
                        <i class="bi bi-arrow-right mr-1"></i>
                        Kelola
                    </button>
                </div>
            </div>
        </div>

        <!-- Surat Sita (SP.Sita) -->
        <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300 group">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-info/10 text-info rounded-full w-16 h-16 flex items-center justify-center group-hover:bg-info group-hover:text-white transition-colors duration-300">
                            <i class="bi bi-archive text-2xl"></i>
                        </div>
                    </div>
                    <div class="badge badge-info">Inventaris</div>
                </div>
                
                <h3 class="card-title text-xl mb-2">SP.Sita</h3>
                <p class="text-base-content/70 mb-4">
                    Surat Perintah Penyitaan barang bukti dan aset terkait kasus yang ditangani
                </p>
                
                <div class="flex items-center justify-between text-sm text-base-content/60 mb-6">
                    <span><i class="bi bi-box-seam mr-1"></i> 12 item</span>
                    <span><i class="bi bi-clock mr-1"></i> Terdaftar</span>
                </div>
                
                <div class="card-actions justify-end">
                    <button class="btn btn-info btn-sm group-hover:btn-outline transition-all duration-300">
                        <i class="bi bi-arrow-right mr-1"></i>
                        Kelola
                    </button>
                </div>
            </div>
        </div>

        <!-- Surat Tugas (SP.Gas) -->
        <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300 group">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-success/10 text-success rounded-full w-16 h-16 flex items-center justify-center group-hover:bg-success group-hover:text-white transition-colors duration-300">
                            <i class="bi bi-clipboard-check text-2xl"></i>
                        </div>
                    </div>
                    <div class="badge badge-success">Operasional</div>
                </div>
                
                <h3 class="card-title text-xl mb-2">SP.Gas</h3>
                <p class="text-base-content/70 mb-4">
                    Surat Perintah Tugas untuk penugasan khusus dan operasional lapangan
                </p>
                
                <div class="flex items-center justify-between text-sm text-base-content/60 mb-6">
                    <span><i class="bi bi-people mr-1"></i> 15 tugas</span>
                    <span><i class="bi bi-clock mr-1"></i> Aktif</span>
                </div>
                
                <div class="card-actions justify-end">
                    <button class="btn btn-success btn-sm group-hover:btn-outline transition-all duration-300">
                        <i class="bi bi-arrow-right mr-1"></i>
                        Kelola
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card bg-gradient-to-br from-primary/5 to-secondary/5 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300">
            <div class="card-body p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-secondary/10 text-secondary rounded-full w-16 h-16 flex items-center justify-center">
                            <i class="bi bi-plus-circle text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <h3 class="card-title text-xl mb-2 text-center">Quick Actions</h3>
                <p class="text-base-content/70 mb-4 text-center">
                    Akses cepat untuk membuat surat baru atau melihat laporan
                </p>
                
                <div class="space-y-2">
                    <button class="btn btn-secondary btn-sm w-full">
                        <i class="bi bi-plus mr-2"></i>
                        Buat Surat Baru
                    </button>
                    <button class="btn btn-outline btn-secondary btn-sm w-full">
                        <i class="bi bi-graph-up mr-2"></i>
                        Lihat Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="mt-16">
        <h3 class="text-2xl font-bold mb-6 flex items-center">
            <i class="bi bi-clock-history mr-3 text-primary"></i>
            Aktivitas Terbaru
        </h3>
        
        <div class="bg-base-100 rounded-lg shadow-lg border border-base-300 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr class="bg-base-200">
                            <th>Waktu</th>
                            <th>Jenis Surat</th>
                            <th>Aksi</th>
                            <th>User</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>10:30 WIB</td>
                            <td>
                                <div class="flex items-center">
                                    <i class="bi bi-send text-primary mr-2"></i>
                                    Surat Keluar
                                </div>
                            </td>
                            <td>Dibuat</td>
                            <td>
                                <div class="flex items-center">
                                    <div class="avatar placeholder mr-2">
                                        <div class="bg-primary text-white rounded-full w-8 h-8">
                                            <span class="text-xs">JD</span>
                                        </div>
                                    </div>
                                    John Doe
                                </div>
                            </td>
                            <td><span class="badge badge-success">Selesai</span></td>
                        </tr>
                        <tr>
                            <td>09:15 WIB</td>
                            <td>
                                <div class="flex items-center">
                                    <i class="bi bi-person-x text-warning mr-2"></i>
                                    SP.Kap
                                </div>
                            </td>
                            <td>Diperbarui</td>
                            <td>
                                <div class="flex items-center">
                                    <div class="avatar placeholder mr-2">
                                        <div class="bg-warning text-white rounded-full w-8 h-8">
                                            <span class="text-xs">AS</span>
                                        </div>
                                    </div>
                                    Admin System
                                </div>
                            </td>
                            <td><span class="badge badge-warning">Proses</span></td>
                        </tr>
                        <tr>
                            <td>08:45 WIB</td>
                            <td>
                                <div class="flex items-center">
                                    <i class="bi bi-archive text-info mr-2"></i>
                                    SP.Sita
                                </div>
                            </td>
                            <td>Diarsipkan</td>
                            <td>
                                <div class="flex items-center">
                                    <div class="avatar placeholder mr-2">
                                        <div class="bg-info text-white rounded-full w-8 h-8">
                                            <span class="text-xs">MS</span>
                                        </div>
                                    </div>
                                    Maria Silva
                                </div>
                            </td>
                            <td><span class="badge badge-info">Arsip</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .card:hover {
        transform: translateY(-4px);
    }
    
    .card .avatar {
        transition: all 0.3s ease;
    }
    
    .hero {
        background-image: 
            radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.1) 0%, transparent 50%);
    }
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>