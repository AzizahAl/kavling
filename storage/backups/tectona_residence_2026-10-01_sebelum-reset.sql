-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: tectona_residence
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `agens`
--

DROP TABLE IF EXISTS `agens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_agen` varchar(255) NOT NULL,
  `nama_agen` varchar(255) NOT NULL,
  `no_hp` varchar(255) DEFAULT NULL,
  `lead` int(10) unsigned NOT NULL DEFAULT 0,
  `prospek` int(10) unsigned NOT NULL DEFAULT 0,
  `closing` int(10) unsigned NOT NULL DEFAULT 0,
  `nilai_penjualan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `komisi_persen` decimal(5,2) NOT NULL DEFAULT 0.00,
  `komisi_terhitung` decimal(15,2) NOT NULL DEFAULT 0.00,
  `dibayar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sisa_komisi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agens_kode_agen_unique` (`kode_agen`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agens`
--

LOCK TABLES `agens` WRITE;
/*!40000 ALTER TABLE `agens` DISABLE KEYS */;
INSERT INTO `agens` VALUES (7,'AG-001','WERTY','345678',0,0,0,0.00,0.00,0.00,0.00,0.00,'2026-09-27 11:18:39','2026-09-27 11:18:39'),(8,'AG-002','DFGHJ','34567',0,0,0,0.00,0.00,0.00,0.00,0.00,'2026-09-27 11:18:47','2026-09-27 11:18:47');
/*!40000 ALTER TABLE `agens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kas_transaksis`
--

DROP TABLE IF EXISTS `kas_transaksis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kas_transaksis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `kode` varchar(255) NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `jenis` enum('masuk','keluar') NOT NULL,
  `uraian` varchar(255) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `sumber` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kas_transaksis_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_transaksis`
--

LOCK TABLES `kas_transaksis` WRITE;
/*!40000 ALTER TABLE `kas_transaksis` DISABLE KEYS */;
/*!40000 ALTER TABLE `kas_transaksis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kavlings`
--

DROP TABLE IF EXISTS `kavlings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kavlings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_kavling` varchar(255) NOT NULL,
  `blok` varchar(255) NOT NULL,
  `no` varchar(255) NOT NULL,
  `tipe` varchar(255) DEFAULT NULL,
  `skema_harga_id` bigint(20) unsigned DEFAULT NULL,
  `ukuran` varchar(255) DEFAULT NULL,
  `luas` decimal(10,2) DEFAULT NULL,
  `harga_per_m2` decimal(15,2) DEFAULT NULL,
  `harga_jual` decimal(15,2) DEFAULT NULL,
  `status` enum('tersedia','reservasi','booking','dp','terjual') NOT NULL DEFAULT 'tersedia',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kavlings_kode_kavling_unique` (`kode_kavling`),
  KEY `kavlings_skema_harga_id_foreign` (`skema_harga_id`),
  CONSTRAINT `kavlings_skema_harga_id_foreign` FOREIGN KEY (`skema_harga_id`) REFERENCES `skema_hargas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kavlings`
--

LOCK TABLES `kavlings` WRITE;
/*!40000 ALTER TABLE `kavlings` DISABLE KEYS */;
INSERT INTO `kavlings` VALUES (1,'TR-A01','A','A1','Prima',1,'7 x 14',99.00,500000.00,49500000.00,'terjual','2026-09-27 07:21:14','2026-09-28 20:06:41'),(2,'TR-A02','A','A2','Standard Hook',1,'7 x ±8.5 x ±8.3 x ±10',10.00,500000.00,5000000.00,'terjual','2026-09-27 07:43:11','2026-09-28 20:06:41'),(3,'TR-A24','A','A24','Standard',1,'7 x 10',70.00,60000.00,4200000.00,'terjual','2026-09-27 07:56:21','2026-09-28 20:06:41'),(5,'TR-A05','A','A5','Prima',2,'7 x 14',98.00,550000.00,53900000.00,'terjual','2026-09-28 20:08:33','2026-09-28 20:08:33');
/*!40000 ALTER TABLE `kavlings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `konsumens`
--

DROP TABLE IF EXISTS `konsumens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `konsumens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_konsumen` varchar(255) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `nik` varchar(16) NOT NULL,
  `no_hp` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `alamat` text NOT NULL,
  `kavling_id` bigint(20) unsigned NOT NULL,
  `agen_id` bigint(20) unsigned DEFAULT NULL,
  `status_transaksi` enum('reservasi','booking','dp','cash_lunas','angsuran') NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `nominal_reservasi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `nominal_booking` decimal(15,2) NOT NULL DEFAULT 0.00,
  `down_payment` decimal(15,2) NOT NULL DEFAULT 0.00,
  `skema_bayar` varchar(255) DEFAULT NULL,
  `jumlah_angsuran` int(10) unsigned DEFAULT NULL,
  `status_reservasi` enum('belum','proses','selesai') NOT NULL DEFAULT 'belum',
  `status_booking` enum('belum','proses','selesai') NOT NULL DEFAULT 'belum',
  `status_ppjb` enum('belum','proses','selesai') NOT NULL DEFAULT 'belum',
  `status_ajb` enum('belum','proses','selesai') NOT NULL DEFAULT 'belum',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `konsumens_id_konsumen_unique` (`id_konsumen`),
  KEY `konsumens_kavling_id_foreign` (`kavling_id`),
  KEY `konsumens_agen_id_foreign` (`agen_id`),
  CONSTRAINT `konsumens_agen_id_foreign` FOREIGN KEY (`agen_id`) REFERENCES `agens` (`id`) ON DELETE SET NULL,
  CONSTRAINT `konsumens_kavling_id_foreign` FOREIGN KEY (`kavling_id`) REFERENCES `kavlings` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `konsumens`
--

LOCK TABLES `konsumens` WRITE;
/*!40000 ALTER TABLE `konsumens` DISABLE KEYS */;
INSERT INTO `konsumens` VALUES (1,'CUS-2026-0001','Azizah Al-Ghani',NULL,'3205366111050001','082127440877','azzalghani21@gmail.com','Jl. Ibu sangki Gang Umjani',5,7,'reservasi','2026-09-29',250000.00,2000000.00,8085000.00,'cash_lunas',0,'selesai','proses','belum','belum',NULL,'2026-09-28 20:41:09','2026-09-28 20:41:09'),(2,'CUS-2026-0002','khalis',NULL,'2134256789187625','0897765516728','azzalghani21@gmail.com','Jl. Ibu sangki Gang Umjani',1,8,'booking','2026-09-29',2000000.00,2000000.00,7425000.00,'angsuran',18,'proses','proses','belum','belum',NULL,'2026-09-28 21:05:29','2026-09-28 21:05:29');
/*!40000 ALTER TABLE `konsumens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_08_170437_create_kavlings_table',1),(6,'2026_09_09_090257_create_skema_hargas_table',2),(7,'2026_09_09_084636_create_agens_table',3),(8,'2026_09_09_090814_create_konsumens_table',3),(9,'2026_09_09_091622_add_skema_bayar_to_konsumens_table--table=konsumens',3),(10,'2026_09_09_091653_create_riwayat_pembayarans_table',3),(11,'2026_09_14_165357_create_transaksi_penjualans_table',3),(12,'2026_09_14_174300_create_rabs_table',3),(13,'2026_09_14_175332_create_kas_transaksis_table',3),(14,'2026_09_27_145805_change_luas_harga_to_integer_in_kavlings_table',4),(15,'2026_09_29_025622_add_skema_harga_id_to_kavlings_table',4);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rabs`
--

DROP TABLE IF EXISTS `rabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rabs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kategori` varchar(255) NOT NULL,
  `uraian` varchar(255) NOT NULL,
  `anggaran` decimal(15,2) DEFAULT NULL,
  `realisasi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status_realisasi` enum('belum_direalisasikan','sudah_direalisasikan') NOT NULL DEFAULT 'belum_direalisasikan',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rabs`
--

LOCK TABLES `rabs` WRITE;
/*!40000 ALTER TABLE `rabs` DISABLE KEYS */;
/*!40000 ALTER TABLE `rabs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riwayat_pembayarans`
--

DROP TABLE IF EXISTS `riwayat_pembayarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `riwayat_pembayarans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `konsumen_id` bigint(20) unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `bukti_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `riwayat_pembayarans_konsumen_id_foreign` (`konsumen_id`),
  CONSTRAINT `riwayat_pembayarans_konsumen_id_foreign` FOREIGN KEY (`konsumen_id`) REFERENCES `konsumens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riwayat_pembayarans`
--

LOCK TABLES `riwayat_pembayarans` WRITE;
/*!40000 ALTER TABLE `riwayat_pembayarans` DISABLE KEYS */;
INSERT INTO `riwayat_pembayarans` VALUES (1,1,'2026-09-29','Pembayaran Reservasi (NUP)',250000.00,NULL,'2026-09-28 20:41:10','2026-09-28 20:41:10');
/*!40000 ALTER TABLE `riwayat_pembayarans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('Ia7HDdVaUYheHB5om9vh2TPdgld4tZPZhpQeCYfT',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiejZ0bmlxaUY0alV4WjhDaDlIWUhrVVVhbG9SQkpjOG42RTQ1NWVQQSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXRhLWtvbnN1bWVuIjtzOjU6InJvdXRlIjtzOjE0OiJrb25zdW1lbi5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1790794397);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skema_hargas`
--

DROP TABLE IF EXISTS `skema_hargas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skema_hargas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_tahap` varchar(255) NOT NULL,
  `unit_mulai` int(10) unsigned NOT NULL,
  `unit_sampai` int(10) unsigned NOT NULL,
  `harga_per_m2` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skema_hargas`
--

LOCK TABLES `skema_hargas` WRITE;
/*!40000 ALTER TABLE `skema_hargas` DISABLE KEYS */;
INSERT INTO `skema_hargas` VALUES (1,'Tahap 1',0,2,500000.00,'2026-09-27 08:09:34','2026-09-28 20:04:45'),(2,'Tahap 2',3,5,550000.00,'2026-09-27 08:09:50','2026-09-28 20:05:36'),(3,'Tahap 3',6,8,600000.00,'2026-09-28 20:05:21','2026-09-28 20:05:45'),(4,'Tahap 4',9,11,650000.00,'2026-09-28 20:05:59','2026-09-28 20:05:59'),(5,'Tahap 5',12,14,700000.00,'2026-09-28 20:06:08','2026-09-28 20:06:08');
/*!40000 ALTER TABLE `skema_hargas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi_penjualans`
--

DROP TABLE IF EXISTS `transaksi_penjualans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaksi_penjualans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('reservasi','booking','dp','lunas') NOT NULL DEFAULT 'reservasi',
  `konsumen_id` bigint(20) unsigned NOT NULL,
  `kavling_id` bigint(20) unsigned NOT NULL,
  `agen_id` bigint(20) unsigned DEFAULT NULL,
  `jenis_pembayaran` enum('cash','angsuran') NOT NULL DEFAULT 'cash',
  `nilai_jual` decimal(15,2) NOT NULL,
  `tenor` int(10) unsigned DEFAULT NULL,
  `nominal_dp` decimal(15,2) DEFAULT NULL,
  `total_bayar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sisa_pembayaran` decimal(15,2) NOT NULL DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaksi_penjualans_kode_transaksi_unique` (`kode_transaksi`),
  KEY `transaksi_penjualans_konsumen_id_foreign` (`konsumen_id`),
  KEY `transaksi_penjualans_kavling_id_foreign` (`kavling_id`),
  KEY `transaksi_penjualans_agen_id_foreign` (`agen_id`),
  CONSTRAINT `transaksi_penjualans_agen_id_foreign` FOREIGN KEY (`agen_id`) REFERENCES `agens` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaksi_penjualans_kavling_id_foreign` FOREIGN KEY (`kavling_id`) REFERENCES `kavlings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaksi_penjualans_konsumen_id_foreign` FOREIGN KEY (`konsumen_id`) REFERENCES `konsumens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_penjualans`
--

LOCK TABLES `transaksi_penjualans` WRITE;
/*!40000 ALTER TABLE `transaksi_penjualans` DISABLE KEYS */;
INSERT INTO `transaksi_penjualans` VALUES (1,'TRX-2026-0001','2026-09-30','reservasi',1,5,7,'cash',53900000.00,NULL,1000000.00,1250000.00,52650000.00,NULL,'2026-09-29 21:15:25','2026-09-29 21:15:25'),(2,'TRX-2026-0002','2026-09-30','booking',2,1,8,'angsuran',49500000.00,18,2500000.00,13925000.00,35575000.00,NULL,'2026-09-29 21:18:46','2026-09-29 21:18:46');
/*!40000 ALTER TABLE `transaksi_penjualans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-01  2:06:07
