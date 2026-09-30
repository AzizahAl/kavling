-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2026 at 03:59 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tectona_residence`
--

-- --------------------------------------------------------

--
-- Table structure for table `agens`
--

CREATE TABLE `agens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_agen` varchar(255) NOT NULL,
  `nama_agen` varchar(255) NOT NULL,
  `no_hp` varchar(255) DEFAULT NULL,
  `lead` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `prospek` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `closing` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `nilai_penjualan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `komisi_persen` decimal(5,2) NOT NULL DEFAULT 0.00,
  `komisi_terhitung` decimal(15,2) NOT NULL DEFAULT 0.00,
  `dibayar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sisa_komisi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agens`
--

INSERT INTO `agens` (`id`, `kode_agen`, `nama_agen`, `no_hp`, `lead`, `prospek`, `closing`, `nilai_penjualan`, `komisi_persen`, `komisi_terhitung`, `dibayar`, `sisa_komisi`, `created_at`, `updated_at`) VALUES
(7, 'AG-001', 'WERTY', '345678', 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-09-27 11:18:39', '2026-09-27 11:18:39'),
(8, 'AG-002', 'DFGHJ', '34567', 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-09-27 11:18:47', '2026-09-27 11:18:47');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kas_transaksis`
--

CREATE TABLE `kas_transaksis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `kode` varchar(255) NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `jenis` enum('masuk','keluar') NOT NULL,
  `uraian` varchar(255) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `sumber` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kavlings`
--

CREATE TABLE `kavlings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_kavling` varchar(255) NOT NULL,
  `blok` varchar(255) NOT NULL,
  `no` varchar(255) NOT NULL,
  `tipe` varchar(255) DEFAULT NULL,
  `skema_harga_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ukuran` varchar(255) DEFAULT NULL,
  `luas` decimal(10,2) DEFAULT NULL,
  `harga_per_m2` decimal(15,2) DEFAULT NULL,
  `harga_jual` decimal(15,2) DEFAULT NULL,
  `status` enum('tersedia','reservasi','booking','dp','terjual') NOT NULL DEFAULT 'tersedia',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kavlings`
--

INSERT INTO `kavlings` (`id`, `kode_kavling`, `blok`, `no`, `tipe`, `skema_harga_id`, `ukuran`, `luas`, `harga_per_m2`, `harga_jual`, `status`, `created_at`, `updated_at`) VALUES
(1, 'TR-A01', 'A', 'A1', 'Prima', 1, '7 x 14', 99.00, 500000.00, 49500000.00, 'terjual', '2026-09-27 07:21:14', '2026-09-28 20:06:41'),
(2, 'TR-A02', 'A', 'A2', 'Standard Hook', 1, '7 x ±8.5 x ±8.3 x ±10', 10.00, 500000.00, 5000000.00, 'terjual', '2026-09-27 07:43:11', '2026-09-28 20:06:41'),
(3, 'TR-A24', 'A', 'A24', 'Standard', 1, '7 x 10', 70.00, 60000.00, 4200000.00, 'terjual', '2026-09-27 07:56:21', '2026-09-28 20:06:41'),
(5, 'TR-A05', 'A', 'A5', 'Prima', 2, '7 x 14', 98.00, 550000.00, 53900000.00, 'terjual', '2026-09-28 20:08:33', '2026-09-28 20:08:33');

-- --------------------------------------------------------

--
-- Table structure for table `konsumens`
--

CREATE TABLE `konsumens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_konsumen` varchar(255) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `nik` varchar(16) NOT NULL,
  `no_hp` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `alamat` text NOT NULL,
  `kavling_id` bigint(20) UNSIGNED NOT NULL,
  `agen_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_transaksi` enum('reservasi','booking','dp','cash_lunas','angsuran') NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `nominal_reservasi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `nominal_booking` decimal(15,2) NOT NULL DEFAULT 0.00,
  `down_payment` decimal(15,2) NOT NULL DEFAULT 0.00,
  `skema_bayar` varchar(255) DEFAULT NULL,
  `jumlah_angsuran` int(10) UNSIGNED DEFAULT NULL,
  `status_reservasi` enum('belum','proses','selesai') NOT NULL DEFAULT 'belum',
  `status_booking` enum('belum','proses','selesai') NOT NULL DEFAULT 'belum',
  `status_ppjb` enum('belum','proses','selesai') NOT NULL DEFAULT 'belum',
  `status_ajb` enum('belum','proses','selesai') NOT NULL DEFAULT 'belum',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `konsumens`
--

INSERT INTO `konsumens` (`id`, `id_konsumen`, `nama_lengkap`, `label`, `nik`, `no_hp`, `email`, `alamat`, `kavling_id`, `agen_id`, `status_transaksi`, `tanggal_transaksi`, `nominal_reservasi`, `nominal_booking`, `down_payment`, `skema_bayar`, `jumlah_angsuran`, `status_reservasi`, `status_booking`, `status_ppjb`, `status_ajb`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 'CUS-2026-0001', 'Azizah Al-Ghani', NULL, '3205366111050001', '082127440877', 'azzalghani21@gmail.com', 'Jl. Ibu sangki Gang Umjani', 5, 7, 'reservasi', '2026-09-29', 250000.00, 2000000.00, 8085000.00, 'cash_lunas', 0, 'selesai', 'proses', 'belum', 'belum', NULL, '2026-09-28 20:41:09', '2026-09-28 20:41:09'),
(2, 'CUS-2026-0002', 'khalis', NULL, '2134256789187625', '0897765516728', 'azzalghani21@gmail.com', 'Jl. Ibu sangki Gang Umjani', 1, 8, 'booking', '2026-09-29', 2000000.00, 2000000.00, 7425000.00, 'angsuran', 18, 'proses', 'proses', 'belum', 'belum', NULL, '2026-09-28 21:05:29', '2026-09-28 21:05:29');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_08_170437_create_kavlings_table', 1),
(6, '2026_09_09_090257_create_skema_hargas_table', 2),
(7, '2026_09_09_084636_create_agens_table', 3),
(8, '2026_09_09_090814_create_konsumens_table', 3),
(9, '2026_09_09_091622_add_skema_bayar_to_konsumens_table--table=konsumens', 3),
(10, '2026_09_09_091653_create_riwayat_pembayarans_table', 3),
(11, '2026_09_14_165357_create_transaksi_penjualans_table', 3),
(12, '2026_09_14_174300_create_rabs_table', 3),
(13, '2026_09_14_175332_create_kas_transaksis_table', 3),
(14, '2026_09_27_145805_change_luas_harga_to_integer_in_kavlings_table', 4),
(15, '2026_09_29_025622_add_skema_harga_id_to_kavlings_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rabs`
--

CREATE TABLE `rabs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `uraian` varchar(255) NOT NULL,
  `anggaran` decimal(15,2) DEFAULT NULL,
  `realisasi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status_realisasi` enum('belum_direalisasikan','sudah_direalisasikan') NOT NULL DEFAULT 'belum_direalisasikan',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_pembayarans`
--

CREATE TABLE `riwayat_pembayarans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `konsumen_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `bukti_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `riwayat_pembayarans`
--

INSERT INTO `riwayat_pembayarans` (`id`, `konsumen_id`, `tanggal`, `keterangan`, `nominal`, `bukti_path`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-09-29', 'Pembayaran Reservasi (NUP)', 250000.00, NULL, '2026-09-28 20:41:10', '2026-09-28 20:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('OFnVd8c9mZePI9dtoSMHbqVgOUSZgGS1L3GLwsWE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYktDZG5LNlVFeWF6aWJ2eTRvQUsxbnVCOXVYdEdjaDBjUjVnS0RVbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXRhLWFnZW4iO3M6NToicm91dGUiO3M6MTA6ImFnZW4uaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1790741947),
('QvN8HX3wiqCwZJmxxv7VNsrTLFIN92SBScoIkWcn', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiY1hRT0k4TFRhQTdVd1lnUDM0RUpJaWZxRGZiWDlGMzQ3ZkpaM3lBdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXRhLWFnZW4iO3M6NToicm91dGUiO3M6MTA6ImFnZW4uaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1790756690),
('RIiD0MbaB3pFTnWqtf3QwfozMzKswtcSXoyKY8ez', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVTVjTkNmMzhRQnVTdzR5aTRzRHhlVnMwSEYxbDNtRFd6S0hIcVI4bSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1790756573),
('YsMIKNHkMRTT8ZG2CpcZhVcYDAGMsNOJjElVDFMx', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUEJXcWM3NUN6OEdnUUs1aWduYTlJZ1VscXJmd0JubUN1NUJGNGlQdyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9rYXMtcHJveWVrIjtzOjU6InJvdXRlIjtzOjE2OiJrYXMtcHJveWVrLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1790700841);

-- --------------------------------------------------------

--
-- Table structure for table `skema_hargas`
--

CREATE TABLE `skema_hargas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_tahap` varchar(255) NOT NULL,
  `unit_mulai` int(10) UNSIGNED NOT NULL,
  `unit_sampai` int(10) UNSIGNED NOT NULL,
  `harga_per_m2` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skema_hargas`
--

INSERT INTO `skema_hargas` (`id`, `nama_tahap`, `unit_mulai`, `unit_sampai`, `harga_per_m2`, `created_at`, `updated_at`) VALUES
(1, 'Tahap 1', 0, 2, 500000.00, '2026-09-27 08:09:34', '2026-09-28 20:04:45'),
(2, 'Tahap 2', 3, 5, 550000.00, '2026-09-27 08:09:50', '2026-09-28 20:05:36'),
(3, 'Tahap 3', 6, 8, 600000.00, '2026-09-28 20:05:21', '2026-09-28 20:05:45'),
(4, 'Tahap 4', 9, 11, 650000.00, '2026-09-28 20:05:59', '2026-09-28 20:05:59'),
(5, 'Tahap 5', 12, 14, 700000.00, '2026-09-28 20:06:08', '2026-09-28 20:06:08');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_penjualans`
--

CREATE TABLE `transaksi_penjualans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_transaksi` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('reservasi','booking','dp','lunas') NOT NULL DEFAULT 'reservasi',
  `konsumen_id` bigint(20) UNSIGNED NOT NULL,
  `kavling_id` bigint(20) UNSIGNED NOT NULL,
  `agen_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis_pembayaran` enum('cash','angsuran') NOT NULL DEFAULT 'cash',
  `nilai_jual` decimal(15,2) NOT NULL,
  `tenor` int(10) UNSIGNED DEFAULT NULL,
  `nominal_dp` decimal(15,2) DEFAULT NULL,
  `total_bayar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sisa_pembayaran` decimal(15,2) NOT NULL DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi_penjualans`
--

INSERT INTO `transaksi_penjualans` (`id`, `kode_transaksi`, `tanggal`, `status`, `konsumen_id`, `kavling_id`, `agen_id`, `jenis_pembayaran`, `nilai_jual`, `tenor`, `nominal_dp`, `total_bayar`, `sisa_pembayaran`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 'TRX-2026-0001', '2026-09-30', 'reservasi', 1, 5, 7, 'cash', 53900000.00, NULL, 1000000.00, 1250000.00, 52650000.00, NULL, '2026-09-29 21:15:25', '2026-09-29 21:15:25'),
(2, 'TRX-2026-0002', '2026-09-30', 'booking', 2, 1, 8, 'angsuran', 49500000.00, 18, 2500000.00, 13925000.00, 35575000.00, NULL, '2026-09-29 21:18:46', '2026-09-29 21:18:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agens`
--
ALTER TABLE `agens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `agens_kode_agen_unique` (`kode_agen`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kas_transaksis`
--
ALTER TABLE `kas_transaksis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kas_transaksis_kode_unique` (`kode`);

--
-- Indexes for table `kavlings`
--
ALTER TABLE `kavlings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kavlings_kode_kavling_unique` (`kode_kavling`),
  ADD KEY `kavlings_skema_harga_id_foreign` (`skema_harga_id`);

--
-- Indexes for table `konsumens`
--
ALTER TABLE `konsumens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `konsumens_id_konsumen_unique` (`id_konsumen`),
  ADD KEY `konsumens_kavling_id_foreign` (`kavling_id`),
  ADD KEY `konsumens_agen_id_foreign` (`agen_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `rabs`
--
ALTER TABLE `rabs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `riwayat_pembayarans`
--
ALTER TABLE `riwayat_pembayarans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_pembayarans_konsumen_id_foreign` (`konsumen_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `skema_hargas`
--
ALTER TABLE `skema_hargas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi_penjualans`
--
ALTER TABLE `transaksi_penjualans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaksi_penjualans_kode_transaksi_unique` (`kode_transaksi`),
  ADD KEY `transaksi_penjualans_konsumen_id_foreign` (`konsumen_id`),
  ADD KEY `transaksi_penjualans_kavling_id_foreign` (`kavling_id`),
  ADD KEY `transaksi_penjualans_agen_id_foreign` (`agen_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agens`
--
ALTER TABLE `agens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kas_transaksis`
--
ALTER TABLE `kas_transaksis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kavlings`
--
ALTER TABLE `kavlings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `konsumens`
--
ALTER TABLE `konsumens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `rabs`
--
ALTER TABLE `rabs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `riwayat_pembayarans`
--
ALTER TABLE `riwayat_pembayarans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `skema_hargas`
--
ALTER TABLE `skema_hargas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transaksi_penjualans`
--
ALTER TABLE `transaksi_penjualans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kavlings`
--
ALTER TABLE `kavlings`
  ADD CONSTRAINT `kavlings_skema_harga_id_foreign` FOREIGN KEY (`skema_harga_id`) REFERENCES `skema_hargas` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `konsumens`
--
ALTER TABLE `konsumens`
  ADD CONSTRAINT `konsumens_agen_id_foreign` FOREIGN KEY (`agen_id`) REFERENCES `agens` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `konsumens_kavling_id_foreign` FOREIGN KEY (`kavling_id`) REFERENCES `kavlings` (`id`);

--
-- Constraints for table `riwayat_pembayarans`
--
ALTER TABLE `riwayat_pembayarans`
  ADD CONSTRAINT `riwayat_pembayarans_konsumen_id_foreign` FOREIGN KEY (`konsumen_id`) REFERENCES `konsumens` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi_penjualans`
--
ALTER TABLE `transaksi_penjualans`
  ADD CONSTRAINT `transaksi_penjualans_agen_id_foreign` FOREIGN KEY (`agen_id`) REFERENCES `agens` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transaksi_penjualans_kavling_id_foreign` FOREIGN KEY (`kavling_id`) REFERENCES `kavlings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_penjualans_konsumen_id_foreign` FOREIGN KEY (`konsumen_id`) REFERENCES `konsumens` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
