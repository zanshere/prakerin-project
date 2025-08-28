-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 28, 2025 at 01:03 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_reskrim`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang_sitaan`
--

CREATE TABLE `barang_sitaan` (
  `id_barang` int NOT NULL,
  `id_spsita` int NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `merk` varchar(100) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `warna` varchar(50) DEFAULT NULL,
  `ukuran` varchar(50) DEFAULT NULL,
  `jumlah` int NOT NULL DEFAULT '1',
  `satuan` varchar(50) DEFAULT 'unit',
  `kondisi` enum('Baik','Rusak','Hilang') DEFAULT 'Baik',
  `keterangan` text,
  `foto_barang` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id_log` int NOT NULL,
  `id_user` int NOT NULL,
  `jenis_surat` enum('surat_keluar','sp_kap','sp_han','sp_sita','sp_gas') NOT NULL,
  `id_surat` int NOT NULL,
  `aksi` enum('create','update','delete','view','print') NOT NULL,
  `keterangan` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id_log`, `id_user`, `jenis_surat`, `id_surat`, `aksi`, `keterangan`, `created_at`) VALUES
(1, 3, 'sp_gas', 1, 'create', 'Membuat surat tugas: SP.Gas/001/RESKRIM/2025', '2025-08-27 03:09:21'),
(2, 3, 'sp_gas', 2, 'create', 'Membuat surat tugas: SP.Gas/002/RESKRIM/2025', '2025-08-27 03:10:49'),
(3, 3, 'sp_gas', 3, 'create', 'Membuat surat tugas: SP.Gas/003/RESKRIM/2025', '2025-08-27 03:28:20'),
(4, 3, 'sp_gas', 4, 'create', 'Membuat surat tugas: SP.Gas/004/RESKRIM/2025', '2025-08-27 03:29:04');

-- --------------------------------------------------------

--
-- Table structure for table `nomor_surat`
--

