-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 16, 2026 at 07:39 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ekspedisi_logistik`
--

-- --------------------------------------------------------

--
-- Table structure for table `armada`
--

CREATE TABLE `armada` (
  `id_armada` int NOT NULL,
  `id_armada_code` varchar(50) NOT NULL,
  `kapasitas_maksimal_kg` decimal(10,2) DEFAULT NULL,
  `status_kelaikan` enum('Laik','Tidak Laik') DEFAULT 'Laik',
  `biaya_operasional_dasar` decimal(10,2) DEFAULT NULL,
  `jenis_armada` enum('TrukDarat','KapalLaut','PesawatKargo') DEFAULT NULL,
  `jumlah_roda` int DEFAULT NULL,
  `rute_tol` text,
  `nama_dermaga` varchar(100) DEFAULT NULL,
  `jenis_kontainer` varchar(50) DEFAULT NULL,
  `batas_ketinggian` decimal(10,2) DEFAULT NULL,
  `izin_penerbangan_khusus` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `armada`
--

INSERT INTO `armada` (`id_armada`, `id_armada_code`, `kapasitas_maksimal_kg`, `status_kelaikan`, `biaya_operasional_dasar`, `jenis_armada`, `jumlah_roda`, `rute_tol`, `nama_dermaga`, `jenis_kontainer`, `batas_ketinggian`, `izin_penerbangan_khusus`, `created_at`) VALUES
(1, 'TRK-001', 1.00, 'Laik', 0.10, 'TrukDarat', 4, 'sdfjsr', '', '', 0.00, '', '2026-06-16 05:44:14'),
(3, 'TRK-004', 1.00, 'Laik', 24444.00, 'PesawatKargo', 4, '', '', '', 0.04, 'sdfsrgf', '2026-06-16 05:45:51');

-- --------------------------------------------------------

--
-- Table structure for table `kargo`
--

CREATE TABLE `kargo` (
  `id_kargo` int NOT NULL,
  `id_resi` varchar(50) NOT NULL,
  `pengirim` varchar(100) DEFAULT NULL,
  `kota_tujuan` varchar(100) DEFAULT NULL,
  `berat_barang` decimal(10,2) DEFAULT NULL,
  `tarif_dasar_per_kg` decimal(10,2) DEFAULT NULL,
  `jenis_kargo` enum('Reguler','BahanKimia','PecahBelah') DEFAULT NULL,
  `jenis_paket` varchar(50) DEFAULT NULL,
  `estimasi_hari` int DEFAULT NULL,
  `tingkat_bahaya` varchar(10) DEFAULT NULL,
  `jenis_sertifikasi` varchar(100) DEFAULT NULL,
  `ketebalan_bubble_wrap` decimal(5,2) DEFAULT NULL,
  `biaya_asuransi_wajib` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int NOT NULL,
  `id_pelanggan_code` varchar(50) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `total_transaksi_bulan_ini` decimal(15,2) DEFAULT NULL,
  `poin_reward` int DEFAULT NULL,
  `jenis_pelanggan` enum('Retail','VIP','MitraKorporat') DEFAULT NULL,
  `promo_voucher` varchar(100) DEFAULT NULL,
  `batas_berat_max` decimal(10,2) DEFAULT NULL,
  `akses_layanan_prioritas` tinyint(1) DEFAULT NULL,
  `personal_assistant` varchar(100) DEFAULT NULL,
  `npwp_perusahaan` varchar(50) DEFAULT NULL,
  `batas_tempo_pembayaran` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `id_pelanggan_code`, `nama_lengkap`, `total_transaksi_bulan_ini`, `poin_reward`, `jenis_pelanggan`, `promo_voucher`, `batas_berat_max`, `akses_layanan_prioritas`, `personal_assistant`, `npwp_perusahaan`, `batas_tempo_pembayaran`, `created_at`) VALUES
(1, 'PLG004', 'INDSFI', 0.00, 100, 'VIP', NULL, NULL, 1, 'KERTHUITYOIY ', NULL, NULL, '2026-06-15 22:08:02');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int NOT NULL,
  `id_transaksi` varchar(50) NOT NULL,
  `total_tagihan` decimal(15,2) DEFAULT NULL,
  `status_lunas` enum('Lunas','Belum Lunas') DEFAULT 'Belum Lunas',
  `waktu_pembayaran` datetime DEFAULT NULL,
  `metode_pembayaran` enum('CashOnDelivery','TransferBank','EWallet') DEFAULT NULL,
  `biaya_penanganan_kurir` decimal(10,2) DEFAULT NULL,
  `batas_maksimal_nominal` decimal(15,2) DEFAULT NULL,
  `kode_virtual_account` varchar(50) DEFAULT NULL,
  `nama_bank` varchar(50) DEFAULT NULL,
  `nomor_hp` varchar(20) DEFAULT NULL,
  `biaya_layanan_aplikasi` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id_staff` int NOT NULL,
  `id_staff_code` varchar(50) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `gaji_pokok` decimal(10,2) DEFAULT NULL,
  `jam_kerja` int DEFAULT NULL,
  `jenis_staff` enum('SupirTruk','AdminGudang','KurirMotor') DEFAULT NULL,
  `nomor_sim_b` varchar(50) DEFAULT NULL,
  `uang_makan_jalan` decimal(10,2) DEFAULT NULL,
  `shift_kerja` varchar(20) DEFAULT NULL,
  `zona_gudang` varchar(50) DEFAULT NULL,
  `plat_nomor_motor` varchar(20) DEFAULT NULL,
  `area_cakupan` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id_staff`, `id_staff_code`, `nama_lengkap`, `gaji_pokok`, `jam_kerja`, `jenis_staff`, `nomor_sim_b`, `uang_makan_jalan`, `shift_kerja`, `zona_gudang`, `plat_nomor_motor`, `area_cakupan`, `created_at`) VALUES
(1, 'ftju4356', 'fgkhtghjk', '3465467.00', 766, 'KurirMotor', NULL, NULL, NULL, NULL, 'B 3455 PP', 'Jaksel', '2026-06-16 03:53:58'),
(2, 'Stf388', 'Jumlina', '2300000.00', 144, 'AdminGudang', NULL, NULL, 'Siang', 'Maos', NULL, NULL, '2026-06-16 03:59:46'),
(3, 'STF001', 'Ahmad Fahrudin', '4500000.00', 160, 'SupirTruk', 'SIMB-123456', '50000.00', 'Pagi', 'Zona A', 'B 1234 ABC', 'Jabodetabek', '2026-06-16 07:21:34'),
(4, 'STF002', 'Budi Santoso', '4200000.00', 144, 'SupirTruk', 'SIMB-234567', '45000.00', 'Pagi', 'Zona B', 'B 5678 DEF', 'Cirebon - Indramayu', '2026-06-16 07:21:34'),
(5, 'STF003', 'Candra Wijaya', '4800000.00', 168, 'SupirTruk', 'SIMB-345678', '55000.00', 'Malam', 'Zona C', 'B 9012 GHI', 'Bandung Raya', '2026-06-16 07:21:34'),
(6, 'STF004', 'Dian Purnama', '4300000.00', 152, 'SupirTruk', 'SIMB-456789', '47000.00', 'Pagi', 'Zona A', 'B 3456 JKL', 'Bekasi - Karawang', '2026-06-16 07:21:34'),
(7, 'STF005', 'Eko Prasetyo', '4600000.00', 160, 'SupirTruk', 'SIMB-567890', '52000.00', 'Siang', 'Zona D', 'B 7890 MNO', 'Semarang Raya', '2026-06-16 07:21:34'),
(8, 'STF006', 'Fitri Handayani', '3500000.00', 160, 'AdminGudang', NULL, NULL, 'Pagi', 'Zona A', NULL, NULL, '2026-06-16 07:21:34'),
(9, 'STF007', 'Gilang Ramadhan', '3700000.00', 168, 'AdminGudang', NULL, NULL, 'Malam', 'Zona B', NULL, NULL, '2026-06-16 07:21:34'),
(10, 'STF008', 'Hesti Puspita', '3400000.00', 152, 'AdminGudang', NULL, NULL, 'Pagi', 'Zona C', NULL, NULL, '2026-06-16 07:21:34'),
(11, 'STF009', 'Indra Kusuma', '3800000.00', 160, 'AdminGudang', NULL, NULL, 'Siang', 'Zona A', NULL, NULL, '2026-06-16 07:21:34'),
(12, 'STF010', 'Joko Widodo', '3600000.00', 144, 'AdminGudang', NULL, NULL, 'Malam', 'Zona D', NULL, NULL, '2026-06-16 07:21:34'),
(13, 'STF011', 'Karina Maharani', '3200000.00', 160, 'KurirMotor', NULL, NULL, NULL, NULL, 'B 1234 XYZ', 'Jakarta Selatan', '2026-06-16 07:21:34'),
(14, 'STF012', 'Lukman Hakim', '3100000.00', 144, 'KurirMotor', NULL, NULL, NULL, NULL, 'B 5678 ABC', 'Jakarta Timur', '2026-06-16 07:21:34'),
(15, 'STF013', 'Mawar Sari', '3300000.00', 168, 'KurirMotor', NULL, NULL, NULL, NULL, 'B 9012 DEF', 'Jakarta Pusat', '2026-06-16 07:21:34'),
(16, 'STF014', 'Nanda Putri', '3150000.00', 152, 'KurirMotor', NULL, NULL, NULL, NULL, 'B 3456 GHI', 'Jakarta Barat', '2026-06-16 07:21:34'),
(17, 'STF015', 'Oki Setiawan', '3250000.00', 160, 'KurirMotor', NULL, NULL, NULL, NULL, 'B 7890 JKL', 'Jakarta Utara', '2026-06-16 07:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_pengiriman`
--

