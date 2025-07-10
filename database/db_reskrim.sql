-- Database Sistem Surat Unit Reskrim
-- Dibuat berdasarkan formulir yang ada dan kebutuhan sistem

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Database: `db_reskrim`

-- --------------------------------------------------------

-- Tabel users
CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `nrp` varchar(8) NOT NULL,
  `rank` enum('AKP','IPTU','IPDA','AIPTU','AIPDA','BRIPKA','BRIGPOL','BRIPTU','BRIPDA','KOMPOL','AKBP') NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `profile_image` varchar(255) DEFAULT 'profil.jpg',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Tabel surat_keluar
CREATE TABLE `surat_keluar` (
  `id_surat` int NOT NULL,
  `nomor_surat` varchar(100) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `perihal` varchar(255) NOT NULL,
  `tujuan` varchar(255) NOT NULL,
  `lampiran` varchar(100) DEFAULT NULL,
  `isi_surat` text NOT NULL,
  `file_surat` varchar(255) DEFAULT NULL,
  `created_by` int NOT NULL,
  `status` enum('draft','dikirim','arsip') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Tabel surat_penangkapan (SP.Kap)
CREATE TABLE `surat_penangkapan` (
  `id_spkap` int NOT NULL,
  `nomor_spkap` varchar(100) NOT NULL,
  `tanggal_spkap` date NOT NULL,
  `nama_tersangka` varchar(255) NOT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `tempat_lahir` varchar(100) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `umur` int NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `kebangsaan` varchar(100) DEFAULT 'Indonesia',
  `agama` enum('Islam','Kristen','Katolik','Hindu','Buddha','Konghucu') NOT NULL,
  `pekerjaan` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `pasal_yang_disangkakan` text NOT NULL,
  `uraian_singkat_perkara` text NOT NULL,
  `masa_berlaku` date NOT NULL,
  `created_by` int NOT NULL,
  `status` enum('aktif','expired','dibatalkan') DEFAULT 'aktif',
  `file_attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Tabel surat_penahanan (SP.Han)
CREATE TABLE `surat_penahanan` (
  `id_sphan` int NOT NULL,
  `nomor_sphan` varchar(100) NOT NULL,
  `tanggal_sphan` date NOT NULL,
  `nama_tersangka` varchar(255) NOT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `tempat_lahir` varchar(100) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `umur` int NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `kebangsaan` varchar(100) DEFAULT 'Indonesia',
  `agama` enum('Islam','Kristen','Katolik','Hindu','Buddha','Konghucu') NOT NULL,
  `pekerjaan` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `pasal_yang_disangkakan` text NOT NULL,
  `uraian_singkat_perkara` text NOT NULL,
  `jenis_penahanan` enum('Rumah Tahanan','Tahanan Kota','Tahanan Rumah') NOT NULL,
  `lama_penahanan` int NOT NULL COMMENT 'dalam hari',
  `tanggal_mulai` date NOT NULL,
  `tanggal_berakhir` date NOT NULL,
  `tempat_penahanan` varchar(255) NOT NULL,
  `created_by` int NOT NULL,
  `status` enum('aktif','berakhir','diperpanjang','dibatalkan') DEFAULT 'aktif',
  `file_attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Tabel surat_penyitaan (SP.Sita)
CREATE TABLE `surat_penyitaan` (
  `id_spsita` int NOT NULL,
  `nomor_spsita` varchar(100) NOT NULL,
  `tanggal_spsita` date NOT NULL,
  `nama_tersangka` varchar(255) NOT NULL,
  `alamat_tersangka` text NOT NULL,
  `pasal_yang_disangkakan` text NOT NULL,
  `uraian_singkat_perkara` text NOT NULL,
  `created_by` int NOT NULL,
  `status` enum('aktif','selesai','dibatalkan') DEFAULT 'aktif',
  `file_attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Tabel barang_sitaan (detail barang untuk SP.Sita)
CREATE TABLE `barang_sitaan` (
  `id_barang` int NOT NULL,
  `id_spsita` int NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `merk` varchar(100) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `warna` varchar(50) DEFAULT NULL,
  `ukuran` varchar(50) DEFAULT NULL,
  `jumlah` int NOT NULL DEFAULT 1,
  `satuan` varchar(50) DEFAULT 'unit',
  `kondisi` enum('Baik','Rusak','Hilang') DEFAULT 'Baik',
  `keterangan` text DEFAULT NULL,
  `foto_barang` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Tabel surat_tugas (SP.Gas)
CREATE TABLE `surat_tugas` (
  `id_spgas` int NOT NULL,
  `nomor_spgas` varchar(100) NOT NULL,
  `tanggal_spgas` date NOT NULL,
  `jenis_tugas` varchar(255) NOT NULL,
  `tujuan_tugas` text NOT NULL,
  `tempat_tugas` varchar(255) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `lama_tugas` int NOT NULL COMMENT 'dalam hari',
  `keterangan` text DEFAULT NULL,
  `created_by` int NOT NULL,
  `status` enum('aktif','selesai','dibatalkan') DEFAULT 'aktif',
  `file_attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Tabel personel_tugas
CREATE TABLE `personel_tugas` (
  `id_personel` int NOT NULL,
  `id_spgas` int NOT NULL,
  `id_user` int NOT NULL,
  `jabatan_dalam_tugas` varchar(100) NOT NULL,
  `peran` enum('Penyidik','Penyidik Pembantu') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Tabel log_aktivitas (untuk tracking aktivitas sistem)
CREATE TABLE `log_aktivitas` (
  `id_log` int NOT NULL,
  `id_user` int NOT NULL,
  `jenis_surat` enum('surat_keluar','sp_kap','sp_han','sp_sita','sp_gas') NOT NULL,
  `id_surat` int NOT NULL,
  `aksi` enum('create','update','delete','view','print') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Tabel password_reset_requests
CREATE TABLE `password_reset_requests` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `request_date` datetime NOT NULL,
  `status` enum('pending','completed','rejected') DEFAULT 'pending',
  `reset_token` varchar(64) DEFAULT NULL,
  `completed_by` int DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Tabel remember_tokens
CREATE TABLE `remember_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Tabel nomor_surat (untuk tracking nomor surat)
CREATE TABLE `nomor_surat` (
  `id_nomor` int NOT NULL,
  `jenis_surat` varchar(50) NOT NULL,
  `tahun` year NOT NULL,
  `nomor_terakhir` int NOT NULL DEFAULT 0,
  `format_nomor` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- INSERT DATA AWAL

-- Data users
INSERT INTO `users` (`id_user`, `username`, `email`, `password`, `full_name`, `phone`, `nrp`, `rank`, `role`, `profile_image`, `created_at`, `updated_at`) VALUES
(2, 'zanshere', 'ryuzenofficial@gmail.com', '$2y$10$xDax.z9sgOYapUDDEZXfxeDjCFPO6hioXaASW9O6fbAfyWwHIBiEe', 'Admin', '085123658885', '12345678', 'AKP', 'admin', 'profile_686d23c1533bd.png', '2025-07-01 12:11:07', '2025-07-08 13:57:21'),
(3, 'user', 'fantasyb224321@gmail.com', '$2y$10$ueO4Mb4n4b1fM3oRnBQgdub6AIlc.Ot7n39xpX.jVt8KffKKhmavu', 'users', '081949362067', '19720384', 'AKP', 'user', 'profil.jpg', '2025-07-07 13:02:31', '2025-07-08 12:19:59');

-- Data password_reset_requests
INSERT INTO `password_reset_requests` (`id`, `user_id`, `username`, `email`, `request_date`, `status`, `reset_token`, `completed_by`, `completed_date`) VALUES
(1, 2, 'Ryuzen', 'ryuzenofficial@gmail.com', '2025-07-07 20:01:04', 'rejected', 'eea35ba4319b7b60c9b394d313809ee90fd73a8a28e6aedf9591348cfddb1f2e', 2, '2025-07-07 20:01:41'),
(2, 3, 'user', 'fantasyb224321@gmail.com', '2025-07-07 20:02:55', 'completed', '3ac6c7117edea4f08aaf203206df47cd523846fa1e8b1f36f7ba3bae6c3a23b3', 2, '2025-07-07 20:03:26'),
(3, 3, 'user', 'fantasyb224321@gmail.com', '2025-07-08 19:19:16', 'completed', '44aeb7484d407377bb217bfd0a0319a7869996b06d4227f186df44692a13025e', 2, '2025-07-08 19:19:59');

-- --------------------------------------------------------

-- INDEXES

-- Indexes for table `users`
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `nrp` (`nrp`);

-- Indexes for table `surat_keluar`
ALTER TABLE `surat_keluar`
  ADD PRIMARY KEY (`id_surat`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD KEY `created_by` (`created_by`);

-- Indexes for table `surat_penangkapan`
ALTER TABLE `surat_penangkapan`
  ADD PRIMARY KEY (`id_spkap`),
  ADD UNIQUE KEY `nomor_spkap` (`nomor_spkap`),
  ADD KEY `created_by` (`created_by`);

-- Indexes for table `surat_penahanan`
ALTER TABLE `surat_penahanan`
  ADD PRIMARY KEY (`id_sphan`),
  ADD UNIQUE KEY `nomor_sphan` (`nomor_sphan`),
  ADD KEY `created_by` (`created_by`);

-- Indexes for table `surat_penyitaan`
ALTER TABLE `surat_penyitaan`
  ADD PRIMARY KEY (`id_spsita`),
  ADD UNIQUE KEY `nomor_spsita` (`nomor_spsita`),
  ADD KEY `created_by` (`created_by`);

-- Indexes for table `barang_sitaan`
ALTER TABLE `barang_sitaan`
  ADD PRIMARY KEY (`id_barang`),
  ADD KEY `id_spsita` (`id_spsita`);

-- Indexes for table `surat_tugas`
ALTER TABLE `surat_tugas`
  ADD PRIMARY KEY (`id_spgas`),
  ADD UNIQUE KEY `nomor_spgas` (`nomor_spgas`),
  ADD KEY `created_by` (`created_by`);

-- Indexes for table `personel_tugas`
ALTER TABLE `personel_tugas`
  ADD PRIMARY KEY (`id_personel`),
  ADD KEY `id_spgas` (`id_spgas`),
  ADD KEY `id_user` (`id_user`);

-- Indexes for table `log_aktivitas`
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

-- Indexes for table `password_reset_requests`
ALTER TABLE `password_reset_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

-- Indexes for table `remember_tokens`
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

-- Indexes for table `nomor_surat`
ALTER TABLE `nomor_surat`
  ADD PRIMARY KEY (`id_nomor`),
  ADD UNIQUE KEY `jenis_tahun` (`jenis_surat`,`tahun`);

-- --------------------------------------------------------

-- AUTO_INCREMENT

ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `surat_keluar`
  MODIFY `id_surat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `surat_penangkapan`
  MODIFY `id_spkap` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `surat_penahanan`
  MODIFY `id_sphan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `surat_penyitaan`
  MODIFY `id_spsita` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `barang_sitaan`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `surat_tugas`
  MODIFY `id_spgas` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `personel_tugas`
  MODIFY `id_personel` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `log_aktivitas`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `password_reset_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `remember_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `nomor_surat`
  MODIFY `id_nomor` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- --------------------------------------------------------

-- FOREIGN KEY CONSTRAINTS

-- Constraints for table `surat_keluar`
ALTER TABLE `surat_keluar`
  ADD CONSTRAINT `surat_keluar_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

-- Constraints for table `surat_penangkapan`
ALTER TABLE `surat_penangkapan`
  ADD CONSTRAINT `surat_penangkapan_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

-- Constraints for table `surat_penahanan`
ALTER TABLE `surat_penahanan`
  ADD CONSTRAINT `surat_penahanan_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

-- Constraints for table `surat_penyitaan`
ALTER TABLE `surat_penyitaan`
  ADD CONSTRAINT `surat_penyitaan_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

-- Constraints for table `barang_sitaan`
ALTER TABLE `barang_sitaan`
  ADD CONSTRAINT `barang_sitaan_ibfk_1` FOREIGN KEY (`id_spsita`) REFERENCES `surat_penyitaan` (`id_spsita`) ON DELETE CASCADE;

-- Constraints for table `surat_tugas`
ALTER TABLE `surat_tugas`
  ADD CONSTRAINT `surat_tugas_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

-- Constraints for table `personel_tugas`
ALTER TABLE `personel_tugas`
  ADD CONSTRAINT `personel_tugas_ibfk_1` FOREIGN KEY (`id_spgas`) REFERENCES `surat_tugas` (`id_spgas`) ON DELETE CASCADE,
  ADD CONSTRAINT `personel_tugas_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

-- Constraints for table `log_aktivitas`
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

-- Constraints for table `password_reset_requests`
ALTER TABLE `password_reset_requests`
  ADD CONSTRAINT `password_reset_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

-- Constraints for table `remember_tokens`
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;