CREATE TABLE `nomor_surat` (
  `id_nomor` int NOT NULL,
  `jenis_surat` varchar(50) NOT NULL,
  `tahun` year NOT NULL,
  `nomor_terakhir` int NOT NULL DEFAULT '0',
  `format_nomor` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nomor_surat`
--

INSERT INTO `nomor_surat` (`id_nomor`, `jenis_surat`, `tahun`, `nomor_terakhir`, `format_nomor`, `created_at`, `updated_at`) VALUES
(6, 'sp_gas', 2025, 4, 'SP.Gas/{nomor}/RESKRIM/{tahun}', '2025-08-27 03:09:20', '2025-08-27 03:29:04');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_requests`
--

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

--
-- Dumping data for table `password_reset_requests`
--

INSERT INTO `password_reset_requests` (`id`, `user_id`, `username`, `email`, `request_date`, `status`, `reset_token`, `completed_by`, `completed_date`) VALUES
(1, 2, 'Ryuzen', 'ryuzenofficial@gmail.com', '2025-07-07 20:01:04', 'rejected', 'eea35ba4319b7b60c9b394d313809ee90fd73a8a28e6aedf9591348cfddb1f2e', 2, '2025-07-07 20:01:41'),
(2, 3, 'user', 'fantasyb224321@gmail.com', '2025-07-07 20:02:55', 'completed', '3ac6c7117edea4f08aaf203206df47cd523846fa1e8b1f36f7ba3bae6c3a23b3', 2, '2025-07-07 20:03:26'),
(3, 3, 'user', 'fantasyb224321@gmail.com', '2025-07-08 19:19:16', 'completed', '44aeb7484d407377bb217bfd0a0319a7869996b06d4227f186df44692a13025e', 2, '2025-07-08 19:19:59');

-- --------------------------------------------------------

--
-- Table structure for table `personel_tugas`
--

CREATE TABLE `personel_tugas` (
  `id_personel` int NOT NULL,
  `id_spgas` int NOT NULL,
  `id_user` int NOT NULL,
  `jabatan_dalam_tugas` varchar(100) NOT NULL,
  `peran` enum('Ketua','Anggota','Pengawas') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `surat_keluar`
--

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

--
-- Table structure for table `surat_penangkapan`
--

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

--
-- Table structure for table `surat_penyitaan`
--

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

--
-- Table structure for table `surat_tugas`
--

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
  `keterangan` text,
  `created_by` int NOT NULL,
  `status` enum('aktif','selesai','dibatalkan') DEFAULT 'aktif',
  `file_attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `surat_tugas`
--

INSERT INTO `surat_tugas` (`id_spgas`, `nomor_spgas`, `tanggal_spgas`, `jenis_tugas`, `tujuan_tugas`, `tempat_tugas`, `tanggal_mulai`, `tanggal_selesai`, `lama_tugas`, `keterangan`, `created_by`, `status`, `file_attachment`, `created_at`, `updated_at`) VALUES
(1, 'SP.Gas/001/RESKRIM/2025', '2025-08-27', '1', '1', '1', '2025-08-27', '2025-08-30', 1, '1', 3, 'aktif', NULL, '2025-08-27 03:09:21', '2025-08-27 03:09:21'),
(2, 'SP.Gas/002/RESKRIM/2025', '2025-08-27', '1', '1', '1', '2025-08-27', '2025-08-30', 1, '1', 3, 'aktif', NULL, '2025-08-27 03:10:49', '2025-08-27 03:10:49'),
(3, 'SP.Gas/003/RESKRIM/2025', '2025-08-27', '1', '1', '1', '2025-08-27', '2025-08-30', 1, '1', 3, 'aktif', NULL, '2025-08-27 03:28:20', '2025-08-27 03:28:20'),
(4, 'SP.Gas/004/RESKRIM/2025', '2025-08-03', '1', '1', '1', '2025-08-13', '2025-08-15', 6, '1', 3, 'aktif', NULL, '2025-08-27 03:29:04', '2025-08-27 03:29:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `nrp` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `rank` enum('AKP','IPTU','IPDA','AIPTU','AIPDA','BRIPKA','BRIGPOL','BRIPTU','BRIPDA') NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `profile_image` varchar(255) DEFAULT 'profil.jpg',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `email`, `password`, `full_name`, `phone`, `nrp`, `rank`, `role`, `profile_image`, `created_at`, `updated_at`) VALUES
(2, 'zanshere', 'ryuzenofficial@gmail.com', '$2y$10$xDax.z9sgOYapUDDEZXfxeDjCFPO6hioXaASW9O6fbAfyWwHIBiEe', 'Admin', '085123658885', '12345678', 'AKP', 'admin', 'profile_686d23c1533bd.png', '2025-07-01 12:11:07', '2025-07-08 13:57:21'),
(3, 'user', 'fantasyb224321@gmail.com', '$2y$10$LvT7it377FIPPnKfeaX1feOgL1HzpSl1J.Vn7NSuzFN87E/ZFCZ8S', 'users', '081949362067', '19720384', 'AKP', 'user', 'profil.jpg', '2025-07-07 13:02:31', '2025-08-27 01:23:40'),
(4, 'test', 'test@gmail.com', '$2y$10$zHokqAF1NNffkxR9aKnujuSo/C1yLD/ZrzXumZ7IHGxC6to3ICOfG', 'tester', '081234567890', '01293874', 'AKP', 'user', 'profil.jpg', '2025-07-08 13:26:00', '2025-07-08 13:26:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang_sitaan`
--
ALTER TABLE `barang_sitaan`
  ADD PRIMARY KEY (`id_barang`),
  ADD KEY `id_spsita` (`id_spsita`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `nomor_surat`
--
ALTER TABLE `nomor_surat`
  ADD PRIMARY KEY (`id_nomor`),
  ADD UNIQUE KEY `jenis_tahun` (`jenis_surat`,`tahun`);

--
-- Indexes for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `personel_tugas`
--
ALTER TABLE `personel_tugas`
  ADD PRIMARY KEY (`id_personel`),
  ADD KEY `id_spgas` (`id_spgas`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD PRIMARY KEY (`id_surat`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `surat_penangkapan`
--
ALTER TABLE `surat_penangkapan`
  ADD PRIMARY KEY (`id_spkap`),
  ADD UNIQUE KEY `nomor_spkap` (`nomor_spkap`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `surat_penyitaan`
--
ALTER TABLE `surat_penyitaan`
  ADD PRIMARY KEY (`id_spsita`),
  ADD UNIQUE KEY `nomor_spsita` (`nomor_spsita`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `surat_tugas`
--
ALTER TABLE `surat_tugas`
  ADD PRIMARY KEY (`id_spgas`),
  ADD UNIQUE KEY `nomor_spgas` (`nomor_spgas`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang_sitaan`
--
ALTER TABLE `barang_sitaan`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `nomor_surat`
--
ALTER TABLE `nomor_surat`
  MODIFY `id_nomor` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `personel_tugas`
--
ALTER TABLE `personel_tugas`
  MODIFY `id_personel` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  MODIFY `id_surat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `surat_penangkapan`
--
ALTER TABLE `surat_penangkapan`
  MODIFY `id_spkap` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `surat_penyitaan`
--
ALTER TABLE `surat_penyitaan`
  MODIFY `id_spsita` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `surat_tugas`
--
ALTER TABLE `surat_tugas`
  MODIFY `id_spgas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang_sitaan`
--
ALTER TABLE `barang_sitaan`
  ADD CONSTRAINT `barang_sitaan_ibfk_1` FOREIGN KEY (`id_spsita`) REFERENCES `surat_penyitaan` (`id_spsita`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD CONSTRAINT `password_reset_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD CONSTRAINT `surat_keluar_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `surat_penangkapan`
--
ALTER TABLE `surat_penangkapan`
  ADD CONSTRAINT `surat_penangkapan_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `surat_penyitaan`
--
ALTER TABLE `surat_penyitaan`
  ADD CONSTRAINT `surat_penyitaan_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `surat_tugas`
--
ALTER TABLE `surat_tugas`
  ADD CONSTRAINT `surat_tugas_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
