-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 10, 2026 at 01:23 PM
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
  MODIFY `id_armada` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kargo`
--
ALTER TABLE `kargo`
  MODIFY `id_kargo` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id_staff` int NOT NULL AUTO_INCREMENT;

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
