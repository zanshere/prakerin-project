-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 08 Jul 2025 pada 14.00
-- Versi server: 9.3.0
-- Versi PHP: 8.3.23

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
-- Struktur dari tabel `password_reset_requests`
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
-- Dumping data untuk tabel `password_reset_requests`
--

INSERT INTO `password_reset_requests` (`id`, `user_id`, `username`, `email`, `request_date`, `status`, `reset_token`, `completed_by`, `completed_date`) VALUES
(1, 2, 'Ryuzen', 'ryuzenofficial@gmail.com', '2025-07-07 20:01:04', 'rejected', 'eea35ba4319b7b60c9b394d313809ee90fd73a8a28e6aedf9591348cfddb1f2e', 2, '2025-07-07 20:01:41'),
(2, 3, 'user', 'fantasyb224321@gmail.com', '2025-07-07 20:02:55', 'completed', '3ac6c7117edea4f08aaf203206df47cd523846fa1e8b1f36f7ba3bae6c3a23b3', 2, '2025-07-07 20:03:26'),
(3, 3, 'user', 'fantasyb224321@gmail.com', '2025-07-08 19:19:16', 'completed', '44aeb7484d407377bb217bfd0a0319a7869996b06d4227f186df44692a13025e', 2, '2025-07-08 19:19:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `remember_tokens`
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
-- Struktur dari tabel `users`
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
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `email`, `password`, `full_name`, `phone`, `nrp`, `rank`, `role`, `profile_image`, `created_at`, `updated_at`) VALUES
(2, 'zanshere', 'ryuzenofficial@gmail.com', '$2y$10$xDax.z9sgOYapUDDEZXfxeDjCFPO6hioXaASW9O6fbAfyWwHIBiEe', 'Admin', '085123658885', '12345678', 'AKP', 'admin', 'profile_686d23c1533bd.png', '2025-07-01 12:11:07', '2025-07-08 13:57:21'),
(3, 'user', 'fantasyb224321@gmail.com', '$2y$10$ueO4Mb4n4b1fM3oRnBQgdub6AIlc.Ot7n39xpX.jVt8KffKKhmavu', 'users', '081949362067', '19720384', 'AKP', 'user', 'profil.jpg', '2025-07-07 13:02:31', '2025-07-08 12:19:59'),
(4, 'test', 'test@gmail.com', '$2y$10$zHokqAF1NNffkxR9aKnujuSo/C1yLD/ZrzXumZ7IHGxC6to3ICOfG', 'tester', '081234567890', '01293874', 'AKP', 'user', 'profil.jpg', '2025-07-08 13:26:00', '2025-07-08 13:26:00');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD CONSTRAINT `password_reset_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`);

--
-- Ketidakleluasaan untuk tabel `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
