-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 23, 2026 at 02:52 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_uas_pbo_trpl1b_lanjuwidhunuastuti`
--

-- --------------------------------------------------------

--
-- Table structure for table `tabel_karyawan`
--

CREATE TABLE `tabel_karyawan` (
  `id_karyawan` int NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `departemen` varchar(100) NOT NULL,
  `hari_kerja_masuk` varchar(50) NOT NULL,
  `gaji_dasar_per_hari` int NOT NULL,
  `jenis_karyawan` enum('Kontrak','Tetap','Magang') NOT NULL,
  `durasi_kontrak_bulan` int DEFAULT NULL,
  `agensi_penyalur` varchar(100) DEFAULT NULL,
  `tunjangan_kesehatan` int DEFAULT NULL,
  `opsi_saham_id` varchar(50) DEFAULT NULL,
  `uang_saku_bulanan` int DEFAULT NULL,
  `sertifikat_kampus_merdeka` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tabel_karyawan`
--

INSERT INTO `tabel_karyawan` (`id_karyawan`, `nama_karyawan`, `departemen`, `hari_kerja_masuk`, `gaji_dasar_per_hari`, `jenis_karyawan`, `durasi_kontrak_bulan`, `agensi_penyalur`, `tunjangan_kesehatan`, `opsi_saham_id`, `uang_saku_bulanan`, `sertifikat_kampus_merdeka`) VALUES
(1, 'Andi', 'IT', 'Senin-Jumat', 150000, 'Kontrak', 12, 'PT Maju', NULL, NULL, NULL, NULL),
(2, 'Budi', 'HRD', 'Senin-Sabtu', 130000, 'Kontrak', 6, 'PT Sumber', NULL, NULL, NULL, NULL),
(3, 'Citra', 'Finance', 'Senin-Jumat', 140000, 'Kontrak', 18, 'PT Abadi', NULL, NULL, NULL, NULL),
(4, 'Deni', 'Marketing', 'Senin-Sabtu', 125000, 'Kontrak', 9, 'PT Mitra', NULL, NULL, NULL, NULL),
(5, 'Eka', 'IT', 'Senin-Jumat', 160000, 'Kontrak', 24, 'PT Digital', NULL, NULL, NULL, NULL),
(6, 'Fajar', 'Operasional', 'Senin-Sabtu', 120000, 'Kontrak', 3, 'PT Solusi', NULL, NULL, NULL, NULL),
(7, 'Gita', 'IT', 'Senin-Jumat', 200000, 'Tetap', NULL, NULL, 1000000, 'SAHAM01', NULL, NULL),
(8, 'Hendra', 'Finance', 'Senin-Jumat', 190000, 'Tetap', NULL, NULL, 900000, 'SAHAM02', NULL, NULL),
(9, 'Indah', 'HRD', 'Senin-Sabtu', 180000, 'Tetap', NULL, NULL, 800000, 'SAHAM03', NULL, NULL),
(10, 'Joko', 'Marketing', 'Senin-Jumat', 175000, 'Tetap', NULL, NULL, 850000, 'SAHAM04', NULL, NULL),
(11, 'Karin', 'IT', 'Senin-Jumat', 220000, 'Tetap', NULL, NULL, 1200000, 'SAHAM05', NULL, NULL),
(12, 'Lukman', 'Operasional', 'Senin-Sabtu', 170000, 'Tetap', NULL, NULL, 750000, 'SAHAM06', NULL, NULL),
(13, 'Maya', 'Finance', 'Senin-Jumat', 185000, 'Tetap', NULL, NULL, 950000, 'SAHAM07', NULL, NULL),
(14, 'Nanda', 'IT', 'Senin-Jumat', 50000, 'Magang', NULL, NULL, NULL, NULL, 1500000, 'Kampus Merdeka'),
(15, 'Olivia', 'HRD', 'Senin-Jumat', 45000, 'Magang', NULL, NULL, NULL, NULL, 1200000, 'Kampus Merdeka'),
(16, 'Putri', 'Finance', 'Senin-Sabtu', 40000, 'Magang', NULL, NULL, NULL, NULL, 1000000, 'Kampus Merdeka'),
(17, 'Rizky', 'Marketing', 'Senin-Jumat', 55000, 'Magang', NULL, NULL, NULL, NULL, 1300000, 'Kampus Merdeka'),
(18, 'Salsa', 'IT', 'Senin-Jumat', 60000, 'Magang', NULL, NULL, NULL, NULL, 1500000, 'Kampus Merdeka'),
(19, 'Tio', 'Operasional', 'Senin-Sabtu', 35000, 'Magang', NULL, NULL, NULL, NULL, 900000, 'Kampus Merdeka'),
(20, 'Vina', 'Finance', 'Senin-Jumat', 50000, 'Magang', NULL, NULL, NULL, NULL, 1100000, 'Kampus Merdeka');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tabel_karyawan`
--
ALTER TABLE `tabel_karyawan`
  ADD PRIMARY KEY (`id_karyawan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tabel_karyawan`
--
ALTER TABLE `tabel_karyawan`
  MODIFY `id_karyawan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
