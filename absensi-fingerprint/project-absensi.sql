-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql8.serv00.com
-- Waktu pembuatan: 27 Okt 2024 pada 08.33
-- Versi server: 8.0.39
-- Versi PHP: 8.1.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `m6780_project-absensi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(6, 'sultan', '$2y$10$RT852FD1Py9wQ5EOdM2hleiZBGa3EAv8ww29vQszwLUOn4XFn6f/y'),
(7, 'revanza', '$2y$10$qveGYPQg.fFrilRGY8kVouHYHvqLXpevdco6pp4Gf/Hk/dXMG9j9K');

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_absensi`
--

CREATE TABLE `log_absensi` (
  `id` int NOT NULL,
  `fingerprint_id` int NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `log_absensi`
--

INSERT INTO `log_absensi` (`id`, `fingerprint_id`, `waktu`) VALUES
(113, 1, '2024-10-27 07:16:43'),
(114, 1, '2024-10-27 07:16:53'),
(115, 1, '2024-10-27 07:17:07'),
(116, 1, '2024-10-27 07:17:19'),
(117, 2, '2024-10-27 07:17:29'),
(118, 2, '2024-10-27 07:17:34'),
(119, 1, '2024-10-27 07:17:50'),
(120, 2, '2024-10-27 07:18:14'),
(121, 2, '2024-10-27 07:18:27'),
(122, 2, '2024-10-27 07:18:34'),
(123, 2, '2024-10-27 07:19:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peserta`
--

CREATE TABLE `peserta` (
  `id` int NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `fingerprint_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peserta`
--

INSERT INTO `peserta` (`id`, `nama`, `fingerprint_id`) VALUES
(22, 'PESERTA 1', 1),
(25, 'PESERTA 2', 2);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `log_absensi`
--
ALTER TABLE `log_absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fingerprint_id` (`fingerprint_id`);

--
-- Indeks untuk tabel `peserta`
--
ALTER TABLE `peserta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fingerprint_id` (`fingerprint_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `log_absensi`
--
ALTER TABLE `log_absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT untuk tabel `peserta`
--
ALTER TABLE `peserta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `log_absensi`
--
ALTER TABLE `log_absensi`
  ADD CONSTRAINT `log_absensi_ibfk_1` FOREIGN KEY (`fingerprint_id`) REFERENCES `peserta` (`fingerprint_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