CREATE TABLE `transaksi_pengiriman` (
  `id_transaksi_pengiriman` int NOT NULL,
  `id_resi` varchar(50) DEFAULT NULL,
  `id_pelanggan` int DEFAULT NULL,
  `id_kargo` int DEFAULT NULL,
  `id_armada` int DEFAULT NULL,
  `id_staff` int DEFAULT NULL,
  `id_pembayaran` int DEFAULT NULL,
  `total_biaya_akhir` decimal(15,2) DEFAULT NULL,
  `diskon_diterapkan` decimal(10,2) DEFAULT NULL,
  `status_pengiriman` enum('Diproses','Dikirim','Selesai','Batal') DEFAULT 'Diproses'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `armada`
--
ALTER TABLE `armada`
  ADD PRIMARY KEY (`id_armada`),
  ADD UNIQUE KEY `id_armada_code` (`id_armada_code`);

--
-- Indexes for table `kargo`
--
ALTER TABLE `kargo`
  ADD PRIMARY KEY (`id_kargo`),
  ADD UNIQUE KEY `id_resi` (`id_resi`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `id_pelanggan_code` (`id_pelanggan_code`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id_staff`),
  ADD UNIQUE KEY `id_staff_code` (`id_staff_code`);

--
-- Indexes for table `transaksi_pengiriman`
--
ALTER TABLE `transaksi_pengiriman`
  ADD PRIMARY KEY (`id_transaksi_pengiriman`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_kargo` (`id_kargo`),
  ADD KEY `id_armada` (`id_armada`),
  ADD KEY `id_staff` (`id_staff`),
  ADD KEY `id_pembayaran` (`id_pembayaran`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `armada`
--
ALTER TABLE `armada`
  MODIFY `id_armada` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kargo`
--
ALTER TABLE `kargo`
  MODIFY `id_kargo` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id_staff` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `transaksi_pengiriman`
--
ALTER TABLE `transaksi_pengiriman`
  MODIFY `id_transaksi_pengiriman` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transaksi_pengiriman`
--
ALTER TABLE `transaksi_pengiriman`
  ADD CONSTRAINT `transaksi_pengiriman_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `transaksi_pengiriman_ibfk_2` FOREIGN KEY (`id_kargo`) REFERENCES `kargo` (`id_kargo`),
  ADD CONSTRAINT `transaksi_pengiriman_ibfk_3` FOREIGN KEY (`id_armada`) REFERENCES `armada` (`id_armada`),
  ADD CONSTRAINT `transaksi_pengiriman_ibfk_4` FOREIGN KEY (`id_staff`) REFERENCES `staff` (`id_staff`),
  ADD CONSTRAINT `transaksi_pengiriman_ibfk_5` FOREIGN KEY (`id_pembayaran`) REFERENCES `pembayaran` (`id_pembayaran`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;